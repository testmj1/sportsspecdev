<?php
/**
 * Advanced Ads – Tracking
 *
 * Plugin Name:       Advanced Ads – Tracking
 * Plugin URI:        https://wpadvancedads.com/add-ons/tracking/
 * Description:       Track ad impressions and clicks.
 * Version:           2.7.1
 * Author:            Advanced Ads GmbH
 * Author URI:        https://wpadvancedads.com
 * Text Domain:       advanced-ads-tracking
 * Domain Path:       /languages
 */

// return if we already have a version running, instead of showing error.
if ( defined( 'AAT_IMP_SHORTCODE' ) ) {
	return;
}

// load basic path and url to the plugin
require_once __DIR__ . '/bootstrap.php';

// init plugin
require_once __DIR__ . '/addon-init.php';

