<?php
/**
 * 404 template.
 *
 * @package Switch_Graphics_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main section-space">
	<div class="container">
		<section class="sgt-loop-card not-found-page">
			<h1><?php esc_html_e( 'Page Not Found', 'switch-graphics-theme' ); ?></h1>
			<p><?php esc_html_e( 'The page you are trying to reach does not exist. Try searching or return to the homepage.', 'switch-graphics-theme' ); ?></p>
			<?php get_search_form(); ?>
			<p>
				<a class="sgt-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Back to Home', 'switch-graphics-theme' ); ?>
				</a>
			</p>
		</section>
	</div>
</main>

<?php
get_footer();
