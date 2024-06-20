<?php

global $theme_url;
$theme_url = get_template_directory_uri() . '/';

$template_directory = get_template_directory();

/*
 * The print_r function with <pre> tags for clear view
 */
if ( !function_exists(i_print) ) {
    function i_print( $array )
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}

require($template_directory . '/inc/updater/theme-updater.php');
require($template_directory . '/inc/theme-options.php');
require($template_directory . '/inc/shortcodes.php');
if ( !class_exists('ThemeUpdateChecker') ) {
    require($template_directory . '/inc/theme-update-checker/theme-update-checker.php');
}
$example_update_checker = new ThemeUpdateChecker(
    'wiserthemes',
    'https://bitbucket.org/wisersites/wiserthemes-updates/raw/master/info.json'
);

function wiser_wp_title( $title, $sep )
{
    global $paged, $page;

    if ( is_feed() )
        return $title;

    // Add the site name.
    $title .= get_bloginfo('name', 'display');

    // Add the site description for the home/front page.
    $site_description = get_bloginfo('description', 'display');
    if ( $site_description && (is_home() || is_front_page()) )
        $title = "$title $sep $site_description";

    // Add a page number if necessary.
    if ( ($paged >= 2 || $page >= 2) && !is_404() )
        $title = "$title $sep " . sprintf(__('Page %s', 'wisersites'), max($paged, $page));

    return $title;
}

add_filter('wp_title', 'wiser_wp_title', 10, 2);


/**
 * Register three Twenty Fourteen widget areas.
 *
 * @since Twenty Fourteen 1.0
 */
