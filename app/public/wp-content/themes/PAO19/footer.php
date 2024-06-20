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
if(isset($wiser_option['footer_logo'])){
    
$footer_logo = $wiser_option['footer_logo'];
}
?>
</div><!-- .site-content -->

<div id="footer" class="header container-fluid">
    <footer id="footer_inner" class="footer_inner container" role="footerinfo">
        <div class="row">

            <div class="left_footer col-md-6">
                <?php
                wp_nav_menu( array(
                    'items_wrap'    => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'menu_id'   => 'footer_menu_ul',
                    'menu_class'    => 'footer_menu',
                    'container' => 'none',
                    'theme_location'    => 'foot_menu'
                ) ); ?>
            </div>
            <div class="right_footer col-md-6">
                <?php
                wp_nav_menu( array(
                    'items_wrap'    => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'menu_id'   => 'footer_menu_ul',
                    'menu_class'    => 'footer_menu',
                    'container' => 'none',
                    'theme_location'    => 'foot_menu_2'
                ) ); ?>
                <div class="a_copyright">
                    <p>&copy;Copyright <?php echo date("Y"); ?></p>
                </div>
            </div>
        </div><!-- .row -->
    </footer><!-- .footer_inner -->
</div>

</div><!-- .site -->

<?php wp_footer(); ?>

</body>
</html>

