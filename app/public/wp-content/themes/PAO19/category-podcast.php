
<?php
get_header();
global $cat;
$curent_term_id = $cat;
?>
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
<div id="content" class="video_gallery_template home_content container page_content">
    <div class="left_and_sidebar">
        <div id="home_content_left" class="content_left_part">
            
            <?php
            $a_queried_object = get_category( $cat );
            $a_term_name = $a_queried_object->name;
            $a_term_description = $a_queried_object->description;
//            echo '<pre>';
//            print_r($a_queried_object);
//            echo '</pre>';
            ?>
                                <h2 class="above_line"><?php echo $a_term_name; ?></h2>
                                <div class="line"></div>
                                <?php
                                if(!empty($a_term_description)){
                                echo '<div class="a_category_description">'.$a_term_description.'</div>';
                                }
                                ?>
    
            

<div class="archive_videos_and_content archive_posts_and_content">

            <?php
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                    $loop = new WP_Query(array(
                        'post_type' => 'post',
                        'posts_per_page' => 21,
                        'paged' => $paged,
                        'tax_query' => array(
                                array(
                                    'taxonomy' => 'category',
                                    'field' => 'term_id',
                                    'terms' => $cat
                                )
                            )
                            )
                    );
                    
                    if ($loop->have_posts()) {
                        while ($loop->have_posts()) {
                            $loop->the_post();
//                            $show_single_post_page_and_link = get_field('show_single_post_page_and_link',$post->ID);
                            $categories = wp_get_post_terms($post->ID,'video_category');
                            $class = '';
                            foreach ($categories as $categories_value) {
                                $class .= "cat_".$categories_value->term_id." ";
                            }
                            ?>
            <div data-id="<?php echo $post->ID; ?>" class="archive_post_preview <?php echo $class;?>">
                                <?php
//                                $post_video= get_field('td_post_video', $post->ID);
                                $class = 'video_permalink';
                                
                                $video_link = '#';
                                    $video_link = get_permalink($post->ID);
                                    $target = "_blank";
                                    
                                $libsyn_player = get_post_meta($post->ID, "meta-box-text", true);
                                 echo '<div class="libsyn_player">';
                                if(!empty($libsyn_player)){
                                    echo $libsyn_player;
                                }
                                echo '</div>';
                                $add_external_link = get_field( "add_external_link", $post->ID );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "target = '_blank'";
                                    $permalink = $add_external_link;
                                } else {
                                $permalink = get_the_permalink();
                                }
                                if (has_post_thumbnail()) {
                                    $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
                                    $prevthumb = $prevthumb[0];
                                    echo '<div class="post_thumb_preview">';
//                                    $prev_img = '<img src="' . esc_url($prevthumb) . '" class="not_visible">';
                                    echo '<a class="a_post_img_class '.$class.'"  href="'.  $video_link . '"  style="background-image:url('.$prevthumb.')"></a></div>';
                                } else {
                                    echo '<div class="post_thumb_preview">';
                                    $prev_img = get_stylesheet_directory_uri() . '/assets/images/hompage_image_1.png';
                                    echo '<a class="a_post_img_class '.$class.'"  href="' .  $video_link . '"  style="background-image:url('.$prev_img.')"></a></div>';
                                }
                                ?>   
                <h3><a <?php echo $target; ?> href="<?php echo $permalink; ?>"><?php the_title(); ?></a></h3>
                            </div>

                            <?php
                        }
                        ?>
    <div class="pagenav">
                <div class="alignleft"><?php  previous_posts_link('< Previous', $the_query->max_num_pages); ?></div>
                <div class="alignright"><?php next_posts_link('Next >', $the_query->max_num_pages); ?></div>
            </div>
    <?php
//                        previous_posts_link('Previous', $loop->max_num_pages);
//                        next_posts_link('Next', $loop->max_num_pages);
                    } else {
                        get_template_part('content', 'none');
                    }
            ?>
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