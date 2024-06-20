<?php
/* Template Name: Authors Template */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div id="content" class="home_content container page_content">
    <div class="left_and_sidebar">
            <div id="home_content_left" class="content_left_part">
                <?php
        if (have_posts()) {
            the_post();
            ?>
                <div id="post-<?php the_ID(); ?>" <?php post_class('post_inner_content'); ?>>

                    <div class="a_page_title">
                        <h1><?php the_title(); ?></h1>
                        <hr>
                    </div>
                    <div class="a_page_content">
                        <?php the_content(); ?>
                    </div>
                </div>
                <?php
        } else {
            
        }
        ?>
                <div class="row">
            <?php
            global $wpdb;
            $avatars_folder_name = 'Cimy_User_Extra_Fields';
            $avatar_folder_name = 'avatar';

            $args = array(
                'role'         => 'champion',
            );
            $champions = get_users( $args );
            foreach( $champions as $author ){
                $curauth = $author;
                $user_link = get_author_posts_url($curauth->ID);
                ?>
                <div class="col-md-4 author-item">
                    <a href="<?php echo $user_link; ?>" title="Articles by <?php echo $curauth->display_name; ?>">
                        <?php
                        $avatar = get_cimyFieldValue($curauth->ID, 'AVATAR');
//                        echo '<pre>';
//                        print_r($avatar);
//                        echo '</pre>';

                        //GX check if empty, if yes, change folder name with space
//                        if( !empty($avatar) ){
//                            $parse_avatar = parse_url( $avatar );
//                        echo '<pre>';
//                        print_r($parse_avatar);
//                        echo '</pre>';
//                            $avatar_file = $_SERVER['DOCUMENT_ROOT'].$parse_avatar['path'];
//                            if ( !file_exists($avatar_file) ) {
//                                $folder_name = str_replace( $avatars_folder_name.'/', '', strstr( $avatar, $avatars_folder_name ) );
//                                $folder_name = str_replace( array( $avatar_folder_name, '/' ), '', strstr( $folder_name, $avatar_folder_name, true ) );
//                                $folder_name_array = splitAtUpperCase( $folder_name );
//                                $new_folder_name = ''; $n_l=0;
//                                foreach ( $folder_name_array as $folder_name_item ){
//
//                                    $new_folder_name.= $folder_name_item;
//                                    if( $n_l < 1)
//                                        $new_folder_name.= ' ';
//
//                                    $n_l++;
//                                }
//                                $avatar = str_replace( $folder_name, $new_folder_name, $avatar);
//                            }
//                        }

                        ?>
                        <img class="img-responsive" alt="<?php echo $curauth->display_name; ?> Avatar" src="<?php echo cimy_uef_sanitize_content($avatar)?>"/>
                        <h4 class="text-center"><?php echo $curauth->display_name; ?></h4>
                        </a>
                </div>
        <?php } ?>
                    </div>
            </div>
                <div id="sidebar">
        <?php include get_template_directory().'/inc/custom-sidebar.php'; ?>
        </div>
    </div>
</div>
<?php
get_footer();
?>
