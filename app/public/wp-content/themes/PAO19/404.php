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
	<div id="content" class="container page_content">
    <div class="left_and_sidebar">
    <div class="a_404_page">
        <div id="home_content_left" class="content_left_part">
			<?php //wiser_breadcrumb(); ?>
			<?php //the_content(); ?>
            <h1>404</h1>
			<!--<img src="<?php // echo get_template_directory_uri(); ?>/resources/images/404.png" class="i_404_img">-->
			<div class="searchform_404">
				<h3 class="i_s404_title">
					<?php _e("Sorry, we coldn't find the page you are looking for. Try these other great articles:", "wiserthemes"); ?>
				</h3>
                            <h4>
                                <?php _e("Latest Posts", "wiserthemes"); ?>
                            </h4>
                            <hr>
                            <?php
                            $image = '';
        ?>
            <div id="realted_posts_404">
                <div class="row">
                <?php
                $args = array('numberposts' => 12,
            'post_status' => 'publish');
$recent_posts = wp_get_recent_posts($args);
        foreach ($recent_posts as $recent) {
            if (has_post_thumbnail($recent["ID"])) {
                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($recent["ID"]), 'full');
                $prevthumb = $prevthumb[0];
                $image = esc_url($prevthumb);
            } else {
                $image =get_template_directory_uri() . '/resources/images/test_photo.png';
            }
            ?>
                <div class="a_404__all_posts col-md-4">
                    <div class="a_post_img_class a_404__posts_image col-md-5" style="background-image: url(<?php echo $image;?>);">
                        <?php // echo $image; ?>
                    </div>
                    <div class="a_404__posts_title col-md-7">
                        <h3>
                            <a href="<?php echo get_permalink($recent["ID"]); ?>">
                                <?php echo $recent["post_title"]; ?>
                            </a>
                        </h3>
                    </div>
                </div>
            <?php
        }?>
            </div>
            </div>
    <?php
    
                            ?>
			</div>
		</div>
            <div id="sidebar">
            <?php include 'inc/custom-sidebar.php'; ?>
        </div>
		</div>
		</div>
	</div>

<?php
get_footer();
?>
