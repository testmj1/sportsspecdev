<?php
/**
 * The Sidebar containing the main widget area
 *
 * @package Wiser Sites
 * @subpackage Wiser_Sites
 * @since Wiser Sites 1.0
 */

if ( ! is_active_sidebar( 'sidebar-right' ) ) {
	return;
}
?>
<div id="right_sidebar_div">
	<div id="right_sidebar" class="right_sidebar widget_area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-right' ); ?>
	</div><!-- #right_sidebar -->
</div><!-- #right_sidebar_div -->
