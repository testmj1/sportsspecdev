<?php
//global $wiser_option;
$featured_posts_image_or_icon = $wiser_option['featured_posts_image_or_icon'];
$social_title = $wiser_option['social_title'];
$social = $wiser_option['social'];
$facebook_link = $social['facebook'];
$twitter_link = $social['twitter'];
$google_plus_link = $social['google_plus'];
$linkedin_link = $social['linkedin'];
$youtube_link = $social['youTube'];
$instagram_link = $social['instagram'];
$pinterest_link = $social['pinterest'];
$vimeo_link = $social['vimeo'];
$newsletter_link = $social['newsletter'];
?>
 <?php
            $sidebar_logo = $wiser_option['sidebar_logo'];
            if(!empty($sidebar_logo)){
            ?>
            <div id="sidebar_logo">
                <img src="<?php echo $sidebar_logo;?>">
            </div>
            <?php
            }
            ?>
<?php
if (!empty($featured_posts_image_or_icon[0]) || $featured_posts_image_or_icon[0]!=='null') {
$args = array(
                        'numberposts' => 4,
                        'post_status' =>'publish'
                        );
$recent_posts = wp_get_recent_posts($args);
?>
<div class="a_sidebar_posts">
    <div class="sidebar_posts_headline">
        <h2>Latest</h2>
        <div class="widget follow">
    </h2>
    <ul>
        <?php
        if (!empty($twitter_link)) {
        ?>
        <li class="twitter">
            <a target="_blank" class="" href="<?php echo $twitter_link; ?>">
                <span><i class="fab fa-twitter"></i></span></a>
        </li>
        <?php
}
        ?>
        <?php
        if (!empty($facebook_link)) {
        ?>
        <li class="facebook">
            <a target="_blank" class="" href="<?php echo $facebook_link; ?>">
                <span><i class="fab fa-facebook-f"></i></span></a>
        </li>
        <?php
}
        ?>
        <?php
        if (!empty($google_plus_link)) {
        ?>
        <li class="google_plus">
            <a target="_blank" class="" href="<?php echo $google_plus_link; ?>">
                <span><i class="fab fa-google-plus-g"></i></span></a>
        </li>
        <?php
}
        ?>
        <?php
        if (!empty($linkedin_link)) {
        ?>
        <li class="linkedin">
            <a target="_blank" class="" href="<?php echo $linkedin_link; ?>">
                <span><i class="fab fa-linkedin-in"></i></span></a>
        </li>
        <?php
}
        ?>
        <?php
        if (!empty($youtube_link)) {
        ?>
        <li class="youtube">
            <a target="_blank" class="" href="<?php echo $youtube_link; ?>">
                <span><i class="fab fa-youtube"></i></span></a>
        </li>
        <?php
}
        ?>
        <?php
        if (!empty($instagram_link)) {
        ?>
        <li class="instagram">
            <a target="_blank" class="" href="<?php echo $instagram_link; ?>">
                <span><i class="fab fa-instagram"></i></span></a>
        </li>
        <?php
}
        ?>
        <?php
        if (!empty($pinterest_link)) {
        ?>
        <li class="pinterest">
            <a target="_blank" class="" href="<?php echo $pinterest_link; ?>">
                <span><i class="fab fa-pinterest-p"></i></span></a>
        </li>
        <?php
}
        ?>
        <?php
        if (!empty($vimeo_link)) {
        ?>
        <li class="vimeo">
            <a target="_blank" class="" href="<?php echo $vimeo_link; ?>">
                <span><i class="fab fa-vimeo-v"></i></span></a>
        </li>
        <?php
}
        ?>
    </ul>
</div>
    </div>
    <hr>
    <?php
    if ($featured_posts_image_or_icon[0] == 'Post Thumbnail') {
        $image = '';
        ?>
            <div id="posts_with_featured_image">
                <?php
        foreach ($recent_posts as $recent) {
            if (has_post_thumbnail($recent["ID"])) {
                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($recent["ID"]), 'full');
                $prevthumb = $prevthumb[0];
                $image = '<img src="' . esc_url($prevthumb) . '">';
            } else {
                $image = '<img src="' . get_template_directory_uri() . '/resources/images/test_photo.png" >';
            }
            ?>
                <div class="sidebar_all_posts">
                <div class="row">
                    <div class="sidebar_posts_image col-md-4">
                        <?php echo $image; ?>
                    </div>
                    <div class="sidebar_posts_title col-md-8">
                        <h3>
                            <a href="<?php echo get_permalink($recent["ID"]); ?>">
                                <?php echo $recent["post_title"]; ?>
                            </a>
                        </h3>
                    </div>

                </div>
                </div>
            <?php
        }?>
            </div>
    <?php
    } elseif ($featured_posts_image_or_icon[0] == 'Category Icon') {
        $args = array(
                        'numberposts' => 8,
                        'post_status' =>'publish'
                        );
$recent_posts = wp_get_recent_posts($args);
        $image = '';?>
            <div id="posts_with_category_icon">
                <?php
        foreach ($recent_posts as $recent) {
            $categories = get_the_category($recent["ID"]);
            if (has_post_thumbnail($recent["ID"])) {
                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($recent["ID"]), 'full');
                $prevthumb = $prevthumb[0];
                $image = '<img src="' . esc_url($prevthumb) . '">';
            } else {
                $image = '<img src="' . get_stylesheet_directory_uri() . '/resources/images/test_photo.png" >';
            }
            ?>
                <div class="sidebar_all_posts">
                <div class="row">
                    <div class="sidebar_posts_icon">
                        <?php
                        $cat_id = $categories[0]->term_id;
                        $image_id = get_term_meta($cat_id, 'category-image-id', true);
                        echo wp_get_attachment_image($image_id, 'large');   
                        ?>
                    </div>
                    <div class="sidebar_posts_title">
                        <h3>
                            <a href="<?php echo get_permalink($recent["ID"]); ?>">
                                <?php echo $recent["post_title"]; ?>
                            </a>
                        </h3>
                    </div>

                </div>
                </div>
            <?php
        }?>
            </div>
        <?php }
        ?>
        <?php
    }
    ?>
</div>
<div id="primary_sidebar">
<?php if ( is_active_sidebar( 'sidebar-primary' ) ) : ?>
    <div id="primary_sidebar_inner">
        <?php dynamic_sidebar( 'sidebar-primary' ); ?>
    </div>
<?php endif; ?>
</div>