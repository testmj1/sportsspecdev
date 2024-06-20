<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Theme Options
 *
 *
 * @file           theme-options.php
 * @package        Wiser Sites
 * @author         WiserSites
 * @copyright      2015 WiserSites
 * @license        license.txt
 * @version        Release: 1.0.0
 * @filesource     wp-content/themes/wisersites/inc/theme-options.php
 * @link
 * @since          available since Release 1.0
 */


define('WONAME', 'wiser_option');
global $wiser_option;
$wiser_option = get_option(WONAME, true);


if (!is_admin()) return;

/*
 * Setup Theme Admin Menus
 */
function wiser_setup_theme_admin_menus()
{
    add_action('admin_init', 'register_wiser_settings');
    add_menu_page('Theme settings', 'PAO19 Settings', 'manage_options', 'wiser_theme_settings', 'wiser_theme_settings_page', 'dashicons-art');
}

add_action("admin_menu", "wiser_setup_theme_admin_menus");


function register_wiser_settings()
{
    register_setting('wiser_option_settings', WONAME);
    register_setting('wiser_option_settings', 'blogname');
    register_setting('wiser_option_settings', 'blogdescription');
}

function wiser_theme_settings_page()
{
    $wiser_options = get_option(WONAME, true); //i_print( $wiser_options );
    $theme_url = get_template_directory_uri() . '/';
    /*wp_enqueue_script('editor');
    wp_enqueue_script('tiny_mce');
    wp_enqueue_script('thickbox');
    */
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_media();

    wp_enqueue_script('theme_option_js', $theme_url . '/inc/theme-options/js.js', array('wp-color-picker'), false, true);

    echo '<link rel="stylesheet" href="' . $theme_url . '/resources/css/bootstrap/css/bootstrap.min.css" type="text/css" media="all">';
    echo '<link rel="stylesheet" href="' . $theme_url . '/inc/theme-options/style.css" type="text/css" media="all">';
    echo '<script type="text/javascript" src="' . $theme_url . '/inc/theme-options/js.js" ></script>';

    $sections = apply_filters('wiser_option_sections_filter',
        array(
            array(
                'title' => __('General', 'wisersites'),
                'id' => 'general'
            ),
            array(
                'title' => __('Theme Elements', 'wisersites'),
                'id' => 'theme_elements'
            ),
            array(
                'title' => __('Logo Upload', 'wisersites'),
                'id' => 'logo_upload'
            ),
            array(
                'title' => __('Home Page', 'wisersites'),
                'id' => 'home_page'
            ),
//            array(
//                'title' => __( 'Social Icons', 'wisersites' ),
//                'id'    => 'social'
//            ),
            array(
                'title' => __('Header Elements', 'wisersites'),
                'id' => 'header_elements'
            ),
//            array(
//                'title' => __( 'Sidebar Elements', 'wisersites' ),
//                'id'    => 'sidebar_elements'
//            ),
//            array(
//                'title' => __( 'Blog Elements', 'wisersites' ),
//                'id'    => 'blog_elements'
//            )
        )
    );

    $options = apply_filters('wiser_options_filter',
        array(
            'general' => array(
                array(
                    'title' => __('Site Title', 'wisersites'),
                    'subtitle' => __('Blogname', 'wisersites'),
                    'type' => 'text',
                    'id' => 'blogname',
                    'global_option' => 'true',
                    'description' => __('Change your Site Title', 'wisersites')
                ),
                array(
                    'title' => __('Tagline', 'wisersites'),
                    'subtitle' => __('About site', 'wisersites'),
                    'type' => 'text',
                    'id' => 'blogdescription',
                    'global_option' => 'true',
                    'description' => __('In a few words, explain what this site is about.', 'wisersites')
                ),
                array(
                    'title' => __('Blog pages show at most', 'wisersites'),
                    'subtitle' => __('', 'wisersites'),
                    'type' => 'number',
                    'id' => 'posts_per_page',
                    'global_option' => 'true',
                    'description' => __('How many posts show up on your archive pages ?', 'wisersites')
                ),
                array(
                    'title' => __('Site Description', 'wisersites'),
                    'subtitle' => 'Enter site Description here',
                    'heading' => '',
                    'type' => 'textarea_editor',
                    'id' => 'site_description',
                    'description' => '',
                    'placeholder' => __('Enter site Description here', 'wisersites')
                ),
            ),
            'theme_elements' => array(
                array(
                    'title' => __('Enable breadcrumb list?', 'wisersites'),
                    'subtitle' => __('You can enable/disable breadcrumbs', 'wisersites'),
                    'heading' => '',
                    'type' => 'checkbox',
                    'id' => 'breadcrumb',
                    'description' => __('Check to enable', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Google Analytics Tracking Code', 'wisersites'),
                    'subtitle' => 'Enter your tracking code here for Google Analytics',
                    'heading' => '',
                    'type' => 'textarea',
                    'id' => 'analytics_code',
                    'description' => '',
                    'placeholder' => __('Custom Google Analytics Tracking Code', 'wisersites')
                ),
                array(
                    'title' => __('Exclude Google Analytics Tracking', 'wisersites'),
                    'subtitle' => 'Exclude Google Analytics Tracking for logged users and for special IP-s',
                    'heading' => '',
                    'type' => 'checkbox',
                    'id' => 'exclude_analytics_tracking',
                    'description' => '',
                    'placeholder' => __('Exclude Google Analytics Tracking for logged users', 'wisersites')
                ),
                array(
                    'title' => __('Exclude IP for Google Analytics Tracking', 'wisersites'),
                    'subtitle' => 'Select Exclude IP-s for Google Analytics Tracking',
                    'heading' => '',
                    'type' => 'textarea',
                    'id' => 'exclude_ips_analytics_tracking',
                    'description' => 'Fill each IP in new line',
                    'placeholder' => __('xxx.xxx.xxx.xxx', 'wisersites')
                ),
                array(
                    'title' => __('Header menu background color', 'wisersites'),
                    'subtitle' => __('Header menu background color ( none )', 'wisersites'),
                    'heading' => '',
                    'type' => 'color_picker',
                    'id' => 'header_menu_bgcolor',
                    'default' => 'none',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Header menu text color', 'wisersites'),
                    'subtitle' => __('Header menu text color ( #829097 )', 'wisersites'),
                    'heading' => '',
                    'type' => 'color_picker',
                    'id' => 'header_menu_color',
                    'default' => '#829097',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Header active menu background color', 'wisersites'),
                    'subtitle' => __('Header active menu background color ( #666666 )', 'wisersites'),
                    'heading' => '',
                    'type' => 'color_picker',
                    'id' => 'header_active_menu_bgcolor',
                    'default' => '#666666',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Header active menu text color', 'wisersites'),
                    'subtitle' => __('Header active menu text color ( #ffffff )', 'wisersites'),
                    'heading' => '',
                    'type' => 'color_picker',
                    'id' => 'header_active_menu_color',
                    'default' => '#ffffff',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Body background color', 'wisersites'),
                    'subtitle' => __('Body background color ( #ffffff )', 'wisersites'),
                    'heading' => '',
                    'type' => 'color_picker',
                    'id' => 'body_bg_color',
                    'default' => '#ffffff',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Content text color', 'wisersites'),
                    'subtitle' => __('Content text color ( #333333 )', 'wisersites'),
                    'heading' => '',
                    'type' => 'color_picker',
                    'id' => 'content_text_color',
                    'default' => '#333333',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Submit button color', 'wisersites'),
                    'subtitle' => __('Submit button color ( #458ac5 )', 'wisersites'),
                    'heading' => '',
                    'type' => 'color_picker',
                    'id' => 'submit_buttons_color',
                    'default' => '#458ac5',
                    'description' => '',
                    'placeholder' => ''
                ),

                array(
                    'title' => __('Footer background color', 'wisersites'),
                    'subtitle' => __('Footer background color ( #fafafa )', 'wisersites'),
                    'heading' => '',
                    'type' => 'color_picker',
                    'id' => 'footer_bgcolor',
                    'default' => '#fafafa',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Footer text color', 'wisersites'),
                    'subtitle' => __('Footer text color ( #829097 )', 'wisersites'),
                    'heading' => '',
                    'type' => 'color_picker',
                    'id' => 'footer_txt_color',
                    'default' => '#829097',
                    'description' => '',
                    'placeholder' => ''
                ),
            ),
            'logo_upload' => array(
                array(
                    'title' => __('Favicon', 'wisersites'),
                    'subtitle' => __('Change favicon', 'wisersites'),
                    'heading' => '',
                    'type' => 'image_url',
                    'id' => 'favicon',
                    'description' => '',
                    'placeholder' => 'favicon url'
                ),
                array(
                    'title' => __('Header Circle Logo', 'wisersites'),
                    'subtitle' => '',
                    'heading' => '',
                    'type' => 'image_url',
                    'id' => 'header_circle_logo',
                    'description' => '',
                    'placeholder' => __('Header Circle Logo', 'wisersites')
                ),
                array(
                    'title' => __('Header text Logo', 'wisersites'),
                    'subtitle' => '',
                    'heading' => '',
                    'type' => 'image_url',
                    'id' => 'header_text_logo',
                    'description' => '',
                    'placeholder' => __('Header Text Logo', 'wisersites')
                ),
//                array(
//                    'title'       => __( 'Sidebar Logo', 'wisersites' ),
//                    'subtitle'    => '',
//                    'heading'     => '',
//                    'type'        => 'image_url',
//                    'id'          => 'sidebar_logo',
//                    'description' => '',
//                    'placeholder' => __( 'Sidebar Logo', 'wisersites' )
//                ),
//                array(
//                    'title'       => __( 'Sidebar Logo Link', 'wisersites' ),
//                    'subtitle'    => __( 'Add Sidebar Logo Link', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'sidebar_logo_link',
//                    'default'      => '',
//                    'description' => __( 'Enter Sidebar Logo Link', 'wisersites' ),
//                    'placeholder' => ''
//                ),
            ),
            'home_page' => array(
                array(
                    'title' => __('Home Slider Slids Count', 'wisersites'),
                    'subtitle' => __('Slids Count', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'slides_count',
                    'default' => '3',
                    'description' => __('Enter Slids Count', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Select Post For Home Slider', 'wisersites'),
                    'subtitle' => __('You can select posts for Home Page Slider', 'wisersites'),
                    'heading' => '',
                    'type' => 'a_select_slider_posts',
                    'id' => 'select_slider_posts',
                    'description' => __('Select Post To Show', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( First Section )', 'wisersites'),
                    'subtitle' => __('First Section Title', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'first_section_title',
                    'default' => '',
                    'description' => __('Enter First Section Title', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( First Section )', 'wisersites'),
                    'subtitle' => 'First Section Image',
                    'heading' => '',
                    'type' => 'image_url',
                    'id' => 'first_section_image',
                    'description' => '',
                    'placeholder' => __('First Section Image', 'wisersites')
                ),
                array(
                    'title' => __('Home 3 Sections ( First Section )', 'wisersites'),
                    'subtitle' => __('First Section Button text', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'first_section_Button_text',
                    'default' => '',
                    'description' => __('Enter First Section Button text', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( First Section )', 'wisersites'),
                    'subtitle' => __('First Section Button Link', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'first_section_Button_link',
                    'default' => '',
                    'description' => __('Enter First Section Button link', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( First Section )', 'wisersites'),
                    'subtitle' => 'Open First Section Link On New Tab',
                    'heading' => '',
                    'type' => 'checkbox',
                    'id' => 'first_section_target',
                    'description' => '',
                    'placeholder' => __('Open First Section Link On New Tab', 'wisersites')
                ),
                array(
                    'title' => __('Home 3 Sections ( Second Section )', 'wisersites'),
                    'subtitle' => __('Second Section Title', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'second_section_title',
                    'default' => '',
                    'description' => __('Enter Second Section Title', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( Second Section )', 'wisersites'),
                    'subtitle' => 'Second Section Image',
                    'heading' => '',
                    'type' => 'image_url',
                    'id' => 'second_section_image',
                    'description' => '',
                    'placeholder' => __('Second Section Image', 'wisersites')
                ),
                array(
                    'title' => __('Home 3 Sections ( Second Section )', 'wisersites'),
                    'subtitle' => __('Second Section Button text', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'second_section_Button_text',
                    'default' => '',
                    'description' => __('Enter Second Section Button text', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( Second Section )', 'wisersites'),
                    'subtitle' => __('Second Section Button Link', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'second_section_Button_link',
                    'default' => '',
                    'description' => __('Enter Second Section Button link', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( Second Section )', 'wisersites'),
                    'subtitle' => 'Open Second Section Link On New Tab',
                    'heading' => '',
                    'type' => 'checkbox',
                    'id' => 'second_section_target',
                    'description' => '',
                    'placeholder' => __('Open Second Section Link On New Tab', 'wisersites')
                ),
                array(
                    'title' => __('Home 3 Sections ( Third Section )', 'wisersites'),
                    'subtitle' => __('Third Section Title', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'third_section_title',
                    'default' => '',
                    'description' => __('Enter Third Section Title', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( Third Section )', 'wisersites'),
                    'subtitle' => 'Third Section Image',
                    'heading' => '',
                    'type' => 'image_url',
                    'id' => 'third_section_image',
                    'description' => '',
                    'placeholder' => __('Third Section Image', 'wisersites')
                ),
                array(
                    'title' => __('Home 3 Sections ( Third Section )', 'wisersites'),
                    'subtitle' => __('Third Section Button text', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'third_section_Button_text',
                    'default' => '',
                    'description' => __('Enter Third Section Button text', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( Third Section )', 'wisersites'),
                    'subtitle' => __('Third Section Button Link', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'third_section_Button_link',
                    'default' => '',
                    'description' => __('Enter Third Section Button link', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Home 3 Sections ( Third Section )', 'wisersites'),
                    'subtitle' => 'Open Third Section Link On New Tab',
                    'heading' => '',
                    'type' => 'checkbox',
                    'id' => 'third_section_target',
                    'description' => '',
                    'placeholder' => __('Open Third Section Link On New Tab', 'wisersites')
                ),
                array(
                    'title' => __('Home Page Recent Post ( Post Blog or Category Filter )', 'wisersites'),
                    'subtitle' => __('Select Post Blog or Category Filte', 'wisersites'),
                    'heading' => '',
                    'type' => 'a_post_blog_or_category_filter',
                    'id' => 'post_blog_or_category_filter',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Chack categories', 'wisersites'),
                    'subtitle' => __('You can enable/disable Category for Home Page', 'wisersites'),
                    'heading' => '',
                    'type' => 'category_checkbox',
                    'id' => 'cat_checkbox',
                    'description' => __('Check to enable', 'wisersites'),
                    'placeholder' => ''
                ),
            ),
//            'social' => array(
//                array(
//                    'title'       => __( 'Social Title', 'wisersites' ),
//                    'subtitle'    => __( 'Header of social icons', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social_title',
//                    'default'      => 'Follow Us',
//                    'description' => __( 'Enter Title of social icons area', 'wisersites' ),
//                    'placeholder' => ''
//                ),
//                array(
//                    'title'       => __( 'Facebook', 'wisersites' ),
//                    'subtitle'    => __( 'Facebook page URL', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social',
//                    'id_key'      => 'facebook',
//                    'description' => __( 'Enter your Facebook URL', 'wisersites' ),
//                    'placeholder' => ''
//                ),  
//                array(
//                    'title'       => __( 'Twitter', 'wisersites' ),
//                    'subtitle'    => __( 'Twitter page URL', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social',
//                    'id_key'      => 'twitter',
//                    'description' => __( 'Enter your Twitter URL', 'wisersites' ),
//                    'placeholder' => ''
//                ),
//                array(
//                    'title'       => __( 'Google+', 'wisersites' ),
//                    'subtitle'    => __( 'Google+ page URL', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social',
//                    'id_key'      => 'google_plus',
//                    'description' => __( 'Enter your Google+ URL', 'wisersites' ),
//                    'placeholder' => ''
//                ),
//                array(
//                    'title'       => __( 'LinkedIn', 'wisersites' ),
//                    'subtitle'    => __( 'LinkedIn page URL', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social',
//                    'id_key'      => 'linkedin',
//                    'description' => __( 'Enter your LinkedIn URL', 'wisersites' ),
//                    'placeholder' => ''
//                ),
//                array(
//                    'title'       => __( 'YouTube', 'wisersites' ),
//                    'subtitle'    => __( 'YouTube page URL', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social',
//                    'id_key'      => 'youTube',
//                    'description' => __( 'Enter your YouTube URL', 'wisersites' ),
//                    'placeholder' => ''
//                ),
//                array(
//                    'title'       => __( 'Instagram', 'wisersites' ),
//                    'subtitle'    => __( 'Instagram page URL', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social',
//                    'id_key'      => 'instagram',
//                    'description' => __( 'Enter your Instagram URL', 'wisersites' ),
//                    'placeholder' => ''
//                ),
//                array(
//                    'title'       => __( 'Pinterest', 'wisersites' ),
//                    'subtitle'    => __( 'Pinterest page URL', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social',
//                    'id_key'      => 'pinterest',
//                    'description' => __( 'Enter your Pinterest URL', 'wisersites' ),
//                    'placeholder' => ''
//                ),
//                array(
//                    'title'       => __( 'Vimeo', 'wisersites' ),
//                    'subtitle'    => __( 'Vimeo page URL', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social',
//                    'id_key'      => 'vimeo',
//                    'description' => __( 'Enter your Vimeo URL', 'wisersites' ),
//                    'placeholder' => ''
//                ),
//                array(
//                    'title'       => __( 'Newsletter', 'wisersites' ),
//                    'subtitle'    => __( 'Vimeo page URL', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'text',
//                    'id'          => 'social',
//                    'id_key'      => 'newsletter',
//                    'description' => __( 'Enter your Newsletter URL', 'wisersites' ),
//                    'placeholder' => ''
//                ),
//            ),
            'header_elements' => array(
                array(
                    'title' => __('Header Advertisement Image ( Optimal Image Size is 1200 x 120 px )', 'wisersites'),
                    'subtitle' => '',
                    'heading' => '',
                    'type' => 'image_url',
                    'id' => 'header_advertisement_image',
                    'description' => '',
                    'placeholder' => __('Header Advertisement Image', 'wisersites')
                ),
                array(
                    'title' => __('Header Advertisement Url', 'wisersites'),
                    'subtitle' => __('Advertisement page URL', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'advertisement_url',
                    'id_key' => 'advertisement',
                    'description' => __('Enter Header Advertisement Url', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Header Subscribe Text', 'wisersites'),
                    'subtitle' => __('Enter Subscribe Text', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'header_subscribe_text',
                    'id_key' => 'subscribe_text',
                    'description' => __('Enter Subscribe Text', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Header Subscribe Link', 'wisersites'),
                    'subtitle' => __('Enter Subscribe Link', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'header_subscribe_link',
                    'id_key' => 'subscribe_link',
                    'description' => __('Enter Subscribe Link', 'wisersites'),
                    'placeholder' => ''
                ),
                array(
                    'title' => __('Header Right Text', 'wisersites'),
                    'subtitle' => __('Add Header Right Text', 'wisersites'),
                    'heading' => '',
                    'type' => 'text',
                    'id' => 'header_right_text',
                    'id_key' => 'right_text',
                    'description' => __('Enter Header Right Text', 'wisersites'),
                    'placeholder' => ''
                ),
            ),
//            'sidebar_elements' => array(
//                array(
//                    'title'       => __( 'Sidebar Recent Post ( Post Thumbnail or Category Icon )', 'wisersites' ),
//                    'subtitle'    => __( 'Select Post Thumbnail or Category Icon', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'a_image_or_icon_select',
//                    'id'          => 'featured_posts_image_or_icon',
//                    'description' => '',
//                    'placeholder' => ''
//                ),
//                
//            ),
//            'blog_elements' => array(
//                array(
//                    'title'       => __( 'Blog Page Post ( Post Blog or Category Filter )', 'wisersites' ),
//                    'subtitle'    => __( 'Select Blog or Category Filte', 'wisersites' ),
//                    'heading'     => '',
//                    'type'        => 'a_blog_or_category_filter',
//                    'id'          => 'blog_or_category_filter',
//                    'description' => '',
//                    'placeholder' => ''
//                ),
//            ),
        )
    );

    //Builing Option Area

    echo '<div id="wiser_option_area"><form method="post" action="options.php">'; // option area div -
    settings_fields('wiser_option_settings');
    do_settings_sections('wiser_option_settings');

    echo '<h1>' . __('PAO19 Theme Option', 'wisersites') . '</h1>';
    /*
    if ( false !== $_REQUEST['settings-updated'] ) {
        echo '<div> <p><strong>'. _e( 'Options saved', 'wisersites' ).'</strong></p></div>';
    }*/

    echo '<table id="wiser_option_content"> <tr> '; // #wiser_option_content -
    //	Building Option Menu Sections
    echo '<td class="wiser_option_sections col-md-3"> <ul class="">';
    foreach ($sections as $section) {
        echo '<li class="i_wiser_section_tab"><a href="#" id="i_' . $section['id'] . '" >' . $section['title'] . '</a></li>';
    }
    echo '</ul></td>';


    //	Building Options Content Sections
    echo '<td class="wiser_option_fields_div col-md-12">';
    foreach ($options as $option => $fileds) {
        echo '<div id="i_' . $option . '_option" class="i_wiser_section_content">';

        foreach ($fileds as $field) {
            echo '<div class="wiser_option_field_div field_' . $field['id'] . '_div">';

            if (isset($field['global_option']) && $field['global_option']) {
                $f_value = get_option($field['id']);
            } else {
                $f_value = (isset($wiser_options[$field['id']])) ? $wiser_options[$field['id']] : '';
            }
            if (isset($field['id_key'])) {
                $f_value = ($f_value) ? $f_value[$field['id_key']] : '';
            } else {
                $field['id_key'] = false;
            }

            if (!isset($field['placeholder'])) {
                $field['placeholder'] = '';
            }

            switch ($field['type']) {
                case "text";
                    echo create_section_for_text($field, $f_value);
                    break;

                case "textarea":
                    echo create_section_for_textarea($field, $f_value);
                    break;

                case "textarea_editor":
                    create_section_for_textarea_editor($field, $f_value);
                    break;

                case "checkbox":
                    echo create_section_for_checkbox($field, $f_value);
                    break;

                case "radio":
                    echo create_section_for_radio($field, $f_value);
                    break;

                case "electbox":
                    echo create_section_for_selectbox($field, $f_value);
                    break;

                case "number":
                    echo create_section_for_number($field, $f_value);
                    break;

                case "post_selectbox":
                    echo create_section_for_post_selectbox($field, $f_value);
                    break;

//                case "a_image_or_icon_select":
//                    echo create_section_for_a_image_or_icon_select( $field, $f_value );
//                    break;

                case "a_post_blog_or_category_filter":
                    echo create_section_for_a_post_blog_or_category_filter($field, $f_value);
                    break;

                case "a_select_slider_posts":
                    echo create_section_for_a_select_slider_posts($field, $f_value);
                    break;

                case "category_checkbox":
                    echo create_section_for_category_checkbox($field, $f_value);
                    break;

                case "a_blog_or_category_filter":
                    echo create_section_for_a_blog_or_category_filter($field, $f_value);
                    break;

                case "image_url":
                    echo create_section_for_image_url($field, $f_value);
                    break;

                case "color_picker":
                    echo create_section_for_color_picker($field, $f_value);
                    break;

                case "intro_view":
                    echo create_section_for_intro_view($field, $f_value);
                    break;
            }

            echo '</div>';
        }

        echo '</div>';
    }
    echo '</td> ';


    echo '</tr></table>'; // - #wiser_option_content
    echo get_submit_button();
    echo '</form></div>'; // - option area div

}


function wiser_option_name($field, $key = false)
{
    $name = $field['id'];

    if (isset($field['global_option']) && $field['global_option']) {
        return $name;
    }

    if ($key) {
        return WONAME . '[' . $name . '][' . $key . ']';
    }
    return WONAME . '[' . $name . ']';
}

/*
 *	Field generators
 */
function create_section_for_text($field, $value = '')
{
    if (!$value && $field['default']) $value = $field['default'];
    $html = '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html .= '<input type="text" name="' . wiser_option_name($field, $field['id_key']) . '" value="' . $value . '" ' .
        ' id="field_' . $field['id'] . '_' . $field['id_key'] . '" placeholder="' . $field['placeholder'] . '" class="i_input" >';
    $html .= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_number($field, $value = '')
{
    if (!$value && $field['default']) $value = $field['default'];
    $html = '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html .= '<input type="number" name="' . wiser_option_name($field, $field['id_key']) . '" value="' . $value . '" ' .
        ' id="field_' . $field['id'] . '_' . $field['id_key'] . '" placeholder="' . $field['placeholder'] . '" class="i_input" >';
    $html .= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_textarea($field, $value = '')
{
    $html = '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html .= '<textarea type="text" name="' . wiser_option_name($field) . '" ' .
        ' id="field_' . $field['id'] . '_' . $field['id_key'] . '" placeholder="' . $field['placeholder'] . '" class="i_input" >' . $value . '</textarea>';
    $html .= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_textarea_editor($field, $value = '')
{
    $html = '';
    echo '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    echo '<p class="subtitle">' . $field['subtitle'] . '</p>';
    wp_editor($value, 'field_' . $field['id'] . '_' . $field['id_key'],
        array(
            'textarea_rows' => 12,
            'textarea_name' => wiser_option_name($field),
            //'media_buttons' => 1,
        )
    );
    /*echo '<textarea type="text" name="'.wiser_option_name( $field ).'" ' .
        ' id="field_'.$field['id'].'_'.$field['id_key'].'" placeholder="' . $field['placeholder'].'" class="i_input i_texteditor" >'. $value . '</textarea>';*/
    echo '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_checkbox($field, $value = '')
{
    $checked = '';
    if ($value) $checked = 'checked';
    $html = '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html .= '<input type="checkbox" name="' . wiser_option_name($field) . '" value="1" ' .
        ' id="field_' . $field['id'] . '_' . $field['id_key'] . '" class="i_checkbox" ' . $checked . ' >';
    $html .= '<span class="description">' . $field['description'] . '</span>';

    return $html;
}

function create_section_for_a_select_slider_posts($field, $value = '')
{
    ?>
    <style>
        .a_slider_all_posts {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            max-width: 49%;
            width: 100%;
            display: block;
            float: left;
            min-height: 200px;
        }

        .slider_all_selected_posts {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            max-width: 49%;
            width: 100%;
            display: block;
            min-height: 200px;
        }

        .slider_all_selected_posts li {
            list-style-type: none;
        }

        .slider_all_selected_posts h4,
        .a_slider_all_posts h4 {
            font-size: 12px;
            margin-top: 0;
            margin-bottom: 5px;
            line-height: 100%;
        }

        .close_button,
        .a_each_slide_post {
            display: inline-block;
        }

        .a_slider_all_posts h4:hover,
        .close_button:hover {
            cursor: pointer;
        }

        .close_button {
            padding: 3px 5px;
            background-color: #ddd;
            color: #fff;
            border-radius: 100%;
            margin-left: 5px;
        }

        .select_posts_for_slider li {
            line-height: 100%;
            margin-bottom: 0;
        }
    </style>
    <script>
        jQuery(document).ready(function ($) {
            $(".a_slider_all_posts .slider_post_title h4").click(function () {
                var post_li_content = $(this).parents('li');
                var a_each_slide_post_data_id = $(this).parents('li').find('.a_each_slide_post').attr('data-id');
                $(".slider_all_selected_posts ul").append(post_li_content);
                post_li_content.append('<span class="close_button">X</span>');
                post_li_content.append('<input value="' + a_each_slide_post_data_id + '" type="hidden" name="wiser_option[select_slider_posts][][post-id]">');

            });
            $(document).on("click", ".close_button", function () {
                var appended_post_li_content = $(this).parents('li');
                appended_post_li_content.find('.close_button').remove();
                appended_post_li_content.find('input').remove();
                $(".a_slider_all_posts ul").prepend(appended_post_li_content);
            });
        });
    </script>
    <?php
    global $wiser_option;
    $html = '';

    $posts = array();
    $posts_ids = get_posts(array(
        'post_status'   => 'publish',
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_type' => 'post',
        'suppress_filters' => true,
        'fields' => 'ids'
    ));

    $html .= '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<div  id="field_' . $field['id'] . '_' . $field['id_key'] . '" class="select_posts_for_slider">';
    $html .= '<div class="a_slider_all_posts"><ul>';
    foreach ($posts_ids as $post_id) {

        $post_title = get_the_title($post_id);
        //$posts[$post_id] = $post_title;
        $html .= '<li>';
        $html .= '<div class="a_each_slide_post slider_post_' . $post_id . '" data-id = "' . $post_id . '">';
        $html .= '<div class=slider_post_title><h4>' . $post_title . '</h4></div>';
        $html .= '</div>';
        $html .= '</li>';
    }
    $posts_ids = array();
    $html .= '</ul></div>';

    $select_slider_posts = (isset($wiser_option['select_slider_posts'])) ? $wiser_option['select_slider_posts'] : array();
    $html .= '<div class="slider_all_selected_posts"><ul>';
    if (count($select_slider_posts)) {
        $counter = 0;
        foreach ($select_slider_posts as $select_slider_posts_value) {
            $select_slider_posts_id = $select_slider_posts_value['post-id'];
            $select_slider_posts_title = get_the_title($select_slider_posts_id);
            $html .= '<li>';
            $html .= '<div class="a_each_slide_post slider_post_' . $select_slider_posts_id . '" data-id = "' . $select_slider_posts_id . '">';
            $html .= '<div class=slider_post_title><h4>' . $select_slider_posts_title . '</h4></div>';
            $html .= '</div>';
            $html .= '<span class="close_button">X</span>';
            $html .= '<input value="' . $select_slider_posts_id . '" type="hidden" name="wiser_option[select_slider_posts][' . $counter . '][post-id]">';
            $html .= '</li>';
            $counter++;
        }
    }
    $html .= '</ul></div>';
    $html .= '</div>';
    return $html;
}


function create_section_for_category_checkbox($field, $value = '')
{
    ?>
    <script>
        jQuery(document).ready(function ($) {
            $(".a_sortable_custom").sortable({
                revert: true
            });
        });
    </script>
    <?php

    $idObj = get_category_by_slug('sport');
    $sport_parent_category_id = $idObj->term_id;
    $sport_parent_category = get_categories();
//            $sport_parent_category = get_categories(
//                    array('parent' => $sport_parent_category_id,
//                        "hide_empty" => 0)
//            );
    global $wiser_option;
    $html = '';
    $cat_checkbox = $wiser_option['cat_checkbox'];
    $categories = $sport_parent_category;
    $html .= '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<ul  class="a_sortable_custom">';
    $counter = 0;
    if (!empty($cat_checkbox)) {
        foreach ($cat_checkbox as $key => $cat_checkbox_value) {
//            $ordered_term = get_term( $term_id, $taxonomy );;
            $ordered_term = get_category($cat_checkbox_value['cat_id']);
//        echo '<pre>';
//        print_r($cat_checkbox_value);
//        echo '</pre>';
            $ordered_term_link = get_category_link($ordered_term->term_id);
            $ordered_category = $ordered_term->name;
            $ordered_category_slug = $ordered_term->slug;
            $ordered_term_id = $ordered_term->term_id;
            $html .= '<li class="ui-state-default col-md-3"><label class="subtitle">';
            $html .= '<input type="checkbox" name="' . wiser_option_name(array("id" => 'cat_checkbox][][cat_id')) . '" value="' . $ordered_term_id . '" ' .
                ' id="field_' . $field['id'] . '_' . $field['id_key'] . '" class="i_checkbox" checked >' . $ordered_category . '</label>';
            $html .= '</li>';
        }
    }
    foreach ($categories as $categories_value) {

        $checked = '';
        $term_link = get_category_link($categories_value->term_id);
        $sport_category = $categories_value->name;
        $sport_category_slug = $categories_value->slug;
        $term_id = $categories_value->term_id;
        if (!empty($cat_checkbox[$sport_category_slug])) {
            $checked = 'checked';
        }
        $is_exist = true;
        foreach ($cat_checkbox as $key => $cat_checkbox_value) {
            $ordered_term = get_category($cat_checkbox_value['cat_id']);
            $ordered_term_id = $ordered_term->term_id;
            if ($ordered_term_id == $term_id) {
                $is_exist = false;
            }
        }
        if ($is_exist) {
            $html .= '<li class="ui-state-default col-md-3"><label class="subtitle">';
            $html .= '<input type="checkbox" name="' . wiser_option_name(array("id" => 'cat_checkbox][][cat_id')) . '" value="' . $term_id . '" ' .
                ' id="field_' . $field['id'] . '_' . $field['id_key'] . '" class="i_checkbox" ' . $checked . ' >' . $sport_category . '</label>';
            $html .= '</li>';
        }
        $counter++;
    }
    $html .= '</ul>';

    return $html;
}

function create_section_for_radio($field, $options)
{
    $html = '';
    return $html;
}

function create_section_for_selectbox($field, $value = '')
{

    $html = '';
    $html .= '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<p class="subtitle">' . $field['subtitle'] . '</p>';


    $html .= '<select name="' . wiser_option_name($field) . '" id="field_' . $field['id'] . '_' . $field['id_key'] . '" >';
    //$html.= '<option value="null" > --- </option>';
    foreach ($options as $option_name => $option) {
        $i_selected = '';
        if ($option == $value) $i_selected = 'selected';
        $html .= '<option value="' . $option . '" ' . $i_selected . '  >' . $option_name . '</option>';
    }
    $html .= '</select>';

    $html .= '<span class="description">' . $field['description'] . '</span>';

    return $html;
}

//function create_section_for_a_image_or_icon_select( $field, $value = '' ){
//    $html = '';
//    $a_selected = "";
//    $a_i_selected = "";
//    $a_selected_null = "";
//    global $wiser_option;
//    $html.= '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label> <br>';
//    $featured_posts_image_or_icon = $wiser_option['featured_posts_image_or_icon'];
//        if($featured_posts_image_or_icon[0] == "Post Thumbnail"){
//            $a_selected = 'selected';
//        }elseif($featured_posts_image_or_icon[0] == 'Category Icon'){
//            $a_i_selected = 'selected';
//        } else {
//            $a_selected_null = 'selected';
//        }
//     $html.= '<select name="'.wiser_option_name( $field ).'[]" id="field_'.$field['id'].'_'.$field['id_key'].'" >';
//        $html.= '<option value="null" '.$a_selected_null.'> --- </option>';
//        $html.= '<option value="Post Thumbnail" '.$a_selected.'  >Post Thumbnail</option>';
//        $html.= '<option value="Category Icon" '.$a_i_selected.'  >Category Icon</option>';
//        $html.= '</select>';
//        return $html;
//}
function create_section_for_a_blog_or_category_filter($field, $value = '')
{
    $html = '';
    $a_selected = "";
    $a_i_selected = "";
    $a_selected_null = "";
    global $wiser_option;
    $html .= '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label> <br>';
    $featured_posts_image_or_icon = $wiser_option['post_blog_or_category_filter'];
    if ($featured_posts_image_or_icon[0] == "Post Blog") {
        $a_selected = 'selected';
    } elseif ($featured_posts_image_or_icon[0] == 'Category Filter') {
        $a_i_selected = 'selected';
    } else {
        $a_selected_null = 'selected';
    }
    $html .= '<select name="' . wiser_option_name($field) . '[]" id="field_' . $field['id'] . '_' . $field['id_key'] . '" >';
    $html .= '<option value="null" ' . $a_selected_null . '> --- </option>';
    $html .= '<option value="Post Blog" ' . $a_selected . '  >Post Blog</option>';
    $html .= '<option value="Category Filter" ' . $a_i_selected . '  >Category Filter</option>';
    $html .= '</select>';
    return $html;
}

function create_section_for_a_post_blog_or_category_filter($field, $value = '')
{
    $html = '';
    $a_selected = "";
    $a_i_selected = "";
    $class = '';
    $a_selected_null = "";
    global $wiser_option;
    $html .= '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label> <br>';
    $featured_posts_image_or_icon = $wiser_option['post_blog_or_category_filter'];
    if ($featured_posts_image_or_icon[0] == "Post Blog") {
        $a_selected = 'selected';
        $class = 'a_home_blog_posts_option';
    } elseif ($featured_posts_image_or_icon[0] == 'Category Filter') {
        $class = 'a_home_category_filter_option';
        $a_i_selected = 'selected';
    } else {
        $class = 'a_home_blog_posts_option';
        $a_selected_null = 'selected';
    }
    $html .= '<select class="a_select_posts_view_select" name="' . wiser_option_name($field) . '[]" id="field_' . $field['id'] . '_' . $field['id_key'] . '" >';
    $html .= '<option class= "' . $class . ' a_select_posts_view" value="null" ' . $a_selected_null . '> --- </option>';
    $html .= '<option class= "' . $class . ' a_select_posts_view" value="Post Blog" ' . $a_selected . '  >Post Blog</option>';
    $html .= '<option class= "' . $class . ' a_select_posts_view" value="Category Filter" ' . $a_i_selected . '  >Category Filter</option>';
    $html .= '</select>';
    ?>

    <?php
    return $html;
}

function create_section_for_post_selectbox($field, $value = '')
{
    $posts = get_pages(array('numberposts' => -1, 'post_type' => 'page', 'post_parent' => 0));
    $front_page_elements = $value;

    if (empty($front_page_elements) || !count($front_page_elements)) {
        $front_page_elements = array('null');
    }
    //i_print($value);

    $html = '';
    $html .= '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label> <br>';

    $html .= '<ul id="featured_posts_list">';

    foreach ($front_page_elements as $element) {
        $html .= '<li class="featured_post_ex"><select name="' . wiser_option_name($field) . '[]" id="field_' . $field['id'] . '_' . $field['id_key'] . '" >';
        $html .= '<option value="null" > --- </option>';
        foreach ($posts as $post) {
            $i_selected = '';
            if ($element == $post->ID) $i_selected = 'selected';
            $html .= '<option value="' . $post->ID . '" ' . $i_selected . '  >' . $post->post_title . '</option>';
        }
        $html .= '</select><span class="dashicons dashicons-sort i_dragicon" title="Drag for sorting"></span> ';
        $html .= '<p class=""><a href="#" class="i_remove_feature_post"><span class="dashicons dashicons-no"></span> Remove</a></p></li>';
    }

    $html .= '</ul>';
    $html .= '<a href="#" class="i_add_featured_post"><span class="dashicons dashicons-plus"></span>Add featured post</a>';
    //$html.= '<input type="hidden" id="i_the_max_id" value="'.$element_counter.'" />';

    return $html;
}

function create_section_for_image_url($field, $value = '')
{
    $class = '';
    if (trim($value) == '') $class = 'i_hidden';
    $html = '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html .= '<input type="text" name="' . wiser_option_name($field, $field['id_key']) . '" value="' . $value . '" ' .
        ' id="field_' . $field['id'] . '_' . $field['id_key'] . '" placeholder="' . $field['placeholder'] . '" class="i_input i_input_url upload_image_button" >';
    $html .= '<img src="' . $value . '" class="i_preview_img i_preview_field_' . $field['id'] . '_' . $field['id_key'] . ' ' . $class . '" >';
    $html .= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_color_picker($field, $value = '')
{
    if (!$value) $value = $field['default'];
    $html = '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html .= '<input type="text" name="' . wiser_option_name($field) . '" value="' . $value . '" ';
    $html .= ' id="field_' . $field['id'] . '_' . $field['id_key'] . '" class="i_input i_color_picker"  >';
    $html .= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_intro_view($field, $value = '')
{
    //if( !$value )
    $value = $field['value'];
    $html = '<label for="field_' . $field['id'] . '_' . $field['id_key'] . '">' . $field['title'] . '</label>';
    $html .= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html .= '<input type="text" value="' . $value . '" ' . ' id="field_' . $field['id'] . '_' . $field['id_key'] . '" class="i_input i_click_checkall" readonly  >';
    $html .= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}
