<div class="row">
<div id="filter-toggle-block">
    <div>
        <ul class="filter-toggle" id="i_filter_toggle_ul">
            <li data-filter="all">
                <a class="controlsStyle active i_filters" data-term_id="all" data-paged="1" data-filter=".start" href="#i_filter_toggle_ul">
                    <?php _e('All', 'sport_theme'); ?>
                </a>
            </li>
            <?php
            global $wiser_option;
            $idObj = get_category_by_slug( 'sport' );
            $sport_parent_category_id = $idObj->term_id;
            $taxonomy = 'category';
            global $sport_parent_category;
            $sport_parent_category = get_categories(
                    array('parent' => $sport_parent_category_id,
                        "hide_empty" => 0)
            );

            $categories = $sport_parent_category;
//            foreach ($categories as $categories_value) {
            $cat_checkbox = $wiser_option['cat_checkbox'];
//            $categories = $sport_parent_category;
            foreach ($cat_checkbox as $key=>$cat_checkbox_value) {
                $categories_value = get_term_by('id', $cat_checkbox_value['cat_id'], 'category');
                $term_link = get_category_link($categories_value->term_id);
                $sport_category = $categories_value->name;
                $term_id = $categories_value->term_id;
                $sport_category_for_data = strtolower(str_replace(' ', '-', $sport_category));
                ?>

                <li data-filter="<?php echo $term_id; ?>">
                    <a class="controlsStyle a_category i_filters i_filter_<?php echo $term_id; ?>" href="#i_filter_toggle_ul" data-term_id="<?php echo $term_id; ?>" data-paged="1" data-filter=".<?php echo $sport_category_for_data; ?>">
                        <?php echo $sport_category; ?>
                    </a>
                </li>
                <?php
            }
            ?>
        </ul>

        <hr class="medium-up divide red">
        <div class="isotope i_posts_container filtr-container">
                <?php
                $p_count = 1;
                $posts_per_page = 5;
                $paged = 1;
                $categories = $sport_parent_category;
                $term_ids = array();
                $out_sport_term = '';
                if (!$out_sport_term) {
//                    foreach ($categories as $categories_value) {
                    foreach ($cat_checkbox as $key=>$cat_checkbox_value) {
                $categories_value = get_term_by('id', $cat_checkbox_value['cat_id'], 'category');
                        $term_id = $categories_value->term_id;
                        array_push($term_ids, $term_id);
                    }
                } else {
                    $term_ids = array(
                        $current_term_id
                    );
                }
                $posts_array = array();
                $posts_counter = 0;
                $posts_ids_array_list = array();
                $category_ids_array_list = array();
                foreach ($term_ids as $term_id_key => $term_id_value) {
        
                    $args = array(
                        'post_type' => 'post',
                        'numberposts' => $posts_per_page,
                        'post__not_in' => $posts_ids_array_list,
                        'suppress_filters' => false,
                        'post_status' => 'publish',
                        'paged' => $paged
//                        'tax_query' => array(
//                            array(
//                                'taxonomy' => $taxonomy,
//                                'field' => 'term_id',
//                                'terms' => $term_id_value,
//                                'operator' => 'NOT IN'
//                            )
//                        )
                    );
                    array_push($category_ids_array_list, $term_id_value);
                    $posts_array1_key  = '';
                    $posts_array1 = wp_get_recent_posts($args);
                    foreach ($posts_array1 as $$posts_array1_key => $posts_array1_value) {
                        if (!in_array($posts_array1_value['ID'], $posts_ids_array_list)) {
                            $posts_array[$posts_array1_value['ID']] = $posts_array1_value;
                            array_push($posts_ids_array_list, $posts_array1_value['ID']);
                            $posts_counter++;
                        }
                    }
                }
                foreach ($posts_array as $posts_array_value) {
//                    echo '<pre>';
//                    print_r($posts_array_value);
//                    echo '</pre>';
                    $post_id = $posts_array_value['ID'];
                    $post_author_id = $posts_array_value['post_author'];
                    $post_title = $posts_array_value['post_title'];
                    $add_external_link = get_field( "add_external_link", $post_id );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_permalink($post_id);
                                }
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
                    <div id="i_post_<?php echo $post_id; ?>" data-category="<?php echo $term_id; ?>" data-sort="value" data-timestamp="<?php echo strtotime(get_the_date('Y/m/d h:i:s', $post_id)); ?>" class="alm-item col-md-4 isotop_elements grid-item filtr-item i_show home_page_posts start <?php echo $sport_category_for_class; ?> <?php echo $p_class; ?>">
                        <div class="grid-item-inner">
                            <?php
                            if (has_post_thumbnail($post_id)) {
                                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'medium');
                                $featured_image_url = $prevthumb[0];
                            } else {
                                $featured_image_url = get_template_directory_uri() . '/resources/images/test_photo.png';
                            }
                            ?>
                            <div class="image">
                                <a class="a_post_img_class a_post_image" <?php echo $target;?> href="<?php echo $permalink; ?>" style="background-image:url(<?php echo $featured_image_url;?>)"></a>
                                <a href="<?php echo $term_link;?>" class="tag hand <?php echo strtolower(str_replace(' ', '-', $sport_category)); ?>"><?php echo $sport_category; ?></a>
                            </div>
                            <div class="text">
                                <h3>
                                    <a <?php echo $target;?> href="<?php echo $permalink; ?>">
                                        <?php echo $post_title; ?>
                                    </a>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
        </div>
        <div class=" outline">
            <?php
            $posts_lazy_load = '';
            if(isset($wiser_option['posts_lazy_load'])){
                
            $posts_lazy_load = $wiser_option['posts_lazy_load'];
            }
            if ($posts_lazy_load) {
                echo '<div class="load_more_posts i_mobile_hide" data-post_type="post" data-taxonomy="' . $taxonomy . '" data-term_id="' . implode(',', $term_ids) . '"
               data-all_term_ids="' . implode(',', $term_ids) . '" data-posts_per_page="' . $posts_per_page . '" data-style="1" data-paged="2" >
        <div class="fountainG_txt">' . __('Load More', 'sport_theme') . '</div>
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
                echo '<a href="#load_more_posts_btn" class="btn large hand outline load_more_posts_btn i_mobile_show" id="load_more_posts_btn" data-post_type="post" data-taxonomy="' . $taxonomy . '" data-term_id="' . implode(',', $term_ids) . '"
               data-all_term_ids="' . implode(',', $term_ids) . '" data-posts_per_page="' . $posts_per_page . '" data-style="1" data-paged="2">' . __('Load More', 'sport_theme') . '</a> ';
            } else {
                echo '<a href="#load_more_posts_btn" class="btn large hand outline load_more_posts_btn" id="load_more_posts_btn" data-post_type="post" data-taxonomy="' . $taxonomy . '" data-term_id="' . implode(',', $term_ids) . '"
               data-all_term_ids="' . implode(',', $term_ids) . '" data-posts_per_page="' . $posts_per_page . '" data-style="1" data-paged="2">' . __('Load More', 'sport_theme') . '</a> ';
            }
            ?>
        </div>
    </div>
</div>
</div>
