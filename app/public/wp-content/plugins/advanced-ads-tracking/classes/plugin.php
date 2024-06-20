<?php

/**
 * Load common and WordPress based resources
 *
 * @since 1.2.0
 */
class Advanced_Ads_Tracking_Plugin {

	/**
	 *
	 * @var Advanced_Ads_Tracking_Plugin
	 */
	protected static $instance;

	/**
	 * Plugin options
	 *
	 * @var     array (if loaded)
	 */
	protected $options;

	/**
	 * Name of options in db
	 *
	 * @var     string
	 */
	public $options_slug;

	/**
	 * Array with ad types that use click tracking
	 *
	 * @var        array
	 */
	private static $types_using_click_tracking = [ 'plain', 'dummy', 'content', 'image', 'adsense', 'gam' ];

	private $default_options = [
		'method'               => 'frontend',
		'everything'           => 'true',
		'linkbase'             => 'linkout',
		'nofollow'             => false,
		'sponsored'            => false,
		'sum-timeout'          => '60',
		'public-stats-slug'    => 'ad-stats',
		'email-addresses'      => '',
		'email-sched'          => 'daily',
		'email-stats-period'   => 'last30days',
		'email-sender-name'    => 'Advanced Ads',
		'email-sender-address' => 'noreply@_',
		'email-subject'        => 'Ads Statistics',
	];

	/**
	 * Advanced_Ads_Tracking_Plugin constructor.
	 */
	private function __construct() {
		if ( ! defined( 'ADVADS_SLUG' ) ) {
			return;
		}
		$this->options_slug = ADVADS_SLUG . '-tracking';

		// register plugin for auto updates
		// -TODO this is true for any AJAX call
		if ( is_admin() ) {
			add_filter( 'advanced-ads-add-ons', [ $this, 'register_auto_updater' ], 10 );
			add_action( 'wp_ajax_advads_track_i327', [ $this, 'db_repair_i327' ] );
			add_action( 'admin_footer', [ $this, 'admin_footer' ] );
		}

		// check if UID is present when tracking with Google Analytics.
		add_filter( 'advanced-ads-ad-health-notices', [ $this, 'add_missing_gauid_notice' ] );
		$this->check_missing_gauid();
	}

	/**
	 * Singleton class instance.
	 *
	 * @return Advanced_Ads_Tracking_Plugin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load advanced ads settings.
	 * If options are empty or in old format, convert to new options.
	 *
	 * @return array empty array if main plugin not active, array of options otherwise.
	 */
	public function options() {
		// don't initiate if main plugin not loaded
		if ( ! class_exists( 'Advanced_Ads', false ) ) {
			return [];
		}

		// return options if already loaded
		if ( isset( $this->options ) ) {
			return $this->options;
		}

		$this->options = get_option( $this->options_slug, [] );

		// get "old" options
		if ( empty( $this->options ) ) {
			$old_options   = Advanced_Ads_Plugin::get_instance()->options();
			$this->options = $this->default_options;

			if ( isset( $old_options['tracking'] ) ) {
				$this->options = array_merge( $this->options, $old_options['tracking'] );
			}
			// save as new options
			$this->update_options( $this->options );
		} else {
			$this->options = wp_parse_args( $this->options, $this->default_options );
		}

		return $this->options;
	}

	/**
	 * Add warning to Ad Health Notices if GA has been chosen as tracking method, but no GA UID present.
	 *
	 * @param array $notices Array of registered health notices.
	 *
	 * @return array
	 */
	public function add_missing_gauid_notice( $notices ) {
		$notices['tracking_missing_gauid'] = [
			/* Translators: 1: opening a-tag with link to settings page 2: closing a-tag */
			'text' => sprintf( __( 'You have selected to track ads with Google Analytics but not provided a tracking ID. Please add the Google Analytics UID %1$shere%2$s', 'advanced-ads-tracking' ), sprintf( '<a href="%s">', admin_url( 'admin.php?page=advanced-ads-settings#top#tracking' ) ), '</a>' ),
			'type' => 'problem',
		];

		return $notices;
	}

