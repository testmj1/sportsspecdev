<?php

/**
 * Class Advanced_Ads_Tracking
 */
class Advanced_Ads_Tracking {
	/**
	 * Name of the impressions table
	 */
	protected $impressions_table = '';

	/**
	 * Name of the clicks table
	 */
	protected $clicks_table = '';

	/**
	 *
	 * @var Advanced_Ads_Tracking_Util
	 */
	protected $util;

	/**
	 * Default click link base
	 */
	const CLICKLINKBASE = 'linkout';

	/**
	 *
	 * @var Advanced_Ads_Tracking_Plugin
	 * @since 1.2.0
	 */
	protected $plugin;

	/**
	 * Whether this is an AJAX request.
	 *
	 * @var bool
	 */
	protected $is_ajax;

	/**
	 * Whether this is a wp-admin request.
	 *
	 * @var bool
	 */
	protected $is_admin;

	/**
	 * Get prefix used for frontend elements.
	 *
	 * @var string
	 */
	protected $frontend_prefix;

	/**
	 * Correspondence between ad ID-s and target link if any, for Google Analytics usage
	 *
	 * @var arr
	 */
	private $ad_targets = [];

	/**
	 * Ad ids that should be tracked using JavaScript
	 *
	 * @var arr
	 */
	protected $ad_ids = [];

	/**
	 * Ads for which page query string should be transmitted.
	 *
	 * @var array
	 */
	protected $transmit_pageqs = [];

	/**
	 * Holds placements for ads that have been loaded through AJAX.
	 *
	 * @var array
	 */
	private $cache_busting_placements = [];

	/**
	 * Global WordPress database class instance.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Initialize the plugin and styles.
	 *
	 * @param bool $is_admin Whether this is a wp-admin request.
	 * @param bool $is_ajax  Whether this is an AJAX request.
	 *
	 * @since     1.0.0
	 */
	public function __construct( $is_admin, $is_ajax ) {
		$this->util   = Advanced_Ads_Tracking_Util::get_instance();
		$this->plugin = Advanced_Ads_Tracking_Plugin::get_instance();

		// load table names
		$this->impressions_table = $this->util->get_impression_table();
		$this->clicks_table      = $this->util->get_click_table();

		$this->is_ajax         = $is_ajax;
		$this->is_admin        = $is_admin;
		$this->frontend_prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
		$this->wpdb            = $GLOBALS['wpdb'];

		// anyone (even admin previews)
		// wrap ad in tracking link
		add_filter( 'advanced-ads-output-inside-wrapper', [ $this, 'add_tracking_link' ], 10, 2 );
		add_filter( 'advanced-ads-rest-ad-content', [ $this, 'add_tracking_link' ], 10, 2 );

		add_action( 'advanced-ads-rest-ad-request', [ $this, 'track_rest_impression' ] );

		add_filter( 'advanced-ads-can-display', [ $this, 'can_display' ], 10, 2 );

		add_filter( 'advanced-ads-privacy-output-attributes', [ $this, 'privacy_output_attributes' ], 10, 2 );

		// Load tracking method for AJAX requests.
		if ( $this->is_ajax ) {
			$this->load_tracking_method();
		}

		// Frontend request, not AJAX request.
		if ( ! $this->is_admin ) {
			// register two redirect methods, because the first might fail if other plugins also use it
			add_action( 'plugins_loaded', [ $this, 'url_redirect' ], 1 );
			add_action( 'wp_loaded', [ $this, 'url_redirect' ], 1 );
			// load functions based on tracking method settings (after the 'parse_query' hook)
			add_action( 'wp', [ $this, 'load_tracking_method' ], 10 );
			add_filter( 'advanced-ads-pro-passive-cb-for-ad', [ $this, 'add_passive_cb_for_ad' ], 10, 2 );
			// add click tracking/link cloaking to background placement.
			add_filter( 'advanced-ads-pro-background-url', [ $this, 'filter_background_placement_url' ], 10, 2 );
			add_filter( 'advanced-ads-pro-background-click-matches-script', [ $this, 'add_background_placement_script' ], 10, 2 );
		}

		$this->load_plugin_textdomain();

		if ( ! defined( 'ADVANCED_ADS_TRACKING_NO_PUBLIC_STATS' ) ) {
			add_action( 'wp_loaded', [ $this, 'is_public_stat' ] );
		}

		// scheduled email hook
		add_action( 'advanced_ads_daily_email', [ $this, 'daily_email' ] );

		add_shortcode( AAT_IMP_SHORTCODE, [ $this, 'impression_shortcode' ] );
		add_shortcode( 'the_ad_clicks', [ $this, 'click_shortcode' ] );

		add_action( 'advanced_ads_daily_report', [ $this, 'individual_email_report' ] );
	}

	/**
	 * Add a wrapper for the ad
	 *
	 * @param array           $wrapper The wrapper object.
	 * @param Advanced_Ads_Ad $ad      The ad object.
	 *
	 * @return array
	 */
	public function add_wrapper( $wrapper, $ad ) {

		// If this ad should not be tracked, don't show wrapper.
		if ( ! $this->plugin->check_ad_tracking_enabled( $ad, 'min_one' ) ) {
			return $wrapper;
		}

		$frontend_prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
		// Add the ad id to the wrapper.
		$wrapper[ 'data-' . $frontend_prefix . 'trackid' ]  = $ad->id;
		$wrapper[ 'data-' . $frontend_prefix . 'trackbid' ] = get_current_blog_id();
		$wrapper[ 'data-' . $frontend_prefix . 'redirect' ] = (bool) $ad->options( 'tracking.cloaking' ) && ! empty( $ad->options( 'url' ) );

		// Add class to wrapper for click tracking, if ad is image or dummy only if it has a URL.
		if (
			$this->plugin->check_ad_tracking_enabled( $ad, 'click' )
			&& ! ( empty( $ad->url ) && ( $ad->type === 'dummy' || $ad->type === 'image' ) )
		) {
			$wrapper['class'][] = $this->frontend_prefix . 'target';
		}

		$options = $ad->options();
		if (
			( ! isset( $options['placement_type'] ) || false === strpos( $options['placement_type'], 'sticky' ) || ! isset( $options['sticky']['trigger'] ) || 'timeout' !== $options['sticky']['trigger'] ) &&
			( ! isset( $options['layer_placement'] ) || empty( $options['layer_placement']['trigger'] ) )
		) {

			// If not sticky, or sticky but no timeout, AND not layer ad or no trigger, abort
			return $wrapper;
		}

		// add data attribute if this ad's impressions should be tracked.
		if ( $this->plugin->check_ad_tracking_enabled( $ad ) ) {
			$wrapper[ 'data-' . $frontend_prefix . 'impression' ] = true;
		}

		// Add delayed marker.
		$wrapper['data-delayed'] = 1;

		return $wrapper;
	}

	/**
	 * Send email report for individual ads
	 */
	public function send_individual_email() {
		$this->individual_email_report();
		die;
	}

