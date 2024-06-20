<div id="content" class="archive_post_preview col-sm-12">

    <?php
    
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
    ?>

    <h2><a href="<?php echo $permalink; ?>" target="<?php echo $target ?>"><?php the_title(); ?></a></h2>
    <p class="post_date"><?php the_date('d F Y'); ?></p>
    

</div><!-- end of #content -->