	/**
	 * If tracking method is Google Analytics, but there is no UID, show an Ad_Health_Notice.
	 */
	private function check_missing_gauid() {
		$is_ga = $this->is_forced_analytics() || $this->get_tracking_method() === 'ga';
		if ( ! $is_ga || ! empty( $this->options()['ga-UID'] ) ) {
			Advanced_Ads_Ad_Health_Notices::get_instance()->remove( 'tracking_missing_gauid' );

			return;
		}

		Advanced_Ads_Ad_Health_Notices::get_instance()->add( 'tracking_missing_gauid' );
	}

	/**
	 * Get the tracking method.
	 * Default is `frontend` (AJAX).
	 *
	 * @param string $method Pass a method that should be filtered.
	 *
	 * @return string
	 */
	public function get_tracking_method( $method = '' ) {
		if ( empty( $method ) ) {
			$options = $this->options();
			$method  = ( array_key_exists( 'method', $options ) && is_string( $options['method'] ) ) ? $options['method'] : '';
		}
		$valid_methods = [ 'frontend', 'ga', 'onrequest' ];

		if ( empty( $method ) || ! in_array( $method, $valid_methods, true ) ) {
			$method = 'frontend';
		}

		/**
		 * Filter the tracking method in use.
		 *
		 * @param string $method
		 */
		return (string) apply_filters( 'advanced-ads-tracking-method', $method );
	}

	/**
	 * Check if the legacy ajax method is explicitly set.
	 *
	 * @return bool
	 */
	public function is_legacy_ajax() {
		return defined( 'ADVANCED_ADS_TRACKING_LEGACY_AJAX' ) && ADVANCED_ADS_TRACKING_LEGACY_AJAX;
	}

	/**
	 * Check if the parallel tracking with GA is set.
	 *
	 * @return bool
	 */
	public function is_forced_analytics() {
		return defined( 'ADVANCED_ADS_TRACKING_FORCE_ANALYTICS' ) && ADVANCED_ADS_TRACKING_FORCE_ANALYTICS;
	}

	/**
	 * Load advanced ads settings
	 */
	public function update_options( array $options ) {
		// don’t allow to clear options
		if ( $options === [] ) {
			return;
		}

		$this->options = $options;
		update_option( $this->options_slug, $options );
	}

	/**
	 * Register plugin for the auto updater in the base plugin
	 *
	 * @param array $plugins plugin that are already registered for auto updates.
	 *
	 * @return array
	 */
	public function register_auto_updater( array $plugins = [] ) {
		$plugins['tracking'] = [
			'name'         => AAT_PLUGIN_NAME,
			'version'      => AAT_VERSION,
			'path'         => AAT_BASE_PATH . 'tracking.php',
			'options_slug' => $this->options_slug,
		];

		return $plugins;
	}

	/**
	 * Check, whether to track a specific ad or not
	 *
	 * @param Advanced_Ads_Ad $ad   The ad object.
	 * @param string          $what What to track. default value 'impression'. 'min_one' if you want to check if atleast one method is activated.
	 *
	 * @return bool
	 */
	public function check_ad_tracking_enabled( Advanced_Ads_Ad $ad, $what = 'impression' ) {

		// TODO: write a better implementation; right now this check should be enough and has little performance impact compared to more generic approaches

		$global_options = $this->options();
		$disabled_roles = $global_options['disabled-roles'] ?? [];

		// Disabled tracking for user role.
		if ( ! empty( $disabled_roles ) ) {
			foreach ( wp_get_current_user()->roles as $role ) {
				if ( in_array( $role, $disabled_roles, true ) ) {
					return false;
				}
			}
		}

		// don’t track Yieldscale ad type.
		if ( isset( $ad->type ) && in_array( $ad->type, [ 'yieldscale' ], true ) ) {
			return false;
		}
		$options  = $ad->options();
		$tracking = isset( $options['tracking']['enabled'] ) && $options['tracking']['enabled'] ? $options['tracking']['enabled'] : null;

		// check for default settings
		if ( ! isset( $tracking ) || $tracking === 'default' ) {
			// check global setting
			if ( ! empty( $global_options ) ) {
				if ( ! isset( $global_options['everything'] ) ) {
					return true;
				} else {
					switch ( $global_options['everything'] ) {
						case 'true':
							return true;
						case 'false':
							return false;
						case 'impressions':
							return ( $what !== 'click' );
						case 'clicks':
							return ( $what !== 'impression' );
						default:
					}
				}
			}
		}

		if ( isset( $tracking ) ) {
			switch ( $tracking ) {
				case 'enabled':
					return true;
				case 'disabled':
					return false;
				case 'impressions':
					return ( 'click' !== $what || 'min_one' === $what );
				case 'clicks':
					return ( 'impression' !== $what || 'min_one' === $what );
				default:
			}
		}
	}

