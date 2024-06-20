<?php
/**
 * The template for displaying Category pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Wiser Sites
 * @subpackage Wiser_Sites
 * @since Wiser Sites 1.0
 */

get_header(); ?>

<?php get_template_part('template-parts/content-header', get_post_type()); ?>
<div id="content_archive" class="container">

	<div class="i_left i_sidebar col-md-3">
		<?php get_sidebar('left'); ?>
	</div>

	<div class="i_middle archive_posts col-md-6">
		<?php wiser_breadcrumb(); ?>
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			get_template_part('template-parts/post', 'preview');
			?>
			<?php
		}
		global $wp_query;
		//i_print($wp_query);
		// Pagination Parameters
		$pagiArgs = array(
			'end_size' 	=> 0,
			'mid_size' 	=> 1,
			//'current'	=> 2,
			'type'		=> 'list',
			//'total' => 50,
			'prev_next'	=> true,
			'prev_text'	=> __('Previous'),
			'next_text'	=> __('Next'),
		);

		echo '<div class="pagination">';
		echo paginate_links( $pagiArgs );
		echo '</div>';
	} else {
		get_template_part('content', 'none');
	}
	?>
	</div>

	<div class="i_right i_sidebar col-md-3">
		<?php get_sidebar('right'); ?>
	</div>
</div>
<?php
get_footer();
?>