function wiser_widgets_init()
{
    //require get_template_directory() . '/inc/widgets.php';
    //register_widget( 'Twenty_Fourteen_Ephemera_Widget' );

    register_sidebar(array(
        'name' => __('Primary Sidebar', 'wisersites'),
        'id' => 'sidebar-left',
        'description' => __('Main sidebar that appears on the left.', 'wisersites'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ));
    register_sidebar(array(
        'name' => __('Content Sidebar', 'wisersites'),
        'id' => 'sidebar-right',
        'description' => __('Additional sidebar that appears on the right.', 'wisersites'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ));
    register_sidebar(array(
        'name' => __('Footer Widget Area', 'wisersites'),
        'id' => 'sidebar-footer',
        'description' => __('Appears in the footer section of the site.', 'wisersites'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ));
}

add_action('widgets_init', 'wiser_widgets_init');

if ( !is_admin() ) {
    /**
     * Replaces "[...]" (appended to automatically generated excerpts) with ...
     * and a Continue reading link.
     *
     * @since Wiser Sites 1.0
     *
     * @param string $more Default Read More excerpt link.
     * @return string Filtered Read More excerpt link.
     */
    function wiser_excerpt_more( $more )
    {
        $link = sprintf('<a href="%1$s" class="more-link">%2$s</a>',
            esc_url(get_permalink(get_the_ID())),
            /* translators: %s: Name of current post */
            __('Read more')
        //sprintf(__('Continue reading %s <span class="meta-nav">&rarr;</span>', 'twentythirteen'), '<span class="screen-reader-text">' . get_the_title(get_the_ID()) . '</span>')
        );

        return ' &hellip; ' . $link;
    }

    add_filter('excerpt_more', 'wiser_excerpt_more');
}

function remove_header_info()
{
    add_post_type_support('page', 'excerpt');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'start_post_rel_link');
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'adjacent_posts_rel_link');
    remove_action('wp_head', 'keywords');
    remove_action('wp_head', 'description');
}

//add_action('init', 'remove_header_info');


//Theme enqueue scripts
function wiser_theme_scripts()
{
    global $theme_url;

    wp_enqueue_style('css_bootstrap', $theme_url . "resources/css/bootstrap/css/bootstrap.min.css");
    //wp_enqueue_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
    wp_enqueue_style('style', get_stylesheet_uri()); //wp_enqueue_style( 'style', $theme_url . "style.css");
    wp_enqueue_style('responsive', $theme_url . "resources/css/responsive.css");
    //wp_enqueue_style('amazonka', "https://d1epsqwi8vjoim.cloudfront.net/styles.css");
}

add_action('wp_enqueue_scripts', 'wiser_theme_scripts');

function wiser_theme_foot()
{
    global $theme_url, $wiser_option;

    wp_enqueue_script('jquery');

    //wp_enqueue_script('jquery-ui-tooltip');
    //Sticky JS
    wp_enqueue_script('sticky_js', $theme_url . 'resources/js/jquery.sticky.js', array('jquery'), null, true);

    wp_enqueue_script('js_bootstrap', $theme_url . 'resources/css/bootstrap/js/bootstrap.js');
    wp_enqueue_script('wiser_js', $theme_url . "resources/js/js.js");
    wp_localize_script('wiser_js', 'wiser_options', array(
            'fixed_header_menu' => ($wiser_option['fixed_header_menu']) ? '1' : '0'
        )
    );
}

add_action('wp_footer', 'wiser_theme_foot');

//I Disable admin bar in frontend
/*add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() { show_admin_bar(false); }*/

add_theme_support('post-thumbnails');

//Register nav menus
if ( function_exists('register_nav_menus') ) {
    register_nav_menus(
        array(
            'main_menu' => 'Main Navigation Menu',
            'foot_menu' => 'Footer Navigation Menu',
            'foot_menu_2' => 'Footer Navigation Menu 2',
            'foot_menu_3' => 'Footer Navigation Menu 3',
        )
    );
}

add_filter('comments_open', 'my_comments_open', 10, 2);
function my_comments_open( $open, $post_id )
{

    global $wiser_option;
    if ( !$wiser_option['enable_comments'] )
        $open = false;

    return $open;
}


function i_get_fields( $post_id )
{
    if ( function_exists('get_fields') ) {
        $infos = get_fields($post_id);
    } else {
        $infos = get_post_meta($post_id);
    }

    return $infos;
}

function i_get_field( $post_id, $field_name )
{
    if ( function_exists('get_field') ) {
        $info = get_field($field_name, $post_id);
    } else {
        $info = get_post_meta($post_id, $field_name, true);
    }

    return $info;
}


// wiser_breadcrumb
function wiser_breadcrumb()
{
    global $wiser_option;
    if ( !isset($wiser_option['breadcrumb']) || !$wiser_option['breadcrumb'] ) return;
    //
    /* === OPTIONS === */
    $breadcrums_id = 'wiser_crumbs';

    $text['home'] = __('Home', 'wisersites'); // text for the 'Home' link
    $text['category'] = __('Archive by Category', 'wisersites') . ' "%s"'; // text for a category page
    $text['search'] = __('Search Results for', 'wisersites') . ' "%s" Query'; // text for a search results page
    $text['tag'] = __('Posts Tagged', 'wisersites') . ' "%s"'; // text for a tag page
    $text['author'] = __('Articles Posted by', 'wisersites') . ' %s'; // text for an author page
    $text['404'] = __('Error 404', 'wisersites'); // text for the 404 page
    $text['page'] = __('Page', 'wisersites') . ' %s'; // text 'Page N'
    $text['cpage'] = __('Comment Page', 'wisersites') . ' %s'; // text 'Comment Page N'

    $delimiter = '/'; // delimiter between crumbs //&gt;
    $delim_before = '<span class="divider">'; // tag before delimiter
    $delim_after = '</span>'; // tag after delimiter
    $show_home_link = 1; // 1 - show the 'Home' link, 0 - don't show
    $show_on_home = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
    $show_current = 1; // 1 - show current page title, 0 - don't show
    $show_title = 1; // 1 - show the title for the links, 0 - don't show
    $before = '<span class="current"><span>'; // tag before the current crumb
    $after = '</span></span>'; // tag after the current crumb
    /* === END OF OPTIONS === */

    global $post;
    $home_link = home_url('/');
    $link_before = '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
    $link_after = '</span>';
    $link_attr = ' itemprop="url"';
    $link_in_before = '<span itemprop="title">';
    $link_in_after = '</span>';
    $link = $link_before . '<a href="%1$s"' . $link_attr . '>' . $link_in_before . '%2$s' . $link_in_after . '</a>' . $link_after;
    $frontpage_id = get_option('page_on_front');
    $parent_id = $post->post_parent;
    $delimiter = ' ' . $delim_before . $delimiter . $delim_after . ' ';

    if ( is_home() || is_front_page() ) {

        if ( $show_on_home == 1 ) echo '<div id="' . $breadcrums_id . '" class="breadcrumbs"><a href="' . $home_link . '">' . $text['home'] . '</a></div>';

    } else {

        echo '<div id="' . $breadcrums_id . '" class="breadcrumbs">';
        if ( $show_home_link == 1 ) echo sprintf($link, $home_link, $text['home']);

        if ( is_category() ) {
            $cat = get_category(get_query_var('cat'), false);
            if ( $cat->parent != 0 ) {
                $cats = get_category_parents($cat->parent, TRUE, $delimiter);
                $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
                $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr . '>' . $link_in_before . '$2' . $link_in_after . '</a>' . $link_after, $cats);
                if ( $show_title == 0 ) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
                if ( $show_home_link == 1 ) echo $delimiter;
                echo $cats;
            }
            if ( get_query_var('paged') ) {
                $cat = $cat->cat_ID;
                echo $delimiter . sprintf($link, get_category_link($cat), get_cat_name($cat)) . $delimiter . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ( $show_current == 1 ) echo $delimiter . $before . sprintf($text['category'], single_cat_title('', false)) . $after;
            }

        } elseif ( is_search() ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            echo $before . sprintf($text['search'], get_search_query()) . $after;

        } elseif ( is_day() ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
            echo sprintf($link, get_month_link(get_the_time('Y'), get_the_time('m')), get_the_time('F')) . $delimiter;
            echo $before . get_the_time('d') . $after;

        } elseif ( is_month() ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
            echo $before . get_the_time('F') . $after;

        } elseif ( is_year() ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            echo $before . get_the_time('Y') . $after;

        } elseif ( is_single() && !is_attachment() ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            if ( get_post_type() != 'post' ) {
                $post_type = get_post_type_object(get_post_type());
                $slug = $post_type->rewrite;
                printf($link, $home_link . '/' . $slug['slug'] . '/', $post_type->labels->singular_name);
                if ( $show_current == 1 ) echo $delimiter . $before . get_the_title() . $after;
            } else {
                $cat = get_the_category();
                $cat = $cat[0];
                $cats = get_category_parents($cat, TRUE, $delimiter);
                if ( $show_current == 0 || get_query_var('cpage') ) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
                $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr . '>' . $link_in_before . '$2' . $link_in_after . '</a>' . $link_after, $cats);
                if ( $show_title == 0 ) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
                echo $cats;
                if ( get_query_var('cpage') ) {
                    echo $delimiter . sprintf($link, get_permalink(), get_the_title()) . $delimiter . $before . sprintf($text['cpage'], get_query_var('cpage')) . $after;
                } else {
                    if ( $show_current == 1 ) echo $before . get_the_title() . $after;
                }
            }

            // custom post type
        } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
            $post_type = get_post_type_object(get_post_type());
            if ( get_query_var('paged') ) {
                echo $delimiter . sprintf($link, get_post_type_archive_link($post_type->name), $post_type->label) . $delimiter . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ( $show_current == 1 ) echo $delimiter . $before . $post_type->label . $after;
            }

        } elseif ( is_attachment() ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            $parent = get_post($parent_id);
            $cat = get_the_category($parent->ID);
            $cat = $cat[0];
            if ( $cat ) {
                $cats = get_category_parents($cat, TRUE, $delimiter);
                $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr . '>' . $link_in_before . '$2' . $link_in_after . '</a>' . $link_after, $cats);
                if ( $show_title == 0 ) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
                echo $cats;
            }
            printf($link, get_permalink($parent), $parent->post_title);
            if ( $show_current == 1 ) echo $delimiter . $before . get_the_title() . $after;

        } elseif ( is_page() && !$parent_id ) {
            if ( $show_current == 1 ) echo $delimiter . $before . get_the_title() . $after;

        } elseif ( is_page() && $parent_id ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            if ( $parent_id != $frontpage_id ) {
                $breadcrumbs = array();
                while ( $parent_id ) {
                    $page = get_page($parent_id);
                    if ( $parent_id != $frontpage_id ) {
                        $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
                    }
                    $parent_id = $page->post_parent;
                }
                $breadcrumbs = array_reverse($breadcrumbs);
                for ( $i = 0; $i < count($breadcrumbs); $i++ ) {
                    echo $breadcrumbs[ $i ];
                    if ( $i != count($breadcrumbs) - 1 ) echo $delimiter;
                }
            }
            if ( $show_current == 1 ) {
                if ( $parent_id != $frontpage_id ) {
                    echo $delimiter;
                }
                echo $before . get_the_title() . $after;
            }

        } elseif ( is_tag() ) {
            if ( $show_current == 1 ) echo $delimiter . $before . sprintf($text['tag'], single_tag_title('', false)) . $after;

        } elseif ( is_author() ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            global $author;
            $author = get_userdata($author);
            echo $before . sprintf($text['author'], $author->display_name) . $after;

        } elseif ( is_404() ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            echo $before . $text['404'] . $after;

        } elseif ( has_post_format() && !is_singular() ) {
            if ( $show_home_link == 1 ) echo $delimiter;
            echo get_post_format_string(get_post_format());
        }

        echo '</div><!-- .breadcrumbs -->';

    }

}

