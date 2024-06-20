<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single Posts Template
 *
 *
 * @file           single.php
 * @package        Wiser Sites
 * @author
 * @copyright      2015 WiserSites
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/wisersites/single.php
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
			<?php wiser_breadcrumb(); ?>
			<?php the_content(); ?>
		</div>
	</div>
<?php
} else {

}
?>
<?php
get_footer();
?>
