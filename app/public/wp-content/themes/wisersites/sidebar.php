<?php
/**
 * The Sidebar containing the main widget area
 *
 * @package Wiser Sites
 * @subpackage Wiser_Sites
 * @since Wiser Sites 1.0
 */

if ( ! is_active_sidebar( 'sidebar-left' ) ) {
	return;
}
?>
<div id="left_sidebar_div">
	<div id="primary_sidebar" class="primary_sidebar left_sidebar widget_area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-left' ); ?>
	</div><!-- #primary-sidebar -->
</div><!-- #left_sidebar_div -->
