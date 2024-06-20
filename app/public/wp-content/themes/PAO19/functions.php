<?php
global $sport_parent_category_id;
$idObj = get_category_by_slug('sport');
$sport_parent_category_id = $idObj->term_id;
//$sport_parent_category_id = '7';
global $theme_url;
$theme_url = get_template_directory_uri() . '/';

$template_directory = get_template_directory();

add_theme_support('post-formats', array('video'));

/*
 * The print_r function with <pre> tags for clear view
 */
if (!function_exists('i_print')) {

    function i_print($array) {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }

}

//require($template_directory . '/inc/updater/theme-updater.php');
require($template_directory . '/inc/theme-options.php');
require($template_directory . '/inc/shortcodes.php');


add_theme_support('post-thumbnails');

function load_files() {
    $t_dir = get_template_directory_uri();

//    wp_deregister_script('jquery');
//    wp_register_script('jquery', $t_dir . '/resources/js/jquery-3.3.1.min.js');
//    wp_enqueue_script('jquery');
    wp_register_style('bootstrap_style', $t_dir . '/resources/css/bootstrap/css/bootstrap.min.css', array(), false, 'all');
    wp_enqueue_style('bootstrap_style');
    wp_register_script('bootstrap_js', $t_dir . '/resources/css/bootstrap/js/bootstrap.min.js');
    wp_enqueue_script('bootstrap_js');
    wp_register_style('font_awesome_style', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css');
    wp_enqueue_style('font_awesome_style');
    wp_register_style('owl_style', $t_dir . '/resources/OwlCarousel/dist/assets/owl.carousel.min.css');
    wp_enqueue_style('owl_style');
    wp_register_style('owl_theme_style', $t_dir . '/resources/OwlCarousel/dist/assets/owl.theme.default.min.css');
    wp_enqueue_style('owl_theme_style');
    wp_register_script('owl_script', $t_dir . '/resources/OwlCarousel/dist/owl.carousel.min.js');
    wp_enqueue_script('owl_script');
    wp_register_style('fancy_style', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.6/jquery.fancybox.min.css');
    wp_enqueue_style('fancy_style');
    wp_register_script('fancy_script', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.6/jquery.fancybox.min.js');
    wp_enqueue_script('fancy_script');
    wp_register_script('picture_fill_script', $t_dir . '/resources/js/js/picturefill.min.js');
    wp_enqueue_script('picture_fill_script');
    wp_register_script('jquery-ui', "https://code.jquery.com/ui/1.12.1/jquery-ui.js");
    wp_enqueue_script('jquery-ui');
//    wp_register_script('modernizr_script',get_template_directory_uri() . '/resources/js/js/modernizr-latest.min.js');
//    wp_enqueue_script('modernizr_script');
//    wp_register_script('isotope_script',get_template_directory_uri() . '/resources/js/js/isotope.min.js');
//    wp_enqueue_script('isotope_script');
    wp_register_script('iframetracker_script', $t_dir . '/resources/js/js/jquery.iframetracker.js');
    wp_enqueue_script('iframetracker_script');
    wp_register_style('style', $t_dir . '/style.css', array(), false, 'all');
    wp_enqueue_style('style');
    wp_enqueue_script ( 'jquery-sticky-script', $t_dir . '/resources/js/sticky-sidebar/jquery.sticky-sidebar.min.js', array('jquery') );
    wp_register_script('script', $t_dir . '/script.js', '', 1, true);
    wp_enqueue_script('script');
}

add_action('wp_enqueue_scripts', 'load_files');


add_action('admin_enqueue_scripts', 'custom_admin_scripts');

function custom_admin_scripts() {
//     wp_register_script('jquery_script',get_template_directory_uri() . '/resources/js/jquery-3.3.1.min.js');
//    wp_enqueue_script('jquery_script');

    wp_register_script('jquery-ui', "https://code.jquery.com/ui/1.12.1/jquery-ui.js");

    if (is_admin()) {
        wp_enqueue_script('jquery-ui');
    }
    wp_register_script('admin-script', get_template_directory_uri() . '/resources/js/admin.js', '', 1, true);
    wp_enqueue_script('admin-script');
    wp_register_style('font_awesome_style', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css');
    wp_enqueue_style('font_awesome_style');
}

add_action('init', 'create_post_cat', 0);

function create_post_cat() {


    register_taxonomy(
            'video_gallery', 'post', array(
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_in_rest' => true,
        'show_tagcloud' => true,
        'label' => 'Video Gallery',
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'video_gallery'
        )
            )
    );
}

function wiser_widgets_init() {

    register_sidebar(array(
        'name' => __('Primary Sidebar', 'wisersites'),
        'id' => 'sidebar-primary',
        'description' => __('Main sidebar that appears on the left.', 'wisersites'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
    register_sidebar(array(
        'name' => __('Sticky Sidebar', 'wisersites'),
        'id' => 'sidebar-sticky',
        'description' => __('', 'wisersites'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
    register_sidebar(array(
        'name' => __('Mobile Top Sidebar', 'wisersites'),
        'id' => 'mobile_top_sidebar',
        'description' => __('Main sidebar that appears on the left.', 'wisersites'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
    register_sidebar(array(
        'name' => __('Mobile Bottom Sidebar', 'wisersites'),
        'id' => 'mobile_bottom_sidebar',
        'description' => __('Main sidebar that appears on the left.', 'wisersites'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
}

add_action('widgets_init', 'wiser_widgets_init');

//Register nav menus
add_action('after_setup_theme', function() {
    register_nav_menus(array(
        'header_top_menu' => 'Header Top Menu',
        'header_bootom_menu' => 'Header Bottom Menu',
        'header_category_menu' => 'Header Category Menu',
        'foot_menu' => 'Footer Navigation Menu',
        'foot_menu_2' => 'Footer Navigation Menu 2'
    ));
});

function load_more_posts() {
    if (!(isset($_REQUEST['action']) && 'i_load_more_posts' == $_POST['action']))
        return;

    $return = array(
        'status' => '0',
        'html' => '',
        'finished' => '0'
    );
    $html = '';
    $html_array = array();

    $post_type = $_POST['post_type'];
    $posts_per_page = $_POST['posts_per_page'];

    $paged = $_POST['paged'];
//    if($paged == 0){
//        $paged=1;
//    }
    $taxonomy = $_POST['taxonomy'];
    $term_id = $_POST['term_id'];
    $style = $_POST['style'];
    if (empty($style))
        $style = '1';

    $offset = $_POST['offset'];

    $max_post_counter = 5;
    $args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'numberposts' => $posts_per_page,
        'offset'=>($paged-1)*$posts_per_page,
        'post_status' => 'publish',
//        'paged' => $paged,
        'suppress_filters' => false
            /* 'tax_query' => array(
              array(
              'taxonomy' => $taxonomy,
              'field'    => 'term_id',
              'terms'    => $term_id,
              ),
              ), */
    );
    $a_is_choosed_all = false;
    if ($term_id) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => explode(',', $term_id)
            ),
        );
        $a_choosed_cat = explode(',', $term_id);
        if (count($a_choosed_cat) > 1) {
            $a_is_choosed_all = true;
        }
    }

//    global $post;
    $posts = wp_get_recent_posts($args);
    $post_counter = 0;
    if (count($posts)) {
        foreach ($posts as $post) {
            $html = '';

            $post_counter++;
            $post_id = $post['ID'];
            $post_author_id = $post['post_author'];
            $post_title = $post['post_title'];
            $post_content = $post['post_content'];
            $post_excerpt = $post['post_excerpt'];
            $post_author = $post['post_author'];
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
                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
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
            if (is_numeric($term_id)) {
//                foreach ($post_categories as $post_item){
//                    if ($term_id == $post_item->term_id){
//                        $sport_category = $post_item->name;
//                    }
//                }
//                echo "<pre>";
//                print_r($term_id);
//                echo "</pre>";
                $post_category = get_category($term_id);
                $sport_category = $post_category->name;
            }

            if (empty($sport_category_for_class))
                $sport_category_for_class = strtolower(str_replace(' ', '-', $sport_category));
            $new_term_id = $post_category->term_id;
            $term_link = get_term_link($new_term_id, 'category');
            $term_taxonomy_id = 'category_' . $new_term_id;
            //$html.= "CATS: ".$post_categories[0];
            $a_hide_for_all_filter = '';
            if (!$a_is_choosed_all) {
                $a_hide_for_all_filter = ' hide_for_all ';
            }
            $html .= '<div id="i_post_' . $post_id . '" data-timestamp="' . strtotime(get_the_date('Y/m/d h:i:s', $post_id)) . '" class="isotop_elements grid-item i_show filtr-item home_page_posts start i_visibility_appear ' . $sport_category_for_class . $a_hide_for_all_filter . '" data-category="1" data-sort="value">'; //
            $html .= '<div class="grid-item-inner">';

            $html .= '<a class="overlay" '.$target.' href="' . $permalink . '"></a>';
            if (has_post_thumbnail($post_id)) {
                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'medium');
                $featured_image_url = $prevthumb[0];
            } else {
                $featured_image_url = get_template_directory_uri() . '/resources/images/test_photo.png';
            }
            if ($style == '2') {
                $html .= '<div class="image col-md-4"><a class="a_post_img_class a_post_image" '.$target.' href="' . $permalink . '" style="background-image:url(' . $featured_image_url . ')">';
//                $html.= '<img src="'.$featured_image_url.'"> ';
                $html .= '</a>';

                $html .= '</div>';
                $html .= '<div class="text col-md-8"><h3><a '.$target.' href="' . $permalink . '">' . $post_title . '</a></h3>';

//                $html .= '<span class="category_post_author_and_date">';
//                $html .= $author . ' <i class="fa fa-circle" aria-hidden="true"></i> ' . date("M j,Y", strtotime($post_date));
//                $html .= '</span>';
                if (!empty($post_excerpt)) {
                    $html .= '<p>' . $post_excerpt . '</p>';
                } else {
                    $html .= '<p>' . wp_trim_words($post_content, 15, "...") . '</p>';
                }
                $html .= '<a class="a_read_more_button" '.$target.' href="' . $permalink . '">READ MORE</a>';
                $html .= '</div>';
            } else {
                $html .= '<div class="image">';
//                $html.= '<a href="'.get_permalink($post_id).'"><img src="'.$featured_image_url.'"> ';
//                $html.= '</a>';
                $html .= '<a class="a_post_img_class a_post_image" '.$target.' href="' . $permalink . '" style="background-image:url(' . $featured_image_url . ')"></a>';
                $html .= '<a href="' . $term_link . '" class="tag hand ' . strtolower(str_replace(' ', '-', $sport_category)) . '">' . $sport_category . '</a>';
                $html .= '</div>';
                $html .= '<div class="text"><h3><a '.$target.' href="' . $permalink . '">' . $post_title . '</a></h3>';


                $html .= '</div>';
            }

            $html .= '</div>';
            $html .= '</div>';

            $html_array['post_' . $post_id] = $html;
            ?>
            <?php
        }
        $finished = 0;
    } else {
        $finished = 1;
    }

























    $return = array(
        'status' => '0',
        'html' => '',
        'finished' => '0'
    );
    $html_second = '';
    $html_second_array = array();

    $post_type = $_POST['post_type'];
    $posts_per_page = $_POST['posts_per_page'];

    $paged = $_POST['paged'];
    $new_paged = $paged + 1;
    $taxonomy = $_POST['taxonomy'];
    $term_id = $_POST['term_id'];
    $style = $_POST['style'];
    if (empty($style))
        $style = '1';

    $offset = $_POST['offset'];

    $max_post_counter = 5;
    $args = array(
        'post_type' => $post_type,
        'numberposts' => $posts_per_page,
        'paged' => $new_paged,
         'offset'=>($new_paged-1)*$posts_per_page,
        'post_status' => 'publish',
        'suppress_filters' => false
            /* 'tax_query' => array(
              array(
              'taxonomy' => $taxonomy,
              'field'    => 'term_id',
              'terms'    => $term_id,
              ),
              ), */
    );

    $a_is_choosed_all = false;
    if ($term_id) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => explode(',', $term_id)
            ),
        );
        $a_choosed_cat = explode(',', $term_id);
        if (count($a_choosed_cat) > 1) {
            $a_is_choosed_all = true;
        }
    }

//    global $post;
    $posts = wp_get_recent_posts($args);
    $post_counter = 0;
    if (count($posts)) {
        foreach ($posts as $post) {

            $html_second = '';

            $post_counter++;
            $post_id = $post['ID'];
            $post_author_id = $post['post_author'];
            $post_title = $post['post_title'];
            $post_content = $post['post_content'];
            $post_excerpt = $post['post_excerpt'];
            $post_author = $post['post_author'];
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
                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
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
            if (is_numeric($term_id)) {
//                foreach ($post_categories as $post_item){
//                    if ($term_id == $post_item->term_id){
//                        $sport_category = $post_item->name;
//                    }
//                }
//                echo "<pre>";
//                print_r($term_id);
//                echo "</pre>";
                $post_category = get_category($term_id);
                $sport_category = $post_category->name;
            }

            if (empty($sport_category_for_class))
                $sport_category_for_class = strtolower(str_replace(' ', '-', $sport_category));
            $new_term_id = $post_category->term_id;
            $term_link = get_term_link($new_term_id, 'category');
            $term_taxonomy_id = 'category_' . $new_term_id;
            //$html.= "CATS: ".$post_categories[0];
            $a_hide_for_all_filter = '';
            if (!$a_is_choosed_all) {
                $a_hide_for_all_filter = ' hide_for_all ';
            }
            $html_second .= '<div id="i_post_' . $post_id . '" data-timestamp="' . strtotime(get_the_date('Y/m/d h:i:s', $post_id)) . '" class="isotop_elements grid-item i_show filtr-item home_page_posts start i_visibility_appear ' . $sport_category_for_class . $a_hide_for_all_filter . '" data-category="1" data-sort="value">'; //
            $html_second .= '<div class="grid-item-inner">';

            $html_second .= '<a class="overlay" '.$target.' href="' . $permalink . '"></a>';
            if (has_post_thumbnail($post_id)) {
                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'medium');
                $featured_image_url = $prevthumb[0];
            } else {
                $featured_image_url = get_template_directory_uri() . '/resources/images/test_photo.png';
            }
            if ($style == '2') {
                $html_second .= '<div class="image col-md-4"><a class="a_post_img_class a_post_image" '.$target.' href="' . $permalink . '" style="background-image:url(' . $featured_image_url . ')">';
//                $html_second.= '<img src="'.$featured_image_url.'"> ';
                $html_second .= '</a>';

                $html_second .= '</div>';
                $html_second .= '<div class="text col-md-8"><h3><a '.$target.' href="' . $permalink . '">' . $post_title . '</a></h3>';

//                $html_second .= '<span class="category_post_author_and_date">';
//                $html_second .= $author . ' <i class="fa fa-circle" aria-hidden="true"></i> ' . date("M j,Y", strtotime($post_date));
//                $html_second .= '</span>';
                if (!empty($post_excerpt)) {
                    $html_second .= '<p>' . $post_excerpt . '</p>';
                } else {
                    $html_second .= '<p>' . wp_trim_words($post_content, 15, "...") . '</p>';
                }
                $html_second .= '<a class="a_read_more_button" '.$target.' href="' . $permalink . '">READ MORE</a>';
                $html_second .= '</div>';
            } else {
                $html_second .= '<div class="image">';
//                $html.= '<a href="'.get_permalink($post_id).'"><img src="'.$featured_image_url.'"> ';
//                $html.= '</a>';
                $html_second .= '<a class="a_post_img_class a_post_image" '.$target.' href="' . $permalink . '" style="background-image:url(' . $featured_image_url . ')"></a>';
                $html_second .= '<a href="' . $term_link . '" class="tag hand ' . strtolower(str_replace(' ', '-', $sport_category)) . '">' . $sport_category . '</a>';
                $html_second .= '</div>';
                $html_second .= '<div class="text"><h3><a '.$target.' href="' . $permalink . '">' . $post_title . '</a></h3>';


                $html_second .= '</div>';
            }

            $html_second .= '</div>';
            $html_second .= '</div>';

            $html_second_array['post_' . $post_id] = $html_second;
            ?>
            <?php
        }
        $finished = 0;
    } else {
        $finished = 1;
    }


















    
    $return = array(
        'status' => 1,
        //'html' => $html,
        'html_array' => $html_array,
        'html_second_array' => $html_second_array,
        'finished' => $finished
    );
    echo json_encode($return);
    exit;
}

add_action('wp_ajax_i_load_more_posts', 'load_more_posts');
add_action('wp_ajax_nopriv_i_load_more_posts', 'load_more_posts');

/** Ad's click Statistics widget * */
function ads_click_calculation_widget() {

    wp_add_dashboard_widget(
            'ads_click_calculation', // Widget slug.
            'Ad Clicks', // Title.
            'ads_click_calculation_func' // Display function.
    );


    global $wp_meta_boxes;

    global $wpdb;
    $table_name = $wpdb->prefix . 'ads_count_calculation';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        //table not in database. Create new table
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  ad_id int(11) NOT NULL,
                  date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE KEY id (id)
             ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    // Get the regular dashboard widgets array 
    // (which has our new widget already but at the end)

    $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

    // Backup and delete our new dashboard widget from the end of the array

    $example_widget_backup = array('ads_click_calculation' => $normal_dashboard['ads_click_calculation']);
    unset($normal_dashboard['ads_click_calculation']);

    // Merge the two arrays together so our widget is at the beginning

    $sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);

    // Save the sorted array back into the original metaboxes 

    $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}

add_action('wp_dashboard_setup', 'ads_click_calculation_widget');

function ads_click_calculation_func() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ads_count_calculation';

    $add_1_result_for_all_time = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=1", ARRAY_A);
    $add_2_result_for_all_time = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=2", ARRAY_A);


    $add_1_result_for_current_day = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=1 and `date` >= DATE_SUB(CURDATE(), INTERVAL 24 HOUR)", ARRAY_A);
    $add_1_result_for_week = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=1 and `date` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)", ARRAY_A);
    $add_1_result_for_month = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=1 and `date` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", ARRAY_A);
    $add_1_result_for_year = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=1 and `date` >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)", ARRAY_A);

    $add_2_result_for_current_day = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=2 and `date` >= DATE_SUB(CURDATE(), INTERVAL 24 HOUR)", ARRAY_A);
    $add_2_result_for_week = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=2 and `date` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)", ARRAY_A);
    $add_2_result_for_month = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=2 and `date` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", ARRAY_A);
    $add_2_result_for_year = $wpdb->get_results("SELECT id FROM " . $table_name . " WHERE ad_id=2 and `date` >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)", ARRAY_A);
    ?>

    <style>
        #ads_click_calculation_table{
            width:100%; 
            border-collapse: collapse;
        }
        #ads_click_calculation_table th,
        #ads_click_calculation_table td{
            border: 1px solid #000;
            text-align: right;
        }
        #ads_click_calculation_table td:first-child{
            text-align: left;
        }
    </style>
    <table id="ads_click_calculation_table" >
        <tr>
            <th></th>
            <th>Day</th>
            <th>Week</th>
            <th>Month</th>
            <th>Year</th>                
            <th>All Time</th>                
        </tr>
        <tr>
            <td>Banner Ad</td>
            <td><?php echo count($add_1_result_for_current_day); ?></td>
            <td><?php echo count($add_1_result_for_week); ?></td>
            <td><?php echo count($add_1_result_for_month); ?></td>
            <td><?php echo count($add_1_result_for_year); ?></td>
            <td><?php echo count($add_1_result_for_all_time); ?></td>
        </tr>
        <tr>
            <td>Sidebar Ad</td>
            <td><?php echo count($add_2_result_for_current_day); ?></td>
            <td><?php echo count($add_2_result_for_week); ?></td>
            <td><?php echo count($add_2_result_for_month); ?></td>
            <td><?php echo count($add_2_result_for_year); ?></td>
            <td><?php echo count($add_2_result_for_all_time); ?></td>
        </tr>
    </table>    
    <?php
}