	/**
	 *  Impression shortcode
	 */
	public function impression_shortcode( $atts ) {
		$atts = shortcode_atts( [
			'id' => 0,
		], $atts, AAT_IMP_SHORTCODE );
		$ID   = absint( $atts['id'] );
		if ( ! $ID ) {
			return;
		}
		$ad = get_post( $ID );
		if ( $ad->post_type !== Advanced_Ads::POST_TYPE_SLUG ) {
			return;
		}
		$title = $ad->post_title;
		$sum   = $this->util->get_sums_for_ad( $ID )['impressions'];
		ob_start();
		echo $sum;
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Display clicks of an ad in the frontend by using the [the_ad_clicks] shortcode
	 *
	 * @param array $atts shortcode attributes.
	 *
	 * @return string
	 */
	public function click_shortcode( array $atts ): string {
		$atts  = shortcode_atts( [
			'id' => 0,
		], $atts, 'the_ad_clicks' );
		$ad_id = absint( $atts['id'] );
		if ( ! $ad_id ) {
			return '';
		}
		$ad = get_post( $ad_id );
		if ( ! $ad || $ad->post_type !== Advanced_Ads::POST_TYPE_SLUG ) {
			return '';
		}
		ob_start();
		echo (int) $this->util->get_sums_for_ad( $ad_id, true )['clicks'];
		return ob_get_clean();
	}

	/**
	 *  Draw the public stat page
	 *
	 * @since N/A
	 */
	protected function display_public_stats( $ad_id ) {
		require_once AAT_BASE_PATH . 'public/views/ad-stats.php';
		die;
	}

	/**
	 * Get ad ID from the public hash
	 *
	 * @param string $hash The public id for the ad.
	 *
	 * @return int|false
	 */
	protected function ad_hash_to_id( $hash ) {
		$all_ads = Advanced_Ads::get_ads( [
			'post_status' => [ 'publish', 'future', 'draft', 'pending', Advanced_Ads_Tracking_Util::get_expired_post_status() ],
		] );
		foreach ( $all_ads as $_ad ) {
			$ad      = new Advanced_Ads_Ad( $_ad->ID );
			$options = $ad->options();
			if ( ! isset( $options['tracking'] ) ) {
				continue;
			}
			if ( ! isset( $options['tracking']['public-id'] ) ) {
				continue;
			}
			if ( $options['tracking']['public-id'] === $hash ) {
				return $_ad->ID;
			}
		}

		return false;
	}

	/**
	 *  Check if it's a public stat url
	 *
	 * @since N/A
	 */
	public function is_public_stat() {
		if ( ! isset( $_SERVER['HTTP_HOST'] ) || is_admin() ) {
			return;
		}

		$protocol = 'http';
		if ( is_ssl() ) {
			$protocol .= 's';
		}
		$protocol .= '://';

		$full_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// site url including eventual blog slug in sub-directory multisite
		$site_url = site_url();

		$sub1       = substr( $full_url, strlen( $site_url ) );
		$stats_slug = Advanced_Ads_Tracking_Public_Stats::get_slug();

		$permalink = get_option( 'permalink_structure' );

		$ad_hash = false;
		if ( empty( $permalink ) ) {
			if ( isset( $_GET[ $stats_slug ] ) ) {
				$ad_hash = $_GET[ $stats_slug ];
			}
		} else {
			if ( 0 === strpos( $sub1, '/' . $stats_slug . '/' ) ) {
				$expl    = explode( '/', $sub1 );
				$ad_hash = $expl[2];
			}
		}
		if ( $ad_hash ) {
			$ad_id = $this->ad_hash_to_id( $ad_hash );
			if ( false !== $ad_id ) {
				$this->display_public_stats( $ad_id );
			}
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.2.6.2
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'advanced-ads-tracking', false, AAT_BASE_DIR . '/languages' );
	}

	/**
	 * Redirect the visitor if he uses click tracking
	 *
	 * @since 1.1.0
	 */
	public function url_redirect() {
		$start_time = microtime( true );
		// check if the current url matches the click base
		$request_uri = trim( urldecode( $_SERVER['REQUEST_URI'] ), '/' );

		// remove subdirectory if exists
		if ( isset( $_SERVER['HTTP_HOST'] ) && $sub_pos = strpos( home_url(), $_SERVER['HTTP_HOST'] ) ) {
			// get subdirectory
			$subdirectory = trim( substr( home_url(), $sub_pos + mb_strlen( $_SERVER['HTTP_HOST'] ) ), '/' );
			// replace subdirectory
			if ( $subdirectory ) {
				$request_uri = str_replace( $subdirectory . '/', '', $request_uri );
			}
		}

		$options  = $this->plugin->options();
		$linkbase = isset( $options['linkbase'] ) ? $options['linkbase'] : self::CLICKLINKBASE;

		$permalink = get_option( 'permalink_structure' );

		// abort if this is obviously not a tracking link
		if ( $permalink ) {
			if ( strpos( $request_uri, $linkbase ) !== 0 ) {
				return;
			}
		} else {
			if ( ! isset( $_GET[ $linkbase ] ) ) {
				return;
			}
		}

		$ad_id = false;

		// check if the current url has a number in it
		if ( $permalink ) {
			$matches = [];
			preg_match( '@/(\d+)\??@', $request_uri, $matches );

			if ( isset( $matches[1] ) ) {
				$ad_id = (int) trim( $matches[1], '/' );
			}
		} else {
			$ad_id = absint( $_GET[ $linkbase ] );
		}

		if ( empty( $ad_id ) ) {
			return;
		}
		// redirect, if ad id was found
		// load the ad
		$ad = new Advanced_Ads_Ad( $ad_id );
		if ( ! isset( $ad->id ) ) {
			return;
		}

		// check if a url is given
		$ad_options = $ad->options();
		$url        = '';
		// get url
		if ( isset( $ad_options['tracking']['link'] ) && $ad_options['tracking']['link'] !== '' ) {
			$url = trim( $ad_options['tracking']['link'] );
		} elseif ( isset( $ad_options['url'] ) && $ad_options['url'] !== '' ) {
			$url = trim( $ad_options['url'] );
		} elseif ( ( strpos( $request_uri, '?advads_amp' ) !== false ) && $ad->type === 'plain' ) {
			// Extract url from content if plain ad on amp.
			$matches = $this->get_url_from_string( $ad->content );
			if ( ! empty( $matches[0] ) ) {
				$url = $matches[0];
			}
		}

		if ( empty( $url ) ) {
			return;
		}
		// Need a referrer because the click base url does not contain any information on the post where the ad was displayed and clicked
		$referrer     = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : false;
		$placeholders = [ '[POST_ID]', '[POST_SLUG]', '[CAT_SLUG]' ];
		$placeholders = array_merge($placeholders, array_map('urlencode', $placeholders));

		if ( $referrer && is_string( $referrer ) ) {

			/**
			 *  If called within the 'plugins_loaded' action, prevent redirecting
			 *  url_to_postid need to be called after the 'init' hook. Also stop tracking
			 *
			 *  [https://codex.wordpress.org/Function_Reference/url_to_postid]
			 */
			if ( ! did_action( 'init' ) ) {
				return;
			}

			// hotfix for WPML – remove url_to_postid filter to get an unchanged url
			global $sitepress;
			remove_filter( 'url_to_postid', [ $sitepress, 'url_to_postid' ] );

			$post_id = url_to_postid( $referrer );

			// reassign WPML filter
			add_filter( 'url_to_postid', [ $sitepress, 'url_to_postid' ] );

			$post = get_post( $post_id );

			parse_str( $_SERVER['QUERY_STRING'], $tracking_query_args );

			if ( $post ) {
				// The post ID was found by its URL.
				$cats = get_the_category( $post->ID );

				$cats_slugs = [];
				foreach ( $cats as $cat ) {
					$cats_slugs[] = $cat->slug;
				}

				// $placeholders exist as escaped and unescaped elements.
				$replacements = [ $post->ID, $post->post_name, implode( ',', $cats_slugs ) ];
				$replacements = array_merge( $replacements, $replacements );
				$url          = str_replace( $placeholders, $replacements, $url );
			} else {
				/***
				 *  Post ID not found by its url ( eg: landing page )
				 */
				$expl_url = explode( '?', $url );
				if ( 1 < count( $expl_url ) ) {
					// if query string is present ( and placeholder must be used in url query string )
					$baseurl = $expl_url[0];
					parse_str( $expl_url[1], $parsed );

					// remove placeholders that can’t be used on non-single posts
					$query_arr = [];
					foreach ( $parsed as $key => $value ) {
						if ( ! in_array( $value, $placeholders, true ) ) {
							// if not related to the placeholder systems, add it to the final url
							$query_arr[ $key ] = $value;
						}
					}
					$url = add_query_arg( $query_arr, $baseurl );
				}
			}

			/**
			 * Pass query arguments from tracking link to the target url.
			 */
			if ( ! empty( $tracking_query_args ) ) {
				// Do not include the tracking link base.
				if ( isset( $tracking_query_args[ $linkbase ] ) ) {
					unset( $tracking_query_args[ $linkbase ] );
				}
				$url = add_query_arg( $tracking_query_args, $url );
			}

			/**
			 * Pass query string from referer (if any);
			 */
			$can_transmit_qs = apply_filters( 'advanced-ads-tracking-query-string', false, $ad_id );
			if ( $can_transmit_qs ) {
				$parsed_query = wp_parse_url( $referrer, PHP_URL_QUERY );
				if ( ! empty( $parsed_query ) ) {
					parse_str( $parsed_query, $referer_query );
					if ( ! empty( $referer_query ) ) {
						$url = add_query_arg( $referer_query, $url );
					}
				}
			}
		} else {
			// remove attributes from URL
			$url = str_replace( $placeholders, '', $url );
		}

		// replace [AD_ID] with the ad’s ID, if given
		$url = str_replace( ['[AD_ID]', '%5BAD_ID%5D'], $ad_id, $url );

		if ( $this->plugin->get_tracking_method() !== 'ga' && $this->plugin->check_ad_tracking_enabled( $ad, 'click' ) ) {
			Advanced_Ads_Tracking_Util::get_instance()->track_click( $ad->id, $start_time );
		}

		/**
		 * Last chance for other scripts to change the redirect URL
		 * originally introduced to allow "fixing" issues when a wrong URL was created
		 */
		$url = apply_filters( 'advanced-ads-tracking-redirect-url', $url );

		if ( isset( $options['nofollow'] ) && $options['nofollow'] ) {
			header( 'X-Robots-Tag: noindex, nofollow' );
		} else {
			header( 'X-Robots-Tag: noindex' );
		}

		// redirect to target URL.
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'HTTP/1.1 307  Temporary Redirect' );
		header( 'Location: ' . esc_url_raw( $url ) );

		die();
	}

