<?php

/**
 * Class Advanced_Ads_Tracking_Amp
 *
 * Track ads on AMP with <amp-pixel>.
 */
class Advanced_Ads_Tracking_Amp {
	/**
	 * The Tracking Plugin instance.
	 *
	 * @var Advanced_Ads_Tracking_Plugin
	 */
	private $plugin;

	/**
	 * Holds ads by blog_id.
	 *
	 * @var array
	 */
	private $ads = [];

	/**
	 * Advanced_Ads_Tracking_Amp constructor.
	 */
	public function __construct() {
		$this->plugin = Advanced_Ads_Tracking_Plugin::get_instance();
	}

	/**
	 * Collect ads and register actions for tracking methods on ad output.
	 */
	public function register_actions() {
		// check if is amp.
		if ( ! function_exists( 'advads_is_amp' ) || ! advads_is_amp() ) {
			return;
		}

		add_action( 'advanced-ads-output', [ $this, 'get_tracking_methods' ] );
	}

	/**
	 * Output the <amp-pixel>.
	 */
	public function add_tracking_pixel() {
		$referrer = preg_replace(
			'%^/?(.+?)[/?&]*amp(?:=1|/)?$%',
			'$1',
			! is_null( $GLOBALS['wp']->request ) ? $GLOBALS['wp']->request : $_SERVER['REQUEST_URI']
		);
		if ( substr( $referrer, 0, 1 ) !== '/' ) {
			$referrer = '/' . $referrer;
		}

		// One pixel for each blog id.
		foreach ( $this->ads as $bid => $ads ) {
			printf(
				'<amp-pixel src="%s" layout="nodisplay"></amp-pixel>',
				esc_url(
					add_query_arg(
						[
							'ads'      => array_keys( $ads ),
							'action'   => 'aatrack-records',
							'referrer' => urlencode( $referrer ),
							'bid'      => $bid,
							'handler'  => urlencode( 'Frontend on AMP' ),
						],
						$this->plugin->is_legacy_ajax() ? admin_url( 'admin-ajax.php' ) : content_url( '/ajax-handler.php' )
					)
				)
			);
		}
	}

