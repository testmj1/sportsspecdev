<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "site-content" div and all content after.
 *
 * @package Wiser Sites
 * @since Wiser Sites 1.0
 */
?>
<?php
global $theme_url, $wiser_option;
$footer_logo = $wiser_option['footer_logo'];
?>
</div><!-- .site-content -->

<div id="footer" class="header container-fluid">
    <footer id="footer_inner" class="footer_inner container" role="footerinfo">
        <div class="row">

            <div class="left_footer col-md-3">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" rel="home">
                    <img src="<?php echo $footer_logo; ?>" alt="footer-logo">
                </a>
            </div>

            <div class="middle_footer footer_menu_div col-md-2 col-sm-4">
                <?php
                wp_nav_menu( array(
                    'items_wrap'    => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'menu_id'   => 'footer_menu_ul',
                    'menu_class'    => 'footer_menu',
                    'container' => 'none',
                    'theme_location'    => 'foot_menu'
                ) ); ?>
            </div>

            <div class="middle_footer footer_menu_div col-md-2 col-sm-4">
                <?php
                wp_nav_menu( array(
                    'items_wrap'    => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'menu_id'   => 'footer_menu_ul',
                    'menu_class'    => 'footer_menu',
                    'container' => 'none',
                    'theme_location'    => 'foot_menu_2'
                ) ); ?>
            </div>

            <div class="middle_footer footer_menu_div col-md-2 col-sm-4">
                <?php
                wp_nav_menu( array(
                    'items_wrap'    => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'menu_id'   => 'footer_menu_ul',
                    'menu_class'    => 'footer_menu',
                    'container' => 'none',
                    'theme_location'    => 'foot_menu_3'
                ) ); ?>
            </div>

            <div class="right_footer col-md-3">
                <h4><?php _e( $wiser_option['social_title'] ); ?></h4>
                <?php echo do_shortcode( '[wiser_social_links]' ); ?>
                <?php /*<ul class="social_ul clearfix">
                    <li>
                        <a href="#" title="" target="_blank"> <img src="<?php echo $theme_url; ?>images/socials/google_plus.png" alt=""></a>
                    </li>
                    <li>
                        <a href="#" title="" target="_blank"> <img src="<?php echo $theme_url; ?>images/socials/twitter.png" alt=""></a>
                    </li>
                    <li>
                        <a href="#" title="" target="_blank"> <img src="<?php echo $theme_url; ?>images/socials/facebook.png" alt=""></a>
                    </li>
                </ul>*/ ?>
            </div>
        </div><!-- .row -->
    </footer><!-- .footer_inner -->
</div>

</div><!-- .site -->

<?php wp_footer(); ?>

</body>
</html>