	/**
	 * Load the scripts and hooks according to the tracking method
	 *
	 * @since 1.0.0
	 */
	public function load_tracking_method() {

		// don’t track if user is logged in and constant to not track actions from logged-in users is set
		if ( $this->plugin->ignore_logged_in_user() ) {
			return;
		}

		$method = $this->plugin->get_tracking_method();

		if ( function_exists( 'advads_is_amp' ) && advads_is_amp() ) {
			if ( $method === 'onrequest' ) {
				add_action( 'advanced-ads-output', [ $this, 'track_on_output' ], 10, 3 );
			}

			return;
		}

		if ( (bool) apply_filters( 'advanced-ads-tracking-load-header-scripts', true ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
			// collect ad id, so that JavaScript can access it
			add_action( 'advanced-ads-output', [ $this, 'collect_ad_id' ], 10, 3 );
			add_filter( 'advanced-ads-output-wrapper-options', [ $this, 'add_wrapper' ], 20, 2 );
			add_action( 'wp_footer', [ $this, 'output_ad_ids' ], PHP_INT_MAX - 1 );
		}

		// If this is an ajax call and tracking is frontend, track the ads on shutdown.
		if ( $method === 'frontend' && $this->is_ajax ) {
			add_filter( 'advanced-ads-cache-busting-item', [ $this, 'collect_cache_busting_placements' ], 10, 2 );
			add_action( 'shutdown', [ $this, 'track_on_shutdown' ] );
		}

		// database tracking method selected.
		if ( $method === 'onrequest' ) {
			add_action( 'advanced-ads-output', [ $this, 'track_on_output' ], 10, 3 );
		}

		// Parallel analytics tracking && multi-site
		if ( is_multisite() || $method === 'ga' || $this->plugin->is_forced_analytics() ) {
			add_action( 'wp_head', [ $this, 'ga_wp_head' ] );
			add_action( 'wp_footer', [ $this, 'ga_wp_footer' ], PHP_INT_MAX - 1 );
		}
	}

	/**
	 *  Print Google Analytics related javascript in <head />
	 */
	public function ga_wp_head() {
		?>
		<script type="text/javascript">
			if ( typeof advadsGATracking === 'undefined' ) {
				window.advadsGATracking = {
					delayedAds: {},
					deferedAds: {}
				};
			}
		</script>
		<?php
	}

	/**
	 *  Print Google Analytics related javascript within the 'wp_footer' action
	 */
	public function ga_wp_footer() {
		if ( ! empty( $this->ad_targets ) ) {
			if ( is_singular() ) {
				$post       = get_post();
				$context    = [
					'postID'   => $post->ID,
					'postSlug' => $post->post_name,
				];
				$categories = get_the_category( $post->ID );
				$cats_slugs = [];
				foreach ( $categories as $cat ) {
					$cats_slugs[] = $cat->slug;
				}
				$cats            = implode( ',', $cats_slugs );
				$context['cats'] = $cats;
			}
			?>
			<script type="text/javascript">
				if ( typeof window.advadsGATracking === 'undefined' ) {
					window.advadsGATracking = {};
				}
				<?php if ( is_singular() ) : ?>
				advadsGATracking.postContext = <?php echo json_encode( $context ); ?>;
				<?php endif; ?>
			</script>
			<?php
		}
	}

	/**
	 * Get placements for cache busting items and collect them to track later on.
	 *
	 * @param array $result Cache busting item results array.
	 * @param array $args   Args for cache busting item.
	 *
	 * @return array The unmodified cache busting item.
	 */
	public function collect_cache_busting_placements( $result, $args ) {
		if ( $result['method'] !== 'placement' || $this->is_delayed_placement( $args['args'] ) ) {
			return $result;
		}
		$this->cache_busting_placements[] = $result['id'];

		return $result;
	}

	/**
	 * Track impression on PHP shutdown.
	 * Used for ads loaded via AJAX, i.e. AJAX-cache busting, Ad Server.
	 */
	public function track_on_shutdown() {
		$start_time           = microtime( true );
		$main_plugin_instance = Advanced_Ads::get_instance();

		// If we don't have any ads or the privacy state is unknown, return early.
		if ( empty( $main_plugin_instance->current_ads ) || Advanced_Ads_Privacy::get_instance()->get_state() === 'unknown' ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce has already been verified, we just need the action name.
		$action = sanitize_text_field( $_REQUEST['action'] );

		// sanitize all ads.
		$current_ads = array_map( [ $this, 'sanitize_ad' ], $main_plugin_instance->current_ads );
		// remove everything that's not an ad, has empty content, shouldn't be tracked.
		$current_ads = array_filter( $current_ads, function( $ad ) {
			return $ad['type'] === 'ad' && ! empty( trim( $ad['output'] ) ) && $ad['tracking_enabled'];
		} );

		// If we don't have any ads, return early.
		if ( empty( $current_ads ) ) {
			return;
		}

		// if we don't need to check for placements, track the found ads and return. We only need to check placements for ad selects form main plugin or Pro Ad Server module.
		if ( ! in_array( $action, [ 'advads_ad_select', 'aa-server-select' ], true ) ) {
			Advanced_Ads_Tracking_Util::get_instance()->track_impressions( $current_ads, $start_time );

			return;
		}

		$placements = $main_plugin_instance->get_model()->get_ad_placements_array();
		if ( $action === 'advads_ad_select' ) {
			// If this is an AJAX cb request, but the placement is not, remove it.
			$placements = array_filter( $placements, function( $placement_id ) {
				return in_array( $placement_id, $this->cache_busting_placements, true );
			}, ARRAY_FILTER_USE_KEY );
		} elseif ( $action === 'aa-server-select' ) {
			// If this is an Ad Server request, but the placement is not, remove it.
			$placements = array_filter( $placements, static function( $placement ) {
				return $placement['type'] === 'server';
			} );
		}

		// ad groups cache.
		$ad_groups = [];

		foreach ( $placements as $placement_id => $placement ) {
			$grouped_ads    = [];
			$ad_group_count = 1;
			// if this is a group, get the group and see how many ads should be shown.
			if ( strpos( $placement['item'], 'group' ) === 0 ) {
				$group_id    = (int) str_replace( 'group_', '', $placement['item'] );
				$grouped_ads = $this->get_ads_in_group( $group_id );
				// we don't have an instance for this ad group yet.
				if ( ! array_key_exists( $placement['item'], $ad_groups ) ) {
					$ad_groups[ $placement['item'] ] = new Advanced_Ads_Group( $group_id );
				}
				$ad_group_count = $ad_groups[ $placement['item'] ]->ad_count;
			}

			foreach ( $current_ads as $current_ad ) {
				if (
					// not the correct ad for the current placement.
					$current_ad['placement_id'] !== $placement_id
					// see if part of ad group.
					|| ( ! empty( $grouped_ads ) && ! in_array( (int) $current_ad['id'], $grouped_ads, true ) )
				) {
					continue;
				}

				// we have found an ad for this placement, track it.
				Advanced_Ads_Tracking_Util::get_instance()->track_impression( (int) $current_ad['id'], $start_time );
				// if we have found enough ads for this placement, check the next one.
				if ( -- $ad_group_count === 0 ) {
					break;
				}
			}
		}
	}

	/**
	 * Sanitize an ad to prevent isset checks.
	 *
	 * @param array $ad the ad to sanitize.
	 *
	 * @return array the sanitized ad.
	 */
	private function sanitize_ad( $ad ) {
		return wp_parse_args(
			$ad,
			[
				'int'              => 0,
				'type'             => '',
				'placement_id'     => '',
				'tracking_enabled' => false,
				'output'           => '',
			]
		);
	}

	/**
	 * Check if this placement holds a delayed ad.
	 *
	 * @param array $placement Options array for placement.
	 *
	 * @return bool whether this is a delayed placement.
	 */
	private function is_delayed_placement( $placement ) {
		return ( isset( $placement['layer_placement'] ) && ! empty( $placement['layer_placement']['trigger'] ) ) || ( isset( $placement['sticky'] ) && ! empty( $placement['sticky']['trigger'] ) );
	}

	/**
	 * Get all ads that belong to a certain group.
	 *
	 * @param int $group_id Group ID.
	 *
	 * @return array The Ad ids for this group.
	 */
	private function get_ads_in_group( $group_id ) {
		return Advanced_Ads::get_instance()->get_model()->get_ads(
			[
				'fields'    => 'ids',
				'tax_query' => [
					[
						'taxonomy' => Advanced_Ads::AD_GROUP_TAXONOMY,
						'field'    => 'term_id',
						'terms'    => $group_id,
					],
				],
			]
		);
	}

	/**
	 * Load header scripts (actually loads in footer).
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		if ( function_exists( 'advads_is_amp' ) && advads_is_amp() ) {
			return;
		}

		$deps            = [];
		$is_script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		$is_ga_tracking  = $this->plugin->get_tracking_method() === 'ga' || is_multisite() || $this->plugin->is_forced_analytics();

		if ( wp_script_is( 'advanced-ads-pro/cache_busting' ) ) {
			$deps[] = 'advanced-ads-pro/cache_busting';

			if ( $is_script_debug ) {
				wp_enqueue_script( 'advadsTrackingPro', AAT_BASE_URL . 'public/assets/js/src/pro.js', array_merge( $deps, [ 'advadsTrackingScript', 'advadsClickTracking' ] ), AAT_VERSION, true );
			}
		}

		if ( $is_script_debug ) {
			wp_register_script( 'advadsTrackingUtils', AAT_BASE_URL . 'public/assets/js/src/tracking-util.js', [], AAT_VERSION, true );
			$deps[] = 'advadsTrackingUtils';

			// Google Analytics instances store and tracking script.
			if ( $is_ga_tracking ) {
				wp_enqueue_script( 'advadsInventoryScript', AAT_BASE_URL . 'public/assets/js/src/ga-instances.js', $deps, AAT_VERSION, true );
				$deps[] = 'advadsInventoryScript';

				wp_enqueue_script( 'advadsTrackingGAFront', AAT_BASE_URL . 'public/assets/js/src/ga-tracking.js', $deps, AAT_VERSION, true );
			}

			// main tracking script
			wp_enqueue_script( 'advadsTrackingScript', AAT_BASE_URL . 'public/assets/js/src/impressions.js', $deps, AAT_VERSION, true );
			$deps[] = 'advadsTrackingScript';
			// click tracking script
			wp_enqueue_script( 'advadsClickTracking', AAT_BASE_URL . 'public/assets/js/src/clicks.js', $deps, AAT_VERSION, true );
		} else {
			wp_enqueue_script( 'advadsTrackingScript', AAT_BASE_URL . 'public/assets/js/dist/tracking.min.js', $deps, AAT_VERSION, true );
			$deps[] = 'advadsTrackingScript';
			if ( $is_ga_tracking ) {
				wp_enqueue_script( 'advadsTrackingGAFront', AAT_BASE_URL . 'public/assets/js/dist/ga-tracking.min.js', $deps, AAT_VERSION, true );
			}
		}

		// pass ajax_action name to script
		wp_localize_script( 'advadsTrackingScript', 'advadsTracking', [
			'impressionActionName' => Advanced_Ads_Tracking_Ajax::TRACK_IMPRESSION,
			'clickActionName'      => Advanced_Ads_Tracking_Ajax::TRACK_CLICK,
			'targetClass'          => $this->frontend_prefix . 'target',
			'blogId'               => get_current_blog_id(),
			'frontendPrefix'       => Advanced_Ads_Plugin::get_instance()->get_frontend_prefix(),
		] );

		if ( $is_ga_tracking ) {
			$impression_name = 'advanced_ads_impression';
			$click_name      = 'advanced_ads_click';
			wp_localize_script( 'advadsTrackingGAFront', 'advadsTrackingGAEvents', [
				/**
				 * Filters the Google Analytics 4 event name for ad impressions
				 *
				 * @param string $impression_name ad impression event name.
				 */
				'impression' => apply_filters( 'advanced-ads-tracking-ga-impression', $impression_name ),
				/**
				 * Filters the Google Analytics 4 event name for clicks
				 *
				 * @param string $click_name ad click event name.
				 */
				'click'      => apply_filters( 'advanced-ads-tracking-ga-click', $click_name ),
			] );
		}

		// if delayed ads add-ons are available
		if ( $this->plugin->has_delayed_ads() ) {
			wp_enqueue_script( 'advadsTrackingDelayed', AAT_BASE_URL . 'public/assets/js' . ( $is_script_debug ? '/src/delayed.js' : '/dist/delayed.min.js' ), array_merge( $deps, [ 'jquery' ] ), AAT_VERSION, true );
		}
	}

	/**
	 * Collect ad id, so that JavaScript can access it
	 *
	 * @param Advanced_Ads_Ad $ad             The ad object.
	 * @param string          $output         Ad HTML output.
	 * @param array           $output_options Output options for this ad.
	 */
	public function collect_ad_id( Advanced_Ads_Ad $ad, $output, $output_options = [] ) {
		if (
			empty( $output_options['global_output'] ) // do not track ad for passive cache-busting
			|| empty( $output ) // do not track empty ads
			|| ! $this->plugin->check_ad_tracking_enabled( $ad ) // check if this ad should be tracked
		) {
			return;
		}

		$blog_id = get_current_blog_id();

		if ( $this->plugin->get_tracking_method() === 'ga' ) {
			$can_transmit_pageqs = apply_filters( 'advanced-ads-tracking-query-string', false, $ad->id );
			if ( $can_transmit_pageqs ) {
				if ( ! isset( $this->transmit_pageqs[ $blog_id ] ) ) {
					$this->transmit_pageqs[ $blog_id ] = [];
				}
				$this->transmit_pageqs[ $blog_id ][ $ad->id ] = true;
			}
		}

		if ( ! isset( $this->ad_ids[ $blog_id ] ) ) {
			$this->ad_ids[ $blog_id ] = [];
		}
		Advanced_Ads_Tracking_Util::get_instance()->collect_blog_data();
		$this->ad_ids[ $blog_id ][] = $ad->id;
	}

	/**
	 * Output ad ids
	 */
	public function output_ad_ids() {
		if ( function_exists( 'advads_is_amp' ) && advads_is_amp() ) {
			return;
		}
		$utils = Advanced_Ads_Tracking_Util::get_instance();
		$utils->collect_blog_data();
		$blog_data = $utils->get_blog_data();
		$variables = [
			'advads_tracking_' => [
				'ads'       => $this->ad_ids,
				'urls'      => $blog_data['ajaxurls'],
				'methods'   => $blog_data['methods'],
				'parallel'  => $blog_data['parallelTracking'],
				'linkbases' => $blog_data['linkbases'],
			],
		];

		// add Google Analytics specific variables.
		if ( is_multisite() || $this->plugin->get_tracking_method() === 'ga' || $this->plugin->is_forced_analytics() ) {
			$variables['advads_gatracking_'] = [
				'uids'           => $blog_data['gaUIDs'],
				'allads'         => $blog_data['allads'],
				'anonym'         => defined( 'ADVANCED_ADS_DISABLE_ANALYTICS_ANONYMIZE_IP' ) && ADVANCED_ADS_DISABLE_ANALYTICS_ANONYMIZE_IP,
				'transmitpageqs' => $this->transmit_pageqs,
			];
		}

		// transpose variables into string.
		$output = '';
		foreach ( $variables as $dimension => $vars ) {
			foreach ( $vars as $var => $value ) {
				$output .= sprintf( 'var %s = %s;', $dimension . $var, wp_json_encode( $value, ( is_array( $value ) && empty( $value ) ) ? JSON_FORCE_OBJECT : 0 ) );
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<script id="%stracking">%s</script>', $this->frontend_prefix, $output );
	}

	/**
	 * Track impression on output.
	 *
	 * @param Advanced_Ads_Ad $ad             The ad instance to track.
	 * @param string          $output         The ad output string.
	 * @param array           $output_options Array with output options.
	 *
	 * @since 1.0.0
	 */
	public function track_on_output( Advanced_Ads_Ad $ad, $output, array $output_options = [] ) {
		$start_time = microtime( true );
		if (
			( ! isset( $output_options['global_output'] ) || ! $output_options['global_output'] ) // do not track ad for passive cache-busting
			|| empty( $output ) // do not track empty ads
			|| ! $this->plugin->check_ad_tracking_enabled( $ad ) // check if this ad should be tracked
		) {
			return;
		}

		// Do not track delayed ads when AJAX option enabled.
		$ad_options = $ad->options();

		if (
			( ! empty( $ad_options['layer_placement']['trigger'] ) || ! empty( $ad_options['sticky']['trigger'] ) )
			&& $this->plugin->has_delayed_ads()
		) {
			return;
		}

		Advanced_Ads_Tracking_Util::get_instance()->track_impression( $ad->id, $start_time );
	}

	/**
	 * Add impression to database
	 *
	 * @param array $args array with ad_id.
	 *
	 * @deprecated 1.2.0 use util class instead
	 * @since      1.0.0
	 */
	public function track_impression( $args = [] ) {
		$this->util->track_impression( $args );
	}

	/**
	 * Add click to database
	 *
	 * @since      1.1.0
	 * @deprecated 1.2.0 use util class instead
	 */
	public function track_click( $args = [] ) {
		$this->util->track_click( $args );
	}

	/**
	 * Add a link to the ad content either for the %link% placeholder or a wrapper.
	 *
	 * @param string          $content The ad content.
	 * @param Advanced_Ads_Ad $ad      The ad object.
	 *
	 * @return string
	 * @since 1.1.0
	 */
	public function add_tracking_link( $content, $ad ) {
		$ad_options  = $ad->options();
		$options     = $this->plugin->options();
		$amp_plain   = false;

		// do not add link if click tracking is not supported by the ad type
		if ( ! in_array( $ad->type, Advanced_Ads_Tracking_Plugin::get_clickable_types(), true ) ) {
			return $content;
		}

		// get url
		$url = '';
		if ( isset( $ad_options['tracking']['link'] ) && $ad_options['tracking']['link'] !== '' ) {
			$url = $ad_options['tracking']['link'];
		} elseif ( isset( $ad_options['url'] ) && $ad_options['url'] !== '' ) {
			$url = $ad_options['url'];
		}

		$is_amp = function_exists( 'advads_is_amp' ) && advads_is_amp();

		// Extract custom link if plain ad on amp.
		if ( $is_amp && $ad->type === 'plain' && ! $url ) {
			$amp_plain = true;
			$matches   = $this->get_url_from_string( $content );
			if ( ! empty( $matches[0] ) ) {
				$url     = $matches[0];
				$link    = $url;
				$content = str_replace( sprintf( 'href="%s"', $link ), 'href="%link%"', $content );
			}
		}

		// we haven't found a URL, so we return the original content.
		if ( ! $url ) {
			return $content;
		}

		$link = self::build_click_tracking_url( $ad );
		if ( ! $link ) {
			return $content;
		}

		$bid                                 = get_current_blog_id();
		$this->ad_targets[ $bid ][ $ad->id ] = $url;
		$attributes                          = [
			'data-bid'        => $bid,
			'data-no-instant' => true,
			'href'            => $link,
			'rel'             => [],
			'class'           => [],
			'target'          => Advanced_Ads_Tracking_Util::get_target( $ad, true ),
		];

		if ( $attributes['target'] !== '' ) {
			$attributes['rel'][] = 'noopener';
		}

		// parse rel attribute.
		foreach ( [ 'nofollow', 'sponsored' ] as $relationship ) {
			$option = isset( $ad_options['tracking'][ $relationship ] ) ? $ad_options['tracking'][ $relationship ] : 'default';
			if ( $option === 'default' ) {
				$option = ! empty( $options[ $relationship ] );
			}
			if ( $option ) {
				$attributes['rel'][] = $relationship;
			}
		}

		if ( strpos( $content, '%link%' ) !== false ) {

			// Add custom parameter to recognise amp origin.
			if ( $amp_plain ) {
				$attributes['href'] = add_query_arg( [ 'advads_amp' => '' ], $attributes['href'] );
			}

			// return content if there aren't any link tags.
			if ( ! preg_match_all( '/(<a[^<]+?%link%[^<]+?>)/', $content, $links_to_replace ) ) {
				return $content;
			}

			if ( ! $this->plugin->check_ad_tracking_enabled( $ad, 'click' ) ) {
				return str_replace( '%link%', esc_url( $url ), $content );
			}

			// Add `notrack` class to disable js-based tracking for this link in case it is not a redirect link on Google Analytics.
			if ( $this->plugin->get_tracking_method() !== 'ga' && ! $this->plugin->is_forced_analytics() && ( $this->plugin->get_tracking_method() === 'frontend' && $ad->options( 'tracking.cloaking' ) ) ) {
				$attributes['class'][] = 'notrack';
			}
			if ( ! $ad->options( 'tracking.cloaking' ) ) {
				$attributes['href'] = esc_url( $url );
			}
			$attributes = array_filter( $attributes );
			$attributes = $this->filter_link_attributes( $attributes, $ad );

			foreach ( $links_to_replace[0] as $link_tag ) {
				$link_attributes = $this->attributes_merge_recursive( $this->parse_link_attributes( $link_tag ), $attributes );
				$content         = str_replace( $link_tag, sprintf( '<a %s>', $this->create_attributes_string( $link_attributes ) ), $content );
			}

			return $content;
		}

		// There is no placeholder. If the content of a plain ad itself contains links abort and return the content.
		if ( $ad->type === 'plain' && preg_match( "/<a[\s]+/", $content ) ) {
			return $content;
		}

		// Wrap ad into tracking link if delivered by ad server or amp and url field is empty.
		if (
			$is_amp
			|| (
				( isset( $ad_options['placement_type'] ) && $ad_options['placement_type'] === 'server' )
				&& $this->plugin->check_ad_tracking_enabled( $ad, 'click' )
			)
			|| $ad->options( 'tracking.cloaking' )
		) {
			$attributes['class'][] = 'notrack';
		} else {
			$placeholders = [ '[POST_ID]', '[POST_SLUG]', '[CAT_SLUG]', '[AD_ID]' ];
			$placeholders = array_merge($placeholders, array_map('urlencode', $placeholders));
			// use str_replace to decide whether URL has placeholder.
			str_replace($placeholders, '', $url, $count);

			// if there are placeholders, use the redirect click-tracking link.
			if ($count) {
				$attributes['class'][] = 'notrack';
			} else {
				// else use the original, uncloaked URL.
				$attributes['class'][] = 'adv-link';
				$attributes['href']    = esc_url( $url );
				unset( $attributes['data-bid'] );
			}
		}

		if( $ad->type === 'image' ) {
			$id 		= $ad->output['image_id'] ?? '';
			$alt      	= trim( esc_textarea( get_post_meta( $id, '_wp_attachment_image_alt', true ) ) );
			$aria_label	= !empty( $alt ) ? $alt : wp_basename( get_the_title( $id ) );
			$attributes['aria-label'][] = $aria_label;
		}

		if( $ad->type === 'dummy' ) {
			$attributes['aria-label'][] = 'dummy';
		}

		$attributes = $this->filter_link_attributes( $attributes, $ad );

		return sprintf( '<a %s>%s</a>', $this->create_attributes_string( array_filter( $attributes ) ), $content );
	}

	/**
	 * Get all defined attributes from link tag.
	 *
	 * @param string $input HTML string link tag.
	 *
	 * @return array
	 */
	private function parse_link_attributes( $input ) {
		if ( ! extension_loaded( 'dom' ) ) {
			return [];
		}
		$libxml_previous_state = libxml_use_internal_errors( true );
		$dom                   = new DOMDocument( '1.0', 'utf-8' );
		$dom->loadHTML( '<!DOCTYPE html><html><body>' . mb_convert_encoding( $input, 'HTML-ENTITIES', 'UTF-8' ) . '</body></html>' );
		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		$attributes = [];
		/** @var DOMElement $link */
		foreach ( $dom->getElementsByTagName( 'a' ) as $link ) {
			foreach ( $link->attributes as $attribute ) {
				$attributes[ $attribute->name ] = in_array( $attribute->name, [ 'class', 'rel' ], true ) ? explode( ' ', $attribute->value ) : $attribute->value;
			}
		}

		return $attributes;
	}

	/**
	 * Create a string from array of attributes to be used on HTML element.
	 *
	 * @param array $attributes Array of attributes, attribute name as key, if value is array it gets imploded into space-separated string.
	 *
	 * @return string
	 */
	private function create_attributes_string( $attributes ) {
		return implode( ' ', array_map( static function( $value, $name ) {
			if ( is_array( $value ) ) {
				$value = implode( ' ', array_unique( $value ) );
			}
			$sep = strpos( $value, '"' ) !== false ? "'" : '"';

			return sprintf( '%1$s=%2$s%3$s%2$s', $name, $sep, $value );
		}, $attributes, array_keys( $attributes ) ) );
	}

	/**
	 * Merge two arrays with attributes recursively.
	 * If the values is a string, the value form array2 replaces array1.
	 * If the value is an array, the array from array2 gets merged into array1.
	 *
	 * @param array $array1 This array holds the original values that may get overridden.
	 * @param array $array2 This array holds the values that get merged into $array1.
	 *
	 * @return array
	 */
	private function attributes_merge_recursive( array $array1, array $array2 ) {
		$merged = $array1;
		foreach ( $array2 as $key => $value ) {
			if ( isset( $merged[ $key ] ) && is_array( $value ) && is_array( $merged[ $key ] ) ) {
				$merged[ $key ] = array_merge( $merged[ $key ], $value );
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Extract custom urls from string
	 *
	 * @param string $content HTML string.
	 *
	 * @return array Array of urls or empty array.
	 */
	private function get_url_from_string( $content ) {
		$regex = '#\bhttps?://[^\s()<>]+(?:\(\w+\)|([^[:punct:]\s]|/))#';
		preg_match_all( $regex, $content, $matches );

		return $matches[0];
	}

	/**
	 * Build click tracking url.
	 *
	 * @param Advanced_Ads_Ad $ad The ad object.
	 *
	 * @return string $url click tracking url
	 * @since 1.1.0
	 */
	public static function build_click_tracking_url( Advanced_Ads_Ad $ad ) {
		if ( empty( $ad->id ) ) {
			return '';
		}

		$options  = Advanced_Ads_Tracking_Plugin::get_instance()->options();
		$linkbase = isset( $options['linkbase'] ) ? $options['linkbase'] : self::CLICKLINKBASE;
		$base     = apply_filters( 'advanced-ads-tracking-click-url-base', $linkbase, $ad );

		$permalink = get_option( 'permalink_structure' );

		if ( ! $permalink ) {
			$home = home_url( '/' );
			if ( false !== strpos( $home, '?' ) ) {
				$target_url = $home . '&' . $base . '=' . $ad->id;
			} else {
				$target_url = $home . '?' . $base . '=' . $ad->id;
			}
		} else {
			$target_url = home_url( '/' . $base . '/' . $ad->id );
			/**
			 * Hotfix caused by WPML plugin that adds variables through home_url filter
			 * but useful for similar scripts too
			 */
			$pos = strpos( $target_url, '?' );
			if ( $pos ) {
				$target_url = substr( $target_url, 0, $pos );
			}
		}

		/**
		 * Allow to manipulate the click tracking URL
		 */
		$target_url = apply_filters( 'advanced-ads-tracking-click-tracking-url', $target_url );

		return $target_url;
	}

	/**
	 * Check if ad can be displayed based on tracking options
	 *
	 * @param bool            $can_display Whether this ad can be displayed.
	 * @param Advanced_Ads_Ad $ad          The ad object.
	 *
	 * @return bool $can_display false if should not be displayed in frontend
	 * @since 1.2.6
	 */
	public function can_display( $can_display, $ad ) {
		if ( ! $can_display ) {
			return false;
		}

		$can_display = ( new Advanced_Ads_Tracking_Limiter( $ad->id ) )->can_display();

		return $can_display;
	}

	/**
	 * If the ad doesn't have impression tracking enabled, add data attribute.
	 *
	 * @param array           $attributes Data attributes array.
	 * @param Advanced_Ads_Ad $ad         The current ad.
	 *
	 * @return array
	 */
	public function privacy_output_attributes( $attributes, Advanced_Ads_Ad $ad ) {
		if ( $this->plugin->check_ad_tracking_enabled( $ad ) ) {
			return $attributes;
		}

		$attributes['no-track'] = 'impressions';

		return $attributes;
	}

	/**
	 *  Deactivation
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'advanced_ads_daily_email' );
		wp_clear_scheduled_hook( 'advanced_ads_auto_comp' );
		wp_clear_scheduled_hook( 'advanced_ads_daily_report' );
		delete_option( Advanced_Ads_Tracking_Debugger::DEBUG_OPT );
	}

	/**
	 *  Daily ( & weekly & monthly ) email function
	 */
	public function daily_email() {
		$options = $this->plugin->options();
		if ( empty( $options ) ) {
			return;
		}

		if ( $this->plugin->get_tracking_method() === 'ga' ) {
			$this->log_report_cron( 'full report: email reports are not working with Google Analytics' );

			return;
		}

		$sched = isset( $options['email-sched'] ) ? $options['email-sched'] : 'daily';
		$now   = date_create( 'now', Advanced_Ads_Tracking_Util::get_wp_timezone() );

		$this->log_report_cron( 'full report: schedule: ' . $sched );
		$this->log_report_cron( 'full report: current time: ' . print_r( $now, true ) );

		/**
		 *  Site admin reports
		 */
		$result = 'not sent';
		switch ( $sched ) {
			case 'monthly':
				if ( $now->format( 'd' ) === '01' ) {
					// if start of month
					$result = $this->util->send_email_report();
					$this->log_report_cron( 'full report: schedule: ' . $sched );
				}
				break;

			case 'weekly':
				if ( $now->format( 'w' ) === '1' ) {
					// if monday
					$result = $this->util->send_email_report();
				}
				break;

			default: // daily
				$result = $this->util->send_email_report();
		}

		$this->log_report_cron( 'full report: send?: ' . print_r( $result, true ) );
	}

	/**
	 *  Individual ad email function
	 */
	public function individual_email_report() {
		if ( 'ga' === $this->plugin->get_tracking_method() ) {
			$this->log_report_cron( 'email reports are not working with Google Analytics' );

			return;
		}

		$per_ad_reports = $this->get_ad_reports_params();

		$now = date_create( 'now', Advanced_Ads_Tracking_Util::get_wp_timezone() );

		foreach ( $per_ad_reports as $item ) {
			if ( $item['frequency'] === 'never' ) {
				continue;
			}
			$frequency   = $item['frequency'];
			$ad_id       = $item['id'];
			$period      = $item['period'];
			$recip       = $item['recip'];
			$period_name = $item['period-literal'];

			$order_id = get_post_meta( $ad_id, 'advanced_ads_selling_order', true );
			if ( $order_id ) {
				// if ad was sold via WooCommerce
				$post  = get_post( $ad_id );
				$order = wc_get_order( $order_id );
				global $woocommerce;
				if ( isset( $woocommerce->version ) && version_compare( $woocommerce->version, '3.0', '>=' ) ) {
					$recip = $order->get_billing_email();
				} else {
					$recip = $order->billing_email;
				}
			}

			// string used in debug log, if enabled
			$debug_string = 'report for ad ID ' . $ad_id;

			if ( empty( $recip ) ) {
				$this->log_report_cron( $debug_string . ': recipient missing' );
				continue;
			}

			$this->log_report_cron( $debug_string . ': frequency: ' . $frequency );
			$this->log_report_cron( $debug_string . ': current time: ' . print_r( $now, true ) );

			// translators: 1. statistics period 2. ad name
			$subject = sprintf( __( 'Ad statistics for %1$s for %2$s', 'advanced-ads-tracking' ), $period_name, $item['title'] );
			$result  = 'not sent';

			// if the ad is expired, send one last report after expiration.
			if ( $this->ad_expired_report( (int) $ad_id, $frequency, $now->getTimestamp() ) ) {
				$this->log_report_cron( $debug_string . ': ad is expired and last report has already been sent.' );

				return;
			}

			switch ( $frequency ) {
				case 'monthly':
					if ( $now->format( 'd' ) === '01' ) {
						// if start of month
						$result = $this->util->send_individual_ad_report( [
							'subject' => $subject,
							'to'      => $recip,
							'id'      => $ad_id,
							'period'  => $period,
						] );
					}
					break;

				case 'weekly':
					if ( $now->format( 'w' ) === '1' ) {
						// if monday
						$result = $this->util->send_individual_ad_report( [
							'subject' => $subject,
							'to'      => $recip,
							'id'      => $ad_id,
							'period'  => $period,
						] );
					}
					break;

				default: // daily
					$result = $this->util->send_individual_ad_report( [
						'subject' => $subject,
						'to'      => $recip,
						'id'      => $ad_id,
						'period'  => $period,
					] );
			}

			$this->log_report_cron( $debug_string . ': send?: ' . print_r( $result, true ) );
		}
	}

	/**
	 * Retrieve ad ids, period, frequency and report recipient for all ads.
	 *
	 * @return array
	 */
	private function get_ad_reports_params() {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- we can't add table names as placeholders.
		$ad_ids = array_map( function( $value ) { return (int) $value; }, $this->wpdb->get_col( "SELECT {$this->wpdb->posts}.ID FROM {$this->wpdb->posts} INNER JOIN {$this->wpdb->postmeta} ON {$this->wpdb->posts}.ID = {$this->wpdb->postmeta}.post_id WHERE {$this->wpdb->posts}.post_type = 'advanced_ads' AND {$this->wpdb->postmeta}.meta_value LIKE '%report-frequency%'" ) );

		// The final result.
		$params = [];

		$period_names = [
			'last30days'   => __( 'last 30 days', 'advanced-ads-tracking' ),
			'lastmonth'    => __( 'the last month', 'advanced-ads-tracking' ),
			'last12months' => __( 'last 12 months', 'advanced-ads-tracking' ),
		];

		foreach ( $ad_ids as $ad_id ) {
			$the_ad = new Advanced_Ads_Ad( $ad_id );
			if ( $the_ad->status !== 'publish' ) {
				continue;
			}
			$options = $the_ad->options();
			if ( isset( $options['tracking']['report-frequency'] ) && $options['tracking']['report-frequency'] !== 'never' ) {
				$params[ $the_ad->id ] = [
					'id'             => $the_ad->id,
					'frequency'      => $options['tracking']['report-frequency'],
					'period'         => $options['tracking']['report-period'],
					'recip'          => $options['tracking']['report-recip'],
					'title'          => $the_ad->title,
					'period-literal' => $period_names[ $options['tracking']['report-period'] ],
				];
			}
		}

		return $params;
	}

	/**
	 * Pass tracking info to passive cache-busting.
	 *
	 * @param array           $data Cache Busting data array.
	 * @param Advanced_Ads_Ad $ad   The ad object.
	 *
	 * @return array
	 */
	public function add_passive_cb_for_ad( array $data, Advanced_Ads_Ad $ad ) {
		$data['tracking_enabled'] = Advanced_Ads_Tracking_Plugin::get_instance()->check_ad_tracking_enabled( $ad );

		return $data;
	}

	/**
	 * Log scheduled reports if debugging constant `ADVANCED_ADS_TRACKING_CRON_DEBUG` is set in wp-config.php
	 *
	 * @param string $content Message that should be logged
	 */
	public function log_report_cron( $content ) {
		if ( defined( 'ADVANCED_ADS_TRACKING_CRON_DEBUG' ) && ADVANCED_ADS_TRACKING_CRON_DEBUG ) {
			error_log( $content . "\n", 3, WP_CONTENT_DIR . '/advanced-ads-tracking-cron.csv' );
		}
	}

	/**
	 * Check if the last report for an expired ad has already been sent.
	 *
	 * @param int    $ad_id         The ad id.
	 * @param string $frequency     Email report frequency, `daily`, `weekly`, `monthly`.
	 * @param int    $now_timestamp The current timestamp.
	 *
	 * @return bool
	 */
	private function ad_expired_report( $ad_id, $frequency, $now_timestamp ) {
		$ad            = new Advanced_Ads_Ad( $ad_id );
		$ad_expiration = (int) $ad->options( 'expiry_date', 0 );
		if ( empty( $ad_expiration ) ) {
			return false;
		}
		$offset = [
			'daily'   => DAY_IN_SECONDS,
			'weekly'  => WEEK_IN_SECONDS,
			'monthly' => MONTH_IN_SECONDS,
		];
		if ( ! array_key_exists( $frequency, $offset ) ) {
			return false;
		}

		return $now_timestamp > $ad_expiration + $offset[ $frequency ];
	}

	/**
	 * Filter the ad link for the background placement.
	 * If link cloaking is active and ad clicks should be tracked, build the tracking URL.
	 *
	 * @param string          $url The ad URL.
	 * @param Advanced_Ads_Ad $ad  The current ad object.
	 *
	 * @return string
	 */
	public function filter_background_placement_url( $url, Advanced_Ads_Ad $ad ) {
		if (
			empty( $url )
			|| ! $this->plugin->check_ad_tracking_enabled( $ad, 'click' )
			|| ! $ad->options( 'tracking.cloaking' )
		) {
			return $url;
		}

		return self::build_click_tracking_url( $ad );
	}

	/**
	 * Add JS to background placement to enable click tracking.
	 *
	 * @param string          $script Other script content, probably empty.
	 * @param Advanced_Ads_Ad $ad     The current ad object.
	 *
	 * @return string
	 */
	public function add_background_placement_script( $script, Advanced_Ads_Ad $ad ) {
		$frontend_prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
		ob_start();
		if ( $ad->options( 'tracking.cloaking' ) ) {
			printf( 'e.target.setAttribute( "data-%sredirect", "1");', esc_attr( $frontend_prefix ) );
		}

		if ( $this->plugin->check_ad_tracking_enabled( $ad, 'click' ) ) {
			printf(
				'e.target.setAttribute( "data-%1$strackid", "%2$d");'
				. 'e.target.setAttribute( "data-%1$strackbid", "%3$d");'
				. 'AdvAdsClickTracker.ajaxSend( e.target );',
				esc_attr( $frontend_prefix ),
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- integers in printf
				$ad->id,
				get_current_blog_id()
			// phpcs:enable
			);
		}

		return $script . preg_replace( '/\s+/', ' ', ob_get_clean() );
	}

	/**
	 * Filter the tracking links attributes.
	 * They are used in multiple places, therefore wrap them in a function.
	 * Ensure the href is present so the `a` tag is valid.
	 *
	 * @param array           $attributes Generated HTML attributes.
	 * @param Advanced_Ads_Ad $ad         The current ad object.
	 *
	 * @return array
	 */
	private function filter_link_attributes( array $attributes, Advanced_Ads_Ad $ad ) {
		/**
		 * Allow to filter the link attributes.
		 *
		 * @var array           $attributes The generated attributes. Attribute name is the key and the value as the value. If multiple values exist for a key, they're stored in an array of strings.
		 * @var Advanced_Ads_Ad $ad         The current ad object.
		 */
		$attributes = (array) apply_filters( 'advanced-ads-tracking-link-attributes', $attributes, $ad );
		if ( ! array_key_exists( 'href', $attributes ) ) {
			$attributes['href'] = '';
		}

		return $attributes;
	}

	/**
	 * Track impressions for array of ad_ids with the current timestamp.
	 *
	 * @param Advanced_Ads_Ad $ad The ad to track.
	 *
	 * @return void
	 */
	public function track_rest_impression( $ad ) {
		Advanced_Ads_Tracking_Util::get_instance()->track_impressions( [ $ad->id ], time() );
	}
}
