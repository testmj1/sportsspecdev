<?php
//prev was https://checkout.subscriptiongenius.com/sportsspectrum.com/
class WFASBL {

    private static $initiated = false;

	public static function init( ) {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}

	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks( ) {
		self::$initiated = true;

        //add_action( 'get_header', array('WFASBL', 'wiser_form_scripts') );
        //add_action( 'wp_enqueue_scripts', array('WFASBL', 'load_resources') );
        //add_action( 'get_footer', array('WFASBL', 'wiser_get_form_data') );
	}

    public static function plugin_activation( ) {
	}

    public function load_resources(){
        wp_enqueue_script( 'fancybox_js', WFASBL_PLUGIN_URL.'resources/js/fancybox/jquery.fancybox.js', array( 'jquery' ));
        wp_enqueue_script( 'wfasbl-script', WFASBL_PLUGIN_URL.'resources/js/wfasbl-script.js', array( 'jquery' ));

        wp_enqueue_style ( 'fancybox_css', WFASBL_PLUGIN_URL.'resources/js/fancybox/jquery.fancybox.css');
        wp_enqueue_style ( 'wfasbl_css', WFASBL_PLUGIN_URL.'resources/css/style.css');
    }
    function wiser_form_scripts(){

    }
    function wiser_get_form_data(){
        echo '<div id="wfasbl_form_div" style="display: none">';
        //require_once ( WFASBL_PLUGIN_DIR . 'view/form_html.php' );
        echo '</div>';

        echo '<div id="wfasbl_form2_div" style="display: none">';
        //require_once ( WFASBL_PLUGIN_DIR . 'view/form2_html.php' );
        echo '</div>';
    }

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation( ) {
        //flush_rewrite_rules();
	}

}