	/**
	 * Return true if this is a logged-in user and those should not be tracked
	 * based on constant ADVANCED_ADS_TRACKING_IGNORE_LOGGED_IN_USERS
	 *
	 * @return bool true, if current interaction should not be tracked
	 */
	public function ignore_logged_in_user() {
		return defined( 'ADVANCED_ADS_TRACKING_IGNORE_LOGGED_IN_USERS' ) && ADVANCED_ADS_TRACKING_IGNORE_LOGGED_IN_USERS && is_user_logged_in();
	}

	/**
	 * Fix corrupted data for 2018/12/31
	 */
	public function db_repair_i327() {
		$nonce = wp_unslash( $_GET['nonce'] );
		if ( false !== wp_verify_nonce( $nonce, 'advads-tracking-i327' ) ) {
			global $wpdb;
			$impressions = $wpdb->prefix . Advanced_Ads_Tracking_Util::TABLE_BASENAME;
			$clicks      = $wpdb->prefix . Advanced_Ads_Tracking_Util::TABLE_CLICKS_BASENAME;

			// phpcs:disable WordPress.DB.PreparedSQL -- we can't prepare the table names.
			$result  = $wpdb->query( "UPDATE {$impressions} SET `timestamp` = 1812523106 WHERE `timestamp` = 1812013106" );
			$result2 = $wpdb->query( "UPDATE {$clicks} SET `timestamp` = 1812523106 WHERE `timestamp` = 1812013106" );
			echo $result . '//' . $result2;
			// phpcs:enable

			$options         = $this->options();
			$options['i327'] = true;
			update_option( $this->options_slug, $options );
		}
		die;
	}

	/**
	 * Prints inline scripts markup on admin footer.
	 */
	public function admin_footer() {
		$options = $this->options();
		if ( ! isset( $options['i327'] ) ) {
			$nonce = wp_create_nonce( 'advads-tracking-i327' );
			echo '<iframe frameborder=0 width="1" height="1" style="display:none !important;" src="' .
				 admin_url( 'admin-ajax.php?action=advads_track_i327&nonce=' . $nonce ) . '"></iframe>';
		}
	}

	/**
	 * Check if the "PopUp and Layer Ads" or "Sticky Ads" add-ons are active.
	 *
	 * @return bool
	 */
	public function has_delayed_ads() {
		return defined( 'AAPLDS_BASE_PATH' ) || defined( 'AASADS_BASE_PATH' );
	}

	/**
	 * Return a filterable list of clickable types.
	 *
	 * @return array
	 */
	public static function get_clickable_types() {
		/**
		 * Filter clickable types.
		 *
		 * @param array default clickable types.
		 */
		return (array) apply_filters( 'advanced-ads-tracking-clickable-types', self::$types_using_click_tracking );
	}

	/**
	 * Retrieves the default tracking method's name based on options.
	 *
	 * @return string The name of the default tracking method.
	 */
	public function get_default_track_method() {
		// Fetch options using the options() method
		$options = $this->options();

		// Early bail!!
		if ( ! isset( $options['everything'] ) ) {
			return esc_html__( 'disabled', 'advanced-ads-tracking');
		}

		// tracking methods
		$tracking_choices = [
			'impressions' => esc_html__( 'impressions only', 'advanced-ads-tracking'),
			'clicks' => esc_html__( 'clicks only', 'advanced-ads-tracking'),
			'true' => esc_html__( 'impressions & clicks', 'advanced-ads-tracking')
		];

		// Check if the 'everything' key exists in options
		return  $tracking_choices[$options['everything']] ?? esc_html__( 'disabled', 'advanced-ads-tracking');
	}
}
