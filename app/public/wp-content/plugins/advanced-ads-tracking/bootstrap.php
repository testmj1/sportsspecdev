<?php

define( 'AAT_FILE', __DIR__ . '/tracking.php' );
define( 'AAT_BASE', plugin_basename( AAT_FILE ) );
define( 'AAT_BASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'AAT_BASE_URL', plugin_dir_url( __FILE__ ) );
define( 'AAT_BASE_DIR', dirname( plugin_basename( __FILE__ ) ) );
// used as prefix for wp options; used as gettext domain; used as script/ admin namespace
define( 'AAT_SLUG', 'advads-tracking' );
define( 'AAT_VERSION', '2.7.1' );

define( 'AAT_PLUGIN_URL', 'https://wpadvancedads.com' );
define( 'AAT_PLUGIN_NAME', 'Tracking' );
// impressions shortcode
define( 'AAT_IMP_SHORTCODE', 'the_ad_impressions' );

// autoload
require_once AAT_BASE_PATH . 'lib/autoload.php';
