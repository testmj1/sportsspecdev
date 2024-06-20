

<?php get_header() ?>
<div id="content" class="home_content container page_content">
    <div class="left_and_sidebar">
        <div id="home_content_left" class="content_left_part">
            
            <div id="post-<?php the_ID(); ?>" <?php post_class('post_inner_content'); ?>>
                <?php
                $queried_object = $wp_query->get_queried_object();
                $author_id = $queried_object->ID;
                $author_name = $queried_object->display_name;
//            $term_description = $queried_object->description;
                ?>
                <div class="sport-category-part col-md-12">
                    <div class="headline">
                        <h1><?php echo $author_name; ?></h1>
                        <!--<p><?php // echo $term_description; ?></p>-->
                    <div class="line"></div>
                    </div>
                </div>
                <?php
                if( is_plugin_active( 'cimy-user-extra-fields/cimy_user_extra_fields.php' ) ) {
                ?>
                <div class="auther_block">
                <?php
            $curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
            $avatar = get_cimyFieldValue($curauth->ID, 'AVATAR');

            //GX check if empty, if yes, change folder name with space
            $avatars_folder_name = 'Cimy_User_Extra_Fields';
            $avatar_folder_name = 'avatar';

            ?>
            <div class="col-xs-12 col-sm-4">
                <img class="img-responsive avatar" alt="<?php echo $curauth->display_name; ?> Avatar" src="<?php echo cimy_uef_sanitize_content($avatar)?>"/>
                <a class="author-website" target="_blank" href="<?php echo $curauth->user_url; ?>"><?php echo $curauth->user_url; ?></a>
            </div>
            <div class="col-xs-12 col-sm-8">
                <h2><?php echo $curauth->display_name; ?></h2>
                <?php echo apply_filters("the_content",$curauth->user_description); ?>

            </div>
            <div class="col-xs-12">


                <?php
                $values = get_cimyFieldValue($curauth->ID, false);
                $i=0;
                foreach ($values as $value) {

                if($value['VALUE']!=''&&$value['NAME']!='AVATAR'){
                if($i==0){
                ?>
                <h3 data-toggle="collapse" data-target="#collapseQA" aria-expanded="false" aria-controls="collapseQA">Five Increase Questions <i class="fa fa-chevron-down" aria-hidden="true"></i></h3>
                <div class="collapse" id="collapseQA">
                    <?php
                    $i++;
                    } else if ($i>0)?>

                    <div class="row">
                        <div class="col-xs-12 col-md-5">
                            <h5><?php echo $value['LABEL']; ?></h5>
                        </div>
                        <div class="col-xs-12 col-md-7">
                            <p><?php echo cimy_uef_sanitize_content($value['VALUE']); ?></p>
                        </div>
                    </div>
                    <?php

                    }}$i=0;foreach ($values as $value) { if($value['VALUE']!=''&&$value['NAME']!='AVATAR'){if($i==0){
                    $i++;?>
                </div>
                <?php }}} ?>
            </div>
            </div>
                <?php }?>
                
                <div class="category_blog post_blog">
                    <?php
                    $a_post_type = "";
                    if(!empty($curauth->ID)){
                        $author_id = $curauth->ID;
                        $a_post_type = array('post', 'featured-articles', 'videos', 'stories');
                    }else{
                        $author_id = $author_id;
                        $a_post_type = 'post';
                        
                    }
                    $posts_array = get_posts(
                            array(
                                'posts_per_page' => -1,
                                'author' => $author_id,
                                'post_type' => $a_post_type,
                                "orderby" => "date",
                                'order' => 'DESC'
                            )
                    );
                    ?>
                    <div class="isotope post_all_content">


                        <?php
//                echo do_shortcode('[ajax_load_more post_type="post, featured-articles, videos, stories" repeater="template_3" author="'.$curauth->ID.'" posts_per_page="-1"]');
                        if (!empty($posts_array)) {
                            foreach ($posts_array as $posts_array_value) {
                                $post_id = $posts_array_value->ID;
                                $post_content = $posts_array_value->post_content;
                                $post_excerpt = $posts_array_value->post_excerpt;
                                $category = get_the_category($post_id);
                                $term_id = $category[0]->term_id;
                                $term_name = $category[0]->name;
                                $term_slug = $category[0]->slug;
                                $sport_category_for_class = strtolower($term_slug);
                                $add_external_link = get_field( "add_external_link", $post_id );
                                $target = '';
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                    $permalink = get_permalink($post_id);
                                }
                                $term_taxonomy_id = "category_" . $category[0]->term_taxonomy_id;
                                $post_date = $posts_array_value->post_date;
                                $post_author = $posts_array_value->post_author;
                                $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large');
                                ?>
                                <div class="author_blog_post category_blog_post">
                                    <div class="grid-item start <?php echo $sport_category_for_class; ?>">
                                        <div class="grid-item-inner">
                                            <div class="image col-md-4">
                                                    <?php
                                                    if (!empty($prevthumb[0])) {
                                                        $image_url = $prevthumb[0];
                                                    } else {
                                                        $image_url = '/wp-content/themes/sport/assets/images/logo_new.png';
                                                        }
                                                    ?>
                                                <a class="a_post_img_class a_featured_image" <?php echo $target;?> href="<?php echo $permalink; ?>" style="background-image:url(<?php echo $image_url;?>)">
                                                </a>
                                            
                                            
                                            </div>
                                            <div class="text col-md-8">
                                                <h3> <a class=""  <?php echo $target;?> href="<?php echo $permalink; ?>"><?php echo $posts_array_value->post_title ?></a></h3>
                                                
                                                <?php if (!empty($post_excerpt)) { ?>   
                                                    <p><?php echo $post_excerpt; ?></p>
                                                <?php } else {
                                                    ?>
                                                    <p><?php echo wp_trim_words($post_content, 15, '...'); ?>
                                                    </p>
                                                <?php } ?>
                                                <a class="read_more_button" <?php echo $target;?> href="<?php echo $permalink; ?>">READ MORE</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="sidebar">
<?php include 'inc/custom-sidebar.php'; ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>