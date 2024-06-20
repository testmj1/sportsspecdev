<?php

use AdvancedAds\Utilities\WordPress;

/**
 * Class Advanced_Ads_Tracking_Admin
 */
class Advanced_Ads_Tracking_Admin {

	const PLUGIN_LINK = 'https://wpadvancedads.com/add-ons/tracking/';
	const CRONJOBS    = [ 'advanced_ads_daily_email', 'advanced_ads_daily_report' ];

	private $settings_page_hook = 'advanced-ads-tracking-settings-page';
	private $settings_page_id   = 'advanced-ads_page_advanced-ads-settings';
	private $stat_page_hook;
	private $db_op_page_slug    = 'advads-tracking-db-page';

	/**
	 * @var Advanced_Ads_Tracking_Plugin
	 */
	protected $plugin;

	/**
	 *
	 * @var Advanced_Ads_Tracking_Admin
	 */
	protected static $instance;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		$this->plugin = Advanced_Ads_Tracking_Plugin::get_instance();

		// migrate shutdown tracking method to onrequest
		$this->migrate_deprecated_tracking_method();

		// print scripts in admin page
		add_action( 'admin_print_scripts', [ $this, 'admin_print_scripts' ] );

		// add styles
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

		// add settings tabs after plugin loaded
		add_action( 'advanced-ads-plugin-loaded', [ $this, 'advads_loaded' ] );

		// ad menu item
		add_action( 'advanced-ads-submenu-pages', [ $this, 'add_menu_item' ], 10, 2 );

		// add stats page to array of pages that belong to Advanced Ads
		add_action( 'advanced-ads-dashboard-screens', [ $this, 'add_menu_page_to_array' ] );

		// add setting whether to track or not to track this ad
		add_action( 'advanced-ads-ad-params-after', [ $this, 'render_ad_tracking_options' ] );

		// add our new options using the options filter before saving
		add_filter( 'advanced-ads-save-options', [ $this, 'save_options' ] );

		// add metabox
		add_action( 'admin_init', [ $this, 'add_meta_box' ] );

		// show ad specific notices
		add_filter( 'advanced-ads-ad-notices', [ $this, 'ad_notices' ], 10, 3 );

		// email report cron jobs.
		add_filter( 'pre_update_option_timezone_string', [ $this, 'timezone_changed' ], 50, 2 );
		add_filter( 'pre_update_option_gmt_offset', [ $this, 'timezone_changed' ], 10, 2 );
		$this->check_cron_schedule();

		$options = $this->plugin->options();

		// Check tables only when dbversion changed
		if ( ! isset( $options['dbversion'] ) || $options['dbversion'] !== Advanced_Ads_Tracking_Util::DB_VERSION ) {
			$this->check_tables();
		}

		// add notice about GA event names change.
		add_filter( 'advanced-ads-notices', [ $this, 'ga_events_notice' ] );

		// add the stats column into custom columns white list
		add_filter( 'advanced-ads-ad-list-allowed-columns', [ $this, 'column_white_list' ] );

		// add custom column
		add_filter( 'manage_advanced_ads_posts_columns', [ $this, 'add_column' ] );

		// stats columns in ads list
		add_filter( 'manage_advanced_ads_posts_custom_column', [ $this, 'ad_list_columns_content' ], 10, 2 );

		add_action( 'wp_loaded', [ $this, 'check_tracking_tables' ] );