function can_tracking()
{
    global $wiser_option;
    if ( is_user_logged_in() )
        return false;

    if ( !$wiser_option['exclude_analytics_tracking'] ) return true;
    $exclude_ips = $wiser_option['exclude_ips_analytics_tracking'];
    if ( trim($exclude_ips) != '' ) {
        $exclude_ips = explode(PHP_EOL, $exclude_ips);
    } else {
        $exclude_ips = array();
    }

    if ( is_user_logged_in() || in_array(get_client_ip(), $exclude_ips) ) {
        return false;
    }

    return true;
}

function get_client_ip()
{
    $ipaddress = '';
    if ( getenv('HTTP_CLIENT_IP') )
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if ( getenv('HTTP_X_FORWARDED_FOR') )
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if ( getenv('HTTP_X_FORWARDED') )
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if ( getenv('HTTP_FORWARDED_FOR') )
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if ( getenv('HTTP_FORWARDED') )
        $ipaddress = getenv('HTTP_FORWARDED');
    else if ( getenv('REMOTE_ADDR') )
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}

/*
 * The print_r function with <pre> tags for clear view
 */
if ( !function_exists("i_print") ) {
    function i_print( $array )
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}
/////////////////////


//redirect attachment pages to their parent page.
if ( ! defined( 'ATTACHMENT_REDIRECT_CODE' ) )
	define ( 'ATTACHMENT_REDIRECT_CODE', '301' ); // Default redirect code for attachments with existing parent post

