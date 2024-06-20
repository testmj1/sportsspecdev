<?php

use AdvancedAds\Utilities\WordPress;

class Advanced_Ads_Responsive {

	/**
	 * holds plugin base class
	 *
	 * @var Advanced_Ads_Responsive_Plugin
	 * @since 1.2.0
	 */
	protected $plugin;

	/**
	 * can current user edit ads? – = necessary user right to see frontend helper
	 */
	protected  $can_edit_ads = false;

        /**
         * Initialize the plugin
         * and styles.
         *
         * @since     1.0.0
         */
        public function __construct() {

		$this->plugin = Advanced_Ads_Responsive_Plugin::get_instance();

		// init action
		add_action( 'init', array( $this, 'init' ) );

		// register events when all plugins are loaded
		add_action( 'plugins_loaded', array( $this, 'wp_admin_plugins_loaded' ) );
	}

	/**
	 * init
	 *
	 * @since 1.2.0
	 */
	public function init() {
		$options = $this->plugin->options();
		$cap = method_exists( 'AdvancedAds\Utilities\WordPress', 'user_cap' ) ? WordPress::user_cap( 'advanced_ads_edit_ads' ) : 'manage_options';

		if ( current_user_can( $cap ) ) {
			$this->can_edit_ads = true;
		}

		$this->show_tooltip = isset( $options[ AAR_SLUG ]['show-tooltip'] ) && '1' == $options[ AAR_SLUG ]['show-tooltip'];
	}

	/**
	 * load actions and filters
	 */
	public function wp_admin_plugins_loaded(){
		// force advanced JS file
		add_filter( 'advanced-ads-activate-advanced-js', '__return_true' );
	}

	/**
	 * check for tablet devices
	 *
	 * @since 1.3
	 * @param arr $options options of the condition
	 * @return bool true if can be displayed
	 */
	static function check_tablet( $options = array() ){
		global $advads_mobile_detect;

		if ( ! isset( $options['operator'] ) ) {
			return true;
		}

		switch ( $options['operator'] ) {
			case 'is' :
				if ( ! $advads_mobile_detect->isTablet() ) { return false; }
				break;
			case 'is_not' :
				if ( $advads_mobile_detect->isTablet() ) { return false; }
				break;
		}

		return true;
	}
}
