<?php
/* Template Name: Blog Template */
get_header();
?>
<?php
global $post;
global $wiser_option;
?>

<div id="content" class="home_content container page_content">
    <div class="left_and_sidebar">
        <div id="home_content_left" class="content_left_part">
            
            <?php
            $post_blog_or_category_filter = $wiser_option['blog_or_category_filter'];
            if($post_blog_or_category_filter[0] == 'Post Blog'){
            ?>
            <div class="home_page_post_blog">
    <h2><?php echo $post->post_title;?></h2>
                
                <?php include 'inc/blog_page_blog.php'; ?>
            </div>
            <?php 
            }elseif($post_blog_or_category_filter[0] == 'Category Filter'){
                ?>
            <div id="category_filter">
                <?php include 'inc/blog_page_category_filter.php'; ?>
            </div>
            <?php
            }else{?>
            <div class="home_page_post_blog">
                
    <h2><?php echo $post->post_title;?></h2>
     <hr>
            </div>
                <?php
            }
            ?>

            </div>
        <div id="sidebar">
            <?php include 'inc/custom-sidebar.php'; ?>
        </div>
    </div>
</div>
<?php
get_footer();
?>