add_action('wp_ajax_ad_click_count', 'ad_click_count_func');
add_action('wp_ajax_nopriv_ad_click_count', 'ad_click_count_func');

function ad_click_count_func() {
//    echo $_POST['ad_id'];
    $response = false;
    if (!empty($_POST['ad_id']) && !is_user_logged_in()) {
        global $wpdb;
        $tablename = $wpdb->prefix . 'ads_count_calculation';
        $data = array('ad_id' => $_POST['ad_id']);
        $wpdb->insert($tablename, $data);
        if (!empty($wpdb->insert_id)) {
            $response = true;
        }
    }
    echo $response;
    exit;
}

add_filter('request', 'rudr_change_term_request', 1, 1);

function rudr_change_term_request($query) {

    $tax_name = 'category'; // specify you taxonomy name here, it can be also 'category' or 'post_tag'
    // Request for child terms differs, we should make an additional check
    if ( isset( $query['attachment'] )) :
        $include_children = true;
        $name = $query['attachment'];
    else:
        $include_children = false;
        $name = isset( $query['name'] )?$query['name']:'';
    endif;


    $term = get_term_by('slug', $name, $tax_name); // get the current term to make sure it exists

    if (isset($name) && $term && !is_wp_error($term)): // check it here

        if ($include_children) {
            unset($query['attachment']);
            $parent = $term->parent;
            while ($parent) {
                $parent_term = get_term($parent, $tax_name);
                $name = $parent_term->slug . '/' . $name;
                $parent = $parent_term->parent;
            }
        } else {
            unset($query['name']);
        }

        switch ($tax_name):
            case 'category': {
                    $query['category_name'] = $name; // for categories
                    break;
                }
            case 'post_tag': {
                    $query['tag'] = $name; // for post tags
                    break;
                }
            default: {
                    $query[$tax_name] = $name; // for another taxonomies
                    break;
                }
        endswitch;

    endif;

    return $query;
}

