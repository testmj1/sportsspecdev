<?php
get_header();
?>
<?php
global $wiser_option;
$select_slider_posts = array();
if(isset($wiser_option['select_slider_posts'])){
    $select_slider_posts = $wiser_option['select_slider_posts'];
}

?>

<div id="content" class="home_content container page_content">
    <div class="left_and_sidebar">
        <div id="home_content_left" class="content_left_part">
            <div class="home_carusel">
                <div id="owl-home" class="owl-carousel owl-theme">
                    <?php
                    $slids_count = $wiser_option['slides_count'];
                    $select_slider_posts_count = count($select_slider_posts);
                    $diff = $slids_count - $select_slider_posts_count;
                    $ids_array = array();
                    if(isset($select_slider_posts)){
                    foreach ($select_slider_posts as $select_slider_posts_value) {
                        $ids_array[] = $select_slider_posts_value['post-id'];
                    }
                    }
                    if ($diff > 0) {
                        $args = array(
                            'numberposts' => $diff,
                            'post_status' => 'publish',
                            'orderby'     => 'date',
                            'order'       => 'DESC',
                            'slideSpeed' => 7000,
                            'post_type'   => 'post',
                            'post__not_in' => $ids_array
                        );
                        $recent_posts = get_posts($args);
                        foreach ($recent_posts as $recent_posts_value) {
                            $recent_posts_value_id = $recent_posts_value->ID;
                    $select_slider_posts[]['post-id'] = $recent_posts_value_id;
                        }
                    }
                    $image = '';
                    $posts_count = 1;
                    if(isset($select_slider_posts)){
                    foreach ($select_slider_posts as $select_slider_posts_value) {
                    if($posts_count > $slids_count){
                        break;
                    }
                    $add_external_link = get_field( "add_external_link", $select_slider_posts_value['post-id'] );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_permalink($select_slider_posts_value['post-id']);
                                }
                        $select_slider_post = get_post( $select_slider_posts_value['post-id'] );
                        if (has_post_thumbnail($select_slider_posts_value['post-id'])) {
                            $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($select_slider_posts_value['post-id']), 'full');
                            $prevthumb = esc_url($prevthumb[0]);
                            $image = $prevthumb;
                        } else {
                            $image =  get_template_directory_uri() . '/resources/images/test_photo.png';
                        }
                        ?>
                        <div class="item">
                            <div class="a_slide">
                                <a <?php echo $target;?> href="<?php echo $permalink; ?>">
                                <div class="slid_image" style="background-image: url(<?php echo $image;?>)">
                                        <?php // echo $image; ?>
                                </div>
                                <div class="slide_title">
                                    <h3>
                                        <a <?php echo $target;?> href="<?php echo $permalink; ?>">
                                            <?php echo $select_slider_post->post_title; ?>
                                        </a>
                                    </h3>
                                </div>
                                <div class="slid_excerpt_and_read_more">
                                    <?php
                                    $content = $select_slider_post->post_content;
                                        $excerpt = $select_slider_post->post_excerpt;
                                        ?>
                                    <div class="slid_excerpt">
                                    <?php
                                        if(!empty($excerpt)){
                                        ?>
                                        <?php echo $excerpt; ?>
                                        <?php
                                        }elseif(!empty ($content)){
                                            $new_excerpt = wp_trim_words($content, 10, '...');
                                            
                                        ?>
                                           <?php echo $new_excerpt;?>
                                        <?php
                                        }
                                        ?>
                                        <div class="slid_read_more">
                                        <a <?php echo $target;?> href="<?php echo $permalink; ?>">
                                            <i class="fas fa-arrow-right"></i>Read more
                                        </a>
                                        </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    $posts_count ++;
                        }
                        }
                    ?>

                </div>
            </div>
            <?php include 'inc/mobile-custom-sidebar-top.php'; ?> 


            <div id="home_3_sections">
                <?php if( have_rows('home_page_links', 'option') ): ?>
                <div class="row">
                    <?php while( have_rows('home_page_links', 'option') ): the_row(); 
                    $title = get_sub_field('title');
                    $image = get_sub_field('image');
                    $cta_text = get_sub_field('cta_text');
                    $cta_link = get_sub_field('cta_link');
                    $open_new_tab = get_sub_field('open_link_in_new_tab');
                    $row_index = get_row_index();
                    $section_class = '';
                    switch ($row_index) {
                        case 0:
                            $section_class = 'first_section';
                            break;
                        case 1:
                            $section_class = 'second_section';
                            break;
                        case 2:
                            $section_class = 'third_section';
                            break;
                    }
                    ?>
                    <div class="all_3_sections <?php echo $section_class; ?> col-md-4">
                        <div class="inner_section">
                            <h3><?php echo $title;?></h3>
                            <hr>
                            <div class="all_3_sections_image_block" style="background-image:url(<?php echo $image;?>)"></div>
                            <?php
                            $target = '';
                            if(!empty($cta_text) && !empty($cta_link)){
                                if($open_new_tab == true){
                                    $target = "target='_blank'";
                                }
                            ?>
                            <a <?php echo $target;?> href="<?php echo $cta_link;?>"><?php echo $cta_text;?></a>
                            <?php }?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <?php endif; ?>
            </div>
            
            <?php include 'inc/mobile-custom-sidebar-bottom.php'; ?>
            <?php
            $post_blog_or_category_filter = $wiser_option['post_blog_or_category_filter'];
            if($post_blog_or_category_filter[0] == 'Post Blog'){
            ?>
            <div class="home_page_post_blog">
                
                <?php include 'inc/home_page_blog.php'; ?>
            </div>
            <?php 
            }elseif($post_blog_or_category_filter[0] == 'Category Filter'){
                ?>
            <div id="category_filter">
                <?php include 'inc/category_filter.php'; ?>
                <!-- <?php include 'alm_templates/default.php'; ?> -->
            </div>
            <?php
            }
            ?>
        </div>
        <div id="sidebar">
            <?php include 'inc/custom-sidebar.php'; ?>
        </div>
    </div>
</div>
<?php
get_footer();
?>