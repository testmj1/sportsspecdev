<div id="content" class="archive_post_preview">

    <?php
    $permalink = get_the_permalink();
    if( has_post_thumbnail() ) {
        $prevthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
        $prevthumb = $prevthumb[0];
        $prev_img = '<img src="'.esc_url( $prevthumb ).'" class="not_visible">';
        echo '<div class="post_thumb_preview" style="background-image: url(' . esc_url( $prevthumb ) . ');">';
        echo '<a href="'.$permalink.'" >'.$prev_img.'</a></div>';
    }
    ?>

    <h2><a href="<?php echo $permalink; ?>"><?php the_title(); ?></a></h2>
    <p class="post_date"><?php the_date('d F Y'); ?></p>
    <div class="post_description">
        <?php the_excerpt(); ?>
    </div>

</div><!-- end of #content -->
