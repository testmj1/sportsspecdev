<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Left Sidebar Template
 *
Template Name:  Left Sidebar
 *
 * @file           template-sidebar-right.php
 * @package        WiserSites
 * @author         Jason
 * @copyright      20015 Wiser Sites
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/wisersites/template-left-sidebar.php
 * @link
 * @since          available since Release 1.0
 */

get_header(); ?>

<?php
if ( have_posts() ) {
	the_post();
?>
	<?php get_template_part( 'template-parts/content-header', 'left-sidebar' ); ?>
	<div id="content" class="container with_sidebar">

		<div class="i_left i_sidebar col-md-3">
			<?php get_sidebar('left'); ?>
		</div>

		<div class="i_right col-md-9">
			<div id="post-<?php the_ID(); ?>" <?php post_class('post_inner_content'); ?>>
				<?php the_content(); ?>
			</div>

			<div id="comments_div" class="middle_width col-md-9">
				<?php
				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</div>

	</div>
<?php
} else {

}
?>
<?php
get_footer();
?>