	/**
	 * Output the amp-analytics JS; only once per request.
	 */
	public function add_amp_analytics() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		echo '<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>';
	}

	/**
	 * Output the <amp-analytics> script once per blog_id with all ads included.
	 */
	public function add_amp_analytics_ads() {
		foreach ( $this->ads as $blog_id => $ads ) {
			$ga_uid = $this->get_gauid( $blog_id );
			if ( empty( $ga_uid ) ) {
				continue;
			}
			$amp_analytics = [
				'requests' => [
					'impressionEvent' => '${event}&ni=1',
				],
				'vars'     => [
					'account'       => $ga_uid,
					'eventCategory' => 'Advanced Ads',
					'eventAction'   => __( 'Impressions', 'advanced-ads-tracking' ),
				],
				'triggers' => [],
			];
			foreach ( $ads as $ad_id => $ad_title ) {
				$amp_analytics['triggers'][ 'impression of ad ' . $ad_id ] = [
					'on'      => 'visible',
					'request' => 'impressionEvent',
					'vars'    => [
						'eventLabel' => sprintf( '[%d] %s', $ad_id, $ad_title ),
					],
				];
			}

			printf( '<amp-analytics type="googleanalytics"><script type="application/json">%s</script></amp-analytics>', wp_json_encode( $amp_analytics ) );
		}
	}

	/**
	 * Get the GA tracking ID.
	 *
	 * @param int $blog_id Current blog id.
	 *
	 * @return string tracking id or empty string.
	 */
	private function get_gauid( $blog_id ) {
		$options = $this->get_blog_option( $blog_id );
		if ( empty( $options['ga-UID'] ) ) {
			return '';
		}

		return $options['ga-UID'];
	}

	/**
	 * If a custom AJAX handler is used, set it to amp tracking.
	 * If frontend tracking method is used with admin-ajax.php set it to `onrequest`.
	 *
	 * @param string $method tracking method as set under Advanced Ads > Settings > Tracking.
	 *
	 * @return string
	 */
	public function set_tracking_method( $method ) {
		return $method !== 'frontend' ? $method : 'amp_pixel';
	}

	/**
	 * Get options for the blog specified.
	 * Defaults to blog id 1.
	 *
	 * @param int $blog_id Current blog id.
	 *
	 * @return array
	 */
	private function get_blog_option( $blog_id ) {
		static $options;
		if ( ! empty( $options[ $blog_id ] ) ) {
			return $options[ $blog_id ];
		}
		$option = function_exists( 'get_blog_option' )
			? get_blog_option( $blog_id, $this->plugin->options_slug, [] )
			: get_option( $this->plugin->options_slug, [] );

		$options[ $blog_id ] = $option;

		return $option;
	}

	/**
	 * Get the tracking method for the current blog.
	 *
	 * @param int $blog_id Current blog id.
	 *
	 * @return string
	 */
	private function get_tracking_method( $blog_id ) {
		$options = $this->get_blog_option( $blog_id );

		return $this->plugin->get_tracking_method( isset( $options['method'] ) ? $options['method'] : '' );
	}

	/**
	 * Collect ads and add relevant hooks for JS (amp-pixel) and GA (amp-analytics) tracking.
	 *
	 * @param Advanced_Ads_Ad $ad The ad being output.
	 */
	public function get_tracking_methods( Advanced_Ads_Ad $ad ) {
		// if impression tracking is not allowed for this ad, skip it.
		if ( ! $this->plugin->check_ad_tracking_enabled( $ad ) ) {
			return;
		}
		// try setting the tracking method to amp-pixel.
		add_filter( 'advanced-ads-tracking-method', [ $this, 'set_tracking_method' ] );
		$blog_id = get_current_blog_id();

		// collect ad ids per blog.
		$this->ads[ $blog_id ][ $ad->id ] = $ad->title;

		// add tracking pixel if method is amp-pixel.
		if ( $this->get_tracking_method( $blog_id ) === 'amp_pixel' ) {
			$this->tracking_pixel_actions();
		}

		// add google analytics amp code.
		if (
			( $this->get_tracking_method( $blog_id ) === 'ga' || $this->plugin->is_forced_analytics() )
			&& ! empty( $this->get_gauid( $blog_id ) )
		) {
			$this->amp_analytics_actions();
		}
	}

	/**
	 * Output tracking pixel for local AMP tracking.
	 */
	private function tracking_pixel_actions() {
		// Transitional/Standard mode.
		add_action( 'wp_footer', [ $this, 'add_tracking_pixel' ] );

		// WP AMP — Accelerated Mobile Pages for WordPress and WooCommerce (https://codecanyon.net/item/wp-amp-accelerated-mobile-pages-for-wordpress-and-woocommerce/16278608).
		add_action( 'amphtml_after_footer', [ $this, 'add_tracking_pixel' ] );

		// AMP - AMP Project Contributors (https://wordpress.org/plugins/amp/), Reader mode.
		// AMP for WP - Accelerated Mobile Pages for WordPress (https://wordpress.org/plugins/accelerated-mobile-pages/).
		add_action( 'amp_post_template_footer', [ $this, 'add_tracking_pixel' ] );

		// AMP WP - pixelative (https://wordpress.org/plugins/amp-wp/).
		add_action( 'amp_wp_template_footer', [ $this, 'add_tracking_pixel' ] );
	}

	/**
	 * Output relevant scripts for AMP Analytics tracking.
	 */
	private function amp_analytics_actions() {
		$actions = [
			// AMP - AMP Project Contributors (https://wordpress.org/plugins/amp/), Transitional/Standard mode.
			'amp'        => 'wp',
			// AMP - AMP Project Contributors (https://wordpress.org/plugins/amp/), Reader mode.
			// AMP for WP - Accelerated Mobile Pages for WordPress (https://wordpress.org/plugins/accelerated-mobile-pages/).
			'amp_reader' => 'amp_post_template',
			// AMP WP - pixelative (https://wordpress.org/plugins/amp-wp/).
			'amp_wp'     => 'amp_wp_template',
		];

		foreach ( $actions as $action ) {
			if ( ! did_action( $action . '_head' ) ) {
				add_action( $action . '_head', [ $this, 'add_amp_analytics' ] );
			} else {
				add_action( $action . '_footer', [ $this, 'add_amp_analytics' ] );
			}
			add_action( $action . '_footer', [ $this, 'add_amp_analytics_ads' ] );
		}
	}
}
