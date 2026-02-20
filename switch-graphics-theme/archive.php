<?php
/**
 * Archive template.
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
					<h1 class="page-title"><?php the_archive_title(); ?></h1>
					<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
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
