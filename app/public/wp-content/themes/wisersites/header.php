<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package Wiser Sites
 * @since Wiser Sites 1.0
 */
?>
<?php
    global $wiser_option;
?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) & !(IE 8)]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
    <?php global $theme_url; ?>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width">
    <link rel="icon" href="<?php echo $wiser_option['favicon']; ?>" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo $wiser_option['favicon']; ?>" type="image/x-icon" />
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <!--[if lt IE 9]>
    <script src="<?php echo get_template_directory_uri(); ?>/resources/js/html5.js"></script>
    <![endif]-->
    <link href='http://fonts.googleapis.com/css?family=Lato:400,300italic,300,700,700italic,400italic' rel='stylesheet' type='text/css'>
    <?php wp_head(); ?>
    <style type="text/css">
        body {
            background-color: <?php echo $wiser_option['body_bg_color']; ?>; ;
        }
        #header.home_header {
            background: rgba(0, 0, 0, 0)
                radial-gradient(ellipse at center center ,
                <?php echo $wiser_option['custom_header_bg_gradient']; ?> 0%, /* #30ac5e */
                <?php echo $wiser_option['custom_header_bg']; ?> 100%) repeat scroll 0 0; /* #1d8241 */
        }
        .main_menu, .main_menu a {
            background-color: <?php echo $wiser_option['header_menu_bgcolor']; ?>;
            color: <?php echo $wiser_option['header_menu_color']; ?>;
        }
        .nav > li > a:focus, .nav > li > a:hover, .nav li.current-menu-item > a {
            background-color: <?php echo $wiser_option['header_active_menu_bgcolor']; ?>;
            color: <?php echo $wiser_option['header_active_menu_color']; ?>;
        }
        .post_inner_content {
            color: <?php echo $wiser_option['content_text_color']; ?>;
        }
        input[type="submit"] {
            background-color: <?php echo $wiser_option['submit_buttons_color']; ?> ;
        }
        .learn_more_btn {
            background-color: <?php echo $wiser_option['lear_more_buttons_color']; ?> ;
        }
        #footer {
            background-color: <?php echo $wiser_option['footer_bgcolor']; ?>;
            color: <?php echo $wiser_option['footer_txt_color']; ?>;
        }
        #footer h4,
        #footer a {
            color: <?php echo $wiser_option['footer_txt_color']; ?>;
        }
    </style>
    <?php
    if( can_tracking() ) {
        echo $wiser_option['analytics_code'];
    }
    ?>
</head>

<body <?php body_class(); ?>>
    <?php
    $is_home = false; $logo = $wiser_option['header_logo']; $fixed_header_logo = $wiser_option['fixed_header_logo'];
    if ( is_home() || is_front_page() ) {
        $is_home = true;
        if( $wiser_option['custom_header'] ){ $logo = $wiser_option['custom_header_logo']; } // custom logo for home page
    }
    ?>
    <div id="header" class="header container-fluid <?php if( $is_home && $wiser_option['custom_header'] ){ echo 'home_header'; } ?>">
        <div id="header_top_area">
            <header id="header_inner" class="container" role="banner">
                <div class="row">
                    <div class="left_header col-md-3">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php bloginfo( 'name' ); ?>" class="logo" rel="home">
                            <img src="<?php echo $logo; ?>" alt="logo" class="static_header_logo">
                            <img src="<?php echo $fixed_header_logo; ?>" alt="fixed header logo" class="fixed_header_logo">
                        </a>
                    </div>
                    <div class="right_header col-md-9 clearfix">
                        <?php
                        if( $wiser_option['phone_number'] ){
                            ?>
                            <span class="head_phone phone_icon" title="Phone number">
                                <a href="tel:<?php echo $wiser_option['phone_number']; ?>"> <?php echo $wiser_option['phone_number']; ?> </a>
                            </span>
                        <?php
                        }
                        ?>

                        <div id="main_menu_div" class="main_menu_div">
                            <div class="navbar-header">
                                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main_menu" aria-expanded="false" aria-controls="navbar">
                                    <span class="sr-only"><?php _e('Toggle navigation'); ?></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                            </div>
                            <div id="main_menu" class="collapse navbar-collapse">
                                <?php
                                wp_nav_menu( array(
                                    'items_wrap'    => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                                    'menu_id'   => 'main_menu_ul',
                                    'menu_class'    => 'main_menu nav navbar-nav',
                                    'container' => 'none',
                                    'theme_location'    => 'main_menu'
                                ) ); ?>
                            </div>
                        </div>

                    </div>
                </div>
            </header>
            <!-- #header_inner -->
        </div>

        <?php
        if( $is_home ){
            if ( have_posts() ) {
                the_post();
                ?>
        <div id="home_header_container" class="<?php if( $is_home && !$wiser_option['custom_header'] ){ echo 'content_header'; } ?>">
            <div class="container">
                <?php if( has_post_thumbnail() ) {
                    $prevthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
                    $prevthumb = $prevthumb[0];
                    $prev_img = '<img src="'.esc_url( $prevthumb ).'" class="home_top_image">';
                    echo $prev_img;
                }

                $learn_more = '';
                $infos = i_get_fields( $post->ID );
                $featured_posts = $wiser_option['featured_posts'];
                if( $infos['learn_more_link'] ){
                    $learn_more = $infos['learn_more_link'];
                }
                ?>
                <h1><?php the_title(); ?></h1>
                <?php
                $featured_posts_view = $wiser_option['featured_posts_view'];
                if( $featured_posts_view && !empty( $featured_posts ) && count( $featured_posts ) && $featured_posts[0] != 'null' ){
                    ?>
                    <div class="home_description"><?php the_content(); ?></div>
                    <?php
                }
                ?>
                <!--<a href="<?php /*echo $learn_more; */?>" class="learn_more_primary"><?php /*_e('Learn More'); */?></a>-->
            </div>
        </div>
                <?php
            }
        }
        ?>
    </div><!-- #header -->
    <div id="content" class="site-content">