		add_action( 'dp_duplicate_post', [ $this, 'on_duplicate_post' ], 20 );
		add_action( 'advanced-ads-export-options', [ $this, 'export_options' ] );
		add_filter( 'posts_clauses', [ $this, 'request_clauses' ], 10, 2 );
		add_filter( 'default_hidden_columns', [ $this, 'set_hidden_columns' ], 10, 2 );
		add_filter( 'manage_edit-advanced_ads_sortable_columns', [$this, 'set_sortable_columns'] );
	}

	/**
	 * Make impressions, clicks and ctr columns sortable
	 *
	 * @param array $columns sortable columns.
	 *
	 * @return mixed
	 */
	public function set_sortable_columns( $columns ) {
		$columns['ad_imprs'] = [ 'impressions', __( 'Impressions', 'advanced-ads-tracking' ) ];
		$columns['ad_clicks'] = [ 'clicks', __( 'Clicks', 'advanced-ads-tracking' ) ];
		$columns['ad_ctrs'] = [ 'ctr', __( 'CTR', 'advanced-ads-tracking' ) ];

		return $columns;
	}

	/**
	 * Make impressions, clicks and ctr columns hidden by default
	 *
	 * @param array $hidden default hidden columns.
	 * @param WP_Screen $screen current screen.
	 *
	 * @return array|mixed
	 */
	public function set_hidden_columns( $hidden, $screen ) {
		if ( isset( $screen->id ) && 'edit-advanced_ads' === $screen->id ){
			$hidden = array_merge( $hidden, ['ad_imprs', 'ad_clicks', 'ad_ctrs'] );
		}
		return $hidden;
	}

	/**
	 * Add statistics data to the query on ad overview page
	 *
	 * @param array $clauses clauses in current query.
	 * @param WP_Query $query current query object.
	 *
	 * @return mixed
	 */
	public function request_clauses( $clauses, $query ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $clauses;
		}
		$screen = get_current_screen();
		if ( ! $screen || 'edit-advanced_ads' !== $screen->id || ! $query->is_main_query() ) {
			return $clauses;
		}

		global $wpdb;
		$prefix = $wpdb->prefix;

		$clauses['fields']  .= ", imp.count as impressions, cl.count as clicks, cl.count / imp.count as ctr";
		$clauses['join']    .= " LEFT JOIN (SELECT ad_id, SUM(count) as count from {$prefix}advads_impressions GROUP BY {$prefix}advads_impressions.ad_id) as imp ON {$prefix}posts.ID = imp.ad_id";
		$clauses['join']    .= " LEFT JOIN (SELECT ad_id, SUM(count) as count from {$prefix}advads_clicks GROUP BY {$prefix}advads_clicks.ad_id) as cl ON imp.ad_id = cl.ad_id";
		$clauses['groupby'] .= empty( trim( $clauses['groupby'] ) ) ? '' : ',';
		$clauses['groupby'] .= " {$prefix}posts.ID";

		$order = [
			'title'       => "{$prefix}posts.post_title",
			'impressions' => 'impressions',
			'clicks'      => 'clicks',
			'ctr'         => 'ctr',
		];

		if ( $query->query_vars['orderby'] && in_array( $query->query_vars['orderby'], $order, true ) ) {
			$clauses['orderby'] = "{$order[$query->query_vars['orderby']]} {$query->query_vars['order']}";
		}

		return $clauses;
	}

	/**
	 * Get Instance
	 *
	 * @return Advanced_Ads_Tracking_Admin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * After Advanced Ads core is loaded.
	 */
	public function advads_loaded() {
		// add add-on settings to plugin settings page
		add_action( 'advanced-ads-settings-init', [ $this, 'settings_init' ] );
		add_filter( 'advanced-ads-setting-tabs', [ $this, 'setting_tabs' ] );
		$notices = get_option( 'advanced-ads-notices' );

		if ( ! array_key_exists( 'tracking_ga_events_change', $notices['closed'] ?? [] ) ) {
			Advanced_Ads_Admin_Notices::get_instance()->add_to_queue( 'tracking_ga_events_change' );
		}
	}

	/**
	 * Add a notice about event names change for the Google Analytics tracking method
	 *
	 * @param array $notices existing notices.
	 *
	 * @return mixed
	 */
	public function ga_events_notice( $notices ) {
		$message = wp_kses(
			sprintf(
				__( 'With the latest update, we adjusted the event names used by Google Analytics to track ad clicks and impressions. This change aims to reduce confusion and enhance the clarity of reports. "Clicks" and "Impressions" will now be referred to as "advanced-ads-click" and "advanced-ads-impression". If you prefer the original names or wish to customize them further, use a filter. %1$sManual%2$s', 'advanced-ads-tracking' ),
				'<a href="https://wpadvancedads.com/manual/ad-tracking-with-google-analytics/?utm_source=advanced-ads&utm_medium=link&utm_campaign=notice-tracking-GA-update#Customizing_the_event_names" class="advads-manual-link" target="_blank">',
				'</a>'
			),
			[
				'a' => [
					'href'   => true,
					'target' => true,
					'class'  => true,
				],
			]
		);

		$notices['tracking_ga_events_change'] = [
			'type'   => 'info',
			'text'   => $message,
			'global' => true,
		];

		return $notices;
	}

	/**
	 *  Recreate public stats link on post duplication
	 *
	 * @param int $new_id New post id.
	 */
	public function on_duplicate_post( $new_id ) {
		$meta = get_post_meta( $new_id, 'advanced_ads_ad_options', true );
		if ( isset( $meta['tracking']['public-id'] ) ) {
			$meta['tracking']['public-id'] = wp_generate_password( 48, false );
			update_post_meta( $new_id, 'advanced_ads_ad_options', $meta );
		}
	}

	/**
	 *  (Re-)Create tables if they don't exist for some reason.
	 */
	public function check_tracking_tables() {
		global $wpdb;
		$util = Advanced_Ads_Tracking_Util::get_instance();

		if (
			empty( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $util->get_impression_table() ) ) )
			|| empty( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $util->get_click_table() ) ) )
		) {
			self::create_tables();
		}
	}

	/**
	 * Render the DB management page
	 */
	public function db_operation_page_cb() {
		if ( ! current_user_can( advanced_ads_tracking_db_cap() ) ) {
			return;
		}

		$_request = wp_unslash( $_REQUEST );
		if ( isset( $_request['delete-debug-nonce'] )
			 && false !== wp_verify_nonce( $_request['delete-debug-nonce'], 'delete-debug-log' )
			 && file_exists( Advanced_Ads_Tracking_Debugger::get_debug_file_path() ) ) {
			require_once AAT_BASE_PATH . 'admin/views/deleted-ads-form.php';

			return;
		}

		$nonce             = wp_create_nonce( 'advads_tracking_dbop' );
		$impressions_table = Advanced_Ads_Tracking_Util::get_instance()->get_impression_table();
		$clicks_table      = Advanced_Ads_Tracking_Util::get_instance()->get_click_table();
		$db_size           = Advanced_Ads_Tracking_Dbop::get_instance()->get_db_size();
		$date_format       = get_option( 'date_format' );
		$deleted_ads       = Advanced_Ads_Tracking_Dbop::get_instance()->get_deleted_ads();
		$debug_option      = get_option( Advanced_Ads_Tracking_Debugger::DEBUG_OPT, false );
		$debug_ad          = false;
		$debug_time        = [
			'hours' => 0,
			'mins'  => 0,
		];

		if ( $debug_option ) {
			$rem_time            = $debug_option['time'] + ( Advanced_Ads_Tracking_Debugger::DEBUG_HOURS * 3600 ) - time();
			$debug_time['hours'] = floor( $rem_time / 3600 );
			$debug_time['mins']  = floor( ( $rem_time - ( 3600 * $debug_time['hours'] ) ) / 60 );
		}

		if ( $debug_option && is_numeric( $debug_option['id'] ) ) {
			$debug_ad = get_post( $debug_option['id'] );
		}

		$export_periods_args = [
			'period-options' => Advanced_Ads_Tracking_Dbop::get_instance()->get_export_periods(),
		];

		$remove_periods_args = [
			'custom'         => false,
			'period-options' => Advanced_Ads_Tracking_Dbop::get_instance()->get_remove_periods(),
		];

		$ads_with_any_status = Advanced_Ads::get_instance()->get_model()->get_ads( [
			'post_status' => [ 'publish', 'future', 'draft', 'pending', Advanced_Ads_Tracking_Util::get_expired_post_status() ],
			'orderby'     => 'title',
			'order'       => 'ASC',
		] );

		$ads_published = Advanced_Ads::get_instance()->get_model()->get_ads( [
			'post_status' => [ 'publish' ],
			'orderby'     => 'title',
			'order'       => 'ASC',
		] );

		$delete_debug_link = admin_url( 'admin.php?page=advads-tracking-db-page&delete-debug-nonce=' . wp_create_nonce( 'delete-debug-log' ) );

		include_once AAT_BASE_PATH . 'admin/views/db-operations.php';

		// display current time
		$time_format = _x( 'Y-m-d H:i:s', 'current time format on stats page', 'advanced-ads-tracking' );
		$time_wp     = get_date_from_gmt( gmdate( 'Y-m-d H:i:s' ), $time_format );
		$util        = Advanced_Ads_Tracking_Util::get_instance();
		$time_db     = $util->get_date_from_db( $util->get_timestamp(), $time_format );
		$time_utc    = gmdate( $time_format );

		include_once AAT_BASE_PATH . 'admin/views/db-operations-time.php';
	}

	/**
	 *  Admin print scripts
	 */
	public function admin_print_scripts() {
		global $pagenow;
		/**
		 *  If on the ad lists page
		 */
		if ( $pagenow === 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'advanced_ads' ) {
			// target url can be long (very  very long). So display it in a tooltip like box
			?>
			<style type="text/css">
				.target-link-div {
					display: inline;
				}

				.target-link-div .target-link-text {
					display: none;
					position: absolute;
					background-color: #fff;
					border: 1px solid #d6d6d6;
					padding: 0.5em;
					max-width: 14%;
				}

				.target-link-div:hover .target-link-text {
					display: block;
				}
			</style>
			<?php
		}
		if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'advanced-ads-stats' ) {
			$gmt_offset = 3600 * 1000 * (float) get_option( 'gmt_offset' );
			?>
			<script type="text/javascript">
				/* <![CDATA[ */
				var WPGmtOffset        = <?php echo $gmt_offset; ?>;
				var _dataTableLang     = {
					processing:     '<?php esc_attr_e( 'processing...', 'advanced-ads-tracking' ); ?>',
					search:         '<?php esc_attr_e( 'search:', 'advanced-ads-tracking' ); ?>',
					lengthMenu:     '<?php esc_attr_e( 'show _MENU_ entries', 'advanced-ads-tracking' ); ?>',
					info:           '<?php esc_attr_e( 'showing _START_ to _END_ of _TOTAL_ entries', 'advanced-ads-tracking' ); ?>',
					infoEmpty:      '<?php esc_attr_e( 'no element to show', 'advanced-ads-tracking' ); ?>',
					infoFiltered:   '<?php esc_attr_e( 'filtered from _MAX_ total entries', 'advanced-ads-tracking' ); ?>',
					infoPostFix:    '',
					loadingRecords: '<?php esc_attr_e( 'Loading...', 'advanced-ads-tracking' ); ?>',
					zeroRecords:    '<?php esc_attr_e( 'no matching records found', 'advanced-ads-tracking' ); ?>',
					emptyTable:     '<?php esc_attr_e( 'no data available in table', 'advanced-ads-tracking' ); ?>',
					paginate:       {
						first:    '<?php esc_attr_e( 'first', 'advanced-ads-tracking' ); ?>',
						previous: '<?php esc_attr_e( 'previous', 'advanced-ads-tracking' ); ?>',
						next:     '<?php esc_attr_e( 'next', 'advanced-ads-tracking' ); ?>',
						last:     '<?php esc_attr_e( 'last', 'advanced-ads-tracking' ); ?>'
					},
					aria:           {
						sortAscending:  '<?php esc_attr_e( ': activate to sort column ascending', 'advanced-ads-tracking' ); ?>',
						sortDescending: '<?php esc_attr_e( ': activate to sort column descending', 'advanced-ads-tracking' ); ?>'
					}
				};
				var _dateName          = {
					shortMonths: [
						'<?php advads_e( 'Jan' ); ?>',
						'<?php advads_e( 'Feb' ); ?>',
						'<?php advads_e( 'Mar' ); ?>',
						'<?php advads_e( 'Apr' ); ?>',
						'<?php echo advads_x( 'May', 'May abbreviation' ); ?>',
						'<?php advads_e( 'Jun' ); ?>',
						'<?php advads_e( 'Jul' ); ?>',
						'<?php advads_e( 'Aug' ); ?>',
						'<?php advads_e( 'Sep' ); ?>',
						'<?php advads_e( 'Oct' ); ?>',
						'<?php advads_e( 'Nov' ); ?>',
						'<?php advads_e( 'Dec' ); ?>'
					],
					longMonths:  [
						'<?php advads_e( 'January' ); ?>',
						'<?php advads_e( 'February' ); ?>',
						'<?php advads_e( 'March' ); ?>',
						'<?php advads_e( 'April' ); ?>',
						'<?php advads_e( 'May' ); ?>',
						'<?php advads_e( 'June' ); ?>',
						'<?php advads_e( 'July' ); ?>',
						'<?php advads_e( 'August' ); ?>',
						'<?php advads_e( 'September' ); ?>',
						'<?php advads_e( 'October' ); ?>',
						'<?php advads_e( 'November' ); ?>',
						'<?php advads_e( 'December' ); ?>'
					],
					shortDays:   [
						'<?php advads_e( 'Sun' ); ?>',
						'<?php advads_e( 'Mon' ); ?>',
						'<?php advads_e( 'Tue' ); ?>',
						'<?php advads_e( 'Wed' ); ?>',
						'<?php advads_e( 'Thu' ); ?>',
						'<?php advads_e( 'Fri' ); ?>',
						'<?php advads_e( 'Sat' ); ?>'
					],
					longDays:    [
						'<?php advads_e( 'Sunday' ); ?>',
						'<?php advads_e( 'Monday' ); ?>',
						'<?php advads_e( 'Tuesday' ); ?>',
						'<?php advads_e( 'Wednesday' ); ?>',
						'<?php advads_e( 'Thursday' ); ?>',
						'<?php advads_e( 'Friday' ); ?>',
						'<?php advads_e( 'Saturday' ); ?>'
					]
				};
				var adminUrl           = '<?php echo admin_url(); ?>';
				var wpDateFormat       = '<?php echo str_replace( '\\', '\\\\', get_option( 'date_format', 'Y/m/d' ) ); ?>';
				var wpDateTimeZoneName = '<?php echo esc_html( Advanced_Ads_Tracking_Util::get_timezone_name() ); ?>';
				/* ]]> */
			</script>
			<?php
		}
	}

	/**
	 *  Get the url to the admin stats for the last 30 days for a given ad ID
	 *
	 * @param int $id ad id.
	 *
	 * @return string
	 */
	public static function admin_30days_stats_url( $id ) {
		$today       = time();
		$wp_timezone = Advanced_Ads_Tracking_Util::get_wp_timezone();
		// 30 days ago.
		$stat_from = date_create( '@' . ( $today - ( 29 * 24 * 60 * 60 ) ), $wp_timezone );
		$stat_to   = date_create( '@' . $today, $wp_timezone );
		$stat_url  = 'page=advanced-ads-stats&advads-stats[period]=custom&advads-stats[groupby]=day&advads-stats[ads]=all-ads';
		$stat_url  .= '&advads-stats[from]=' . $stat_from->format( 'm/d/Y' );
		$stat_url  .= '&advads-stats[to]=' . $stat_to->format( 'm/d/Y' );
		$stat_url  .= '&advads-stats-filter[]=' . $id;

		return admin_url( 'admin.php?' . $stat_url );
	}

	/**
	 *  Add custom column.
	 *
	 * @param array $columns Columns array.
	 *
	 * @return array
	 */
	public function add_column( $columns ) {
		if ( $this->plugin->get_tracking_method() !== 'ga' ) {
			$columns['ad_stats']  = esc_attr__( 'Statistics', 'advanced-ads-tracking' );
			$columns['ad_imprs']  = esc_attr__( 'Impressions', 'advanced-ads-tracking' );
			$columns['ad_clicks'] = esc_attr__( 'Clicks', 'advanced-ads-tracking' );
			$columns['ad_ctrs']   = esc_attr__( 'CTR', 'advanced-ads-tracking' );
		}

		return $columns;
	}

	/**
	 * Add `ad_stats` to column white list.
	 *
	 * @param array $list White list array.
	 *
	 * @return array
	 */
	public function column_white_list( $list ) {
		if ( $this->plugin->get_tracking_method() !== 'ga' ) {
			$list[] = 'ad_stats';
			$list[] = 'ad_imprs';
			$list[] = 'ad_clicks';
			$list[] = 'ad_ctrs';
		}

		return $list;
	}

	/**
	 *  Draw the content of stat column in ads list.
	 *
	 * @param string $column_name Current column name.
	 * @param int    $ad_id       The current ad id.
	 *
	 * @noinspection PhpUnusedParameterInspection -- $ad_id is used in template.
	 */
	public function ad_list_columns_content( $column_name, $ad_id ) {
		switch ( $column_name ) {
			case 'ad_stats':
				include AAT_BASE_PATH . 'admin/views/ad-list-stats-column.php';
				break;
			case 'ad_imprs':
				$post = get_post();
				if ( ! $post || ! $post->impressions ) {
					return;
				}
				echo wp_kses_post( $post->impressions );
				break;
			case 'ad_clicks':
				$post = get_post();
				if ( ! $post || ! $post->clicks ) {
					return;
				}
				echo wp_kses_post( $post->clicks );
				break;
			case 'ad_ctrs':
				$post = get_post();
				if ( ! $post ) {
					return;
				}
				echo $post->ctr ? esc_html( number_format_i18n( 100 * $post->ctr, 2 ) ) . '%' : '';
				break;
			default:
		}
	}

	/**
	 * Reschedule cron job if time zone updated in WP.
	 *
	 * @param string $new New timezone or GMT offset string.
	 * @param string $old Old timezone or GMT offset string.
	 *
	 * @return string
	 */
	public function timezone_changed( $new, $old ) {
		// no change
		if ( $new === $old || empty( $new ) ) {
			return $new;
		}

		$timezone = $new;
		if ( preg_match( '/^\d/', $new ) ) {
			$timezone = '+' . $new;
		}
		try {
			$_00h15 = ( new DateTime( 'tomorrow 00:15', new DateTimeZone( $timezone ) ) )->format( 'U' );
		} catch ( Exception $e ) {
			return $new;
		}

		// reschedule reports to 00:15 local time.
		foreach ( self::CRONJOBS as $cron ) {
			if ( wp_get_schedule( $cron ) ) {
				wp_clear_scheduled_hook( $cron );
				wp_schedule_event( $_00h15, 'daily', $cron );
			}
		}

		return $new;
	}

	/**
	 *  Check if cron jobs are registered correctly.
	 */
	private function check_cron_schedule() {
		try {
			$_00h15 = ( new DateTime( 'tomorrow 00:15', Advanced_Ads_Tracking_Util::get_wp_timezone() ) )->getTimestamp();
		} catch ( Exception $e ) {
			return;
		}

		foreach ( self::CRONJOBS as $cron ) {
			$scheduled = wp_get_schedule( $cron );
			// if there is a registered cron job, but ga is the chosen tracking method, remove cron job.
			if ( $scheduled && $this->plugin->get_tracking_method() === 'ga' ) {
				wp_clear_scheduled_hook( $cron );
				continue;
			}

			// if there is no cron job, register the cron job.
			if ( ! $scheduled ) {
				wp_schedule_event( $_00h15, 'daily', $cron );
			}
		}
	}

	/**
	 * Show warning if Advanced Ads js is not activated
	 */
	public static function missing_plugin_notice() {
		$plugin = 'advanced-ads/advanced-ads.php';
		if ( array_key_exists( $plugin, get_plugins() ) ) {
			$url          = add_query_arg( [
				'action' => 'activate',
				'plugin' => $plugin,
			], self_admin_url( 'plugins.php' ) );
			$url          = wp_nonce_url( $url, 'activate-plugin_' . $plugin );
			$button_label = esc_html__( 'Activate Now', 'advanced-ads-tracking' );
		} else {
			$url          = add_query_arg( [
				'action' => 'install-plugin',
				'plugin' => 'advanced-ads',
			], self_admin_url( 'update.php' ) );
			$url          = wp_nonce_url( $url, 'install-plugin_advanced-ads' );
			$button_label = esc_html__( 'Install Now', 'advanced-ads-tracking' );
		}

		printf(
			'<div class="error"><p>%s %s</p></div>',
			sprintf(
			/* Translators: 1: this plugins' name, 2: link to main plugin */
				esc_html__( '%1$s requires the %2$s plugin to be installed and activated on your site.', 'advanced-ads-tracking' ),
				'<strong>Advanced Ads – Tracking</strong>',
				'<strong><a href="https://wpadvancedads.com" target="_blank">Advanced Ads</a></strong>'
			),
			sprintf( '<a class="button button-primary" href="%s">%s</a>', esc_url( $url ), esc_html( $button_label ) )
		);
	}

	/**
	 * Register and enqueue admin-specific scripts and stylesheets.
	 *
	 * @param string $hook The current admin page.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts( $hook ) {
		$screen = get_current_screen();

		if ( 'edit-advanced_ads' === $screen->id ) {
			wp_add_inline_style( 'advanced-ads-admin', '.wp-list-table th.sorted:last-of-type a,.wp-list-table th.sortable:last-of-type a {display: inline-block}' );
		}

		// ad edit screen
		if ( ! is_null( $screen ) && Advanced_Ads::POST_TYPE_SLUG === $screen->id ) {
			// jplot files
			wp_enqueue_script( 'jplot-js', plugins_url( 'assets/jqplot/jquery.jqplot.min.js', __FILE__ ), [ 'jquery' ], AAT_VERSION );
			wp_enqueue_script( 'jplot-date-js', plugins_url( 'assets/jqplot/plugins/jqplot.dateAxisRenderer.min.js', __FILE__ ), [ 'jplot-js' ], AAT_VERSION );
			wp_enqueue_script( 'jplot-highlighter-js', plugins_url( 'assets/jqplot/plugins/jqplot.highlighter.min.js', __FILE__ ), [ 'jplot-js' ], AAT_VERSION );
			wp_enqueue_script( 'jplot-canvasAxisLabelRenderer-js', plugins_url( 'assets/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js', __FILE__ ), [ 'jplot-js' ], AAT_VERSION );
			wp_enqueue_script( 'jplot-canvasTextRenderer-js', plugins_url( 'assets/jqplot/plugins/jqplot.canvasTextRenderer.min.js', __FILE__ ), [ 'jplot-js' ], AAT_VERSION );
			wp_enqueue_script( 'jplot-cursor-js', plugins_url( 'assets/jqplot/plugins/jqplot.cursor.min.js', __FILE__ ), [ 'jplot-js' ], AAT_VERSION );
			wp_enqueue_style( 'jplot-css', plugins_url( 'assets/jqplot/jquery.jqplot.min.css', __FILE__ ), AAT_VERSION );

			wp_register_script( AAT_SLUG . '-admin-scripts', plugins_url( 'assets/js/script.js', __FILE__ ), [ 'jquery', 'jplot-cursor-js' ], AAT_VERSION );

			$inline_script = 'var advads_tracking_clickable_ad_types = ' . wp_json_encode( Advanced_Ads_Tracking_Plugin::get_clickable_types() ) . ';';
			wp_add_inline_script( AAT_SLUG . '-admin-scripts', $inline_script, 'before' );

			$trackingStatsLocale = [
				'impressions' => __( 'impressions', 'advanced-ads-tracking' ),
				'clicks'      => __( 'clicks', 'advanced-ads-tracking' ),
			];
			wp_localize_script( AAT_SLUG . '-admin-scripts', 'advadsStatsLocale', $trackingStatsLocale );
			wp_enqueue_script( AAT_SLUG . '-admin-scripts' );
		}

		// admin stats page
		if ( $this->stat_page_hook === $hook ) {
			wp_enqueue_media();
			wp_enqueue_style( AAT_SLUG . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), [], AAT_VERSION );
			// add date picker from WP core
			wp_enqueue_script( 'jquery-ui-datepicker', null, [], 0, true );

			// jplot files
			wp_enqueue_script( 'jplot-js', plugins_url( 'assets/jqplot/jquery.jqplot.min.js', __FILE__ ), [ 'jquery' ], 0, true );
			wp_enqueue_script( 'jplot-date-js', plugins_url( 'assets/jqplot/plugins/jqplot.dateAxisRenderer.min.js', __FILE__ ), [ 'jplot-js' ], 0, true );
			wp_enqueue_script( 'jplot-highlighter-js', plugins_url( 'assets/jqplot/plugins/jqplot.highlighter.min.js', __FILE__ ), [ 'jplot-js' ], 0, true );
			wp_enqueue_script( 'jplot-canvasAxisLabelRenderer-js', plugins_url( 'assets/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js', __FILE__ ), [ 'jplot-js' ], AAT_VERSION );
			wp_enqueue_script( 'jplot-canvasTextRenderer-js', plugins_url( 'assets/jqplot/plugins/jqplot.canvasTextRenderer.min.js', __FILE__ ), [ 'jplot-js' ], AAT_VERSION );
			wp_enqueue_script( 'jplot-cursor-js', plugins_url( 'assets/jqplot/plugins/jqplot.cursor.min.js', __FILE__ ), [ 'jplot-js' ], 0, true );
			wp_enqueue_style( 'jplot-css', plugins_url( 'assets/jqplot/jquery.jqplot.min.css', __FILE__ ), null, 0 );
			wp_enqueue_style( 'dtable', AAT_BASE_URL . 'admin/assets/datatables/css/datatables.min.css', [], 0 );

			wp_enqueue_script( 'dtable', AAT_BASE_URL . 'admin/assets/datatables/js/datatables.min.js', [ 'jquery' ], null, true );
			wp_enqueue_script( 'date-format', AAT_BASE_URL . 'admin/assets/date.format/date.format.min.js', [ 'jquery' ], null, true );

			wp_register_script( 'advads-media-frame', AAT_BASE_URL . 'admin/assets/js/wp-media-frame.js', [ 'jquery' ], null, true );
			$media_locale = [
				'selectFile'      => esc_attr__( 'Select file', 'advanced-ads-tracking' ),
				'button'          => advads__( 'select' ),
				'invalidFileType' => esc_attr__( 'invalid file type', 'advanced-ads-tracking' ),
			];
			wp_localize_script( 'advads-media-frame', 'advadsMediaFrameLocale', $media_locale );
			wp_enqueue_script( 'advads-media-frame' );

			wp_register_script( 'advads-stats', AAT_BASE_URL . 'admin/assets/js/stats.js', [ 'jplot-cursor-js', 'dtable', 'jquery-ui-autocomplete', 'date-format', 'advads-media-frame' ], null, true );
			$stats_translations = [
				'clicks'              => esc_attr__( 'clicks', 'advanced-ads-tracking' ),
				'clicksFor'           => esc_attr__( 'clicks for "%s"', 'advanced-ads-tracking' ),
				'Clicks'              => esc_attr__( 'Clicks', 'advanced-ads-tracking' ),
				'impressions'         => esc_attr__( 'impressions', 'advanced-ads-tracking' ),
				'impressionsFor'      => esc_attr__( 'impressions for "%s"', 'advanced-ads-tracking' ),
				'Impressions'         => esc_attr__( 'Impressions', 'advanced-ads-tracking' ),
				'prevDay'             => esc_attr__( 'previous day', 'advanced-ads-tracking' ),
				'nextDay'             => esc_attr__( 'next day', 'advanced-ads-tracking' ),
				'prevMonth'           => esc_attr__( 'previous month', 'advanced-ads-tracking' ),
				'nextMonth'           => esc_attr__( 'next month', 'advanced-ads-tracking' ),
				'prevYear'            => esc_attr__( 'previous year', 'advanced-ads-tracking' ),
				'nextYear'            => esc_attr__( 'next year', 'advanced-ads-tracking' ),
				'prev%dDays'          => esc_attr__( 'previous %d days', 'advanced-ads-tracking' ),
				'next%dDays'          => esc_attr__( 'next %d days', 'advanced-ads-tracking' ),
				'clicksFromTo'        => esc_attr__( 'clicks from %1$s to %2$s', 'advanced-ads-tracking' ),
				'imprFromTo'          => esc_attr__( 'impressions from %1$s to %2$s', 'advanced-ads-tracking' ),
				'noDataFor'           => esc_attr__( 'There is no data for %1$s to %2$s', 'advanced-ads-tracking' ),
				'ad'                  => esc_attr__( 'ad', 'advanced-ads-tracking' ),
				'ctr'                 => esc_attr__( 'CTR', 'advanced-ads-tracking' ),
				'deletedAds'          => esc_attr__( 'deleted ads', 'advanced-ads-tracking' ),
				'date'                => esc_attr__( 'date', 'advanced-ads-tracking' ),
				'aTob'                => esc_attr__( '%1$s to %2$s', 'advanced-ads-tracking' ),
				'total'               => esc_attr__( 'total', 'advanced-ads-tracking' ),
				'noRecords'           => esc_attr__( 'There is no record for this period :(', 'advanced-ads-tracking' ),
				'periodNotConsistent' => esc_attr__( 'The period you have chosen is not consistent', 'advanced-ads-tracking' ),
				'customPeriodMissing' => esc_attr__( 'Some fields are missing for the custom period', 'advanced-ads-tracking' ),
				'invalidRecord'       => esc_attr__( 'One or more invalid records have been found in the database', 'advanced-ads-tracking' ),
				'noFile'              => esc_attr__( 'no file selected', 'advanced-ads-tracking' ),
				'group'               => esc_attr__( 'group', 'advanced-ads-tracking' ),
			];
			wp_localize_script( 'advads-stats', 'statsLocale', $stats_translations );
			wp_enqueue_script( 'advads-stats' );
			wp_enqueue_script( AAT_SLUG . '-period', AAT_BASE_URL . 'admin/assets/js/period-select.js', [ 'jquery', 'jquery-ui-datepicker' ], null, true );
			wp_register_script( 'advads-stats-file', AAT_BASE_URL . 'admin/assets/js/stats-from-file.js', [ 'advads-stats', 'advads-media-frame', AAT_SLUG . '-period' ], null, true );
			$stats_file_locale = [
				'unknownError'        => esc_attr__( 'An unexpected error occurred.', 'advanced-ads-tracking' ),
				'statsFrom'           => esc_attr__( 'stats from %1$s to %2$s', 'advanced-ads-tracking' ),
				'periodNotConsistent' => esc_attr__( 'The period you have chosen is not consistent', 'advanced-ads-tracking' ),
				'statsNotFoundInFile' => __( 'No stats found in file', 'advanced-ads-tracking' ),
				'prev%dDays'          => esc_attr__( 'previous %d days', 'advanced-ads-tracking' ),
				'next%dDays'          => esc_attr__( 'next %d days', 'advanced-ads-tracking' ),
				'prevMonth'           => esc_attr__( 'previous month', 'advanced-ads-tracking' ),
				'nextMonth'           => esc_attr__( 'next month', 'advanced-ads-tracking' ),
			];
			wp_localize_script( 'advads-stats-file', 'statsFileLocale', $stats_file_locale );
			wp_enqueue_script( 'advads-stats-file' );

			// jQuery ui smoothness style 1.11.4.
			wp_enqueue_style( 'aatracking-jquery-ui-styles', plugins_url( 'assets/jquery-ui/jquery-ui.min.css', __FILE__ ), [], '1.11.4' );
		}

		// settings page
		if ( ! is_null( $screen ) && $screen->id === $this->settings_page_id ) {
			wp_register_script( AAT_SLUG . 'settings', AAT_BASE_URL . 'admin/assets/js/settings.js', [ 'jquery' ], null, true );
			$tracking_locale = [
				'serverFail'    => esc_attr__( 'The server failed to respond to your request. Link structure not available.', 'advanced-ads-tracking' ),
				'unknownError'  => esc_attr__( 'An unexpected error occurred. Link structure not available.', 'advanced-ads-tracking' ),
				'linkAvailable' => esc_attr__( 'Link structure available.', 'advanced-ads-tracking' ),
				'emailSent'     => esc_attr__( 'email sent', 'advanced-ads-tracking' ),
				'emailNotSent'  => esc_attr__( 'email not sent. Please check your server configuration', 'advanced-ads-tracking' ),
			];
			wp_localize_script( AAT_SLUG . 'settings', 'trackingSettingsLocale', $tracking_locale );
			wp_enqueue_script( AAT_SLUG . 'settings' );

			wp_register_style( AAT_SLUG . 'settings-css', AAT_BASE_URL . 'admin/assets/css/settings.css', [], AAT_VERSION );
			wp_enqueue_style( AAT_SLUG . 'settings-css' );
		}

		// db operations page
		if ( current_user_can( advanced_ads_tracking_db_cap() ) && isset( $_GET['page'] ) && $_GET['page'] === 'advads-tracking-db-page' ) {
			wp_enqueue_script( AAT_SLUG . '-period', AAT_BASE_URL . 'admin/assets/js/period-select.js', [ 'jquery', 'jquery-ui-datepicker' ], null, true );
			wp_register_script( AAT_SLUG . 'dbop', AAT_BASE_URL . 'admin/assets/js/db-operations.js', [ AAT_SLUG . '-period', 'wp-util' ], '1.25.0', true );
			$dbop_locale = [
				'serverFail'      => esc_attr__( 'The server failed to respond to your request.', 'advanced-ads-tracking' ),
				'unknownError'    => esc_attr__( 'An unexpected error occurred.', 'advanced-ads-tracking' ),
				'resetNoAd'       => esc_attr__( 'Please choose an ad', 'advanced-ads-tracking' ),
				'resetConfirm'    => esc_attr__( 'Are you sure you want to reset the stats for', 'advanced-ads-tracking' ),
				'SQLFailure'      => esc_attr__( 'The plugin was not able to perform some requests on the database', 'advanced-ads-tracking' ),
				'optimizeFailure' => esc_attr__( 'Data were compressed but the tracking tables can not be optimized automatically. Please ask the server&#39;s admin on how to proceed.', 'advanced-ads-tracking' ),
			];
			wp_localize_script( AAT_SLUG . 'dbop', 'trackingDbopLocale', $dbop_locale );
			wp_enqueue_script( AAT_SLUG . 'dbop' );

			// jQuery ui smoothness style 1.11.4.
			wp_enqueue_style( 'aatracking-jquery-ui-styles', plugins_url( 'assets/jquery-ui/jquery-ui.min.css', __FILE__ ), [], '1.11.4' );
		}
	}

	/**
	 * Add meta box for stata
	 *
	 * @since 1.2.6
	 */
	public function add_meta_box() {
		add_meta_box(
			'tracking-ads-box',
			esc_attr__( 'Statistics', 'advanced-ads-tracking' ),
			[ $this, 'render_metabox' ],
			Advanced_Ads::POST_TYPE_SLUG,
			'normal',
			'low'
		);

		add_filter( 'advanced-ads-unhide-meta-boxes', [ $this, 'unhide_metabox' ] );
	}

	/**
	 * Render options for tracking meta box
	 *
	 * @since 1.2.6
	 */
	public function render_metabox() {
		global $post;
		$ad      = new Advanced_Ads_Ad( $post->ID );
		$options = $ad->options();

		$ad_options = isset( $options['tracking'] ) ? $options['tracking'] : [];

		// limiter options.
		$impression_limit = isset( $ad_options['impression_limit'] ) ? (int) $ad_options['impression_limit'] : 0;
		$click_limit      = isset( $ad_options['click_limit'] ) ? (int) $ad_options['click_limit'] : 0;
		$use_clicks       = in_array( $ad->type, Advanced_Ads_Tracking_Plugin::get_clickable_types(), true );
		$sums             = Advanced_Ads_Tracking_Util::get_instance()->get_sums_for_ad( $ad->id, $use_clicks );
		$limiter          = new Advanced_Ads_Tracking_Limiter( $post->ID );

		// public stats
		$public      = new Advanced_Ads_Tracking_Public_Stats( $post->ID );
		$public_id   = $public->get_id();
		$public_link = $public->get_url();
		$public_name = $public->get_name();

		$report_recip  = isset( $ad_options['report-recip'] ) ? $ad_options['report-recip'] : '';
		$report_period = (
			isset( $ad_options['report-period'] ) &&
			in_array( $ad_options['report-period'], [ 'last30days', 'lastmonth', 'last12months' ] )
		) ? $ad_options['report-period'] : 'last30days';

		$report_frequency = (
			isset( $ad_options['report-frequency'] ) &&
			in_array( $ad_options['report-frequency'], [ 'never', 'daily', 'weekly', 'monthly' ] )
		) ? $ad_options['report-frequency'] : 'never';

		$billing_email = false;

		$order_id = get_post_meta( $post->ID, 'advanced_ads_selling_order', true );
		if ( $order_id ) {
			// if ad was sold via WooCommerce
			if ( function_exists( 'wc_get_order' ) ) {
				$order = wc_get_order( $order_id );
				global $woocommerce;
				if ( isset( $woocommerce->version ) && version_compare( $woocommerce->version, '3.0', '>=' ) ) {
					$billing_email = $order->get_billing_email();
				} else {
					$billing_email = $order->billing_email;
				}
			}
		}

		$warnings = [];

		// add warning if we are tracking with Analytics
		if ( $this->plugin->get_tracking_method() === 'ga' ) {
			$warnings[] = [
				'text'  => __( '<a href="https://wpadvancedads.com/share-custom-reports-google-analytics/?utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-ad-reports-google-analytics" target="_blank">How to share Google Analytics ad reports with your customers.</a>', 'advanced-ads-tracking' ),
				'class' => 'advads-notice-inline advads-idea',
			];
		} elseif ( $ad->type === 'adsense' ) {
			$warnings[] = [
				'text'  => sprintf(
					'%s <a href="%smanual/tracking-issues?utm_source=advanced-ads&utm_medium=link&utm_campaign=ad-edit-adsense#Different_numbers_compared_to_AdSense_and_other_ad_networks" target="_blank">%s</a>',
					__( 'The number of impressions and clicks can vary from those in your AdSense account.', 'advanced-ads-tracking' ),
					esc_url( ADVADS_URL ),
					__( 'Manual', 'advanced-ads-tracking' )
				),
				'class' => 'advads-notice-inline advads-idea',
			];
		}

		require AAT_BASE_PATH . 'admin/views/metabox.php';
	}

	/**
	 * Add tracking meta box to list of meta boxes to unhide.
	 *
	 * @param array $meta_boxes array of meta box ids to not be hidden.
	 *
	 * @return array
	 */
	public function unhide_metabox( $meta_boxes ) {
		$meta_boxes[] = 'tracking-ads-box';

		return $meta_boxes;
	}

	/**
	 * Add settings to settings page
	 *
	 * @since 1.0.0
	 */
	public function settings_init() {

		// don’t initiate if main plugin not loaded
		if ( ! class_exists( 'Advanced_Ads_Admin', false ) ) {
			return;
		}

		// get settings page hook
		$hook = $this->settings_page_hook;

		register_setting( $this->plugin->options_slug, $this->plugin->options_slug, [ $this, 'sanitize_settings' ] );

		/**
		 * Allow Ad Admin to save tracking options.
		 *
		 * @param array $settings Array with allowed options.
		 *
		 * @return array
		 */
		add_filter( 'advanced-ads-ad-admin-options', function( $options ) {
			$options[] = $this->plugin->options_slug;

			return $options;
		} );

		// add tracking settings section
		add_settings_section(
			'advanced_ads_tracking_setting_section',
			__( 'Tracking', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_section_callback' ],
			$hook
		);

		// add settings section for email reports
		add_settings_section(
			( $reports_section = 'advanced_ads_tracking_reports_setting_section' ),
			__( 'Email Reports', 'advanced-ads-tracking' ),
			[ $this, 'render_reports_settings_section_callback' ],
			$hook
		);

		// Add advanced settings section.
		add_settings_section(
			( $advanced_section = 'advanced_ads_tracking_advanced_setting_section' ),
			__( 'Advanced', 'advanced-ads-tracking' ),
			'__return_empty_string',
			$hook
		);

		// add license key field to license section
		add_settings_field(
			'tracking-license',
			__( 'Tracking', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_license_callback' ],
			'advanced-ads-settings-license-page',
			'advanced_ads_settings_license_section'
		);

		// add setting fields
		add_settings_field(
			'tracking-method',
			__( 'Choose tracking method', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_tracking_method_callback' ],
			$hook,
			'advanced_ads_tracking_setting_section'
		);

		// add GA tracking ID field
		add_settings_field(
			'ga-settings',
			__( 'Google Analytics', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_ga_callback' ],
			$hook,
			'advanced_ads_tracking_setting_section',
			[
				'class' => $this->get_ga_classes(),
			]
		);

		add_settings_field(
			'tracking-everything',
			__( 'What to track by default', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_tracking_everything_callback' ],
			$hook,
			'advanced_ads_tracking_setting_section'
		);

		// Tracking by user role
		add_settings_field(
			'tracking-user-role',
			__( 'Disable tracking for user roles', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_user_role' ],
			$hook,
			'advanced_ads_tracking_setting_section'
		);

		add_settings_field(
			'link-nofollow',
			__( 'Add “nofollow”', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_link_nofollow_callback' ],
			$hook,
			'advanced_ads_tracking_setting_section'
		);

		add_settings_field(
			'link-sponsored',
			__( 'Add “sponsored”', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_link_sponsored_callback' ],
			$hook,
			'advanced_ads_tracking_setting_section'
		);

		if ( $this->show_stats() ) {
			// open database management field.
			add_settings_field(
				'tracking-db-mgmt',
				__( 'Database Management', 'advanced-ads-tracking' ),
				[ $this, 'render_settings_db_mgmt_callback' ],
				$hook,
				$advanced_section
			);
		}

		add_settings_field(
			'link-base',
			__( 'Click-link base', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_link_base_callback' ],
			$hook,
			$advanced_section
		);

		// link base for public report
		add_settings_field(
			'public-stat',
			__( 'Link base for public reports', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_public_stats' ],
			$hook,
			'advanced_ads_tracking_setting_section'
		);

		$reports_args = [
			'class' => $this->plugin->get_tracking_method() === 'ga' ? 'hidden advads-is-hidden' : '',
		];

		// tracking for bots.
		add_settings_field(
			'tracking-bots',
			__( 'Track bots', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_track_bots' ],
			$hook,
			$advanced_section,
			$reports_args
		);

		// add setting fields
		add_settings_field(
			'tracking-uninstall',
			__( 'Delete data on uninstall', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_tracking_uninstall_callback' ],
			$hook,
			$advanced_section
		);

		// scheduled reports recipients
		add_settings_field(
			'email-report-recipient',
			__( 'Recipients', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_email_report_recip' ],
			$hook,
			$reports_section,
			$reports_args
		);

		// scheduled reports frequency
		add_settings_field(
			'email-report-frequency',
			__( 'Frequency', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_email_freq' ],
			$hook,
			$reports_section,
			$reports_args
		);

		// scheduled reports stats period
		add_settings_field(
			'email-report-period',
			__( 'Statistics period', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_email_stats_period' ],
			$hook,
			$reports_section,
			$reports_args
		);

		// scheduled reports sender name
		add_settings_field(
			'email-report-sender-name',
			__( 'From name', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_email_sender_name' ],
			$hook,
			$reports_section,
			$reports_args
		);

		// scheduled reports sender address
		add_settings_field(
			'email-report-sender-address',
			__( 'From address', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_email_sender_address' ],
			$hook,
			$reports_section,
			$reports_args
		);

		// scheduled reports subject
		add_settings_field(
			'email-report-subject',
			__( 'Email subject', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_email_subject' ],
			$hook,
			$reports_section,
			$reports_args
		);

		// scheduled reports test email
		add_settings_field(
			'email-report-test-email',
			__( 'Send test email', 'advanced-ads-tracking' ),
			[ $this, 'render_settings_email_test_email' ],
			$hook,
			$reports_section,
			$reports_args
		);
	}

	/**
	 * Sanitize plugin settings
	 *
	 * @param array $options All options.
	 *
	 * @return array
	 * @since 1.2.6
	 */
	public function sanitize_settings( $options ) {
		if ( isset( $options['linkbase'] ) ) {
			$options['linkbase'] = sanitize_title( $options['linkbase'] );
		}

		if ( ! empty( $options['public-stats-slug'] ) ) {
			$options['public-stats-slug'] = stripslashes( $options['public-stats-slug'] );
		}

		// email reports addresses
		if ( ! empty( $options['email-addresses'] ) ) {
			$options['email-addresses'] = implode( ',', array_filter( array_map(
				'sanitize_email',
				explode( ',', stripslashes( $options['email-addresses'] ) )
			) ) );
		} else {
			$options['email-addresses'] = '';
		}

		// email sender address
		if ( ! empty( $options['email-sender-address'] ) ) {
			$sender_adr                      = stripslashes( $options['email-sender-address'] );
			$options['email-sender-address'] = sanitize_email( $sender_adr );
			if ( ! $options['email-sender-address'] ) {
				$options['email-sender-address'] = 'noreply@' . wp_parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
			}
		} else {
			$options['email-sender-address'] = 'noreply@' . wp_parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
		}

		// email sender name
		if ( ! empty( $options['email-sender-name'] ) ) {
			$options['email-sender-name'] = stripslashes( $options['email-sender-name'] );
		} else {
			$options['email-sender-name'] = get_bloginfo( 'name' );
		}

		// email subject
		if ( ! empty( $options['email-subject'] ) ) {
			$options['email-subject'] = stripslashes( $options['email-subject'] );
		} else {
			$options['email-subject'] = __( 'Ads Statistics', 'advanced-ads-tracking' );
		}

		// sanitize Analytics UID
		if ( isset( $options['ga-UID'] ) ) {
			$ga_ids = array_filter( array_map( function( $ga_id ) {
				return trim( $ga_id, ' /][)(#' );
			}, explode( ',', $options['ga-UID'] ) ) );

			$options['ga-UID'] = implode( ', ', $ga_ids );
		}

		// remove options on uninstall
		if ( isset( $options['uninstall'] ) ) {
			$options['uninstall'] = '1';
		}

		return $options;
	}

	/**
	 * Add tracking options to ad edit page
	 *
	 * @param Advanced_Ads_Ad $ad Ad object.
	 */
	public function render_ad_tracking_options( Advanced_Ads_Ad $ad ) {
		if ( empty( $ad->id ) ) {
			return;
		}

		$options    = $ad->options();
		$ad_options = isset( $options['tracking'] ) ? $options['tracking'] : [];

		$enabled              = isset( $ad_options['enabled'] ) ? $ad_options['enabled'] : 'default';
		$target               = isset( $ad_options['target'] ) ? $ad_options['target'] : 'default';
		$nofollow             = isset( $ad_options['nofollow'] ) ? $ad_options['nofollow'] : 'default';
		$sponsored            = isset( $ad_options['sponsored'] ) ? $ad_options['sponsored'] : 'default';
		$cloaking_enabled     = (bool) $ad->options( 'tracking.cloaking' );
		$cloaking_cb_disabled = has_filter( 'advanced-ads-ad-option-tracking.cloaking', '__return_true' ) || has_filter( 'advanced-ads-ad-option-tracking.cloaking', '__return_false' );
		$link                 = Advanced_Ads_Tracking_Util::get_link( $ad );

		$tracking_choices = [
			'default'  => __( 'default', 'advanced-ads-tracking' ),
			'disabled' => __( 'disabled', 'advanced-ads-tracking' ),
		];

		if ( in_array( $ad->type, Advanced_Ads_Tracking_Plugin::get_clickable_types(), true ) ) {
			$tracking_choices['clicks']      = __( 'clicks only', 'advanced-ads-tracking' );
			$tracking_choices['impressions'] = __( 'impressions only', 'advanced-ads-tracking' );
			$tracking_choices['enabled']     = __( 'impressions & clicks', 'advanced-ads-tracking' );
		} else {
			$tracking_choices['enabled'] = __( 'enabled', 'advanced-ads-tracking' );
		}

		include AAT_BASE_PATH . 'admin/views/ad_tracking_options.php';
	}

	/**
	 * Show AdSense ad specific notices in parameters box
	 *
	 * @param array   $notices Notices array.
	 * @param array   $box     The box data.
	 * @param WP_Post $post    WP Post Object.
	 *
	 * @return mixed
	 */
	public function ad_notices( $notices, $box, $post ) {
		$ad          = new Advanced_Ads_Ad( $post->ID );
		$ad_options  = $ad->options();
		$ad_tracking = isset( $ad_options['tracking']['enabled'] ) ? $ad_options['tracking']['enabled'] : 'default';
		$options     = $this->plugin->options();
		$method      = isset( $options['everything'] ) ? $options['everything'] : 'true';
		switch ( $box['id'] ) {
			case 'ad-parameters-box':
				// general check for following conditions
				$content_contains_a = strpos( $ad->content, 'href=' );
				$link               = Advanced_Ads_Tracking_Util::get_link( $ad );

				// Add warning if %link% parameter is in editor, but url field is empty.
				$notices[] = [
					'text'  => __( 'Use the <code>URL field</code> or remove <code>%link%</code> parameter from your editor.', 'advanced-ads-tracking' ),
					'class' => 'advads-ad-notice-tracking-missing-url-field error hidden',
				];

				// Add warning if url exists, but %link% parameter is not in editor.
				$notices[] = [
					'text'  => __( 'The link found in the ad code is used as the target URL. If you replace it with the <code>%link%</code> placeholder, the ad will be linked to the address specified in the Target URL field.', 'advanced-ads-tracking' ),
					'class' => 'advads-ad-notice-tracking-link-placeholder-missing error hidden',
				];

				// notice, if ad can not open in new window due to existing link attribute and does not have such code in it already
				if ( $content_contains_a
					 && ! strpos( $ad->content, '_blank' )
					 && Advanced_Ads_Tracking_Util::get_target( $ad )
				) {
					$notices[] = [
						'text'  => __( 'Add <code>target="_blank"</code> to the ad code in order to open it in a new window. E.g. <code>&lt;a href="%link%" target="_blank"&gt;</code>', 'advanced-ads-tracking' ),
						'class' => 'advads-ad-notice-tracking-new-window',
					];
				}

				break;
			case 'tracking-ads-box':
				// die();
				break;
		}

		return $notices;
	}

	/**
	 * Save ad tracking options
	 *
	 * @param array $options Options array.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function save_options( $options ) {
		// phpcs:disable WordPress.Security.NonceVerification
		$options['tracking']['enabled']          = isset( $_POST['advanced_ad']['tracking']['enabled'] ) ? $_POST['advanced_ad']['tracking']['enabled'] : 'default';
		$options['url']                          = isset( $_POST['advanced_ad']['url'] ) ? trim( $_POST['advanced_ad']['url'] ) : '';
		$options['tracking']['cloaking']         = isset( $_POST['advanced_ad']['tracking']['cloaking'] );
		$options['tracking']['impression_limit'] = isset( $_POST['advanced_ad']['tracking']['impression_limit'] ) ? (int) $_POST['advanced_ad']['tracking']['impression_limit'] : '';
		$options['tracking']['click_limit']      = isset( $_POST['advanced_ad']['tracking']['click_limit'] ) ? (int) $_POST['advanced_ad']['tracking']['click_limit'] : '';
		$options['tracking']['public-id']        = isset( $_POST['advanced_ad']['tracking']['public-id'] ) ? stripslashes( $_POST['advanced_ad']['tracking']['public-id'] ) : '';
		$options['tracking']['public-name']      = isset( $_POST['advanced_ad']['tracking']['public-name'] ) ? stripslashes( $_POST['advanced_ad']['tracking']['public-name'] ) : '';

		$target_values                 = [ 'default', 'same', 'new' ];
		$options['tracking']['target'] = ( isset( $_POST['advanced_ad']['tracking']['target'] ) && in_array( $_POST['advanced_ad']['tracking']['target'], $target_values, true ) ) ? $_POST['advanced_ad']['tracking']['target'] : 'default';

		foreach ( [ 'nofollow', 'sponsored' ] as $relationship ) {
			$options['tracking'][ $relationship ] = ( isset( $_POST['advanced_ad']['tracking'][ $relationship ] ) && in_array( $_POST['advanced_ad']['tracking'][ $relationship ], [ 'default', '1', '0' ], true ) )
				? $_POST['advanced_ad']['tracking'][ $relationship ]
				: 'default';
		}

		// Email reports
		$options['tracking']['report-recip'] = isset( $_POST['advanced_ad']['tracking']['report-recip'] ) ? esc_textarea( $_POST['advanced_ad']['tracking']['report-recip'] ) : '';

		$options['tracking']['report-period'] = (
			isset( $_POST['advanced_ad']['tracking']['report-period'] ) &&
			in_array( $_POST['advanced_ad']['tracking']['report-period'], [
				'last30days',
				'lastmonth',
				'last12months',
			] )
		) ? $_POST['advanced_ad']['tracking']['report-period'] : 'last30days';

		$options['tracking']['report-frequency'] = (
			isset( $_POST['advanced_ad']['tracking']['report-frequency'] ) &&
			in_array( $_POST['advanced_ad']['tracking']['report-frequency'], [
				'never',
				'daily',
				'weekly',
				'monthly',
			] )
		) ? $_POST['advanced_ad']['tracking']['report-frequency'] : 'never';

		return $options;
	}

	/**
	 * Add stats submenu item.
	 *
	 * @param string $plugin_slug      The slug slug used to add a visible page.
	 * @param string $hidden_page_slug The slug slug used to add a hidden page.
	 *
	 * @since 1.0.0
	 */
	public function add_menu_item( $plugin_slug, $hidden_page_slug ) {
		// check whether to show stats pages.
		if ( ! $this->show_stats() ) {
			return;
		}

		$cap = 'manage_options';

		if ( method_exists( 'AdvancedAds\Utilities\WordPress', 'user_cap' ) ) {
			$cap = WordPress::user_cap( 'advanced_ads_edit_ads' );
		}

		$this->stat_page_hook = add_submenu_page(
			$plugin_slug,
			__( 'Statistics', 'advanced-ads-tracking' ),
			__( 'Statistics', 'advanced-ads-tracking' ),
			$cap,
			$plugin_slug . '-stats',
			[ $this, 'display_stats_page' ]
		);

		add_submenu_page(
			$hidden_page_slug,
			__( 'Tracking database', 'advanced-ads-tracking' ),
			null,
			advanced_ads_tracking_db_cap(),
			$this->db_op_page_slug,
			[ $this, 'db_operation_page_cb' ]
		);
	}

	/**
	 * Add menu page to the array of pages that belong to Advanced Ads
	 *
	 * @param array $pages Screen ids that already belong to Advanced Ads.
	 *
	 * @return array
	 * @since 1.2.4
	 */
	public function add_menu_page_to_array( array $pages ) {
		$pages[] = 'advanced-ads_page_advanced-ads-stats';
		$pages[] = 'admin_page_advads-tracking-db-page';

		return $pages;
	}

	/**
	 * Render the stats page
	 *
	 * @since    1.0.0
	 */
	public function display_stats_page() {
		// load all ads
		$all_ads = Advanced_Ads::get_instance()->get_model()->get_ads( [
			'post_status' => [ 'publish', 'future', 'draft', 'pending', Advanced_Ads_Tracking_Util::get_expired_post_status() ],
		] );
		$ads     = [];
		foreach ( $all_ads as $ad ) {
			$ads[] = $ad->ID;
		}

		// load default values
		$period          = isset( $_REQUEST['advads-stats']['period'] ) ? $_REQUEST['advads-stats']['period'] : null;
		$from            = isset( $_REQUEST['advads-stats']['from'] ) ? $_REQUEST['advads-stats']['from'] : null;
		$to              = isset( $_REQUEST['advads-stats']['to'] ) ? $_REQUEST['advads-stats']['to'] : null;
		$groupby         = isset( $_REQUEST['advads-stats']['groupby'] ) ? $_REQUEST['advads-stats']['groupby'] : null;
		$display_filter  = isset( $_REQUEST['advads-stats-filter'] ) ? wp_unslash( $_REQUEST['advads-stats-filter'] ) : 'all-ads';
		$group_format    = 'Y-m-d';
		$tracking_method = $this->plugin->get_tracking_method();

		// load period options
		$periods = [
			'today'     => __( 'today', 'advanced-ads-tracking' ),
			'yesterday' => __( 'yesterday', 'advanced-ads-tracking' ),
			'last7days' => __( 'last 7 days', 'advanced-ads-tracking' ),
			'thismonth' => __( 'this month', 'advanced-ads-tracking' ),
			'lastmonth' => __( 'last month', 'advanced-ads-tracking' ),
			'thisyear'  => __( 'this year', 'advanced-ads-tracking' ),
			'lastyear'  => __( 'last year', 'advanced-ads-tracking' ),
			// -TODO this is not fully supported for ranges of more than ~200 points; should be reviewed before 2015-09-01
			'custom'    => __( 'custom', 'advanced-ads-tracking' ),
		];
		// load groupby options
		$groupbys = [
			// group format, axis label, value conversion for graph
			'day'   => [ 'Y-m-d', __( 'day', 'advanced-ads-tracking' ), _x( 'Y-m-d', 'date format on stats page', 'advanced-ads-tracking' ) ],
			'week'  => [ 'o-\WW', __( 'week', 'advanced-ads-tracking' ), _x( 'Y-m-d', 'date format on stats page', 'advanced-ads-tracking' ) ],
			'month' => [ 'Y-m', __( 'month', 'advanced-ads-tracking' ), _x( 'Y-m', 'date format on stats page', 'advanced-ads-tracking' ) ],
		];

		// -TODO handle undefined options (should not occur)
		if ( ! isset( $periods[ $period ] ) ) {
			$period = null;
		}
		if ( ! isset( $groupbys[ $groupby ] ) ) {
			$groupby = null;
		} else {
			$group_format = $groupbys[ $groupby ][0];
		}

		$impression_stats = null;
		$click_stats      = null;

		// load stats
		if ( isset( $_REQUEST['advads-stats']['ads'] ) ) {
			$stat_args        = [
				'ad_id'       => $ads,
				'period'      => $period,
				'groupby'     => $groupby,
				'groupFormat' => $group_format,
				'from'        => $from,
				'to'          => $to,
			];
			$util             = Advanced_Ads_Tracking_Util::get_instance();
			$impression_stats = $this->load_stats( $stat_args, $util->get_impression_table() );
			$click_stats      = $this->load_stats( $stat_args, $util->get_click_table() );
		}

		// display stats view
		include AAT_BASE_PATH . 'admin/views/stats.php';
	}

	/**
	 * Load stats from the tracking tables
	 *
	 * @param array  $args  argument to load stats.
	 *                      `ad_id` empty array if all ads
	 * @param string $table name of the table.
	 *
	 * @return array $stats array with stats sorted by date
	 * @since 1.0.0
	 * @link  http://codex.wordpress.org/Class_Reference/wpdb#SELECT_Generic_Results
	 */
	public function load_stats( $args, $table ) {
		global $wpdb;

		if ( ! isset( $args['ad_id'] ) || ! is_array( $args['ad_id'] ) ) {
			return [];
		}

		// sanitize
		$table = ' `' . $wpdb->_real_escape( str_replace( '`', '_', $table ) ) . '`';

		$ad_ids = array_values( $args['ad_id'] );

		$select = 'SQL_NO_CACHE `ad_id`, SUM(`count`) as `impressions`, %s as `date`';

		$groupby          = '`timestamp`';
		$select_timestamp = null;
		$date_format      = isset( $args['groupFormat'] ) ? $args['groupFormat'] : 'Y-m-d';
		$group_increment  = ' + 1 day';

		if ( isset( $args['groupby'] ) ) {
			// group by day
			$group_by_day_clause = '`timestamp` - `timestamp` % ' . Advanced_Ads_Tracking_Util::MOD_HOUR;
			switch ( $args['groupby'] ) {
				case 'day':
					$groupby         = $group_by_day_clause;
					$group_increment = ' + 1 day';
					break;

				case 'week':
					// rather complex to mind weeks overlapping month and year while keeping proper display dates
					// Y + W + MW == 0152 | 1201 ?
					// Year + 00 + Week + 00 + 0 + ( MW == 0152 || MW == 1201 )
					$groupby          =
						'(`timestamp` - `timestamp` % ' . Advanced_Ads_Tracking_Util::MOD_MONTH // year
						. ') + (`timestamp` - `timestamp` % ' . Advanced_Ads_Tracking_Util::MOD_DAY // year + month + week
						. ') - (`timestamp` - `timestamp` % ' . Advanced_Ads_Tracking_Util::MOD_WEEK // - year - month
						. ') + ('
						. '(`timestamp` - `timestamp` % ' . Advanced_Ads_Tracking_Util::MOD_DAY // + year + month + week
						. '- `timestamp` % ' . Advanced_Ads_Tracking_Util::MOD_MONTH // - year
						. ') IN (1520000, 12010000))';
					$select_timestamp = '`timestamp` - `timestamp` % ' . Advanced_Ads_Tracking_Util::MOD_HOUR;
					$group_increment  = ' + 1 week';
					break;

				case 'month':
					$groupby         = '`timestamp` - `timestamp` % ' . Advanced_Ads_Tracking_Util::MOD_WEEK;
					$group_increment = ' + 1 month';
					break;
			}
		}

		$util = Advanced_Ads_Tracking_Util::get_instance();

		// select range
		if ( isset( $args['period'] ) ) {
			// time handling; blog time offset in seconds

			$gmt_offset = 3600 * (float) get_option( 'gmt_offset', 0 );

			// day start in seconds
			$now = $util->get_timestamp();

			$today_start = $now - $now % Advanced_Ads_Tracking_Util::MOD_HOUR;

			$start = null;
			$end   = null;

			switch ( $args['period'] ) {
				case 'today':
					$start = $today_start;
					break;
				case 'yesterday':
					$start = $util->get_timestamp( time() - DAY_IN_SECONDS );
					$start -= $start % Advanced_Ads_Tracking_Util::MOD_HOUR;
					$end   = $today_start;
					break;
				case 'last7days':
					// last seven full days // -TODO might do last or current week as well
					$start = $util->get_timestamp( time() - ( WEEK_IN_SECONDS + DAY_IN_SECONDS ) );
					$start -= $start % Advanced_Ads_Tracking_Util::MOD_HOUR;

					// get yestarday date
					$end = $util->get_timestamp( time() - DAY_IN_SECONDS );
					$end -= $start % Advanced_Ads_Tracking_Util::MOD_HOUR;
					break;
				case 'thismonth':
					// timestamp from first day of the current month
					$start = $now - $now % Advanced_Ads_Tracking_Util::MOD_WEEK;
					break;
				case 'lastmonth':
					// timestamp from first day of the last month
					$start = $util->get_timestamp( mktime( 0, 0, 0, date( 'm' ) - 1, 1, date( 'Y' ) ) );
					$end   = $now - $now % Advanced_Ads_Tracking_Util::MOD_WEEK;
					break;
				case 'thisyear':
					// timestamp from first day of the current year
					$start = $now - $now % Advanced_Ads_Tracking_Util::MOD_MONTH;
					break;
				case 'lastyear':
					// timestamp from first day of previous year
					$start = $util->get_timestamp( mktime( 0, 0, 0, 1, 1, date( 'Y' ) - 1 ) );
					$end   = $now - $now % Advanced_Ads_Tracking_Util::MOD_MONTH;
					break;
				case 'custom':
					$start = $util->get_timestamp( strtotime( $args['from'] ) - $gmt_offset );
					$end   = $util->get_timestamp( strtotime( $args['to'] ) - $gmt_offset + ( 24 * 3600 ) );
					break;
			}
		}
		// TODO limit range (mind groupIncrement/ granularity)
		// values might be null (not set) or false (error in input)

		$where = '';

		if ( isset( $start ) && $start ) {
			$where .= "WHERE `timestamp` >= $start";
		}
		if ( isset( $end ) && $end ) {
			if ( $where ) {
				$where .= " AND `timestamp` < $end";
			} else {
				$where .= "WHERE `timestamp` < $end";
			}
		}

		/**
		 * Select only one ad stats
		 */
		if ( count( $args['ad_id'] ) === 1 ) {
			if ( $where ) {
				$where .= ' AND `ad_id` = ' . $args['ad_id'][0];
			} else {
				$where .= 'WHERE `ad_id` = ' . $args['ad_id'][0];
			}
		} elseif ( count( $args['ad_id'] ) > 1 ) {
			if ( $where ) {
				$where .= ' AND `ad_id` IN (' . implode( ',', $args['ad_id'] ) . ')';
			} else {
				$where .= 'WHERE `ad_id` IN (' . implode( ',', $args['ad_id'] ) . ')';
			}
		}

		// order
		$orderby = ''; // 'ORDER BY `timestamp` ASC'; // this is implicit for current model

		// get results
		$stats  = [];
		$select = sprintf( $select, isset( $select_timestamp ) ? $select_timestamp : $groupby );

		$groupby .= ', `ad_id`';

		$query     = "SELECT $select FROM $table $where $orderby GROUP BY $groupby";
		$num_rows  = $wpdb->query( $query );
		$stat_base = [];

		if ( $num_rows > 0 ) {
			$rows = $wpdb->last_result;

			if ( $ad_ids !== [] ) {
				foreach ( $ad_ids as $adId ) {
					$stat_base[ $adId ] = null;
				}
			}

			foreach ( $rows as $row ) {
				$time = $util->get_date_from_db( $row->date, $date_format );
				if ( ! isset( $stats[ $time ] ) ) {
					$stats[ $time ] = $stat_base;
				}
				// -TODO may select ad_id from row, if defined
				// -TODO click table currently also has "impressions" instead of "clicks" in order to handle both tables equally
				if ( isset( $stats[ $time ][ $row->ad_id ] ) ) {
					$stats[ $time ][ $row->ad_id ] += $row->impressions;
				} else {
					$stats[ $time ][ $row->ad_id ] = $row->impressions;
				}
			}
		}

		if ( empty( $stats ) ) {
			return [];
		}

		try {
			return $this->prepare_stats_array( $stats, $stat_base, $date_format, $group_increment );
		} catch ( Exception $e ) {
			return [];
		}
	}

	/**
	 * Prepare the stats array for templating.
	 * Especially add empty dates.
	 *
	 * @param array  $stats           Graph values by timestamp (grouped).
	 * @param array  $stat_base       Empty stat row.
	 * @param string $group_format    Date format string (x-axis labels).
	 * @param string $group_increment Date increment string.
	 *
	 * @return array $stats input with filled in dates.
	 * @throws Exception Throw exception for DateInterval.
	 */
	private function prepare_stats_array( $stats, $stat_base, $group_format, $group_increment ) {
		if ( empty( $stats ) ) {
			return [];
		}

		// add missing dates
		$old_time = null;
		$time     = null;

		// ensure order
		$stat_keys = array_keys( $stats );
		natsort( $stat_keys );
		$sorted_stats = [];

		$increment_interval = [
			' + 1 day'   => 'P1D',
			' + 1 week'  => 'P1W',
			' + 1 month' => 'P1M',
		];

		$prev_date = null;

		$date_format = 'Y-m-d';
		if ( ' + 1 month' === $group_increment || 'o-\\WW' === $group_format ) {
			$date_format = $group_format;
		}

		// if PHP earlier than 5.3.0 return result directly.
		if ( PHP_VERSION_ID < 50300 ) {
			return $sorted_stats;
		}

		foreach ( $stat_keys as $stat_key ) {
			$current_date = date_create( $stat_key );
			// Fill missing entry for date w/o records
			if ( ! is_null( $prev_date ) ) {
				// not the first
				$next_date = clone $prev_date;
				$next_date->add( new DateInterval( $increment_interval[ $group_increment ] ) );

				if ( $next_date < $current_date && $stat_key !== $next_date->format( $date_format ) ) {
					// current date ( $statKey ) differs from $prevDate + increment ( $nextDate )
					while ( $next_date->format( $date_format ) !== $stat_key && ! ( $next_date > $current_date ) ) {
						// no record for this date, fill it
						$sorted_stats[ $next_date->format( $date_format ) ] = $stat_base;
						$next_date->add( new DateInterval( $increment_interval[ $group_increment ] ) );
					}
				}
			}

			$sorted_stats[ $stat_key ] = $stats[ $stat_key ];
			$prev_date                 = clone $current_date;
		}

		return $sorted_stats;
	}

	/**
	 * Render tracking settings section
	 *
	 * @since 1.0.0
	 */
	public function render_settings_section_callback() {
		// add hidden field to also save db version and not to override it
		$options   = $this->plugin->options();
		$dbversion = isset( $options['dbversion'] ) ? $options['dbversion'] : 0;
		?>
		<input type="hidden" name="<?php echo $this->plugin->options_slug; ?>[dbversion]" value="<?php echo $dbversion; ?>"/>
		<?php
	}

	/**
	 * Render tracking settings section for email reports
	 *
	 * @since 1.2.8
	 */
	public function render_reports_settings_section_callback() {
		if ( $this->plugin->get_tracking_method() === 'ga' ) :
			?>
			<p class="advads-notice-inline advads-idea">
				<?php _e( ' <a href="https://wpadvancedads.com/share-custom-reports-google-analytics/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-reports-google-analytics" target="_blank">How to share Google Analytics ad reports with your customers.</a>', 'advanced-ads-tracking' ); ?>
			</p>
			<?php
		endif;
	}

	/**
	 * Render advanced tracking settings section.
	 */
	public function render_settings_db_mgmt_callback() {
		$mgmt_page = admin_url( 'admin.php?page=' . $this->db_op_page_slug );
		require AAT_BASE_PATH . 'admin/views/setting_advanced_db_mgmt.php';
	}

	/**
	 * Render license key section
	 *
	 * @since 1.2.0
	 */
	public function render_settings_license_callback() {
		$licenses       = get_option( ADVADS_SLUG . '-licenses', [] );
		$license_key    = isset( $licenses['tracking'] ) ? $licenses['tracking'] : '';
		$license_status = get_option( $this->plugin->options_slug . '-license-status', false );
		$index          = 'tracking';
		$plugin_name    = AAT_PLUGIN_NAME;
		$options_slug   = $this->plugin->options_slug;
		$plugin_url     = self::PLUGIN_LINK;

		// template in main plugin
		include ADVADS_BASE_PATH . 'admin/views/setting-license.php';
	}

	/**
	 * Delayed ads settings
	 */
	public function render_settings_delayed_ads_cb() {
		include AAT_BASE_PATH . 'admin/views/setting_delayed_ads.php';
	}

	/**
	 *  Render Google Analytics settings
	 */
	public function render_settings_ga_callback() {
		$options = $this->plugin->options();
		$uid     = ( isset( $options['ga-UID'] ) ) ? $options['ga-UID'] : '';
		$is_ga   = $this->plugin->is_forced_analytics() || $this->plugin->get_tracking_method() === 'ga';

		include AAT_BASE_PATH . 'admin/views/setting_ga.php';
	}

	/**
	 * Generate classes string for GA UID field.
	 *
	 * @return string
	 */
	private function get_ga_classes() {
		$is_ga_forced = $this->plugin->is_forced_analytics();
		$ga_classes   = [
			'advads-ga-uid'       => true,
			'advads-is-ga-forced' => $is_ga_forced,
			'advads-is-visible'   => $is_ga_forced || $this->plugin->get_tracking_method() === 'ga',
		];

		return implode( ' ', array_keys( array_filter( $ga_classes ) ) );
	}

	/**
	 *  Render tracking uninstall settings
	 */
	public function render_settings_tracking_uninstall_callback() {
		$options   = $this->plugin->options();
		$uninstall = isset( $options['uninstall'] ) ? '1' : '0';
		include AAT_BASE_PATH . 'admin/views/setting_uninstall.php';
	}

	/**
	 * Render tracking method setting
	 *
	 * @since 1.0.0
	 */
	public function render_settings_tracking_method_callback() {
		$method                  = $this->plugin->get_tracking_method();
		$show_tcf_warning        = $this->has_tcf_conflict();
		$tcf_warning             = $this->get_tcf_conflict_notice();
		$missing_scripts_warning = ! apply_filters( 'advanced-ads-tracking-load-header-scripts', true );
		$is_amp_nossl            = ! is_ssl()
								   && ( function_exists( 'is_amp_endpoint' ) || function_exists( 'is_wp_amp' ) || function_exists( 'ampforwp_is_amp_endpoint' ) || function_exists( 'is_penci_amp' ) );

		include AAT_BASE_PATH . 'admin/views/setting_method.php';
	}

	/**
	 * Render tracking-everything setting
	 *
	 * @since 1.0.0
	 */
	public function render_settings_tracking_everything_callback() {
		$options = $this->plugin->options();
		$method  = isset( $options['everything'] ) ? $options['everything'] : 'true';

		include AAT_BASE_PATH . 'admin/views/setting_everything.php';
	}

	/**
	 * Render disabled-roles setting
	 *
	 * @return void
	 */
	public function render_settings_user_role() {
		$roles          = wp_roles();
		$options        = $this->plugin->options();
		$disabled_roles = $options['disabled-roles'] ?? [];
		include AAT_BASE_PATH . 'admin/views/setting_user_role.php';
	}

	/**
	 * Render link-nofollow setting
	 *
	 * @since 1.1.0
	 */
	public function render_settings_link_base_callback() {
		$options  = $this->plugin->options();
		$linkbase = isset( $options['linkbase'] ) ? $options['linkbase'] : 'linkout';
		include AAT_BASE_PATH . 'admin/views/setting_linkbase.php';
	}

	/**
	 * Render link-nofollow setting
	 *
	 * @since 1.1.0
	 */
	public function render_settings_link_nofollow_callback() {
		$nofollow = ! empty( $this->plugin->options()['nofollow'] );
		include AAT_BASE_PATH . 'admin/views/setting_nofollow.php';
	}

	/**
	 * Render rel="sponsored" setting.
	 */
	public function render_settings_link_sponsored_callback() {
		$sponsored = ! empty( $this->plugin->options()['sponsored'] );
		include AAT_BASE_PATH . 'admin/views/setting_sponsored.php';
	}

	/**
	 *  Render public stats setting
	 *
	 * @since 1.2.7
	 */
	public function render_settings_public_stats() {
		$public_stats_slug = Advanced_Ads_Tracking_Public_Stats::get_slug();
		$options_slug      = $this->plugin->options_slug;
		$nonce             = wp_create_nonce( 'advads-tracking-public-stats' );
		include AAT_BASE_PATH . 'admin/views/setting_public_stats.php';
	}

	/**
	 *  Render settings email recipient
	 *
	 * @since 1.2.8
	 */
	public function render_settings_email_report_recip() {
		$options    = $this->plugin->options();
		$recipients = isset( $options['email-addresses'] ) ? $options['email-addresses'] : '';
		include AAT_BASE_PATH . 'admin/views/setting_email_report_recip.php';
	}

	/**
	 *  Render settings email frequency
	 *
	 * @since 1.2.8
	 */
	public function render_settings_email_freq() {
		$options = $this->plugin->options();
		$sched   = isset( $options['email-sched'] ) ? $options['email-sched'] : 'daily';
		include AAT_BASE_PATH . 'admin/views/setting_email_report_frequency.php';
	}

	/**
	 *  Render settings email stats period
	 *
	 * @since 1.2.8
	 */
	public function render_settings_email_stats_period() {
		$options = $this->plugin->options();
		$period  = isset( $options['email-stats-period'] ) ? $options['email-stats-period'] : 'last30days';
		include AAT_BASE_PATH . 'admin/views/setting_email_report_stats_period.php';
	}

	/**
	 *  Render settings email sender name
	 *
	 * @since 1.2.8
	 */
	public function render_settings_email_sender_name() {
		$options     = $this->plugin->options();
		$sender_name = ! empty( $options['email-sender-name'] ) ? stripslashes( $options['email-sender-name'] ) : 'Advanced Ads';
		include AAT_BASE_PATH . 'admin/views/setting_email_report_sender_name.php';
	}

	/**
	 *  Render settings email sender address
	 *
	 * @since 1.2.8
	 */
	public function render_settings_email_sender_address() {
		$options        = $this->plugin->options();
		$sender_address = ! empty( $options['email-sender-address'] ) ? $options['email-sender-address'] : false;
		$sender_address = sanitize_email( $sender_address );
		if ( ! $sender_address ) {
			$sender_address = 'noreply@' . wp_parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
		}
		include AAT_BASE_PATH . 'admin/views/setting_email_report_sender_address.php';
	}

	/**
	 *  Render settings email subject
	 *
	 * @since 1.2.8
	 */
	public function render_settings_email_subject() {
		$options       = $this->plugin->options();
		$email_subject = ! empty( $options['email-subject'] ) ? stripslashes( $options['email-subject'] ) : __( 'Ads Statistics', 'advanced-ads-tracking' );

		include AAT_BASE_PATH . 'admin/views/setting_email_report_subject.php';
	}

	/**
	 *  Render settings email subject
	 *
	 * @since 1.2.8
	 */
	public function render_settings_email_test_email() {
		$options    = $this->plugin->options();
		$recipients = isset( $options['email-addresses'] ) ? $options['email-addresses'] : '';
		$sched      = isset( $options['email-sched'] ) ? $options['email-sched'] : 'daily';
		include AAT_BASE_PATH . 'admin/views/setting_email_test_email.php';
	}

	/**
	 *  Render settings tracking bot
	 *
	 * @since to be defined
	 */
	public function render_settings_track_bots() {
		$options    = $this->plugin->options();
		$track_bots = isset( $options['track-bots'] ) ? $options['track-bots'] : '0';
		include AAT_BASE_PATH . 'admin/views/setting_tracking_bots.php';
	}

	/**
	 * Add tracking settings tab
	 *
	 * @param array $tabs existing setting tabs.
	 *
	 * @return array $tabs setting tabs with AdSense tab attached
	 * @since 1.2.0
	 */
	public function setting_tabs( array $tabs ) {
		$tabs['tracking'] = [
			'page'  => $this->settings_page_hook,
			'group' => $this->plugin->options_slug,
			'tabid' => 'tracking',
			'title' => __( 'Tracking', 'advanced-ads-tracking' ),
		];

		return $tabs;
	}

	/**
	 * Create table on installation
	 *
	 * @since 1.0.0
	 * @link  http://codex.wordpress.org/Creating_Tables_with_Plugins
	 */
	public static function create_tables() {
		global $wpdb;
		$charset_collate   = $wpdb->get_charset_collate();
		$impressions_table = $wpdb->prefix . 'advads_impressions';
		$clicks_table      = $wpdb->prefix . 'advads_clicks';
		$sql               = [];
		$sql[]             = "CREATE TABLE IF NOT EXISTS $impressions_table (
			`timestamp` BIGINT UNSIGNED NOT NULL,
			`ad_id` BIGINT UNSIGNED NOT NULL,
			`count` MEDIUMINT UNSIGNED NOT NULL,
			PRIMARY KEY (`timestamp`, `ad_id`)
		) $charset_collate";
		$sql[]             = "CREATE TABLE IF NOT EXISTS $clicks_table (
			`timestamp` BIGINT UNSIGNED NOT NULL,
			`ad_id` BIGINT UNSIGNED NOT NULL,
			`count` MEDIUMINT UNSIGNED NOT NULL,
			PRIMARY KEY (`timestamp`, `ad_id`)
		) $charset_collate";
		foreach ( $sql as $query ) {
			$wpdb->query( $query );
		}
	}

	/**
	 * Check tables on update.
	 *
	 * @return bool
	 */
	public function check_tables() {
		global $wpdb;

		$util              = Advanced_Ads_Tracking_Util::get_instance();
		$impressions_table = $util->get_impression_table();
		$clicks_table      = $util->get_click_table();
		$charset_collate   = $wpdb->get_charset_collate();

		/**
		 *  Hotfix for missing stats on new year
		 */
		$this->check_tracking_tables();
		$corrupted_impr = $wpdb->get_results( "SELECT * FROM $impressions_table WHERE `timestamp` BETWEEN 1601530100 AND 1601530323" );
		if ( count( $corrupted_impr ) ) {
			foreach ( $corrupted_impr as $row ) {
				$ts = str_replace( '53', '01', $row->timestamp );
				$wpdb->query( "UPDATE $impressions_table SET `timestamp` = $ts WHERE `timestamp` = $row->timestamp AND `ad_id` = $row->ad_id" );
			}
		}
		$corrupted_clicks = $wpdb->get_results( "SELECT * FROM $clicks_table WHERE `timestamp` BETWEEN 1601530100 AND 1601530323" );
		if ( count( $corrupted_clicks ) ) {
			foreach ( $corrupted_clicks as $row ) {
				$ts = str_replace( '53', '01', $row->timestamp );
				$wpdb->query( "UPDATE $clicks_table SET `timestamp` = $ts WHERE `timestamp` = $row->timestamp AND `ad_id` = $row->ad_id" );
			}
		}

		// there was a serious issue with non-initialised base plugin
		// the upgrade process must skip if this happens
		// otherwise information is lost for all tracked ads
		$options = $this->plugin->options();
		if ( ! is_array( $options ) ) {
			return false;
		}

		$sql = [];
		if ( ! isset( $options['dbversion'] ) ) {
			$options['dbversion'] = '0';
		}

		if ( $options['dbversion'] === Advanced_Ads_Tracking_Util::DB_VERSION ) {
			return false;
		}

		// handle diffs incrementally
		switch ( $options['dbversion'] ) {
			case '0':
				$sql[] = "CREATE TABLE IF NOT EXISTS $impressions_table (
                    `timestamp` INT UNSIGNED NOT NULL,
                    `ad_id` INT UNSIGNED NOT NULL,
                    `count` MEDIUMINT UNSIGNED NOT NULL,
                    PRIMARY KEY (`timestamp`, `ad_id`)
                ) ENGINE = MyISAM $charset_collate";
				break;
			case '1.0':
				$sql[] = "CREATE TABLE IF NOT EXISTS $clicks_table (
                    `timestamp` INT UNSIGNED NOT NULL,
                    `ad_id` INT UNSIGNED NOT NULL,
                    `count` MEDIUMINT UNSIGNED NOT NULL,
                    PRIMARY KEY (`timestamp`, `ad_id`)
                ) ENGINE = MyISAM $charset_collate";
				break;
			case '1.1':
			case '1.2':
			case '1.3':
				// update INT(10) to BIGINT(20) since this is the max size for WordPress post IDs
				$sql[] = "ALTER TABLE $clicks_table CHANGE `ad_id` `ad_id` BIGINT(20) UNSIGNED NOT NULL";
				$sql[] = "ALTER TABLE $impressions_table CHANGE `ad_id` `ad_id` BIGINT(20) UNSIGNED NOT NULL";
			case '1.4':
				// Change timestamp column type on the clicks and impressions table to BIGINT.
				$sql[] = "ALTER TABLE $clicks_table MODIFY COLUMN `timestamp` BIGINT UNSIGNED NOT NULL";
				$sql[] = "ALTER TABLE $impressions_table MODIFY COLUMN `timestamp` BIGINT UNSIGNED NOT NULL";
		}

		if ( $options['dbversion'] < '1.7' ) {
			$this->update_timestamps_for_week_52_in_january();
		}

		foreach ( $sql as $query ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- queries should be prepared where they get created
			$wpdb->query( $query );
		}
		// add database version number to options
		$options['dbversion'] = Advanced_Ads_Tracking_Util::DB_VERSION;
		$this->plugin->update_options( $options );

		return true;
	}

	/**
	 * Get public link from ad ID
	 *
	 * @param int $ad_id post ID of the ad.
	 *
	 * @return string
	 *
	 * @deprecated 2.0.0
	 * @see        Advanced_Ads_Tracking_Public_Stats::get_url()
	 */
	public function get_public_link( $ad_id ) {
		$public_stats = new Advanced_Ads_Tracking_Public_Stats( $ad_id );

		return $public_stats->get_url();
	}

	/**
	 * Checks, if ad been saved and 'enabled' exists in ad_options.
	 *
	 * @param int $ad_id post ID of the ad.
	 *
	 * @return bool
	 *
	 * @deprecated 2.0.0
	 * @see        Advanced_Ads_Tracking_Public_Stats::ad_has_tracking()
	 */
	public function is_stats_option_saved( $ad_id ) {
		$public_stats = new Advanced_Ads_Tracking_Public_Stats( $ad_id );

		return $public_stats->ad_has_tracking();
	}

	/**
	 * Don't show stats submenu when GA is tracking method and stats db empty.
	 *
	 * @return bool Whether to show the stats submenu.
	 */
	public function show_stats() {
		static $show_stats;
		if ( is_null( $show_stats ) ) {
			$show_stats = ! (
				$this->plugin->get_tracking_method() === 'ga'
				&& empty( array_filter( Advanced_Ads_Tracking_Util::get_instance()->get_sums() ) )
			);
		}

		return $show_stats;
	}

	/**
	 * Migrate deprecated tracking method.
	 */
	private function migrate_deprecated_tracking_method() {
		$options = $this->plugin->options();
		if ( ! isset( $options['method'] ) ) {
			return;
		}

		// if tracking is shutdown, change it to onrequest in database.
		if ( $options['method'] === 'shutdown' ) {
			$options['method'] = 'onrequest';
		}

		$this->plugin->update_options( $options );
	}

	/**
	 * Get TCF/Tracking conflict text.
	 *
	 * @return string
	 */
	public function get_tcf_conflict_notice() {
		return __( 'The selected tracking method is not compatible with the TCF 2.0 integration.', 'advanced-ads-tracking' );
	}

	/**
	 * Whether we have a conflict between TCF and tracking method.
	 *
	 * @return bool
	 */
	public function has_tcf_conflict() {
		$options         = $this->plugin->options();
		$method          = isset( $options['method'] ) ? $options['method'] : 'onrequest';
		$privacy_options = Advanced_Ads_Privacy::get_instance()->options();

		return ! empty( $privacy_options['enabled'] ) && $privacy_options['consent-method'] === 'iab_tcf_20' && in_array( $method, [ 'onrequest', 'shutdown' ], true );
	}

	/**
	 * Add Tracking options to the list of options to be exported
	 *
	 * @param $options Array of option data keyed by option keys.
	 *
	 * @return $options Array of option data keyed by option keys.
	 */
	public function export_options( $options ) {
		$options[ ADVADS_SLUG . '-tracking' ] = get_option( ADVADS_SLUG . '-tracking' );

		return $options;
	}

	/**
	 * Update timestamps in the database for a bug in the timestamp generation for the first week in January if it was still calendar week 52.
	 *
	 * @return void
	 */
	private function update_timestamps_for_week_52_in_january() {
		global $wpdb;
		$util = Advanced_Ads_Tracking_Util::get_instance();
		foreach ( [ $util->get_impression_table(), $util->get_click_table() ] as $table ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- we can't add table names as placeholders
			$results = $wpdb->get_results( "SELECT * FROM {$table} WHERE LENGTH(`timestamp`) = 9", ARRAY_A );
			foreach ( $results as $row ) {
				if ( ! preg_match( '/(?<y>\d{2})(?<m>\d{2})(?<W>\d{1})(?<d>\d{2})(?<H>\d{2})/', $row['timestamp'], $timestamp_exploded ) ) {
					continue;
				}
				$timestamp_exploded = array_filter( $timestamp_exploded, 'is_string', ARRAY_FILTER_USE_KEY );
				if ( $timestamp_exploded['W'] !== '1' ) {
					continue;
				}
				unset( $row['count'] );
				$timestamp_exploded['W'] = '01';
				$updated                 = $row;
				$updated['timestamp']    = implode( '', $timestamp_exploded );
				// phpcs:disable WordPress.DB.PreparedSQL -- we can't prepare the table names.
				if ( ! $wpdb->update( $table, $updated, $row ) && $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE timestamp = %d && ad_id = %d", $updated['timestamp'], $updated['ad_id'] ) ) !== null ) {
					$wpdb->delete( $table, $row );
				}
			}
		}
	}
}
