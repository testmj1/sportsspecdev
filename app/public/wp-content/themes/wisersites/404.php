<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single Posts Template
 *
 *
 * @file           404.php
 * @package        Wiser Themes
 * @author
 * @copyright      2015 WiserThemes
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/wiserthemes/404.php
 * @since          available since Release 1.0
 */

get_header(); ?>

<?php global $theme_url; ?>

	<?php get_template_part( 'template-parts/content-header' ); ?>
	<div id="content" class="container page_content">
		<div id="post-404" <?php post_class('error_404 post_inner_content'); ?>>
			<?php //wiser_breadcrumb(); ?>
			<?php //the_content(); ?>
			<img src="<?php echo $theme_url; ?>/images/404.png" class="i_404_img">
			<div class="searchform_404">
				<h3 class="i_s404_title">
					<?php _e('Did you get here by mistake, maybe you can use this search to find what you were looking for', 'wiserthemes'); ?>
				</h3>
				<?php get_search_form( true ); ?>
			</div>
		</div>
	</div>

<?php
get_footer();
?>
