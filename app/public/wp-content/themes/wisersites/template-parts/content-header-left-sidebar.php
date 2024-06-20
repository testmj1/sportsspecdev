<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content header Template-Part File
 *
 * @file           content-header-left-sidebar.php
 * @package        Wiser Sites
 * @author
 * @copyright      2015 WiserSites
 * @license        license.txt
 * @version        Release: 1.1.0
 * @filesource     /wp-content/themes/wisersites/template-parts/content-header-left-sidebar.php
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
//For Page view
if ( is_page() ) {
	?>
	<div id="inner_content_header" class="container ">
		<div class="page_header to_right col-md-9">
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
</div>