add_filter('term_link', 'rudr_term_permalink', 10, 3);

function rudr_term_permalink($url, $term, $taxonomy) {

    $taxonomy_name = 'category'; // your taxonomy name here
    $taxonomy_slug = 'category'; // the taxonomy slug can be different with the taxonomy name (like 'post_tag' and 'tag' )
    // exit the function if taxonomy slug is not in URL
    if (strpos($url, $taxonomy_slug) === FALSE || $taxonomy != $taxonomy_name)
        return $url;

    $url = str_replace('/' . $taxonomy_slug, '', $url);

    return $url;
}

add_action('template_redirect', 'rudr_old_term_redirect');

function rudr_old_term_redirect() {

    $taxonomy_name = 'category';
    $taxonomy_slug = 'category';

    // exit the redirect function if taxonomy slug is not in URL
    if (strpos($_SERVER['REQUEST_URI'], $taxonomy_slug) === FALSE)
        return;

    if (( is_category() && $taxonomy_name == 'category' ) || ( is_tag() && $taxonomy_name == 'post_tag' ) || is_tax($taxonomy_name)) :

        wp_redirect(site_url(str_replace($taxonomy_slug, '', $_SERVER['REQUEST_URI'])), 301);
        exit();

    endif;
}

class a_theme_social_Widget extends WP_Widget {

