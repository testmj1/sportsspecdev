<?php

add_action( 'plugins_loaded', 'advanced_ads_tracking_init_plugin' );

/**
 * Hook into plugins_loaded and init tracking plugin.
 */
function advanced_ads_tracking_init_plugin() {
	// Show admin notice if main plugin not active and return.
	if ( ! class_exists( 'Advanced_Ads', false ) ) {
		add_action( 'admin_notices', [ 'Advanced_Ads_Tracking_Admin', 'missing_plugin_notice' ] );

		return;
	}

	$is_admin = is_admin();
	$is_ajax  = apply_filters( 'advanced_ads_tracking_is_ajax', wp_doing_ajax() );

	if ( $is_ajax ) {
		new Advanced_Ads_Tracking_Ajax();
		// Hook into resetting stats for ad.
		add_action( 'advanced-ads-pre-reset-stats', [ 'Advanced_Ads_Tracking_Limiter', 'reset_stats' ] );
	}

	// instantiate the public class.
	new Advanced_Ads_Tracking( $is_admin, $is_ajax );

	// only admin, not ajax (which is always admin)
	if ( $is_admin && ! $is_ajax ) {
		// instantiate tracking admin.
		new Advanced_Ads_Tracking_Admin();

		// register limiter on current ad.
		add_action( 'current_screen', 'advanced_ads_tracking_limiter_init' );

		/**
		 * If the current screen is an ad, get the ID and init the limiter.
		 *
		 * @param WP_Screen $screen current WP_Screen object.
		 */
		function advanced_ads_tracking_limiter_init( WP_Screen $screen ) {
			if ( $screen->post_type !== Advanced_Ads::POST_TYPE_SLUG ) {
				return;
			}

			$ad_id = get_the_ID();
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( empty( $ad_id ) && isset( $_POST['post_ID'] ) ) {
				$ad_id = (int) $_POST['post_ID'];
			}
			// phpcs:enable
			if ( ! $ad_id ) {
				return;
			}

			new Advanced_Ads_Tracking_Limiter( $ad_id );
		}
	}

	// register AJAX actions for Database Operations.
	if ( $is_admin && current_user_can( advanced_ads_tracking_db_cap() ) ) {
		require_once AAT_BASE_PATH . '/classes/db-operations.php';
		Advanced_Ads_Tracking_Dbop::get_instance();
	}

	// if this ad has expired, delete all associated limiter crons.
	add_action( 'advanced-ads-ad-expired', [ 'Advanced_Ads_Tracking_Limiter', 'remove_events_for_ad' ] );

	// register limiter cron hooks.
	Advanced_Ads_Tracking_Limiter::register_event_hooks();

	// Register AMP actions for the different plugins.
	add_action( 'wp', [ new Advanced_Ads_Tracking_Amp(), 'register_actions' ] );

	// check if debugging is enabled
	Advanced_Ads_Tracking_Debugger::check_debugging_constant();

	// check debugging expiration
	Advanced_Ads_Tracking_Debugger::delete_expired_option();

	// load compatibility class
	new Advanced_Ads_Tracking_Compatibility();
}

/**
 *  Activation hook
 */
function advanced_ads_tracking_activation() {
	require_once AAT_BASE_PATH . '/admin/admin.php';
	Advanced_Ads_Tracking_Admin::create_tables();
	$ajax_handler_installer = new Advanced_Ads_Tracking_Installer();
	$ajax_handler_installer::trigger_installer_update();
	$ajax_handler_installer->install();
}
register_activation_hook( AAT_FILE, 'advanced_ads_tracking_activation' );

/**
 * Register plugin deactivation hook.
 */
function advanced_ads_tracking_deactivation() {
	Advanced_Ads_Tracking::deactivate();
	( new Advanced_Ads_Tracking_Installer() )->uninstall();
	Advanced_Ads_Tracking_Limiter::deactivate();
}
register_deactivation_hook( AAT_FILE, 'advanced_ads_tracking_deactivation' );

/**
 * Add link to settings page from plugins.php
 *
 * @var array $links
 * @return array
 */
add_action(
	'plugin_action_links_' . AAT_BASE,
	function( $links ) {
		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( 'page', 'advanced-ads-settings', get_admin_url() . 'admin.php' ) ) . '#top#tracking',
			__( 'Settings', 'advanced-ads-tracking' )
		);

		return $links;
	}
);

/**
 * Install/Initialize custom ajax handler.
 */
function advanced_ads_tracking_activate_installer() {
	( new Advanced_Ads_Tracking_Installer() )->install();
}

add_action( 'admin_init', 'advanced_ads_tracking_activate_installer' );

/**
 *  Wrapper for translation from other domains
 */
function advads__( $text, $domain = 'default' ) {
	return translate( $text, $domain );
}

/**
 *  Wrapper for translation from other domain with context
 */
function advads_x( $text, $context, $domain = 'default' ) {
	return translate_with_gettext_context( $text, $context, $domain );
}

function advads_n( $single, $plural, $number, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translation  = $translations->translate_plural( $single, $plural, $number );

	return apply_filters( 'ngettext', $translation, $single, $plural, $number, $domain );
}

/**
 *  Echo translation from other domains
 */
function advads_e( $text, $domain = 'default' ) {
	echo advads__( $text, $domain );
}

/**
 *  Capability needed for database operations (compress/export etc)
 */
function advanced_ads_tracking_db_cap() {
	$default_cap = 'manage_options';
	return apply_filters( 'advanced-ads-tracking-dbop-capability', $default_cap );
}
