<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <?php global $wiser_option; ?>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width">
        <link rel="icon" href="<?php echo $wiser_option['favicon']; ?>" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo $wiser_option['favicon']; ?>" type="image/x-icon" />
        <title><?php wp_title('|', true, 'right'); ?></title>
        <link href='//fonts.googleapis.com/css?family=Lato:400,300italic,300,700,700italic,400italic' rel='stylesheet' type='text/css'>
        <?php wp_head(); ?>
		<script type="text/javascript">
    var _ss = _ss || [];
    _ss.push(['_setDomain', 'https://koi-3QNMR54JQQ.marketingautomation.services/net']);
    _ss.push(['_setAccount', 'KOI-49XMIK97KY']);
    _ss.push(['_trackPageView']);
(function() {
    var ss = document.createElement('script');
    ss.type = 'text/javascript'; ss.async = true;
    ss.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'koi-3QNMR54JQQ.marketingautomation.services/client/ss.js?ver=2.4.0';
    var scr = document.getElementsByTagName('script')[0];
    scr.parentNode.insertBefore(ss, scr);
})();
</script>
        <?php
    echo '<script type="text/javascript" data-cfasync="false">';
    echo 'var i_infos = {"ajax_url":"' . admin_url('admin-ajax.php') . '", "home_url":"' . home_url() . '"};';
    echo '</script>';
     echo $wiser_option['analytics_code'];
    ?>
        <style type="text/css">
            body {
                background-color: <?php echo $wiser_option['body_bg_color']; ?> ;
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
    </head>

    <body <?php body_class(); ?>>
        <?php
        $is_home = false;
        $header_circle_logo = $wiser_option['header_circle_logo'];
        $header_text_logo = $wiser_option['header_text_logo'];
        
        ?>
        <div id="header" class="header container-fluid <?php
        if ($is_home && $wiser_option['custom_header']) {
            echo 'home_header';
        }
        ?>">
            <div id="header_top_area">
                <header id="header_inner" role="banner">
                    <div class="row">
                        <div id="top_header">
                            <div class="container">
                                <div class="top_left_header col-md-4">
                                    <a href="<?php echo esc_url(home_url('/')); ?>" title="<?php bloginfo('name'); ?>" class="logo" rel="home">
                                        <?php
                                        if(!empty($header_circle_logo)){
                                        ?>
                                        <img src="<?php echo $header_circle_logo; ?>" alt="logo" class="static_header_logo header_circle_logo">
                                        <?php }?>
                                        <?php
                                        if(!empty($header_text_logo)){
                                        ?>
                                        <img src="<?php echo $header_text_logo; ?>" alt="logo" class="static_header_logo header_text_logo">
                                        <?php }?>
                                    </a>
                                </div>
                                <div class="top_middle_header col-md-5">
                                    <div id="main_menu_div" class="main_menu_div">
                                        <!--                            <div class="navbar-header">
                                                                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main_menu" aria-expanded="false" aria-controls="navbar">
                                                                            <span class="sr-only"><?php // _e('Toggle navigation');   ?></span>
                                                                            <span class="icon-bar"></span>
                                                                            <span class="icon-bar"></span>
                                                                            <span class="icon-bar"></span>
                                                                        </button>
                                                                    </div>-->
                                        <div id="top_main_menu" class="collapse navbar-collapse">
                                            <?php
                                            if ( has_nav_menu( 'header_top_menu' ) ) {
                                            wp_nav_menu(array(
                                                'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                                                'menu_id' => 'header_top_main_menu_ul',
                                                'menu_class' => 'main_menu_ul main_menu nav navbar-nav',
                                                'container' => 'none',
                                                'theme_location' => 'header_top_menu'
                                            ));
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if(!empty($wiser_option['header_right_text']['right_text'])){
                                ?>
                                <div class="top_right_header col-md-3 clearfix">
                                    <h4><?php echo $wiser_option['header_right_text']['right_text']; ?></h4>
                                </div>
                                <?php }?>
                                
                            </div>
                        </div>
                        <div id="header_bootom">
                            <div class="container">
                                <div class="header_bottom_left_and_right">
                                <div class="bottom_left_header col-md-6">
                                    <div id="top_main_menu" class="menu_mobile collapse navbar-collapse">
                                            <?php
                                            if ( has_nav_menu( 'header_top_menu' ) ) {
                                            wp_nav_menu(array(
                                                'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                                                'menu_id' => 'header_top_main_menu_ul',
                                                'menu_class' => 'main_menu_ul main_menu nav navbar-nav',
                                                'container' => 'none',
                                                'theme_location' => 'header_top_menu'
                                            ));
                                            }
                                            ?>
                                        </div>
                                    <?php
                                    if ( has_nav_menu( 'header_category_menu' ) ) {
                                            wp_nav_menu(array(
                                                'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                                                'menu_id' => 'category_main_menu_ul',
                                                'menu_class' => 'main_menu_ul main_menu nav navbar-nav',
                                                'container' => 'none',
                                                'theme_location' => 'header_category_menu'
                                            ));
                                            }
                                            ?>
                                     <!--<div class="subscribe_link">-->
                                        <!--<a href="<?php // echo $wiser_option['header_subscribe_link']['subscribe_link']; ?>">-->
<?php // echo $wiser_option['header_subscribe_text']['subscribe_text']; ?>
                                        <!--</a>-->
                                    <!--</div>-->
                                    
                                </div>
                                <div class="bottom_right_header col-md-6">
                                    <div id="header_bottom_menu_div" class="main_menu_div">
                                        <div id="bottom_main_menu" class="collapse navbar-collapse">
                                            <?php
                                            if ( has_nav_menu( 'header_bootom_menu' ) ) {
                                            wp_nav_menu(array(
                                                'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                                                'menu_id' => 'header_bottom_main_menu_ul',
                                                'menu_class' => 'main_menu_ul main_menu nav navbar-nav',
                                                'container' => 'none',
                                                'theme_location' => 'header_bootom_menu'
                                            ));
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="search_div">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                        <div class="serch_part">
                                            <form action="/" method="get" class="search_form">
                                                <input class="type_your_text" type="text" name="s" id="search" value="<?php the_search_query(); ?>"
                                                       placeholder="<?php _e('Search', 'wiserthemes'); ?>"/>
                                                <input type="submit" alt="<?php _e('Search', 'wiserthemes'); ?>"
                                                       value="<?php _e('Search', 'wiserthemes'); ?>" class="search_submit_btn"/>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            <div class="hamburger_icon">
                                <i class="fas fa-bars"></i>
                            </div>
                            </div>
                        </div>
                        
                    </div>
                </header>
                <!-- #header_inner -->
            </div>


        </div><!-- #header --> 
        <div id="content_parent" class="site-content">
        <?php
                        if(!empty($wiser_option['advertisement_url']) && !empty($wiser_option['header_advertisement_image'])){
                            
                            ?>
                        <div id="header_advertisement">
                            <div class="container">
                                <?php if( function_exists('the_ad_placement') ) { the_ad_placement('header-advertisement'); } ?>
                            </div>
                        </div>
                        <?php }?>









