<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}

if ( defined( 'AAT_IMP_SHORTCODE' ) ) {
	return;
}

// load basic path and url to the plugin
require_once __DIR__ . '/bootstrap.php';

if ( class_exists( 'Advanced_Ads_Admin_Licenses' ) ) {
	$addon          = 'tracking';
	$plugin_name    = 'Tracking';
	$options_slug   = 'advanced-ads-tracking';
	$advads_license = Advanced_Ads_Admin_Licenses::get_instance();
	$license_status = $advads_license->get_license_status( $options_slug );
	if ( $license_status === 'valid' ) {
		$advads_license->deactivate_license( $addon, $plugin_name, $options_slug );
	}
}

global $wpdb;

// remove ajax drop-in.
$installer = new Advanced_Ads_Tracking_Installer();
$installer->uninstall();

/**
 * Remove database options and tables if user wishes.
 */
function advanced_ads_tracking_uninstall() {
	global $wpdb;
	$options_name     = 'advanced-ads-tracking';
	$tracking_options = get_option( $options_name );
	if ( isset( $tracking_options['uninstall'] ) && $tracking_options['uninstall'] ) {
		$_impr   = $wpdb->prefix . 'advads_impressions';
		$_clicks = $wpdb->prefix . 'advads_clicks';
		$wpdb->query( "DROP TABLE IF EXISTS ${_impr}, ${_clicks};" );
		delete_option( $options_name );
		delete_option( Advanced_Ads_Tracking_Debugger::DEBUG_FILENAME_OPT );
		delete_option( Advanced_Ads_Tracking_Debugger::DEBUG_OPT );
	}
}

if ( is_multisite() ) {
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'" );
	foreach ( $blog_ids as $temp_blog_id ) {
		switch_to_blog( $temp_blog_id );
		advanced_ads_tracking_uninstall();
	}
	restore_current_blog();

	return;
}

advanced_ads_tracking_uninstall();
