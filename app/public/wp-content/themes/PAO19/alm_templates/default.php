<?php

// foreach ($posts_array as $posts_array_value) {
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
                // }
                ?>