    /**
     * To create the example widget all four methods will be 
     * nested inside this single instance of the WP_Widget class.
     * */
    public function __construct() {
        $widget_options = array(
            'classname' => 'theme_social_widget',
            'description' => 'This is Theme Social Widget',
        );
        parent::__construct('theme_social_widget', 'Theme Social Widget', $widget_options);
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $social_links = apply_filters('social_links', $instance['social_links']);
        echo $args['before_widget'];
        ?>

        <div id="follow_us_block">
            <h2><?php echo $title; ?></h2>
            <hr>
            <ul>
                <?php
                foreach ($social_links as $social_links_value) {
                    $name = $social_links_value['name'];
                    $label_content = $social_links_value['label_content'];
                    $value = $social_links_value['value'];
                    $class = $social_links_value['class'];
                    $result = explode(" ", $label_content, 2);
                    if (!empty($value)) {
                        ?>
                        <li class="<?php echo $name; ?>">
                            <?php
                            if ($class != 'newsletter_class') {
                                ?>
                                <a target="_blank" class="" href="<?php echo $value; //$twitter_url ?>">
                                    <span><i class="<?php echo $class; ?>"></i><?php echo $result[0]; ?></span></a>
                                <?php
                            } else {
                                ?>
                                <a target="_blank" class="" href="<?php echo $value; ?>">
                                    <span><img src="<?php echo get_template_directory_uri() . '/resources/images/newsletter.png' ?>" ><?php echo $result[0]; ?></a>
                                    <?php }
                                    ?>
                                    </li>
                                    <?php
                                }
                            }
                            ?>

                            </ul>
                            </div>

                            <?php
                            echo $args['after_widget'];
                        }

                        public function form($instance) {
                            ?>
                            <script>
                                jQuery(document).ready(function ($) {
                                    $(".sortable_custom").sortable({
                                        revert: true,
                                        update: function (event, ui) {

                                            $(this).parents("form").find('.button.button-primary').removeAttr('disabled');
                                            $(this).parents("form").find('.button.button-primary').val('Save');
                                        }
                                    });
                                });
                            </script>
                            <?php
                            $title = !empty($instance['title']) ? $instance['title'] : '';
                            $social_order_default = array(
                                0 => array(
                                    'label_content' => 'Facebook Url',
                                    'name' => 'facebook_url',
                                    'class' => 'fab fa-facebook',
                                    'value' => ''
                                ),
                                1 => array(
                                    'label_content' => 'Twitter Url',
                                    'name' => 'twitter_url',
                                    'class' => 'fab fa-twitter',
                                    'value' => ''
                                ),
                                2 => array(
                                    'label_content' => 'Google+ Url',
                                    'name' => 'google_pluse_url',
                                    'class' => 'fab fa-google-plus-g',
                                    'value' => ''
                                ),
                                3 => array(
                                    'label_content' => 'Linkedin Url',
                                    'name' => 'linkedIn_url',
                                    'class' => 'fab fa-linkedin-in',
                                    'value' => ''
                                ),
                                4 => array(
                                    'label_content' => 'YouTube Url',
                                    'name' => 'youtube_url',
                                    'class' => 'fab fa-youtube',
                                    'value' => ''
                                ),
                                5 => array(
                                    'label_content' => 'Instagram Url',
                                    'name' => 'instagram_url',
                                    'class' => 'fab fa-instagram',
                                    'value' => ''
                                ),
                                6 => array(
                                    'label_content' => 'Pinterest Url',
                                    'name' => 'pinterest_url',
                                    'class' => 'fab fa-pinterest-p',
                                    'value' => ''
                                ),
                                7 => array(
                                    'label_content' => 'Vimeo Url',
                                    'name' => 'vimeo_url',
                                    'class' => 'fab fa-vimeo-v',
                                    'value' => ''
                                ),
                                8 => array(
                                    'label_content' => 'Newsletter Url',
                                    'name' => 'newsletter_url',
                                    'class' => 'newsletter_class',
                                    'value' => ''
                                ),
                            );
                            if (!empty($instance['social_links'])) {
                                $social_order_default = $instance['social_links'];
                            }
                            ?>
                            <style>
                                .admin_social_inputs{
                                    width: 100%;
                                }
                            </style>
                            <div class="a_admin_part">
                                <div>
                                    <label for="<?php echo $this->get_field_id('title'); ?>">Social Title:</label>
                                    <p>
                                        <input class="admin_social_inputs" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>" />
                                    </p>
                                </div>
                                <ul  class="sortable_custom">
                                    <?php
                                    foreach ($social_order_default as $key => $social_order_default_value) {
                                        $social_name = $social_order_default_value['name'];
                                        $social_label_content = $social_order_default_value['label_content'];
                                        $social_value = $social_order_default_value['value'];
                                        $social_class = $social_order_default_value['class'];
                                        ?>
                                        <li class="ui-state-default">
                                            <label for="<?php echo $social_name; ?>"><?php echo $social_label_content; ?></label>
                                            <p>
                                                <input type="hidden" name="<?php echo $this->get_field_name('social_links[' . $key . '][name]'); ?>" value="<?php echo $social_name; ?>">
                                                <input type="hidden" name="<?php echo $this->get_field_name('social_links[' . $key . '][label_content]'); ?>" value="<?php echo $social_label_content; ?>">
                                                <input type="hidden" name="<?php echo $this->get_field_name('social_links[' . $key . '][class]'); ?>" value="<?php echo $social_class; ?>">
                                                <input class="admin_social_inputs" type="url" id="<?php echo $social_name; ?>" name="<?php echo $this->get_field_name('social_links[' . $key . '][value]'); ?>" value="<?php echo esc_attr($social_value); ?>" />
                                            </p>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php
                        }

                        public function update($new_instance, $old_instance) {
                            $instance = $old_instance;
                            $instance['title'] = strip_tags($new_instance['title']);
//  $new_instance['social_links'] = '';
                            return $new_instance;
                        }

                    }

                    ;

                    function theme_social_widget() {
                        register_widget('a_theme_social_Widget');
                    }

                    add_action('widgets_init', 'theme_social_widget');

//Recent Posts with Post Thumbnail Widget

                    class a_recent_post_with_post_thumbnail_widget extends WP_Widget {

                        /**
                         * To create the example widget all four methods will be 
                         * nested inside this single instance of the WP_Widget class.
                         * */
                        public function __construct() {
                            $widget_options = array(
                                'classname' => 'theme_recent_posts_with_post_thumbnail_widget',
                                'description' => 'This is Theme Recent Posts With Post Thumbnail Widget',
                            );
                            parent::__construct('theme_recent_posts_with_post_thumbnail_widget', 'Theme Recent Posts With Post Thumbnail Widget', $widget_options);
                        }

