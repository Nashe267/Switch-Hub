<?php
/**
 * Template part for displaying posts.
 *
 * @package Switch_Graphics_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'sgt-loop-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="sgt-loop-image" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'large' ); ?>
		</a>
	<?php endif; ?>

	<div class="sgt-loop-content">
		<p class="sgt-meta">
			<?php echo esc_html( get_the_date() ); ?>
			<span class="sgt-meta-sep">|</span>
			<?php the_author(); ?>
		</p>

		<?php if ( is_singular() ) : ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php else : ?>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php endif; ?>

		<?php if ( is_singular() ) : ?>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'switch-graphics-theme' ),
						'after'  => '</div>',
					)
				);
				?>
			</div>
		<?php else : ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div>
			<a class="sgt-read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'switch-graphics-theme' ); ?></a>
		<?php endif; ?>
	</div>
</article>
