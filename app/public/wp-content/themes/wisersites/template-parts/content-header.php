<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content header Template-Part File
 *
 * @file           content-header.php
 * @package        Wiser Sites
 * @author
 * @copyright      2015 WiserSites
 * @license        license.txt
 * @version        Release: 1.1.0
 * @filesource     /wp-content/themes/wisersites/template-parts/content-header.php
 * @since          available since Release 1.0
 */

/**
 * Display breadcrumb
 */
//get_responsive_breadcrumb_lists();

/**
 * Display archive information
 */
?>
<div class="content_header">

<?php
//For Single post
if ( is_single() ) {
	$the_category = get_the_category();
	if( !empty( $the_category ) ) {
		$the_category = $the_category[0];
		$d1_class = 'i_left col-md-10';
		$d2_class = 'i_right col-md-2';
	} else {
		$d1_class = 'col-md-12';
		$d2_class = '';
	}
	?>
	<div id="inner_content_header" class="container ">
		<div class="<?php echo $d1_class; ?>">
			<h1 title="<?php the_title(); ?>" ><?php the_title(); ?></h1>
		</div>
		<?php
		if( !empty( $the_category ) ) {
			?>
			<div class="<?php echo $d2_class; ?>">
				<a href="<?php echo get_term_link( $the_category ); ?>" class="back_to_blog" title="<?php echo __('Back to', 'wiserthemes' ).' '.$the_category->name; ?>">
					<?php echo __('Back to', 'wiserthemes' ).' '.$the_category->name; ?>
				</a>
			</div>
		<?php
		}
		?>
		<div class="post_date_div col-md-12">
			<span class="post_date" title="Post date"><?php the_date('d F Y'); ?></span>
		</div>
	</div>
	<?php
	if( has_post_thumbnail() ) {
		$prevthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
		$prevthumb = $prevthumb[0];
		$prev_img = '<img src="'.esc_url( $prevthumb ).'" class="not_visible">';
		echo '<div class="post_preview" style="background-image: url(' . esc_url( $prevthumb ) . ');">'.$prev_img.'</div>';
	}
	?>
	<?php
}
?>


<?php
//For Page view
if ( is_page() ) {
	?>
	<div id="inner_content_header" class="container ">
		<div class="page_header middle_width col-md-8">
			<h1 title="<?php the_title(); ?>" ><?php the_title(); ?></h1>
		</div>
	</div>
	<?php
	if( has_post_thumbnail() ) {
		$prevthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
		$prevthumb = $prevthumb[0];
		$prev_img = '<img src="'.esc_url( $prevthumb ).'" class="not_visible">';
		echo '<div class="post_preview" style="background-image: url(' . esc_url( $prevthumb ) . ');">'.$prev_img.'</div>';
	}
	?>
	<?php
}
?>

<?php
if ( is_category() || is_tag() ) {
	?>
	<div id="inner_content_header" class="container ">
		<div class="page_header">
			<h1><?php echo single_cat_title( '', false ); ?></h1>
		</div>
	</div>
	<?php
}

/**
 * Display Search information
 */

if ( is_search() ) {
	?>
	<div id="inner_content_header" class="container ">
		<div class="page_header">
			<h1><?php printf( __( 'Search results for: %s', 'wiserthemes' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
		</div>
	</div>
<?php
}
?>

<?php
//For 404 Page
if ( is_404() ) {
	?>
	<div id="inner_content_header" class="container ">
		<div class="page_header middle_width col-md-8">
			<h1 title="<?php _e('404 - Page Not Found'); ?>" ><?php _e('404 - Page Not Found', 'wiserthemes'); ?></h1>
		</div>
	</div>
	<?php
}
?>

</div>