                        public function widget($args, $instance) {
                            $title = apply_filters('widget_title', $instance['title']);
                            $new_window = apply_filters('widget_title', $instance['new_window']);
                            $posts_count = apply_filters('widget_title', $instance['posts_count']);
                            $twitter_url = apply_filters('widget_title', $instance['twitter_url']);
                            $facebook_url = apply_filters('widget_title', $instance['facebook_url']);
                            $instagram_url = apply_filters('widget_title', $instance['instagram_url']);
                            $youtube_url = apply_filters('widget_title', $instance['youtube_url']);
                            $target = '';
                            if (!empty($new_window) && $new_window == 1) {
                                $target = 'target="_blank"';
                            }
                            echo $args['before_widget'];
                            ?>

                            <div class="a_sidebar_posts">
                                <div class="sidebar_posts_headline">
                                    <?php if (!empty($title)) { ?>
                                        <h2><?php echo $title; ?></h2>
                                    <?php } ?>
                                    <div class="widget follow">
                                        <ul>
                                            <?php
                                            if (!empty($facebook_url)) {
                                                ?>
                                                <li class="facebook">
                                                    <a <?php echo $target; ?> class="" href="<?php echo $facebook_url; ?>">
                                                        <span><i class="fab fa-facebook-f"></i></span></a>
                                                </li>
                                                <?php
                                            }
                                            ?>
                                            <?php
                                            if (!empty($twitter_url)) {
                                                ?>
                                                <li class="twitter">
                                                    <a <?php echo $target; ?> class="" href="<?php echo $twitter_url; ?>">
                                                        <span><i class="fab fa-twitter"></i></span></a>
                                                </li>
                                                <?php
                                            }
                                            ?>

                                            <?php
                                            if (!empty($instagram_url)) {
                                                ?>
                                                <li class="instagram">
                                                    <a <?php echo $target; ?> class="" href="<?php echo $instagram_url; ?>">
                                                        <span><i class="fab fa-instagram"></i></span></a>
                                                </li>
                                                <?php
                                            }
                                            if (!empty($youtube_url)) {
                                                ?>
                                                <li class="youtube">
                                                    <a <?php echo $target; ?> class="" href="<?php echo $youtube_url; ?>">
                                                        <span><i class="fab fa-youtube"></i></span></a>
                                                </li>
                                                <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <hr>
                                <?php
                                $a_args = array(
                                    'numberposts' => $posts_count,
                                    'post_status' => 'publish'
                                );
                                $recent_posts = wp_get_recent_posts($a_args);
                                if (!empty($recent_posts)) {
                                    ?>
                                    <div id="posts_with_featured_image">
                                        <?php
                                        foreach ($recent_posts as $recent) {
                                             $add_external_link = get_field( "add_external_link", $recent["ID"] );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_permalink($recent["ID"]);
                                }
                                            if (has_post_thumbnail($recent["ID"])) {
                                                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($recent["ID"]), 'medium');
                                                $prevthumb = $prevthumb[0];
                                                $image = esc_url($prevthumb);
                                            } else {
                                                $image = get_template_directory_uri() . '/resources/images/test_photo.png';
                                            }
                                            ?>
                                            <div class="sidebar_all_posts">
                                                <div class="row">
                                                    <div class="sidebar_posts_image col-md-4">
                                                        <a <?php echo $target;?> href="<?php echo $permalink; ?>" style="background-image:url(<?php echo $image; ?>);"></a>
                                                        <?php // echo $image;  ?>
                                                    </div>
                                                    <div class="sidebar_posts_title col-md-8">
                                                        <h3>
                                                            <a <?php echo $target;?> href="<?php echo $permalink; ?>">
                                                                <?php echo $recent["post_title"]; ?>
                                                            </a>
                                                        </h3>
                                                    </div>

                                                </div>
                                            </div>
                                        <?php }
                                        ?>
                                    </div>
                                <?php }
                                ?>
                            </div>
                            <?php
                            echo $args['after_widget'];
                        }

                        public function form($instance) {
                            $title = !empty($instance['title']) ? $instance['title'] : '';
                            $new_window = !empty($instance['new_window']) ? $instance['new_window'] : '';
                            $posts_count = !empty($instance['posts_count']) ? $instance['posts_count'] : '';
                            $twitter_url = !empty($instance['twitter_url']) ? $instance['twitter_url'] : '';
                            $facebook_url = !empty($instance['facebook_url']) ? $instance['facebook_url'] : '';
                            $instagram_url = !empty($instance['instagram_url']) ? $instance['instagram_url'] : '';
                            $youtube_url = !empty($instance['youtube_url']) ? $instance['youtube_url'] : '';
                            ?>
                            <div class="widget_title">
                                <h2><label for="<?php echo $this->get_field_id('title'); ?>">Recent Posts Widget Title:</label></h2>
                                <p>
                                    <input class="admin_social_inputs" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>" />
                                </p>
                            </div>
                            <div class="posts_count">
                                <h2><label for="<?php echo $this->get_field_id('posts_count'); ?>">Recent Posts Count:</label></h2>
                                <p>
                                    <input class="admin_social_inputs" min="3" max="10" type="number" id="<?php echo $this->get_field_id('posts_count'); ?>" name="<?php echo $this->get_field_name('posts_count'); ?>" value="<?php echo esc_attr($posts_count); ?>" />
                                    <small>Post Count Should Be Min 3 or Max 10 (if you will add another number not with in this range, it will not save). </small>
                                </p>
                            </div>
                            <div class="recent_posts_with_post_thumbnail_social">  
                                <h2>Recent Posts Social Links</h2>
                                <div>
                                    <label for="<?php echo $this->get_field_id('facebook_url'); ?>">Facebook Url:</label>
                                    <p>
                                        <input class="admin_social_inputs" type="url" id="<?php echo $this->get_field_id('facebook_url'); ?>" name="<?php echo $this->get_field_name('facebook_url'); ?>" value="<?php echo esc_attr($facebook_url); ?>" />
                                    </p>
                                </div>
                                <div>
                                    <label for="<?php echo $this->get_field_id('twitter_url'); ?>">Twitter Url:</label>
                                    <p>
                                        <input class="admin_social_inputs" type="url" id="<?php echo $this->get_field_id('twitter_url'); ?>" name="<?php echo $this->get_field_name('twitter_url'); ?>" value="<?php echo esc_attr($twitter_url); ?>" />
                                    </p>
                                </div>
                                <div>
                                    <label for="<?php echo $this->get_field_id('instagram_url'); ?>">Instagram Url:</label>
                                    <p>
                                        <input class="admin_social_inputs" type="url" id="<?php echo $this->get_field_id('instagram_url'); ?>" name="<?php echo $this->get_field_name('instagram_url'); ?>" value="<?php echo esc_attr($instagram_url); ?>" />
                                    </p>
                                </div>
                                <div>
                                    <label for="<?php echo $this->get_field_id('youtube_url'); ?>">YouTube Url:</label>
                                    <p>
                                        <input class="admin_social_inputs" type="url" id="<?php echo $this->get_field_id('youtube_url'); ?>" name="<?php echo $this->get_field_name('youtube_url'); ?>" value="<?php echo esc_attr($youtube_url); ?>" />
                                    </p>
                                </div>
                                <?php
                                if (!empty($new_window) && $new_window == 1) {
                                    $checked = 'checked';
                                }
                                ?>
                                <div>
                                    <label for="<?php echo $this->get_field_id('new_window'); ?>">Open Social Links In New Window:</label>
                                    <p>
                                        <input <?php echo $checked; ?> class="admin_social_inputs" type="checkbox" id="<?php echo $this->get_field_id('new_window'); ?>" name="<?php echo $this->get_field_name('new_window'); ?>" value="1" />
                                    </p>
                                </div>
                            </div>
                            <?php
                        }

                        public function update($new_instance, $old_instance) {
                            $instance = $old_instance;
                            $instance['title'] = strip_tags($new_instance['title']);
                            $instance['new_window'] = strip_tags($new_instance['new_window']);
                            $instance['posts_count'] = strip_tags($new_instance['posts_count']);
                            $instance['twitter_url'] = strip_tags($new_instance['twitter_url']);
                            $instance['facebook_url'] = strip_tags($new_instance['facebook_url']);
                            $instance['instagram_url'] = strip_tags($new_instance['instagram_url']);
                            $instance['youtube_url'] = strip_tags($new_instance['youtube_url']);
                            return $instance;
                        }

                    }

                    ;

                    function recent_post_with_post_thumbnail_widget() {
                        register_widget('a_recent_post_with_post_thumbnail_widget');
                    }

                    add_action('widgets_init', 'recent_post_with_post_thumbnail_widget');

