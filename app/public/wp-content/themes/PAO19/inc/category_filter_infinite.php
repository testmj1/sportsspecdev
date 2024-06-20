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
//                 $p_count = 1;
//                 $posts_per_page = 5;
//                 $paged = 1;
//                 $categories = $sport_parent_category;
//                 $term_ids = array();
//                 $out_sport_term = '';
//                 if (!$out_sport_term) {
// //                    foreach ($categories as $categories_value) {
//                     foreach ($cat_checkbox as $key=>$cat_checkbox_value) {
//                 $categories_value = get_term_by('id', $cat_checkbox_value['cat_id'], 'category');
//                         $term_id = $categories_value->term_id;
//                         array_push($term_ids, $term_id);
//                     }
//                 } else {
//                     $term_ids = array(
//                         $current_term_id
//                     );
//                 }
//                 $posts_array = array();
//                 $posts_counter = 0;
//                 $posts_ids_array_list = array();
//                 $category_ids_array_list = array();
//                 foreach ($term_ids as $term_id_key => $term_id_value) {
        
//                     $args = array(
//                         'post_type' => 'post',
//                         'numberposts' => $posts_per_page,
//                         'post__not_in' => $posts_ids_array_list,
//                         'suppress_filters' => false,
//                         'post_status' => 'publish',
//                         'paged' => $paged
// //                        'tax_query' => array(
// //                            array(
// //                                'taxonomy' => $taxonomy,
// //                                'field' => 'term_id',
// //                                'terms' => $term_id_value,
// //                                'operator' => 'NOT IN'
// //                            )
// //                        )
//                     );
//                     array_push($category_ids_array_list, $term_id_value);
//                     $posts_array1_key  = '';
//                     $posts_array1 = wp_get_recent_posts($args);
//                     foreach ($posts_array1 as $$posts_array1_key => $posts_array1_value) {
//                         if (!in_array($posts_array1_value['ID'], $posts_ids_array_list)) {
//                             $posts_array[$posts_array1_value['ID']] = $posts_array1_value;
//                             array_push($posts_ids_array_list, $posts_array1_value['ID']);
//                             $posts_counter++;
//                         }
//                     }
//                 }
                // include 'alm_templates/default.php';
                
                echo do_shortcode('[ajax_load_more post_type="post" category="'.$sport_category.'" loading_style="infinite classic"]');
                ?>
                
        </div>
        
    </div>
</div>
</div>
