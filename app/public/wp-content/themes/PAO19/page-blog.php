<?php
/* Template Name: Blog Template */
get_header();
?>
<?php
global $post;
global $wiser_option;
?>

<div id="content" class="home_content container page_content">
    <div class="left_and_sidebar">
        <div id="home_content_left" class="content_left_part">
            
            
            
            <div id="category_filter">
                
                
                <div class="row">
<div id="filter-toggle-block">
    <div>
        
<h2><?php echo $post->post_title;?></h2>
        <div class="line"></div>
        <div class="isotope i_posts_container filtr-container">
                <?php
                $p_count = 1;
                $posts_per_page = 30;
                $paged = 1;
//                $categories = $sport_parent_category;
                $categories = get_categories();
                $term_ids = array();
                    foreach ($categories as $categories_value) {
//                        echo '<pre>';
//                        print_r($categories_value);
//                        echo '</pre>';
                        $term_id = $categories_value->term_id;
                        array_push($term_ids, $term_id);
                    }
                $posts_array = array();
                $posts_counter = 0;
                $posts_ids_array_list = array();
                $category_ids_array_list = array();
        
                    $args = array(
                        'post_type' => 'post',
                        'posts_per_page' => $posts_per_page,
                        'suppress_filters' => false,
                        'order' => 'DESC',
                        'orderby' => 'date',
                        'paged' => $paged,
                    );
                    array_push($category_ids_array_list, $term_id_value);
                    $posts_array = get_posts($args);
                foreach ($posts_array as $posts_array_value) {
                    $post_id = $posts_array_value->ID;
//        $post_format = get_post_format($post_id);
                    $post_author_id = $posts_array_value->post_author;
                    $post_title = $posts_array_value->post_title;
                    $post_content = $posts_array_value->post_content;
                    $post_excerpt = $posts_array_value->post_excerpt;
                    $post_author = $posts_array_value->post_author;
                    $add_external_link = get_field( "add_external_link", $post_id );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_permalink($post_id);
                                }
                    $author = get_the_author_meta('display_name', $post_author);
                    if (has_post_thumbnail($post_id)) {
                        $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'medium');
                        $prevthumb = $prevthumb[0];
                    } else {
                        $prevthumb = get_template_directory_uri() . '/resources/images/test_photo.png';
                    }
                    $post_categories = wp_get_post_categories($post_id);
                    $sport_category_for_class = '';
                    foreach ($post_categories as $post_category_item) {
                        $post_category = get_category($post_category_item);
                        $sport_category_for_class .= ' ' . strtolower(str_replace(' ', '-', $post_category->name));
                    }
                    $post_category = get_category($post_categories[0]);
                    $sport_category = $post_category->name;
                    if($sport_category =='sport'){
                    $post_category = get_category($post_categories[1]);
                    $sport_category = $post_category->name;
                        
                    }
                    if (empty($sport_category_for_class))
                        $sport_category_for_class = strtolower(str_replace(' ', '-', $sport_category));
                    $term_id = $post_category->term_id;
                    $term_link = get_term_link($term_id, 'category');
                    $term_taxonomy_id = 'category_' . $term_id;
                    $p_class = '';
                    if ($p_count > 10)
                        $p_class = ' i_hidden';
                    ?>
                    <div id="i_post_<?php echo $post_id; ?>" data-category="<?php echo $term_id; ?>" data-sort="value" data-timestamp="<?php echo strtotime(get_the_date('Y/m/d h:i:s', $post_id)); ?>" class="isotop_elements grid-item filtr-item i_show home_page_posts start <?php echo $sport_category_for_class; ?> <?php echo $p_class; ?>">
                        <div class="grid-item-inner">
                            <?php
                            if (has_post_thumbnail($post_id)) {
                                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'medium');
                                $featured_image_url = $prevthumb[0];
                            } else {
                                $featured_image_url = get_template_directory_uri() . '/resources/images/test_photo.png';
                            }
                            ?>
                            <div class="image col-md-4">
                                <a class="a_post_img_class a_post_image" <?php echo $target;?> href="<?php echo $permalink ?>" style="background-image:url(<?php echo $featured_image_url;?>)"></a>
                            </div>
                            <div class="text col-md-8">
                                <h3> <a class="" <?php echo $target;?> href="<?php echo $permalink ?>"><?php echo $posts_array_value->post_title ?></a></h3>
                                                <span class="category_post_author_and_date"><?php echo $author . ' <i class="fa fa-circle" aria-hidden="true"></i> ' . date("M j,Y", strtotime($post_date)); ?> </span>
                                                <?php if (!empty($post_excerpt)) { ?>   
                                                    <p><?php echo $post_excerpt; ?></p>
                                                <?php } else {
                                                    ?>
                                                    <p><?php echo wp_trim_words($post_content, 15, '...'); ?>
                                                    </p>
                                                <?php } ?>
                                                <a class="a_read_more_button" <?php echo $target;?> href="<?php echo $permalink ?>">READ MORE</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
        </div>
        <div class=" outline">
    <?php
    if( $wiser_option['posts_lazy_load'] ){
        echo '<div class="load_more_posts i_mobile_hide" data-post_type="post" data-taxonomy="category" data-term_id="'.implode(',', $term_ids ).'"
               data-all_term_ids="'.implode(',', $term_ids ).'" data-posts_per_page="'.$posts_per_page.'" data-style="2" data-paged="2" >
        <div class="fountainG_txt">'.__('Load More', 'sport_theme').'</div>
        <div class="fountainG_div">
            <div id="fountainG_1" class="fountainG"></div>
            <div id="fountainG_2" class="fountainG"></div>
            <div id="fountainG_3" class="fountainG"></div>
            <div id="fountainG_4" class="fountainG"></div>
            <div id="fountainG_5" class="fountainG"></div>
            <div id="fountainG_6" class="fountainG"></div>
            <div id="fountainG_7" class="fountainG"></div>
            <div id="fountainG_8" class="fountainG"></div>
        </div>
        
    </div>';
        echo '<a href="#load_more_posts_btn" class="btn large hand outline load_more_posts_btn i_mobile_show" id="load_more_posts_btn" data-post_type="post" data-taxonomy="category" data-term_id="'.implode(',', $term_ids ).'"
               data-all_term_ids="'.implode(',', $term_ids ).'" data-posts_per_page="'.$posts_per_page.'" data-style="2" data-paged="2">'.__('Load More', 'sport_theme').'</a> ';
    } else {
        echo '<a href="#load_more_posts_btn" class="btn large hand outline load_more_posts_btn" id="load_more_posts_btn" data-post_type="post" data-taxonomy="category" data-term_id="'.implode(',', $term_ids ).'"
               data-all_term_ids="'.implode(',', $term_ids ).'" data-posts_per_page="'.$posts_per_page.'" data-style="2" data-paged="2">'.__('Load More', 'sport_theme').'</a> ';
    }
    ?>
</div>
    </div>
</div>
</div>
                
                
                
            </div>

            </div>
        <div id="sidebar">
            <?php include 'inc/custom-sidebar.php'; ?>
        </div>
    </div>
</div>
<?php
get_footer();
?>