//Recent Posts with Category Icon Widget
                    class a_recent_post_with_category_icon_widget extends WP_Widget {

                        /**
                         * To create the example widget all four methods will be 
                         * nested inside this single instance of the WP_Widget class.
                         * */
                        public function __construct() {
                            $widget_options = array(
                                'classname' => 'theme_recent_posts_with_category_icon_widget',
                                'description' => 'This is Theme Recent Posts With Category Icon Widget',
                            );
                            parent::__construct('theme_recent_posts_with_category_icon_widget', 'Theme Recent Posts With Category Icon Widget', $widget_options);
                        }

                        public function widget($args, $instance) {
                            $title = apply_filters('widget_title', $instance['title']);
                            $posts_count = apply_filters('widget_title', $instance['posts_count']);
                            $new_window = apply_filters('widget_title', $instance['new_window']);
                            $twitter_url = apply_filters('widget_title', $instance['twitter_url']);
                            $facebook_url = apply_filters('widget_title', $instance['facebook_url']);
                            $instagram_url = apply_filters('widget_title', $instance['instagram_url']);
                            $youtube_url = apply_filters('widget_title', $instance['youtube_url']);
                            $target = '';
                            if (!empty($new_window) && $new_window == 1) {
                                $target = 'target="_blank"';
                            }
                            echo $args['before_widget'];
                            ?>

                            <div class="a_sidebar_posts">
                                <div class="sidebar_posts_headline">
                                    <?php if (!empty($title)) { ?>
                                        <h2><?php echo $title; ?></h2>
                                    <?php } ?>
                                    <div class="widget follow">
                                        <ul>
                                            <?php
                                            if (!empty($facebook_url)) {
                                                ?>
                                                <li class="facebook">
                                                    <a <?php echo $target; ?> class="" href="<?php echo $facebook_url; ?>">
                                                        <span><i class="fab fa-facebook-f"></i></span></a>
                                                </li>
                                                <?php
                                            }
                                            ?>
                                            <?php
                                            if (!empty($twitter_url)) {
                                                ?>
                                                <li class="twitter">
                                                    <a <?php echo $target; ?> class="" href="<?php echo $twitter_url; ?>">
                                                        <span><i class="fab fa-twitter"></i></span></a>
                                                </li>
                                                <?php
                                            }
                                            ?>

                                            <?php
                                            if (!empty($instagram_url)) {
                                                ?>
                                                <li class="instagram">
                                                    <a <?php echo $target; ?> class="" href="<?php echo $instagram_url; ?>">
                                                        <span><i class="fab fa-instagram"></i></span></a>
                                                </li>
                                                <?php
                                            }
                                            if (!empty($youtube_url)) {
                                                ?>
                                                <li class="youtube">
                                                    <a <?php echo $target; ?> class="" href="<?php echo $youtube_url; ?>">
                                                        <span><i class="fab fa-youtube"></i></span></a>
                                                </li>
                                                <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <hr>
                                <?php
                                $a_args = array(
                                    'numberposts' => $posts_count,
                                    'post_status' => 'publish'
                                );
                                $recent_posts = wp_get_recent_posts($a_args);
                                if (!empty($recent_posts)) {
                                    ?>
                                    <div id="posts_with_category_icon">
                                        <?php
                                        foreach ($recent_posts as $recent) {
                                            $categories = get_the_category($recent["ID"]);
//            $new_cat = get_post_primary_category($recent["ID"], 'category');
//            echo '<pre>';
//            print_r($categories);
//            echo '</pre>';
                                            $add_external_link = get_field( "add_external_link", $recent["ID"] );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_permalink($recent["ID"]);
                                }
                                            if (has_post_thumbnail($recent["ID"])) {
                                                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($recent["ID"]), 'full');
                                                $prevthumb = $prevthumb[0];
                                                $image = '<img src="' . esc_url($prevthumb) . '">';
                                            } else {
                                                $image = '<img src="' . get_stylesheet_directory_uri() . '/resources/images/test_photo.png" >';
                                            }
                                            $a_post_categories = get_post_primary_category($recent["ID"], 'category');
                                            $a_primary_category = $a_post_categories['primary_category'];
                                            ?>
                                            <div class="sidebar_all_posts">
                                                <div class="row">
                                                    <div class="sidebar_posts_icon">
                                                        <?php
                                                        $cat_id = $a_primary_category->term_id;
//                        $image_id = get_term_meta($cat_id, 'category-image-id', true);

                                                        $font_awesome_icon_class = get_term_meta($cat_id, 'font_awesome_icon_class', true);
//                        echo wp_get_attachment_image($image_id, 'large');  
                                                        if (!empty($font_awesome_icon_class)) {
                                                            ?>
                                                            <i class="<?php echo $font_awesome_icon_class; ?>"></i>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="sidebar_posts_title">
                                                        <h3>
                                                            <a <?php echo $target;?> href="<?php echo $permalink; ?>">
                                                                <?php echo $recent["post_title"]; ?>
                                                            </a>
                                                        </h3>
                                                    </div>

                                                </div>
                                            </div>
                                        <?php }
                                        ?>
                                    </div>
                                <?php }
                                ?>
                            </div>
                            <?php
                            echo $args['after_widget'];
                        }

                        public function form($instance) {
                            $title = !empty($instance['title']) ? $instance['title'] : '';
                            $posts_count = !empty($instance['posts_count']) ? $instance['posts_count'] : '';
                            $new_window = !empty($instance['new_window']) ? $instance['new_window'] : '';
                            $twitter_url = !empty($instance['twitter_url']) ? $instance['twitter_url'] : '';
                            $facebook_url = !empty($instance['facebook_url']) ? $instance['facebook_url'] : '';
                            $instagram_url = !empty($instance['instagram_url']) ? $instance['instagram_url'] : '';
                            $youtube_url = !empty($instance['youtube_url']) ? $instance['youtube_url'] : '';
                            ?>
                            <div class="widget_title">
                                <h2><label for="<?php echo $this->get_field_id('title'); ?>">Recent Posts Widget Title:</label></h2>
                                <p>
                                    <input class="admin_social_inputs" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>" />
                                </p>
                            </div>
                            <div class="posts_count">
                                <h2><label for="<?php echo $this->get_field_id('posts_count'); ?>">Recent Posts Count:</label></h2>
                                <p>
                                    <input class="admin_social_inputs" min="3" max="10" type="number" id="<?php echo $this->get_field_id('posts_count'); ?>" name="<?php echo $this->get_field_name('posts_count'); ?>" value="<?php echo esc_attr($posts_count); ?>" />
                                    <small>Post Count Should Be Min 3 or Max 10 (if you will add another number not with in this range, it will not save). </small>
                                </p>
                            </div>
                            <div class="recent_posts_with_category_icon_social">  
                                <h2>Recent Posts Social Links</h2>
                                <div>
                                    <label for="<?php echo $this->get_field_id('facebook_url'); ?>">Facebook Url:</label>
                                    <p>
                                        <input class="admin_social_inputs" type="url" id="<?php echo $this->get_field_id('facebook_url'); ?>" name="<?php echo $this->get_field_name('facebook_url'); ?>" value="<?php echo esc_attr($facebook_url); ?>" />
                                    </p>
                                </div>
                                <div>
                                    <label for="<?php echo $this->get_field_id('twitter_url'); ?>">Twitter Url:</label>
                                    <p>
                                        <input class="admin_social_inputs" type="url" id="<?php echo $this->get_field_id('twitter_url'); ?>" name="<?php echo $this->get_field_name('twitter_url'); ?>" value="<?php echo esc_attr($twitter_url); ?>" />
                                    </p>
                                </div>
                                <div>
                                    <label for="<?php echo $this->get_field_id('instagram_url'); ?>">Instagram Url:</label>
                                    <p>
                                        <input class="admin_social_inputs" type="url" id="<?php echo $this->get_field_id('instagram_url'); ?>" name="<?php echo $this->get_field_name('instagram_url'); ?>" value="<?php echo esc_attr($instagram_url); ?>" />
                                    </p>
                                </div>
                                <div>
                                    <label for="<?php echo $this->get_field_id('youtube_url'); ?>">YouTube Url:</label>
                                    <p>
                                        <input class="admin_social_inputs" type="url" id="<?php echo $this->get_field_id('youtube_url'); ?>" name="<?php echo $this->get_field_name('youtube_url'); ?>" value="<?php echo esc_attr($youtube_url); ?>" />
                                    </p>
                                </div>
                                <?php
                                if (!empty($new_window) && $new_window == 1) {
                                    $checked = 'checked';
                                }
                                ?>
                                <div>
                                    <label for="<?php echo $this->get_field_id('new_window'); ?>">Open Social Links In New Window:</label>
                                    <p>
                                        <input <?php echo $checked; ?> class="admin_social_inputs" type="checkbox" id="<?php echo $this->get_field_id('new_window'); ?>" name="<?php echo $this->get_field_name('new_window'); ?>" value="1" />
                                    </p>
                                </div>
                            </div>
                            <?php
                        }

                        public function update($new_instance, $old_instance) {
                            $instance = $old_instance;
                            $instance['title'] = strip_tags($new_instance['title']);
                            $instance['posts_count'] = strip_tags($new_instance['posts_count']);
                            $instance['new_window'] = strip_tags($new_instance['new_window']);
                            $instance['twitter_url'] = strip_tags($new_instance['twitter_url']);
                            $instance['facebook_url'] = strip_tags($new_instance['facebook_url']);
                            $instance['instagram_url'] = strip_tags($new_instance['instagram_url']);
                            $instance['youtube_url'] = strip_tags($new_instance['youtube_url']);
                            return $instance;
                        }

                    }

                    ;

                    function recent_post_with_category_icon_widget() {
                        register_widget('a_recent_post_with_category_icon_widget');
                    }

                    add_action('widgets_init', 'recent_post_with_category_icon_widget');






                    add_action('wp_ajax_load_filtered_vidoes', 'load_filtered_vidoes');
                    add_action('wp_ajax_nopriv_load_filtered_vidoes', 'load_filtered_vidoes');

                    function load_filtered_vidoes() {
                        $html = '';
                        if (!empty($_POST['a_data_cat_id'])) {
                            $curent_term_id = $_POST['a_data_cat_id'];
                            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                            $tax_slug = $_POST['tax'];
                            $category_id = $_POST['category_id'];
//        $terms = 588;
                            $post_per_page = -1;
                            if ($tax_slug == 'category') {
                                $post_per_page = 21;
                            }
                            $terms = $curent_term_id;
                            $loop = new WP_Query(array(
                                'post_type' => 'post',
                                'posts_per_page' => $post_per_page,
                                'paged' => $paged,
                                'tax_query' => array(
                                    'relation' => 'AND',
                                    array(
                                        'taxonomy' => $tax_slug,
                                        'field' => 'term_id',
                                        'terms' => $terms
                                    ),
                                    array(
                                        'taxonomy' => 'category',
                                        'field' => 'term_id',
                                        'terms' => $category_id
                                    ),
                                )
                                    )
                            );
                            if ($loop->have_posts()) {
                                while ($loop->have_posts()) {
                                    $loop->the_post();
                                    $show_single_post_page_and_link = get_field('show_single_post_page_and_link', $post->ID);
                                    $categories = wp_get_post_terms($post->ID, 'video_category');
                                    $add_external_link = get_field( "add_external_link",  $post->ID );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_permalink($post->ID);
                                }
                                    $class = '';
                                    foreach ($categories as $categories_value) {
                                        $class .= "cat_" . $categories_value->term_id . " ";
                                    }
                                    $html .= '<div data-id="' . $post->ID . '" class="archive_post_preview ' . $class . '">';
//                                    $permalink = get_the_permalink();

                                    $post_video = get_field('td_post_video', $post->ID);
                                    $class = 'video_permalink';

                                    $video_link = '#';
                                    $target = "_self";
                                    if (!empty($show_single_post_page_and_link) && $show_single_post_page_and_link == "Yes") {
                                        $video_link = get_permalink($post->ID);
                                        $target = "_blank";
                                    }
                                    if (!empty($post_video)) {
                                        $class = 'fancybox';
                                        if (count($post_video))
                                            $youtube_video_id = $post_video['td_video'];

                                        if (strpos($youtube_video_id, 'youtube') !== false) {
                                            parse_str(parse_url($youtube_video_id, PHP_URL_QUERY), $youtube_video_vars);
                                            $youtube_video_id = $youtube_video_vars['v'];
                                            update_field('td_post_video', array('td_video' => $youtube_video_id), $post->ID);
                                        }
                                        if ($youtube_video_id && strpos($youtube_video_id, '/') === false) {
                                            $video_link = "https://www.youtube.com/embed/" . $youtube_video_id . "?enablejsapi=1&amp;feature=oembed&amp;wmode=opaque&amp;vq=hd720";
                                        }
                                    }

                                    if (has_post_thumbnail()) {
                                        $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
                                        $prevthumb = $prevthumb[0];
                                        $html .= '<div class="post_thumb_preview">';
//                                    $prev_img = '<img src="' . esc_url($prevthumb) . '" class="not_visible">';
                                        $html .= '<a data-fancybox-type="iframe" class="' . $class . '" target="' . $target . '" href="' . $video_link . '"  style="background-image:url(' . $prevthumb . ')"><i class="fa fa-youtube-play"></i></a></div>';
                                    } else {
                                        $html .= '<div class="post_thumb_preview">';
                                        $prev_img = '<img src="' . get_stylesheet_directory_uri() . '/assets/images/hompage_image_1.png" class="not_visible">';
                                        $html .= '<a data-fancybox-type="iframe" class="' . $class . '" target="' . $target . '" href="' . $video_link . '"  style="background-image:url(' . $prevthumb . ')"><i class="fa fa-youtube-play"></i></a></div>';
                                    }





                                    if (!empty($show_single_post_page_and_link) && $show_single_post_page_and_link === "Yes") {
                                        $html .= '<h2>' . get_the_title() . '</h2>';
                                    } else {
                                        $html .= '<h2><a target="_blank" '.$target.' href="' . $permalink . '">' . get_the_title() . '</a></h2>';
                                    }

                                    $html .= '</div>';
                                }
                            }
                        }
                        echo $html;
                        wp_die();
                    }

                    function wcr_category_fields($term) {
                        // we check the name of the action because we need to have different output
                        // if you have other taxonomy name, replace category with the name of your taxonomy. ex: book_add_form_fields, book_edit_form_fields
                        if (current_filter() == 'category_edit_form_fields') {
                            $font_awesome_icon_class = get_term_meta($term->term_id, 'font_awesome_icon_class', true);
                            ?>
                            <tr class="form-field">
                                <th valign="top" scope="row"><label for="term_fields[font_awesome_icon_class]"><?php _e('Font Awesome Icon Class'); ?></label></th>
                                <td>
                                    <?php
                                    $a_font_awesome_icon_class = get_term_meta($term->term_id, 'font_awesome_icon_class', true);
//                        echo wp_get_attachment_image($image_id, 'large');  
                                    if (!empty($a_font_awesome_icon_class)) {
                                        ?>
                                        <i class="<?php echo $font_awesome_icon_class; ?>"></i>
                                    <?php } ?>

                                    <input type="text" size="40" value="<?php echo esc_attr($font_awesome_icon_class); ?>" id="term_fields[font_awesome_icon_class]" name="term_fields[font_awesome_icon_class]"><br/>
                                    <span class="description"><?php _e('Please select and  enter category icon class from font awesome icons'); ?></span>
                                </td>
                            </tr>   
                        <?php } elseif (current_filter() == 'category_add_form_fields') {
                            ?>
                            <div class="form-field">
                                <label for="term_fields[font_awesome_icon_class]"><?php _e('Font Awesome Icon Class'); ?></label>
                                <input type="text" size="40" value="" id="term_fields[font_awesome_icon_class]" name="term_fields[font_awesome_icon_class]">
                                <p class="description"><?php _e('Please select and  enter category icon class from font awesome icons'); ?></p>
                            </div>  
                            <?php
                        }
                    }

