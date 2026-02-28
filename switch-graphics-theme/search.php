<?php
/**
 * Search results template.
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
			<?php if ( have_posts() ) : ?>
				<header class="page-header">
					<h1 class="page-title">
						<?php
						printf(
							/* translators: %s: search query. */
							esc_html__( 'Search Results for: %s', 'switch-graphics-theme' ),
							'<span>' . esc_html( get_search_query() ) . '</span>'
						);
						?>
					</h1>
				</header>

				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
				endwhile;

				the_posts_navigation();
				?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>

<?php
get_footer();
