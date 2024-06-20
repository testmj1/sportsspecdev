<?php get_header() ?>
<div id="content" class="home_content container page_content">
    <div class="left_and_sidebar">
        <div id="home_content_left" class="content_left_part">
<?php
            global $post;
if ( have_posts() ) {
    the_post();
    ?>

    <div class="outer-wrapper single_post single-sport new_container">
            <?php
            $post_id = $post->ID;
            $post_format = get_post_format( $post_id );
            $post_date = $post->post_date;
            $post_author = $post->post_author;
            $post_img_url = wp_get_attachment_url(get_post_thumbnail_id($post_id));
            $categories = wp_get_post_terms($post_id, 'category', array('order' => 'ASC', 'fields' => 'all'));
            $term_link = get_term_link($categories);
            $author = get_the_author_meta('display_name', $post_author);
            $author_nicename = get_the_author_meta('user_nicename', $post_author );
            $author_url = get_author_posts_url( $post_author, $author_nicename );
            ?>
                <div id="post-<?php the_ID(); ?>" <?php post_class('post_inner_content'); ?>>
                    <div class="post_blog">
                        <div class="post_data">
                            <div class="left_post_data">
                                <span class="post_category">
                                    <?php
                                    foreach ($categories as $categories_value) {
                                        $term_id = $categories_value->term_id;
                                        $term_taxonomy_id = "category_" . $categories_value->term_taxonomy_id;
                                        
                                        $categories_name = $categories_value->name;
                                        $term = get_term($term_id, 'category');
                                        $term_link = get_term_link($term);
                                        ?>
                                <a href="<?php echo $term_link; ?>">
                                            <span class="post_category_<?php echo $categories_name; ?> ">
                                                <?php
                                                echo $categories_name;
                                                ?>
                                            </span>
                                        </a>
                                        <?php
                                    }
                                    ?>
                                </span>
                                
                            </div>
                        </div>
                        <div class="line"></div>
                        <h2><?php echo $post->post_title;?></h2>
                        <span class="post_author"><?php echo "By <a href = '".$author_url."'><span>" . $author . "</span></a>"; ?></span>
                                    <span class="post_date"><?php echo date("M j, Y", strtotime($post_date)); ?></span>
                        <div class="post_all_content">
                            
                            <?php
                                    $post_video= get_field('td_post_video', $post_id);
                                    $td_video_ft_image = $post_video['td_video_ft_image'];
                                if( $post_format != 'video' || !$td_video_ft_image){
                                    ?>
                            <div class="a_post_img_class image" style="background-image:url(<?php echo $post_img_url;?>);">
                                    <!--<img src="<?php // echo $post_img_url; ?>">-->
                            </div>
                            
                            <?php
                                } else {
                                    ?>
                            <div class="image video_bg">
                            <?php
                                    $post_video= get_field('td_post_video', $post_id);
                                    if( count($post_video) )
                                        $youtube_video_id = $post_video['td_video'];

                                    if( strpos( $youtube_video_id, 'youtube') !== false ){
                                        parse_str( parse_url( $youtube_video_id, PHP_URL_QUERY ), $youtube_video_vars );
                                        $youtube_video_id = $youtube_video_vars['v'];
                                        update_field( 'td_post_video', array('td_video' => $youtube_video_id), $post_id );
                                    }
                                    ?>
                                    <div class="wpb_video_wrapper">
                                        <?php
                                        if( $youtube_video_id && strpos( $youtube_video_id, '/') === false ){
                                            ?>
                                            <iframe id="td_youtube_player" width="100%" height="560" src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>?enablejsapi=1&amp;feature=oembed&amp;wmode=opaque&amp;vq=hd720" frameborder="0" allowfullscreen="" style="height: 391.5px;"></iframe>
                                            <script type="text/javascript">
                                                var tag = document.createElement("script");
                                                tag.src = "https://www.youtube.com/iframe_api";

                                                var firstScriptTag = document.getElementsByTagName("script")[0];
                                                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

                                                var player;

                                                function onYouTubeIframeAPIReady() {
                                                    player = new YT.Player("td_youtube_player", {
                                                        height: "720",
                                                        width: "960",
                                                        events: {
                                                            "onReady": onPlayerReady
                                                        }
                                                    });
                                                }

                                                function onPlayerReady(event) {
                                                    player.setPlaybackQuality("hd720");
                                                }
                                            </script>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                    <?php
                                }
                                
                                ?>
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                                <?php
                                $get_description = get_post(get_post_thumbnail_id())->post_excerpt;
                                //the_post_thumbnail();
    //                            if ( !empty($get_description)) {
    //                                echo '<div class="featured_caption">' . $get_description . '</div>';
    //                            }
                                if ( ($post_format != 'video' || !$td_video_ft_image ) && !empty($get_description)) {//If description is not empty show the div
                                    echo '<div class="featured_caption">' . $get_description . '</div>';
                                }
                                
                                ?>
                            
                            
                                

                            <div class="text">
                                <?php the_content(); ?>
                            </div>
                            <?php the_tags( '<div id="pao-tags-content-wrapper"><h3 class="pao-tags-pre">Tags:</h3><ul class="pao-post-tags-wrapper"><li class="pao-post-tag">','<span class="pao-tag-comma">, </span></li><li class="pao-post-tag">', '</li></ul></div>' ); ?>
                        </div>
                    </div>
                </div>
                
                <div id="related_posts">
                    <div class="row">
                        <h2>Related Posts</h2>
                    <?php
                    
                    
                    $a_all_categories = get_the_category($post->ID);
//                    echo '<pre>';
//                    print_r($a_all_categories);
//                    echo '</pre>';
                    
                    
                    
                    
                    
                    
                    
                    
                    $orig_post = $post;
                        global $post;
                        $categories = get_the_category($post->ID);
                        if ($categories) {
                            $category_ids = array();
                            foreach ($categories as $individual_category)
                                $category_ids[] = $individual_category->term_id;

                            $args = array(
                                'category__in' => $category_ids,
                                'post__not_in' => array($post->ID),
                                'posts_per_page' => 4, // Number of related posts that will be shown.
                                'caller_get_posts' => 1
                            );

                            $my_query = new wp_query($args);
                            if ($my_query->have_posts()) {
                                while ($my_query->have_posts()) {
                                    $my_query->the_post();
                                    $add_external_link = get_field( "add_external_link", $post->ID );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_the_permalink();
                                }
                                    ?>
                                        <div class="single_each_post col-md-3">
                                            <div class="single_each_post_image" style="background-image: url(<?php the_post_thumbnail_url(); ?>);">
                                                <!--<div class="single_each_post_image">-->
                <?php // echo $image;  ?>
                                                </div>
                                                <div class="single_each_post_title">
                                                    <h3><a <?php echo $target;?> href="<?php echo $permalink;?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3>
                                                </div>
                                                
                                            </div>
                <?
            }
        }
    }
    $post = $orig_post;
    wp_reset_query();









    ?>
                </div>
                </div>
    </div>

    <?php
} else {

}
?>
</div>
        <div id="sidebar">
            <?php include 'inc/custom-sidebar.php'; ?>
        </div>
</div>
</div>

<?php get_footer(); ?>