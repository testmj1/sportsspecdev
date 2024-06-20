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
            ?>
            <div class="sport-category-part col-md-12">
                                <h2 class="above_line"><?php echo $a_term_name; ?></h2>
                                <div class="line"></div>
                        </div>
    <div id="videocategories">
            <?php
            $cat = get_terms(array(
                'taxonomy' => 'video_gallery',
                'hide_empty' => false,
            ));
            if (!empty($cat)) {
                ?>
                <ul>
                    <li data-slug="all" data-cat-id="<?php echo $curent_term_id; ?>" data-tax="category" class="all">
                        <a href="<?php echo get_category_link($curent_term_id); ?>">
                       All
                        </a>
                    </li>
                    <?php
                    foreach ($cat as $cat_value) {
                        $cat_id = $cat_value->term_id;
                        $cat_name = $cat_value->name;
                        $cat_slug = $cat_value->slug;
                        $loop = new WP_Query(array(
                            'post_type' => 'post',
                            'posts_per_page' => -1,
                            'tax_query' => array(
                                'relation' => 'AND',
                                    array(
                                        'taxonomy' => 'video_gallery',
                                        'field' => 'term_id',
                                        'terms' => $cat_id
                                    ),
                                    array(
                                        'taxonomy' => 'category',
                                        'field' => 'term_id',
                                        'terms' => $curent_term_id
                                    ),
                                )
                            )
        );
                                if(!empty($loop->posts)){
                        ?>
                        <li data-slug="<?php echo $cat_slug; ?>" data-tax="video_gallery" data-cat-id="<?php echo $cat_id; ?>" class="selected_category <?php echo $cat_slug; ?>">
                            <span>
                        <?php echo $cat_name; ?>
                            </span>
                        </li>
                    <?php
                                }
                }
                ?>
                </ul>
    <?php
}
?>
        </div>
            

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
                                    'terms' => $curent_term_id
                                )
                            )
                            )
                    );
                    if ($loop->have_posts()) {
                        while ($loop->have_posts()) {
                            $loop->the_post();
                            $show_single_post_page_and_link = get_field('show_single_post_page_and_link',$post->ID);
                            $categories = wp_get_post_terms($post->ID,'video_category');
                            $class = '';
                            foreach ($categories as $categories_value) {
                                $class .= "cat_".$categories_value->term_id." ";
                            }
                            ?>
            <div data-id="<?php echo $post->ID; ?>" class="archive_post_preview <?php echo $class;?>">
                                <?php
                                $post_video= get_field('td_post_video', $post->ID);
                                $class = 'video_permalink';
                                
                                $video_link = '#';
                                $target = "_self";
                                if(!empty($show_single_post_page_and_link) && $show_single_post_page_and_link == "Yes"){
                                    $video_link = get_permalink($post->ID);
                                    $target = "_blank";
                                    
                                }
                                if(!empty($post_video)){
                                    $class = 'fancybox';
                                    if( count($post_video) )
                                            $youtube_video_id = $post_video['td_video'];

                                    if( strpos( $youtube_video_id, 'youtube') !== false ){
                                        parse_str( parse_url( $youtube_video_id, PHP_URL_QUERY ), $youtube_video_vars );
                                        $youtube_video_id = $youtube_video_vars['v'];
                                        update_field( 'td_post_video', array('td_video' => $youtube_video_id), $post->ID );
                                    }
                                    if( $youtube_video_id && strpos( $youtube_video_id, '/') === false ){
                                        $video_link = "https://www.youtube.com/embed/".$youtube_video_id."?enablejsapi=1&amp;feature=oembed&amp;wmode=opaque&amp;vq=hd720";
                                    }                                    
                                }
                                
                                $add_external_link = get_field( "add_external_link", $post->ID );
                                $target = '';
                                $permalink = "";
                                if(!empty($add_external_link)){
                                    $target = "_blank";
                                    $class = 'increase-video-link';
                                    $permalink = $add_external_link;
                                    $video_link = $add_external_link;
                                } else {
                                $permalink = get_the_permalink();
                                }
                                
//                                $permalink = get_the_permalink();
                                if (has_post_thumbnail()) {
                                    $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
                                    $prevthumb = $prevthumb[0];
                                    echo '<div class="post_thumb_preview">';
//                                    $prev_img = '<img src="' . esc_url($prevthumb) . '" class="not_visible">';
                                    echo '<a class="a_post_img_class '.$class.'" target="'.$target.'" href="'.  $video_link . '"  style="background-image:url('.$prevthumb.')"><i class="fa fa-youtube-play"></i></a></div>';
                                } else {
                                    echo '<div class="post_thumb_preview">';
                                    $prev_img = '<img src="' . get_stylesheet_directory_uri() . '/assets/images/hompage_image_1.png" class="not_visible">';
                                    echo '<a  class="a_post_img_class '.$class.'" target="'.$target.'" href="' .  $video_link . '"  style="background-image:url('.$prevthumb.')"><i class="fa fa-youtube-play"></i></a></div>';
                                }
                                ?>      
                <?php
                if(!empty($show_single_post_page_and_link) && $show_single_post_page_and_link === "No"){
                ?>
                <h3><?php the_title(); ?></h3>
                        <?php }else{
                            ?>
                <h3><a href="<?php echo $permalink; ?>" target="<?php echo $target ?>"><?php the_title(); ?></a></h3>
                        <?php } ?>
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