// Add the fields, using our callback function  
// if you have other taxonomy name, replace category with the name of your taxonomy. ex: book_add_form_fields, book_edit_form_fields
                    add_action('category_add_form_fields', 'wcr_category_fields', 10, 2);
                    add_action('category_edit_form_fields', 'wcr_category_fields', 10, 2);

                    function wcr_save_category_fields($term_id) {
                        if (!isset($_POST['term_fields'])) {
                            return;
                        }

                        foreach ($_POST['term_fields'] as $key => $value) {
                            update_term_meta($term_id, $key, sanitize_text_field($value));
                        }
                    }

// Save the fields values, using our callback function
// if you have other taxonomy name, replace category with the name of your taxonomy. ex: edited_book, create_book
                    add_action('edited_category', 'wcr_save_category_fields', 10, 2);
                    add_action('create_category', 'wcr_save_category_fields', 10, 2);

                    function get_post_primary_category($post_id, $term = 'category', $return_all_categories = false) {
                        $return = array();

                        if (class_exists('WPSEO_Primary_Term')) {
                            // Show Primary category by Yoast if it is enabled & set
                            $wpseo_primary_term = new WPSEO_Primary_Term($term, $post_id);
                            $primary_term = get_term($wpseo_primary_term->get_primary_term());

                            if (!is_wp_error($primary_term)) {
                                $return['primary_category'] = $primary_term;
                            }
                        }

                        if (empty($return['primary_category']) || $return_all_categories) {
                            $categories_list = get_the_terms($post_id, $term);

                            if (empty($return['primary_category']) && !empty($categories_list)) {
                                $return['primary_category'] = $categories_list[0];  //get the first category
                            }
                            if ($return_all_categories) {
                                $return['all_categories'] = array();

                                if (!empty($categories_list)) {
                                    foreach ($categories_list as &$category) {
                                        $return['all_categories'][] = $category->term_id;
                                    }
                                }
                            }
                        }

                        return $return;
                    }
