<?php
/* Template Name: Page Template */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Single Posts Template
 *
 *
 * @file           single.php
 * @package        Wiser Sites
 * @author
 * @copyright      2015 WiserSites
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/wisersites/single.php
 * @since          available since Release 1.0
 */
get_header();
?>

<div id="content" class="home_content container page_content">
    <div class="left_and_sidebar">
        <?php
        if (have_posts()) {
            the_post();
            ?>
            <div id="home_content_left" class="content_left_part">
                <div id="post-<?php the_ID(); ?>" <?php post_class('post_inner_content'); ?>>

                    <div class="a_page_title">
                        <h1><?php the_title(); ?></h1>
                        <hr>
                    </div>
                    <div class="a_page_content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
            <?php
        } else {
            
        }
        ?><div id="sidebar">
        <?php include 'inc/custom-sidebar.php'; ?>
        </div>
    </div>
</div>
<?php
get_footer();
?>