if ( ! defined( 'ORPHAN_ATTACHMENT_REDIRECT_CODE' ) )
	define ( 'ORPHAN_ATTACHMENT_REDIRECT_CODE', '302' ); // Default redirect code for attachments with no parent post


function sar_attachment_redirect() {

	global $post;

	if ( is_attachment() && isset( $post->post_parent ) && is_numeric( $post->post_parent ) && ( $post->post_parent != 0 ) ) {

		$parent_post_in_trash = get_post_status( $post->post_parent ) === 'trash' ? true : false; 

		if ( $parent_post_in_trash ) { 
			 wp_die( 'Page not found.', '404 - Page not found', 404 ); // Prevent endless redirection loop in old WP releases and redirecting to trashed posts if an attachment page is visited when parent post is in trash
		}

		wp_safe_redirect( get_permalink( $post->post_parent ), ATTACHMENT_REDIRECT_CODE ); // Redirect to post/page from where attachment was uploaded
		exit;	

	} elseif ( is_attachment() && isset( $post->post_parent ) && is_numeric( $post->post_parent ) && ( $post->post_parent < 1 ) ) {

		wp_safe_redirect( get_bloginfo( 'wpurl' ), ORPHAN_ATTACHMENT_REDIRECT_CODE ); // Redirect to home for attachments not associated to any post/page
		exit;

	}


}

add_action( 'template_redirect', 'sar_attachment_redirect', 1 );


?>