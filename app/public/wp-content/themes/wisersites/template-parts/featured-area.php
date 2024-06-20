<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

global $wiser_option;

$featured_posts_view = $wiser_option['featured_posts_view'];
if( $featured_posts_view ){
	$featured_posts = $wiser_option['featured_posts'];
	$parent_id = get_the_ID();

	$args = array(
		'post_type' => 'page',
		'post_status' => 'publish',
		'numberposts' => -1,
		'orderby' => 'menu_order',
		'order' => 'ASC'
	);

	if( count($featured_posts) ){
		$featured_posts = implode(",", $featured_posts);
		$args['include'] = $featured_posts;
		$args['orderby'] = 'post__in';
	} else {
		$args['post_parent'] = $parent_id;
	}

	$home_posts = get_posts($args);
	$f=1;
//i_print($home_posts);
	if( count($home_posts) ) {

		//Posts menu - scroll
		if( $wiser_option['featured_posts_menu'] ){

			echo '<ul class="i_featured_posts_menus">';
			foreach ($home_posts as $t_post) {
				$icon = i_get_field( $t_post->ID, "scroll_menu_icon");
				echo '<li title="' . __('Jump to ') . ' ' . $t_post->post_title . '"> <a href="#i_featured_post_'.$t_post->ID.'" class="i_scroll_menu">';
				if( trim( $icon ) != '' ) echo '<span class="m_icon"><img src="'.$icon.'" ></span>';
				echo $t_post->post_title . '</a> </li>';
			}
			echo '</ul>';

		}

		//Posts content
		foreach ($home_posts as $t_post) {
			$infos = i_get_fields( $t_post->ID );

			if( $infos['learn_more_link'] ){
				$learn_more = $infos['learn_more_link'];
			} else {
				$learn_more = get_permalink( $t_post->ID );
			}

			?>
			<?php
			if( $f%2 == 0 ){
				$first_class = 'i_right'; $second_class = 'i_left';
			} else {
				$first_class = 'i_left'; $second_class = 'i_right';
			}
			?>
			<div id="i_featured_post_<?php echo $t_post->ID; ?>">
				<div class="<?php echo $first_class; ?> col-md-6 i_feat_image">
					<?php
					$prevthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $t_post->ID ), 'full' );
					if(count($prevthumb)){
						$prevthumb = $prevthumb[0];
						$prev_img = '<img src="'.esc_url( $prevthumb ).'" class="home_featured_post_image ">';
						echo $prev_img;
					}
					?>
				</div>
				<div class="<?php echo $second_class; ?> col-md-6 i_feat_txtinfo">
					<?php
					if( trim( $short_description = $infos['short_description'] ) == '' ){
						$short_description = $t_post->post_content;
					}
					?>
					<h2><?php echo $t_post->post_title; ?></h2>
					<div class="i_description"> <?php echo $short_description; ?> </div>
					<a class="learn_more_btn" href="<?php echo $learn_more; ?>"> Learn More </a>
				</div>
				<div class=" clearfix"></div>
			</div>

			<?php
			$f++;
		}
	}
}

?>