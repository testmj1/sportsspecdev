<?php
/**
 * The Sidebar containing the main widget area
 *
 * @package Wiser Sites
 * @subpackage Wiser_Sites
 * @since Wiser Sites 1.0
 */

if ( ! is_active_sidebar( 'sidebar-footer' ) ) {
	return;
}
?>
<div id="footer_sidebar_div">
	<div id="footer_sidebar" class="footer_sidebar widget_area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-footer' ); ?>
	</div><!-- #footer_sidebar -->
</div><!-- #footer_sidebar_div -->