/*
//                    function thmfdn_add_menu_description_option($widget, $return, $instance) {
//                        $description = isset($instance['description']) ? $instance['description'] : '';
//                        ?>
<!--                        <p>
                            <input class="checkbox" type="checkbox" id="//<?php // echo $widget->get_field_id('description'); ?>" name="<?php // echo $widget->get_field_name('description'); ?>" <?php // checked(true, $description); ?> />
                            <label for="<?php // echo $widget->get_field_id('description'); ?>">
                                <?php // _e('Make Widget Stiky', 'thmfdn_textdomain'); ?>
                            </label>
                        </p>-->
                        <?php
//                    }
//
//                    add_filter('in_widget_form', 'thmfdn_add_menu_description_option', 10, 3);

//                    function thmfdn_save_menu_description_option($instance, $new_instance) {
//
//                        if (!empty($new_instance['description'])) {
//                            $new_instance['description'] = 1;
//                        }
//
//                        return $new_instance;
//                    }
//
//                    add_filter('widget_update_callback', 'thmfdn_save_menu_description_option', 10, 2);*/

                    function kc_dynamic_sidebar_params($params) {
                        global $wp_registered_widgets;
                        $widget_id = $params[0]['widget_id'];
                        $widget_obj = $wp_registered_widgets[$widget_id];
                        $widget_opt = get_option($widget_obj['callback'][0]->option_name);
                        $widget_num = $widget_obj['params'][0]['number'];

                        foreach ($widget_opt as $widget_opt_value) {
                            if (isset($widget_opt_value['description'])) {
                                $params[0]['before_widget'] = preg_replace('/class="/', "class=\"stiky_sidebar ", $params[0]['before_widget'], 1);
//                echo '<pre>';
//                print_r($params[0]);
//                echo '</pre>';
                            }
                        }
                        return $params;
                    }

add_filter( 'dynamic_sidebar_params', 'kc_dynamic_sidebar_params' );






























function add_custom_meta_box()
{
    add_meta_box("demo-meta-box", "Libsyn Player Embed", "custom_meta_box_markup", "post", "side", "high", null);
}

add_action("add_meta_boxes", "add_custom_meta_box");

function custom_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");

    ?>
        <div>
            <p><label for="meta-box-text">Type Embed</label></p>
            <p>
                <textarea style="min-height: 200px; width: 100%;" name="meta-box-text"><?php echo get_post_meta($object->ID, "meta-box-text", true); ?></textarea>
            </p>


        </div>
    <?php  
}


function save_custom_meta_box($post_id, $post, $update)
{
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if(!current_user_can("edit_post", $post_id))
        return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    $slug = "post";
    if($slug != $post->post_type)
        return $post_id;

    $meta_box_text_value = "";

    if(isset($_POST["meta-box-text"]))
    {
        $meta_box_text_value = $_POST["meta-box-text"];
    }   
    update_post_meta($post_id, "meta-box-text", $meta_box_text_value);
}

add_action("save_post", "save_custom_meta_box", 10, 3);


include 'inc/acf_function.php';

add_action('wp_ajax_load_posts_by_ajax_callback', 'load_posts_by_ajax_callback');
add_action('wp_ajax_nopriv_load_posts_by_ajax_callback', 'load_posts_by_ajax_callback');

function load_posts_by_ajax_callback() {
    $paged = $_POST['a_blog_posts_page'];
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 10,
        'paged' => $paged,
        'post_status' => 'publish',
        'ignore_sticky_posts' => true,
        'order' => 'DESC', 
        'orderby' => 'date'
    );
    $a_html = '';
    $my_posts = new WP_Query( $args );
    if ( $my_posts->have_posts() ) :
        ?>
        <?php while ( $my_posts->have_posts() ) : $my_posts->the_post();
        $content = get_the_content();
        $title = get_the_title();
                $add_external_link = get_field( "add_external_link", $post->ID );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_permalink($post->ID);
                                }
              $a_html .= '<div class="a_blog_post col-md-4">';              
              $a_html .= '<article>';              
              $a_html .= '<div class="post_content_image_part">';           
               if (has_post_thumbnail($post->ID)) {
                                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
                                $prevthumb = $prevthumb[0];
//                                echo '<img src="' . esc_url($prevthumb) . '">';
                            } else {
//                                echo '<img src="' . get_stylesheet_directory_uri() . '/images/test_photo.png" class="not_visible">';
                                $prevthumb = get_template_directory_uri() . '/images/test_photo.png';
                            }
                            
              $a_html .= '<a class="a_post_img_class a_post_image" '.$target.' href="'.$permalink.'" style="background-image:url('.$prevthumb.')"></a>';              
              $a_html .= '</div>';   
              $a_html .= '<div class="post_content_title_part">';   
              $a_html .= '<h3><a '.$target.' href="'.$permalink.'">'.$title.'</a></h3>';
              $a_html .= '</div>'; 
              $a_html .= '</article>';              
              $a_html .= '</div>';              
         endwhile; ?>
        <?php
    endif;
 echo $a_html;
    wp_die();
}

if( function_exists('acf_add_options_page') ) {
    
    acf_add_options_page();
    
}



//////////////////////////////////////////////////
// if( $_GET['greg_x'] == 'run'){
//     $args = array(
//         'post_type' => 'post',
//         'posts_per_page' => -1,
//         'tax_query' => array(
//             array(
//                 'taxonomy' => 'category',
//                 'field'    => 'term_id',
//                 'terms'    => '1402',
//             ),
//         ),
//     );

//     $q = new WP_Query( $args );

//     $i=0;
//     if ( $q->have_posts() ) {
//         while ( $q->have_posts() ) {
//             $q->the_post();
//             echo get_permalink().'<br>'; //' '.get_the_title().
//             $i++;
//         }
//         echo 'Total: '.$i;
//         wp_reset_postdata();
//     }
//     exit;
// }




function set_default_page_template($post_ID, $post, $update) {
    // Only set for new pages that are being created for the first time
    if ($post->post_type == 'page' && $post->post_status == 'auto-draft' && !$update) {
        update_post_meta($post_ID, '_wp_page_template', 'elementor_header_footer');
        
    }
}
add_action('wp_insert_post', 'set_default_page_template', 10, 3);

// added
