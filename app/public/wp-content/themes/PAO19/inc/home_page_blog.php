<div class="all_posts">
    <h2>More Stories</h2>
    <hr>
    <?php
    if (get_query_var('paged')) {
        $paged = get_query_var('paged');
    } elseif (get_query_var('page')) { // 'page' is used instead of 'paged' on Static Front Page
        $paged = get_query_var('page');
    } else {
        $paged = 1;
    }

    $custom_query_args = array(
        'post_type' => 'post',
        // 'posts_per_page' => 10,
        'paged' => $paged,
        'post_status' => 'publish',
        'ignore_sticky_posts' => true,
        'order' => 'DESC', 
        'orderby' => 'date'
    );
    $custom_query = new WP_Query($custom_query_args);
    ?>
    <div class="row">
        <?php
        if ($custom_query->have_posts()) :
            while ($custom_query->have_posts()) : $custom_query->the_post();
                $content = get_the_content();
                $add_external_link = get_field( "add_external_link", $post->ID );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_permalink($post->ID);
                                }
                ?>

                <div class="alm-item a_blog_post col-md-4">
                    <article <?php post_class(); ?>>
                        <div class="post_content_image_part">
                            <?php
//                                        echo get_the_post_thumbnail($post->ID); 
                            if (has_post_thumbnail($post->ID)) {
                                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
                                $prevthumb = $prevthumb[0];
//                                echo '<img src="' . esc_url($prevthumb) . '">';
                            } else {
//                                echo '<img src="' . get_stylesheet_directory_uri() . '/images/test_photo.png" class="not_visible">';
                                $prevthumb = get_template_directory_uri() . '/images/test_photo.png';
                            }
                            ?>
                            <a class="a_post_img_class a_post_image" <?php $target;?> href="<?php echo $permalink; ?>" style="background-image:url(<?php echo $prevthumb;?>)"></a>
                        </div>
                        <div class="post_content_title_part">
                            <h3><a <?php $target;?> href="<?php echo $permalink; ?>"><?php the_title(); ?></a></h3>
                        </div>
                    </article>
                </div>

                <?php
            endwhile;
            ?>
        </div>

        <?php if ($custom_query->max_num_pages > 1) :   ?>
            <?php
            $orig_query = $wp_query;
            $wp_query = $custom_query;
            ?>
<!--            <nav class="prev-next-posts">
                <div class="prev-posts-link">
                    <?php // echo get_next_posts_link('Older Entries', $custom_query->max_num_pages); ?>
                </div>
                <div class="next-posts-link">
                    <?php // echo get_previous_posts_link('Newer Entries'); ?>
                </div>
            </nav>-->
            <?php
            $wp_query = $orig_query;
            ?>
        <?php endif; ?>

        <?php
        wp_reset_postdata();
    else:
        echo '<p>' . __('Sorry, no posts matched your criteria.') . '</p>';
    endif;
    ?>
</div>
<div class="loadmore">Load More</div>