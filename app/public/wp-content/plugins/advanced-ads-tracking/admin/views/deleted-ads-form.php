<?php

if ( ! current_user_can( advanced_ads_tracking_db_cap() ) ) {
	return;
}

$log_file          = Advanced_Ads_Tracking_Debugger::get_debug_file_path();
$delete_debug_link = admin_url( 'admin.php?page=advads-tracking-db-page&delete-debug-nonce=' . $_request['delete-debug-nonce'] );
$redirect_script   = sprintf(
	'<script type="text/javascript">document.location.href = "%s";</script>',
	add_query_arg( [
		'page'             => 'advads-tracking-db-page',
		'deleted_log_file' => true,
	], admin_url( 'admin.php' ) )
);

if ( get_filesystem_method() === 'direct' ) {
	unlink( $log_file );
	delete_option( Advanced_Ads_Tracking_Debugger::DEBUG_FILENAME_OPT );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- we can't escape the URL, no user input.
	echo $redirect_script;

	return;
}

$_POST['delete-debug-nonce'] = $_request['delete-debug-nonce'];
$extra_fields                = [ 'delete-debug-nonce' ];
$method                      = '';

echo '<style type="text/css">';
include AAT_BASE_PATH . 'admin/assets/css/filesystem-form.css';
echo '</style>';

$creds = request_filesystem_credentials( $delete_debug_link, $method, false, false, $extra_fields );
if ( $creds === false ) {
	return;
}

if ( ! WP_Filesystem( $creds ) ) {
	// our credentials were no good, ask the user for them again
	request_filesystem_credentials( $delete_debug_link, $method, false, false, $extra_fields );

	return;
}

global $wp_filesystem;
if ( ! $wp_filesystem->delete( $log_file ) ) {
	esc_attr_e( 'Failing to delete the log file.', 'advanced-ads-tracking' );
} else {
	delete_option( Advanced_Ads_Tracking_Debugger::DEBUG_FILENAME_OPT );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- we can't escape the URL, no user input.
	echo $redirect_script;
}
