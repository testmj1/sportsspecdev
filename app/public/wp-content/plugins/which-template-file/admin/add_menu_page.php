<?php
/**
 * @author Gilles Dumas <circusmind@gmail.com>
 * @date   20150225
 * @link   http://codex.wordpress.org/Function_Reference/add_users_page
 * @link   http://codex.wordpress.org/Function_Reference/add_menu_page
 */
class wtf_admin_page extends class_page_admin {

    /*
     * Nonce name.
     * */
    private $nonce_name = 'wtf-nonce-3';

    /*
     * Nonce value.
     * */
    private $nonce_value;

    /**
     * Start up
     */
    public function __construct() {
	    add_action( 'init',       array( $this, 'init' ) );
	    add_action( 'admin_init', array( $this, 'admin_init' ) );
	    add_action( 'admin_head', array( $this, 'admin_head' ) );
	    add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        parent::__construct( get_class() );
        
        $this->text = new stdClass;
        
        // Il faut paramétrer ceci.
        $this->text->tag_title  = 'Which Template File Options';
        $this->text->menu_title = 'Which Template File';
        $this->text->page_h2    = 'Which Template File Options';
    }

	/**
	 * Inits.
	 */
	public function init() {
        $this->nonce_value = wp_create_nonce( $this->nonce_name );
    }

	/**
	 * Les actions à effectuer au cas où l'utilisateur vienne de cliquer sur un lien avec des paramètres.
	 */
	public function admin_init() {
		if ( isset( $_GET[_WTF_OPTION_1] ) ) {
			if ( wp_verify_nonce( $_GET[$this->nonce_name], $this->nonce_name ) ) {
				if ( $_GET[_WTF_OPTION_1] == 'administrator' || $_GET[_WTF_OPTION_1] == 'all' ) {
                    $wtf_option_1 = htmlspecialchars( $_GET[_WTF_OPTION_1] );
                    update_option( _WTF_OPTION_1, $wtf_option_1 );
                    $this->notice_msg   = 'Setting updated !';
                    $this->notice_class = 'updated';
				}
				else {
					$this->notice_msg   = 'Bad setting value !';
					$this->notice_class = 'error';
				}
				add_action( 'admin_notices', array( $this, 'my_admin_notice' ) );
			}
			else {
				$this->notice_msg   = 'Bad nonce value, try again !';
				$this->notice_class = 'error';
				add_action( 'admin_notices', array( $this, 'my_admin_notice' ) );
			}
		}
	}

	/**
	 * Ajout de code dans le <head>.
	 */
	public function admin_head() {
		parent::admin_head( get_class() );
	}

	/**
	* Ajout de la page.
	* @author Gilles Dumas <circusmind@gmail.com>
	* @since 20150918
	* @link http://codex.wordpress.org/Function_Reference/add_menu_page
	*/
	function add_plugin_page() {
        $page_title = $this->text->tag_title;
        $menu_title = $this->text->menu_title;
        $capability = 'manage_options';
        $menu_slug  = get_class();
        $function   = array( $this, 'display_admin_page' );
        $icon_url   = null;
        $position   = '9996';
        add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    }

	/**
	 * Options page callback.
	* @author Gilles Dumas <circusmind@gmail.com>
	* @since 20140729
	*/
	function display_admin_page() {

        $title = $this->text->page_h2;
        parent::display_box_begin( $title, 'display_admin_page' );

        $wtf_option_1 = htmlspecialchars( get_option( _WTF_OPTION_1 ) );
        ?>

        <form name="newsletters-filter" id="newsletters-filter" method="get" action="?">
            <input type="hidden" name="<?php echo $this->nonce_name; ?>" value="<?php echo $this->nonce_value; ?>" />
            <input type="hidden" name="page" value="<?php echo htmlspecialchars( $_GET['page'] ); ?>" />
            
            <p>Who can see the template file name in the admin bar ?</p>
            
            <?php
            $checked = '';
            if ( $wtf_option_1 == 'administrator' || $wtf_option_1 == false ) {
                $checked = 'checked';
            }
            ?>
            <label>Administrators only
                <input type="radio" name="<?php echo _WTF_OPTION_1; ?>" value="administrator" <?php echo $checked; ?>>
            </label><br />
            
            <?php
            $checked = '';
            if ( $wtf_option_1 == 'all' ) {
                $checked = 'checked';
            }
            ?>
            <label>Every logged user
                <input type="radio" name="<?php echo _WTF_OPTION_1; ?>" value="all" <?php echo $checked; ?>>
            </label><br /><br />
            <input type="submit" class="button button-primary" />
        </form>
        <?php
        parent::display_box_stop();
    }

    /**
     * 
     */
    public function my_admin_notice() {
        ?>
        <div class="<?php echo $this->notice_class; ?>">
            <p><?php echo $this->notice_msg; ?></p>
        </div>
        <?php
        $this->notice_class = $this->notice_msg = '';
    }
    
	/**
	 * Génération des liens de bas de page
	 */
	function set_links_footer() {
		$this->links_footer = [];
	}
    
}

if( is_admin() ) $wtf_admin_page = new wtf_admin_page;
