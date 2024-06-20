<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WiserSites
 * @subpackage PAO19
 * @since 1.0.0
 */

get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header pao-archive-title-wrapper">
				<?php
					the_archive_title( '<h1 class="page-title">', '</h1>' );
				?>
			</header><!-- .page-header -->
            <div class="container">
                <div class="row">
                    
                        <?php
                        // Start the Loop.
                        while ( have_posts() ) :
                            the_post();

                            
                            get_template_part('inc/post', 'preview');
                            
                        
                        global $wp_query;
                        //i_print($wp_query);
                        // Pagination Parameters
                        $pagiArgs = array(
                            'end_size' 	=> 0,
                            'mid_size' 	=> 1,
                            //'current'	=> 2,
                            'type'		=> 'list',
                            //'total' => 50,
                            'prev_next'	=> true,
                            'prev_text'	=> __('Previous'),
                            'next_text'	=> __('Next'),
                        );

                                
                    
                        endwhile;?>
                    
                </div>
            </div>
            <?php
            echo '<div class="pagination">';
			echo paginate_links( $pagiArgs );
			echo '</div>';
			

			// If no content, include the "No posts found" template.
		else :
			get_template_part('content', 'none');

		endif;
		?>
		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();