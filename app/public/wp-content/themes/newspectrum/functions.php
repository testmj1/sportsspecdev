<?php

// Adds Dynamic Titles
function newspectrum_theme_support(){
    add_theme_support('title-tag');
}

add_action( 'after_setup_theme', 'newspectrum_theme_support' );

// Adds Menu items
function spectrum_menus(){

    $locations = array(

        'primary' => "Main top bar menu",
        'footer' => "Main Footer",

    );

    register_nav_menus($locations);

}

add_action('init', 'spectrum_menus');

function additional_menu_classes($classes, $item, $args) {
    if($args->theme_location == 'primary') {
      $classes[] = 'nav-item';
    }
    return $classes;
}

add_filter('nav_menu_css_class', 'additional_menu_classes', 1, 3);


function add_link_atts($atts) {
    $atts['class'] = "nav-link";
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'add_link_atts');


function newspectrum_register_styles(){

    $version = wp_get_theme()->get( 'Version' );
    wp_enqueue_style('newspectrum', get_template_directory_uri() . "/style.css", array(), $version, 'all' );
    wp_enqueue_style('newspectrum-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' , array(), '5.3.3', 'all' );
    wp_enqueue_style('newspectrum-fontawesome', 'https://kit.fontawesome.com/0386aaad3a.css' , array(), '1.0', 'all' );
}

add_action( 'wp_enqueue_scripts', 'newspectrum_register_styles' );


function newspectrum_register_scripts(){

    wp_enqueue_script('newspectrum-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array(), '5.3.3', true);
    wp_enqueue_style('newspectrum-script', get_template_directory_uri() . "/assets/js/main.js", array(), '1.0', true);
}

add_action( 'wp_enqueue_scripts', 'newspectrum_register_scripts' );




  function cw_post_type_videos() {
    $supports = array(
    'title', // post title
    'editor', // post content
    'author', // post author
    'thumbnail', // featured images
    'post-formats', // post formats
    );
    $labels = array(
    'name' => _x('Videos', 'plural'),
    'singular_name' => _x('video', 'singular'),
    'menu_name' => _x('Videos', 'admin menu'),
    'name_admin_bar' => _x('Videos', 'admin bar'),
    'add_new' => _x('Add New Video', 'add new'),
    'add_new_item' => __('Add New Video'),
    'new_item' => __('New Video'),
    'edit_item' => __('Edit'),
    'view_item' => __('View'),
    'all_items' => __('All Videos'),
    'search_items' => __('Search videos'),
    'not_found' => __('No videos found.'),
    );
    $args = array(
    'supports' => $supports,
    'labels' => $labels,
    'public' => true,
    'query_var' => true,
    'rewrite' => array('slug' => 'videos'),
    'has_archive' => false,
    'hierarchical' => false,
    'menu_icon'   => 'dashicons-format-video',
    );
    register_post_type('videos', $args);
    }
    add_action('init', 'cw_post_type_videos');

    function cw_post_type_devotionals() {
        $supports = array(
        'title', // post title
        'editor', // post content
        'author', // post author
        'thumbnail', // featured images
        'post-formats', // post formats
        );
        $labels = array(
        'name' => _x('Devotionals', 'plural'),
        'singular_name' => _x('devotional', 'singular'),
        'menu_name' => _x('Devotionals', 'admin menu'),
        'name_admin_bar' => _x('Devotionals', 'admin bar'),
        'add_new' => _x('Add New Devotional', 'add new'),
        'add_new_item' => __('Add New Devotional'),
        'new_item' => __('New Devotional'),
        'edit_item' => __('Edit'),
        'view_item' => __('View'),
        'all_items' => __('All Devotionals'),
        'search_items' => __('Search Devotionals'),
        'not_found' => __('No Devotionals found.'),
        );
        $args = array(
        'supports' => $supports,
        'labels' => $labels,
        'public' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'devotionals'),
        'has_archive' => false,
        'hierarchical' => false,
        'menu_icon'   => 'dashicons-format-book',
        );
        register_post_type('devotionals', $args);
        }
        add_action('init', 'cw_post_type_devotionals');
    

?>