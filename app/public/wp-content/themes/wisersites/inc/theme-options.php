<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
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
$wiser_option = get_option( WONAME, true );


if ( !is_admin() ) return;

/*
 * Setup Theme Admin Menus
 */
function wiser_setup_theme_admin_menus() {
    add_action( 'admin_init', 'register_wiser_settings' );
    add_menu_page('Theme settings', 'Wiser Settings', 'manage_options', 'wiser_theme_settings', 'wiser_theme_settings_page', 'dashicons-art');
}
add_action("admin_menu", "wiser_setup_theme_admin_menus");


function register_wiser_settings() {
    register_setting( 'wiser_option_settings', WONAME );
    register_setting( 'wiser_option_settings', 'blogname' );
    register_setting( 'wiser_option_settings', 'blogdescription' );
}

function wiser_theme_settings_page() {
    $wiser_options = get_option( WONAME, true ); //i_print( $wiser_options );
    $theme_url=get_template_directory_uri().'/';
    /*wp_enqueue_script('editor');
    wp_enqueue_script('tiny_mce');
    wp_enqueue_script('thickbox');
    */
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_media();

    wp_enqueue_script( 'theme_option_js', $theme_url.'/inc/theme-options/js.js', array( 'wp-color-picker' ), false, true );

    echo '<link rel="stylesheet" href="'.$theme_url.'/resources/css/bootstrap/css/bootstrap.min.css" type="text/css" media="all">';
    echo '<link rel="stylesheet" href="'.$theme_url.'/inc/theme-options/style.css" type="text/css" media="all">';
    //echo '<script type="text/javascript" src="'.$theme_url.'/inc/theme-options/js.js" ></script>';

    $sections = apply_filters( 'wiser_option_sections_filter',
        array(
            array(
                'title' => __( 'General', 'wisersites' ),
                'id'    => 'general'
            ),
            array(
                'title' => __( 'Theme Elements', 'wisersites' ),
                'id'    => 'theme_elements'
            ),
            array(
                'title' => __( 'Logo Upload', 'wisersites' ),
                'id'    => 'logo_upload'
            ),
            array(
                'title' => __( 'Home Page', 'wisersites' ),
                'id'    => 'home_page'
            ),
            array(
                'title' => __( 'Social Icons', 'wisersites' ),
                'id'    => 'social'
            ),
            array(
                'title' => __( 'Short Codes', 'wisersites' ),
                'id'    => 'short_codes'
            )
        )
    );

    $options = apply_filters( 'wiser_options_filter',
        array(
            'general' => array(
                array(
                    'title'       => __( 'Site Title', 'wisersites' ),
                    'subtitle'    => __( 'Blogname', 'wisersites' ),
                    'type'        => 'text',
                    'id'          => 'blogname',
                    'global_option'       => 'true',
                    'description' => __( 'Change your Site Title', 'wisersites' )
                ),
                array(
                    'title'       => __( 'Tagline', 'wisersites' ),
                    'subtitle'    => __( 'About site', 'wisersites' ),
                    'type'        => 'text',
                    'id'          => 'blogdescription',
                    'global_option'       => 'true',
                    'description' => __( 'In a few words, explain what this site is about.', 'wisersites' )
                ),
                array(
                    'title'       => __( 'Enable comments', 'wisersites' ),
                    'subtitle'    => 'Allow people to post comments on page/posts',
                    'heading'     => '',
                    'type'        => 'checkbox',
                    'id'          => 'enable_comments',
                    'description' => __( 'Check to enable', 'wisersites' ),
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Blog pages show at most', 'wisersites' ),
                    'subtitle'    => __( '', 'wisersites' ),
                    'type'        => 'number',
                    'id'          => 'posts_per_page',
                    'global_option'       => 'true',
                    'description' => __( 'How many posts show up on your archive pages ?', 'wisersites' )
                ),
                array(
                    'title'       => __( 'Site Description', 'wisersites' ),
                    'subtitle'    => 'Enter site Description here',
                    'heading'     => '',
                    'type'        => 'textarea_editor',
                    'id'          => 'site_description',
                    'description' => '',
                    'placeholder' => __( 'Enter site Description here', 'wisersites' )
                ),
            ),
            'theme_elements' => array(
                array(
                    'title'       => __( 'Enable breadcrumb list?', 'wisersites' ),
                    'subtitle'    => __( 'You can enable/disable breadcrumbs', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'checkbox',
                    'id'          => 'breadcrumb',
                    'description' => __( 'Check to enable', 'wisersites' ),
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Google Analytics Tracking Code', 'wisersites' ),
                    'subtitle'    => 'Enter your tracking code here for Google Analytics',
                    'heading'     => '',
                    'type'        => 'textarea',
                    'id'          => 'analytics_code',
                    'description' => '',
                    'placeholder' => __( 'Custom Google Analytics Tracking Code', 'wisersites' )
                ),
                array(
                    'title'       => __( 'Exclude Google Analytics Tracking', 'wisersites' ),
                    'subtitle'    => 'Exclude Google Analytics Tracking for logged users and for special IP-s',
                    'heading'     => '',
                    'type'        => 'checkbox',
                    'id'          => 'exclude_analytics_tracking',
                    'description' => '',
                    'placeholder' => __( 'Exclude Google Analytics Tracking for logged users', 'wisersites' )
                ),
                array(
                    'title'       => __( 'Exclude IP for Google Analytics Tracking', 'wisersites' ),
                    'subtitle'    => 'Select Exclude IP-s for Google Analytics Tracking',
                    'heading'     => '',
                    'type'        => 'textarea',
                    'id'          => 'exclude_ips_analytics_tracking',
                    'description' => 'Fill each IP in new line',
                    'placeholder' => __( 'xxx.xxx.xxx.xxx', 'wisersites' )
                ),
                array(
                    'title'       => __( 'Enable Fixed/Sticky Header menu', 'wisersites' ),
                    'subtitle'    => __( 'You can enable/disable breadcrumbs sticky header menu', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'checkbox',
                    'id'          => 'fixed_header_menu',
                    'description' => __( 'Check to enable', 'wisersites' ),
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Header menu background color', 'wisersites' ),
                    'subtitle'    => __( 'Header menu background color ( none )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'header_menu_bgcolor',
                    'default'     => 'none',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Header menu text color', 'wisersites' ),
                    'subtitle'    => __( 'Header menu text color ( #829097 )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'header_menu_color',
                    'default'     => '#829097',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Header active menu background color', 'wisersites' ),
                    'subtitle'    => __( 'Header active menu background color ( #666666 )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'header_active_menu_bgcolor',
                    'default'     => '#666666',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Header active menu text color', 'wisersites' ),
                    'subtitle'    => __( 'Header active menu text color ( #ffffff )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'header_active_menu_color',
                    'default'     => '#ffffff',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Body background color', 'wisersites' ),
                    'subtitle'    => __( 'Body background color ( #ffffff )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'body_bg_color',
                    'default'     => '#ffffff',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Content text color', 'wisersites' ),
                    'subtitle'    => __( 'Content text color ( #333333 )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'content_text_color',
                    'default'     => '#333333',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Submit button color', 'wisersites' ),
                    'subtitle'    => __( 'Submit button color ( #458ac5 )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'submit_buttons_color',
                    'default'     => '#458ac5',
                    'description' => '',
                    'placeholder' => ''
                ),

                array(
                    'title'       => __( 'Footer background color', 'wisersites' ),
                    'subtitle'    => __( 'Footer background color ( #fafafa )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'footer_bgcolor',
                    'default'     => '#fafafa',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Footer text color', 'wisersites' ),
                    'subtitle'    => __( 'Footer text color ( #829097 )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'footer_txt_color',
                    'default'     => '#829097',
                    'description' => '',
                    'placeholder' => ''
                ),
            ),
            'logo_upload' => array(
                array(
                    'title'       => __( 'Favicon', 'wisersites' ),
                    'subtitle'    => __( 'Change favicon', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'image_url',
                    'id'          => 'favicon',
                    'description' => '',
                    'placeholder' => 'favicon url'
                ),
                array(
                    'title'       => __( 'Header Logo', 'wisersites' ),
                    'subtitle'    => '',
                    'heading'     => '',
                    'type'        => 'image_url',
                    'id'          => 'header_logo',
                    'description' => '',
                    'placeholder' => __( 'Header Logo', 'wisersites' )
                ),
                array(
                    'title'       => __( 'Fixed/Sticky Header Logo', 'wisersites' ),
                    'subtitle'    => '',
                    'heading'     => '',
                    'type'        => 'image_url',
                    'id'          => 'fixed_header_logo',
                    'description' => '',
                    'placeholder' => __( 'Fixed/Sticky Header Logo', 'wisersites' )
                ),
                array(
                    'title'       => __( 'Footer Logo', 'wisersites' ),
                    'subtitle'    => '',
                    'heading'     => '',
                    'type'        => 'image_url',
                    'id'          => 'footer_logo',
                    'description' => '',
                    'placeholder' => __( 'Footer logo', 'wisersites' )
                ),
            ),
            'home_page' => array(
                array(
                    'title'       => __( 'Phone number', 'wisersites' ),
                    'subtitle'    => __( 'Phone number will using in header/footer etc', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'phone_number',
                    'description' => __( 'Enter phone number', 'wisersites' ),
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Custom Header', 'wisersites' ),
                    'subtitle'    => __( 'You can use Custom header option for home page', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'checkbox',
                    'id'          => 'custom_header',
                    'description' => __( 'Check for custom header', 'wisersites' ),
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Custom Header Logo', 'wisersites' ),
                    'subtitle'    => __( 'Home Logo for Custom header', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'image_url',
                    'id'          => 'custom_header_logo',
                    'description' => __( 'Choose Custom Header Logo for home page', 'wisersites' ),
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Custom Header Background', 'wisersites' ),
                    'subtitle'    => __( 'Background for Custom header ( #666666 )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'custom_header_bg',
                    'default'     => '#666666',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Custom Header Background Gradient', 'wisersites' ),
                    'subtitle'    => __( 'Background Gradient for Custom header ( #666666 )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'custom_header_bg_gradient',
                    'default'     => '#666666',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Home Page Posts', 'wisersites' ),
                    'subtitle'    => __( 'Enable Home Page Featured Posts', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'checkbox',
                    'id'          => 'featured_posts_view',
                    'description' => __( 'Check for enable Home Page Featured Posts', 'wisersites' ),
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Home Page Posts Menu', 'wisersites' ),
                    'subtitle'    => __( 'Enable Home Page Featured Posts Menu', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'checkbox',
                    'id'          => 'featured_posts_menu',
                    'description' => __( 'Check for enable Home Page Featured Posts Menu', 'wisersites' ),
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Home Page Featured Posts', 'wisersites' ),
                    'subtitle'    => __( 'Select posts which you want to show on home page', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'post_selectbox',
                    'id'          => 'featured_posts',
                    'description' => '',
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( '"Learn more" button color', 'wisersites' ),
                    'subtitle'    => __( '"Learn more" button color ( #666666 )', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'color_picker',
                    'id'          => 'lear_more_buttons_color',
                    'default'     => '#666666',
                    'description' => '',
                    'placeholder' => ''
                ),
            ),
            'social' => array(
                array(
                    'title'       => __( 'Social Title', 'wisersites' ),
                    'subtitle'    => __( 'Header of social icons', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'social_title',
                    'default'      => 'Follow Us',
                    'description' => __( 'Enter Title of social icons area', 'wisersites' ),
                    'placeholder' => ''
                ),
                array(
                    'title'       => __( 'Facebook', 'wisersites' ),
                    'subtitle'    => __( 'Facebook page URL', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'social',
                    'id_key'      => 'facebook',
                    'description' => __( 'Enter your Facebook URL', 'wisersites' ),
                    'placeholder' => ''
                ),
                    array(
                        'title'       => __( 'Facebook icon', 'wisersites' ),
                        'subtitle'    => __( 'Facebook icon URL', 'wisersites' ),
                        'heading'     => '',
                        'type'        => 'image_url',
                        'id'          => 'social_icons',
                        'id_key'      => 'facebook',
                        'description' => '',
                        'placeholder' => ''
                    ),
                array(
                    'title'       => __( 'Twitter', 'wisersites' ),
                    'subtitle'    => __( 'Twitter page URL', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'social',
                    'id_key'      => 'twitter',
                    'description' => __( 'Enter your Twitter URL', 'wisersites' ),
                    'placeholder' => ''
                ),
                    array(
                        'title'       => __( 'Twitter icon', 'wisersites' ),
                        'subtitle'    => __( 'Twitter icon URL', 'wisersites' ),
                        'heading'     => '',
                        'type'        => 'image_url',
                        'id'          => 'social_icons',
                        'id_key'      => 'twitter',
                        'description' => '',
                        'placeholder' => ''
                    ),
                array(
                    'title'       => __( 'Google+', 'wisersites' ),
                    'subtitle'    => __( 'Google+ page URL', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'social',
                    'id_key'      => 'google_plus',
                    'description' => __( 'Enter your Google+ URL', 'wisersites' ),
                    'placeholder' => ''
                ),
                    array(
                        'title'       => __( 'Google+ icon', 'wisersites' ),
                        'subtitle'    => __( 'Google+ icon URL', 'wisersites' ),
                        'heading'     => '',
                        'type'        => 'image_url',
                        'id'          => 'social_icons',
                        'id_key'      => 'google_plus',
                        'description' => __( 'Enter your Google+ URL', 'wisersites' ),
                        'placeholder' => ''
                    ),
                array(
                    'title'       => __( 'LinkedIn', 'wisersites' ),
                    'subtitle'    => __( 'LinkedIn page URL', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'social',
                    'id_key'      => 'linkedin',
                    'description' => __( 'Enter your LinkedIn URL', 'wisersites' ),
                    'placeholder' => ''
                ),
                    array(
                        'title'       => __( 'LinkedIn icon', 'wisersites' ),
                        'subtitle'    => __( 'LinkedIn icon URL', 'wisersites' ),
                        'heading'     => '',
                        'type'        => 'image_url',
                        'id'          => 'social_icons',
                        'id_key'      => 'linkedin',
                        'description' => '',
                        'placeholder' => ''
                    ),
                array(
                    'title'       => __( 'YouTube', 'wisersites' ),
                    'subtitle'    => __( 'YouTube page URL', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'social',
                    'id_key'      => 'youTube',
                    'description' => __( 'Enter your YouTube URL', 'wisersites' ),
                    'placeholder' => ''
                ),
                    array(
                        'title'       => __( 'YouTube icon', 'wisersites' ),
                        'subtitle'    => __( 'YouTube icon URL', 'wisersites' ),
                        'heading'     => '',
                        'type'        => 'image_url',
                        'id'          => 'social_icons',
                        'id_key'      => 'youTube',
                        'description' => '',
                        'placeholder' => ''
                    ),
                array(
                    'title'       => __( 'Instagram', 'wisersites' ),
                    'subtitle'    => __( 'Instagram page URL', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'social',
                    'id_key'      => 'instagram',
                    'description' => __( 'Enter your Instagram URL', 'wisersites' ),
                    'placeholder' => ''
                ),
                    array(
                        'title'       => __( 'Instagram icon', 'wisersites' ),
                        'subtitle'    => __( 'Instagram icon URL', 'wisersites' ),
                        'heading'     => '',
                        'type'        => 'image_url',
                        'id'          => 'social_icons',
                        'id_key'      => 'instagram',
                        'description' => '',
                        'placeholder' => ''
                    ),
                array(
                    'title'       => __( 'Pinterest', 'wisersites' ),
                    'subtitle'    => __( 'Pinterest page URL', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'social',
                    'id_key'      => 'pinterest',
                    'description' => __( 'Enter your Pinterest URL', 'wisersites' ),
                    'placeholder' => ''
                ),
                    array(
                        'title'       => __( 'Pinterest icon', 'wisersites' ),
                        'subtitle'    => __( 'Pinterest icon URL', 'wisersites' ),
                        'heading'     => '',
                        'type'        => 'image_url',
                        'id'          => 'social_icons',
                        'id_key'      => 'pinterest',
                        'description' => '',
                        'placeholder' => ''
                    ),
                array(
                    'title'       => __( 'Vimeo', 'wisersites' ),
                    'subtitle'    => __( 'Vimeo page URL', 'wisersites' ),
                    'heading'     => '',
                    'type'        => 'text',
                    'id'          => 'social',
                    'id_key'      => 'vimeo',
                    'description' => __( 'Enter your Vimeo URL', 'wisersites' ),
                    'placeholder' => ''
                ),
                    array(
                        'title'       => __( 'Vimeo icon', 'wisersites' ),
                        'subtitle'    => __( 'Vimeo icon URL', 'wisersites' ),
                        'heading'     => '',
                        'type'        => 'image_url',
                        'id'          => 'social_icons',
                        'id_key'      => 'vimeo',
                        'description' => '',
                        'placeholder' => ''
                    ),
            ),
            'short_codes' => array(
                array(
                    'title'       => __( 'Social Icons', 'wisersites' ),
                    'subtitle'    => __( 'Show your Social Icons where you want', 'wisersites' ),
                    'type'        => 'intro_view',
                    'id'          => 'social_intro',
                    'value'       => '[wiser_social_links]',
                    'description' => __( 'Show your Social Icons where you want', 'wisersites' )
                ),
            ),
        )
    );

    //Builing Option Area

    echo '<div id="wiser_option_area"><form method="post" action="options.php">'; // option area div -
    settings_fields( 'wiser_option_settings' );
    do_settings_sections( 'wiser_option_settings' );

    echo '<h1>'. __( 'Wiser Theme Option', 'wisersites'). '</h1>';
    /*
    if ( false !== $_REQUEST['settings-updated'] ) {
        echo '<div> <p><strong>'. _e( 'Options saved', 'wisersites' ).'</strong></p></div>';
    }*/

echo '<table id="wiser_option_content"> <tr> '; // #wiser_option_content -
    //	Building Option Menu Sections
    echo '<td class="wiser_option_sections col-md-3"> <ul class="">';
    foreach( $sections as $section ){
        echo '<li class="i_wiser_section_tab"><a href="#" id="i_'.$section['id'].'" >'.$section['title'].'</a></li>';
    }
    echo '</ul></td>';


    //	Building Options Content Sections
    echo '<td class="wiser_option_fields_div col-md-12">';
    foreach( $options as $option => $fileds ){
        echo '<div id="i_'.$option.'_option" class="i_wiser_section_content">';

        foreach( $fileds as $field ){
            echo '<div class="wiser_option_field_div field_'.$field['id'].'_div">';

            if( isset($field['global_option']) && $field['global_option'] ){
                $f_value = get_option( $field['id'] );
            } else {
                $f_value = ($wiser_options[ $field['id'] ])?$wiser_options[ $field['id'] ]:'';
                if( isset($field['id_key']) ){
                    $f_value = ( $f_value ) ? $f_value[ $field['id_key'] ] : '';
                } else {
                    $field['id_key'] = false;
                }
            }


            switch ( $field['type'] ) {
                case "text";
                    echo create_section_for_text( $field, $f_value );
                    break;

                case "textarea":
                    echo create_section_for_textarea( $field, $f_value );
                    break;

                case "textarea_editor":
                    create_section_for_textarea_editor( $field, $f_value );
                    break;

                case "checkbox":
                    echo create_section_for_checkbox( $field, $f_value );
                    break;

                case "radio":
                    echo create_section_for_radio( $field, $f_value );
                    break;

                case "electbox":
                    echo create_section_for_selectbox( $field, $f_value );
                    break;

                case "number":
                    echo create_section_for_number( $field, $f_value );
                    break;

                case "post_selectbox":
                    echo create_section_for_post_selectbox( $field, $f_value );
                    break;

                case "image_url":
                    echo create_section_for_image_url( $field, $f_value );
                    break;

                case "color_picker":
                    echo create_section_for_color_picker( $field, $f_value );
                    break;

                case "intro_view":
                    echo create_section_for_intro_view( $field, $f_value );
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



function wiser_option_name( $field, $key = false ){
    $name = $field['id'];

    if( isset($field['global_option']) && $field['global_option'] ){
        return $name;
    }

    if( $key ){
        return WONAME.'['.$name.']['.$key.']';
    }
    return WONAME.'['.$name.']';
}

/*
 *	Field generators
 */
function create_section_for_text( $field, $value = '' ) {
    if( !$value && $field['default'] )$value = $field['default'];
    $html = '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label>';
    $html.= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html.= '<input type="text" name="'.wiser_option_name($field, $field['id_key']).'" value="'. $value . '" ' .
        ' id="field_'.$field['id'].'_'.$field['id_key'].'" placeholder="' . $field['placeholder'].'" class="i_input" >';
    $html.= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_number( $field, $value = '' ) {
    if( !$value && $field['default'] )$value = $field['default'];
    $html = '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label>';
    $html.= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html.= '<input type="number" name="'.wiser_option_name($field, $field['id_key']).'" value="'. $value . '" ' .
        ' id="field_'.$field['id'].'_'.$field['id_key'].'" placeholder="' . $field['placeholder'].'" class="i_input" >';
    $html.= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_textarea( $field, $value = '' ) {
    $html = '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label>';
    $html.= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html.= '<textarea type="text" name="'.wiser_option_name( $field ).'" ' .
        ' id="field_'.$field['id'].'_'.$field['id_key'].'" placeholder="' . $field['placeholder'].'" class="i_input" >'. $value . '</textarea>';
    $html.= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_textarea_editor( $field, $value = '' ) {
    $html = '';
    echo '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label>';
    echo '<p class="subtitle">' . $field['subtitle'] . '</p>';
    wp_editor( $value, 'field_'.$field['id'].'_'.$field['id_key'],
        array(
            'textarea_rows' =>  12,
            'textarea_name' =>  wiser_option_name( $field ),
            //'media_buttons' => 1,
        )
    );
    /*echo '<textarea type="text" name="'.wiser_option_name( $field ).'" ' .
        ' id="field_'.$field['id'].'_'.$field['id_key'].'" placeholder="' . $field['placeholder'].'" class="i_input i_texteditor" >'. $value . '</textarea>';*/
    echo '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_checkbox( $field, $value = '' ) {
    $checked = '';
    if( $value ) $checked='checked';
    $html = '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label>';
    $html.= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html.= '<input type="checkbox" name="'.wiser_option_name( $field ).'" value="1" ' .
        ' id="field_'.$field['id'].'_'.$field['id_key'].'" class="i_checkbox" '.$checked.' >';
    $html.= '<span class="description">' . $field['description'] . '</span>';

    return $html;
}

function create_section_for_radio( $field, $options ) {
    $html = '';
    return $html;
}

function create_section_for_selectbox( $field, $value = '' ){

    $html = '';
    $html.= '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label>';
    $html.= '<p class="subtitle">' . $field['subtitle'] . '</p>';


    $html.= '<select name="'.wiser_option_name( $field ).'" id="field_'.$field['id'].'_'.$field['id_key'].'" >';
    //$html.= '<option value="null" > --- </option>';
    foreach ($options as $option_name => $option) {
        $i_selected ='';
        if( $option == $value ) $i_selected='selected';
        $html.= '<option value="'.$option.'" '.$i_selected.'  >' . $option_name . '</option>';
    }
    $html.= '</select>';

    $html.= '<span class="description">' . $field['description'] . '</span>';

    return $html;
}

function create_section_for_post_selectbox( $field, $value = '' ){
    $posts = get_pages( array( 'numberposts' => -1, 'post_type' => 'page', 'post_parent' => 0 ) );
    $front_page_elements = $value;

    if( empty($front_page_elements) || !count($front_page_elements) ){
        $front_page_elements = array( 'null' );
    }
    //i_print($value);

    $html = '';
    $html.= '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label> <br>';

    $html.= '<ul id="featured_posts_list">';

    foreach ($front_page_elements as $element){
        $html.= '<li class="featured_post_ex"><select name="'.wiser_option_name( $field ).'[]" id="field_'.$field['id'].'_'.$field['id_key'].'" >';
        $html.= '<option value="null" > --- </option>';
        foreach ($posts as $post) {
            $i_selected ='';
            if( $element == $post->ID ) $i_selected='selected';
            $html.= '<option value="'.$post->ID.'" '.$i_selected.'  >' . $post->post_title . '</option>';
        }
        $html.= '</select><span class="dashicons dashicons-sort i_dragicon" title="Drag for sorting"></span> ';
        $html.= '<p class=""><a href="#" class="i_remove_feature_post"><span class="dashicons dashicons-no"></span> Remove</a></p></li>';
    }

    $html.= '</ul>';
    $html.= '<a href="#" class="i_add_featured_post"><span class="dashicons dashicons-plus"></span>Add featured post</a>';
    //$html.= '<input type="hidden" id="i_the_max_id" value="'.$element_counter.'" />';

    return $html;
}
function create_section_for_image_url( $field, $value = '' ) {
    $class = ''; if( trim($value) == '' )$class = 'i_hidden';
    $html = '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label>';
    $html.= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html.= '<input type="text" name="'.wiser_option_name( $field, $field['id_key']).'" value="'. $value . '" ' .
        ' id="field_'.$field['id'].'_'.$field['id_key'].'" placeholder="' . $field['placeholder'].'" class="i_input i_input_url upload_image_button" >';
    $html.= '<img src="'.$value.'" class="i_preview_img i_preview_field_'.$field['id'].'_'.$field['id_key'].' '.$class.'" >';
    $html.= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_color_picker( $field, $value = '' ){
    if( !$value )$value = $field['default'];
    $html = '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label>';
    $html.= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html.= '<input type="text" name="'.wiser_option_name( $field ).'" value="'.$value.'" ';
    $html.= ' id="field_'.$field['id'].'_'.$field['id_key'].'" class="i_input i_color_picker"  >';
    $html.= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}

function create_section_for_intro_view( $field, $value = '' ){
    //if( !$value )
        $value = $field['value'];
    $html = '<label for="field_'.$field['id'].'_'.$field['id_key'].'">' . $field['title'] . '</label>';
    $html.= '<p class="subtitle">' . $field['subtitle'] . '</p>';
    $html.= '<input type="text" value="'.$value.'" ' . ' id="field_'.$field['id'].'_'.$field['id_key'].'" class="i_input i_click_checkall" readonly  >';
    $html.= '<p class="description">' . $field['description'] . '</p>';

    return $html;
}
