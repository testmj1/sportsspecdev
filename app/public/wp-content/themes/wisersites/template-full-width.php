<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Full width Template
 *
Template Name:  Full width Template
 *
 * @file           template-full-width.php
 * @package        Wiser Sites
 * @author
 * @copyright      2015 WiserSites
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/wisersites/template-full-width.php
 * @since          available since Release 1.0
 */

get_header(); ?>

<?php
if ( have_posts() ) {
	the_post();
?>
	<?php get_template_part( 'template-parts/content-header', get_post_type() ); ?>
	<div id="content" class="container page_content">
		<div id="post-<?php the_ID(); ?>" <?php post_class('post_inner_content'); ?>>
			<?php the_content(); ?>
		</div>
		<?php
		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) {
		?>
		<div id="comments_div" class="middle_width col-md-8">
		<?php comments_template(); ?>
		</div>
		<?php
		}
		?>
	</div>
<?php
} else {

}
?>
<?php
get_footer();
?>
