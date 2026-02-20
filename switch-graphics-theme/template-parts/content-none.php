<?php
/**
 * Template part for displaying "no content" states.
 *
 * @package Switch_Graphics_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="no-results not-found sgt-loop-card">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'switch-graphics-theme' ); ?></h1>
	</header>

	<div class="page-content">
		<?php if ( is_search() ) : ?>
			<p><?php esc_html_e( 'No matching result found. Try a different keyword.', 'switch-graphics-theme' ); ?></p>
			<?php get_search_form(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'It looks like there is no content here yet.', 'switch-graphics-theme' ); ?></p>
		<?php endif; ?>
	</div>
</section>
