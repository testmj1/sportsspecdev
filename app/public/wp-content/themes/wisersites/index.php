<?php
get_header();
?>

<?php
global $wiser_option;
$featured_posts = $wiser_option['featured_posts'];
?>

<?php
if( !empty( $featured_posts ) && count( $featured_posts ) && $featured_posts[0] != 'null' ){
    ?>
    <div id="featured_page_content" class="container featured_page_content">
        <?php get_template_part( 'template-parts/featured-area' ); ?>
    </div>
    <?php
} else {
    ?>
    <div id="content" class="home_content container page_content">
        <?php the_content(); ?>
    </div>
    <?php
}
?>

<?php
get_footer();
?>
