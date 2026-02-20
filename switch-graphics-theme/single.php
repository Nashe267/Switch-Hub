<?php
/**
 * Template for single posts.
 *
 * @package Switch_Graphics_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main section-space">
	<div class="container sgt-content-grid">
		<div class="sgt-main-column">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', get_post_type() );

				the_post_navigation(
					array(
						'prev_text' => '&larr; ' . esc_html__( 'Previous Post', 'switch-graphics-theme' ),
						'next_text' => esc_html__( 'Next Post', 'switch-graphics-theme' ) . ' &rarr;',
					)
				);

				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			endwhile;
			?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>

<?php
get_footer();
