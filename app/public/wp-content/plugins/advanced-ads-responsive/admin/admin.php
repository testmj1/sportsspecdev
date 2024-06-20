<?php

use AdvancedAds\Utilities\WordPress;

class Advanced_Ads_Responsive_Admin {

	/**
	 * stores the settings page hook
	 *
	 * @since   1.0.0
	 * @var     string
	 */
	protected $settings_page_hook = '';

	/**
	 * link to plugin page
	 *
	 * @since	1.3
	 * @const
	 */
	const PLUGIN_LINK = 'https://wpadvancedads.com/add-ons/responsive-ads/';

	/**
	 * holds base class
	 *
	 * @var Advanced_Ads_Responsive_Plugin
	 * @since 1.2.0
	 */
	protected $plugin;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		$this->plugin = Advanced_Ads_Responsive_Plugin::get_instance();

		add_action( 'plugins_loaded', array( $this, 'wp_admin_plugins_loaded' ) );
		add_filter( 'advanced-ads-notices', [ $this, 'add_notices' ] );
	}

	/**
	 * load actions and filters
	 */
	public function wp_admin_plugins_loaded(){

		if( ! class_exists( 'Advanced_Ads_Admin', false ) ) {
			// show admin notice
			add_action( 'admin_notices', array( $this, 'missing_plugin_notice' ) );

			return;
		}

		if ( ! defined( 'AAP_VERSION' ) || 1 !== version_compare( AAP_VERSION, '2.24.2' ) ) {
			$notices = get_option('advanced-ads-notices');
			if ( ! array_key_exists( 'pro_responsive_migration', $notices['closed'] ?? [] ) ) {
				Advanced_Ads_Admin_Notices::get_instance()->add_to_queue( 'pro_responsive_migration' );
			}
			return;
		}



		add_action('advanced-ads-settings-init', array($this, 'settings_init'), 10, 1);
		// add list page
		add_action('admin_menu', array($this, 'add_list_page'));
	}

	/**
	 * show warning if Advanced Ads js is not activated
	 */
	public function missing_plugin_notice(){
		$plugins = get_plugins();
		if( isset( $plugins['advanced-ads/advanced-ads.php'] ) ){ // is installed, but not active
			$link = '<a class="button button-primary" href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads/advanced-ads.php&amp', 'activate-plugin_advanced-ads/advanced-ads.php' ) . '">'. __('Activate Now', 'advanced-ads-responsive') .'</a>';
		} else {
			$link = '<a class="button button-primary" href="' . wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . 'advanced-ads'), 'install-plugin_' . 'advanced-ads') . '">'. __('Install Now', 'advanced-ads-responsive') .'</a>';
		}
		echo '
		<div class="error">
		  <p>'.sprintf(__('<strong>%s</strong> requires the <strong><a href="https://wpadvancedads.com" target="_blank">Advanced Ads</a></strong> plugin to be installed and activated on your site.', 'advanced-ads-responsive'), 'Advanced Ads Responsive') .
			 '&nbsp;' . $link . '</p></div>';
	}

	/**
	 * Add potential warning to global array of notices.
	 *
	 * @param array $notices existing notices.
	 *
	 * @return mixed
	 */
	public function add_notices( $notices ) {
		$message                             = wp_kses(
			sprintf(
			/* translators: 1 is the opening link to the Advanced Ads pge, 2 the closing link */
				__(
					'We have renamed the Responsive Ads add-on to ‘Advanced Ads AMP Ads’. With this change, the Browser Width visitor condition moved from that add-on into Advanced Ads Pro. You can deactivate ‘Advanced Ads AMP Ads’ if you don’t utilize AMP ads or the custom sizes feature for responsive AdSense ad units. %1$sRead more%2$s.',
					'advanced-ads-pro'
				),
				'<a href="https://wpadvancedads.com/responsive-ads-add-on-becomes-amp-ads" target="_blank" class="advads-manual-link">',
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
		$notices['pro_responsive_migration'] = [
			'type'   => 'info',
			'text'   => $message,
			'global' => true,
		];

		return $notices;
	}

	/**
	 * render license key section
	 *
	 * @since 1.2.0
	 */
	public function render_settings_license_callback(){
		$licenses = get_option(ADVADS_SLUG . '-licenses', array());
		$license_key = isset($licenses['responsive']) ? $licenses['responsive'] : '';
		$license_status = get_option($this->plugin->options_slug . '-license-status', false);
		$index = 'responsive';
		$plugin_name = AAR_PLUGIN_NAME;
		$options_slug = $this->plugin->options_slug;
		$plugin_url = self::PLUGIN_LINK;

		// template in main plugin
		include ADVADS_BASE_PATH . 'admin/views/setting-license.php';
	}

	/**
	 * add settings to settings page
	 *
	 * @param string $hook settings page hook
	 */
	public function settings_init( $hook ) {

		// don’t initiate if main plugin not loaded
		if ( ! class_exists( 'Advanced_Ads_Admin' ) ) return;

		// add license key field to license section
		add_settings_field(
			'responsive-license',
			__('Responsive', 'advanced-ads-responsive'),
			array($this, 'render_settings_license_callback'),
			'advanced-ads-settings-license-page',
			'advanced_ads_settings_license_section'
		);
	}

	/**
	 * Render settings section
	 */
	public function render_settings_section_callback() {
		return;
	}

	/**
	 * Add ads list page.
	 *
	 * @since 1.1.1
	 */
	public function add_list_page() {
		add_submenu_page(
			'',
			__( 'Responsive Ads', 'advanced-ads-responsive' ),
			__( 'Responsive Ads', 'advanced-ads-responsive' ),
			method_exists( 'AdvancedAds\Utilities\WordPress', 'user_cap' ) ? WordPress::user_cap( 'advanced_ads_edit_ads' ) : 'manage_options',
			AAR_SLUG . '-list',
			[ $this, 'display_responsive_ads_list' ]
		);
	}

	/**
	 * Render the responsive ads list page
	 *
	 * @since    1.0.0
	 */
	public function display_responsive_ads_list() {
		if( ! class_exists( 'Advanced_Ads' ) ) return array();
		// get all ads with responsive settings
		$advads = Advanced_Ads::get_instance();

		// initiate variables
		$sorted_ads = array();
		$widths = array( 0 );
		$groups = array();

		// order ads by group and with ad id
		$ads = $advads->get_ads();

		// iterate through ads and get the responsive settings
		foreach( $ads as $_key => $_ad ){
			// get ad options
			$_ad->ad_options = $_ad->advanced_ads_ad_options;

			// put responsive options into widths array
			if( isset( $_ad->ad_options['visitors'] ) ){
		// iterate through visitor conditions
		foreach( $_ad->ad_options['visitors'] as $_condition ){
			if( 'device_width' === $_condition['type'] ) {
			switch( $_condition['operator'] ){
				case 'is_higher' :
				$widths[] = absint( $_condition['value'] );
				break;
				case 'is_lower' :
				$widths[] = absint( $_condition['value'] ) + 1;
				break;
				default :
				$widths[] = absint( $_condition['value'] );
			}
			}
		}
		}

		// get categories
			$ad_groups = get_the_terms( $_ad->ID, Advanced_Ads::AD_GROUP_TAXONOMY );

		$unsorted_ads = array();
			if( ! $ad_groups ){
				$unsorted_ads[$_ad->ID] = $_ad;
			} else {
				foreach ( $ad_groups as $_group ) {
					$sorted_ads[$_group->term_id][$_ad->ID] = $_ad;
					$groups[$_group->term_id] = $_group;
				}
			}
		}

		$sorted_ads['unsorted'] = $unsorted_ads;

		// order values
		sort( $widths );
		// remove duplicates, rebase keys and exchange keys with values
		$widths = array_flip( array_values( array_unique( $widths ) ) );
		$max_columns = count( $widths );

		include_once( 'views/list.php' );
	}
}
