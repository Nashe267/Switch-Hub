<?php
/**
 * The template for displaying the footer.
 *
 * @package Switch_Graphics_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="container footer-grid">
			<div class="footer-column">
				<h3><?php esc_html_e( 'About Us', 'switch-graphics-theme' ); ?></h3>
				<p><?php echo esc_html( switch_graphics_get_theme_mod( 'footer_about' ) ); ?></p>
			</div>

			<div class="footer-column">
				<h3><?php esc_html_e( 'Information', 'switch-graphics-theme' ); ?></h3>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'menu_class'     => 'footer-menu',
							'container'      => false,
						)
					);
				} else {
					echo '<ul class="footer-menu">';
					echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'switch-graphics-theme' ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/about' ) ) . '">' . esc_html__( 'About', 'switch-graphics-theme' ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/contact' ) ) . '">' . esc_html__( 'Contact', 'switch-graphics-theme' ) . '</a></li>';
					echo '</ul>';
				}
				?>
			</div>

			<div class="footer-column">
				<h3><?php esc_html_e( 'Our Services', 'switch-graphics-theme' ); ?></h3>
				<ul class="footer-menu">
					<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
						<li>
							<a href="<?php echo esc_url( home_url( '/#services' ) ); ?>">
								<?php echo esc_html( switch_graphics_get_theme_mod( "service_{$i}_title" ) ); ?>
							</a>
						</li>
					<?php endfor; ?>
				</ul>
			</div>

			<div id="contact" class="footer-column">
				<h3><?php esc_html_e( 'Contact Info', 'switch-graphics-theme' ); ?></h3>
				<ul class="footer-contact">
					<?php if ( switch_graphics_get_theme_mod( 'address' ) ) : ?>
						<li>
							<i class="fa-solid fa-location-dot" aria-hidden="true"></i>
							<span><?php echo esc_html( switch_graphics_get_theme_mod( 'address' ) ); ?></span>
						</li>
					<?php endif; ?>
					<?php if ( switch_graphics_get_theme_mod( 'phone' ) ) : ?>
						<li>
							<i class="fa-solid fa-phone" aria-hidden="true"></i>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9\+]/', '', switch_graphics_get_theme_mod( 'phone' ) ) ); ?>">
								<?php echo esc_html( switch_graphics_get_theme_mod( 'phone' ) ); ?>
							</a>
						</li>
					<?php endif; ?>
					<?php if ( switch_graphics_get_theme_mod( 'email' ) ) : ?>
						<li>
							<i class="fa-solid fa-envelope" aria-hidden="true"></i>
							<a href="mailto:<?php echo esc_attr( switch_graphics_get_theme_mod( 'email' ) ); ?>">
								<?php echo esc_html( switch_graphics_get_theme_mod( 'email' ) ); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

		<div class="footer-bottom">
			<div class="container">
				<p><?php echo esc_html( switch_graphics_get_theme_mod( 'footer_copyright' ) ); ?></p>
			</div>
		</div>
	</footer>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
