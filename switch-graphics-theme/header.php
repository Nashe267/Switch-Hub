<?php
/**
 * The header for the theme.
 *
 * @package Switch_Graphics_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'switch-graphics-theme' ); ?></a>

<div id="page" class="site">
	<header id="masthead" class="site-header">
		<div class="header-top">
			<div class="container header-top__inner">
				<p class="header-top__text"><?php echo esc_html( switch_graphics_get_theme_mod( 'top_bar_text' ) ); ?></p>
				<ul class="header-contact">
					<?php if ( switch_graphics_get_theme_mod( 'phone' ) ) : ?>
						<li>
							<i class="fa-solid fa-phone-volume" aria-hidden="true"></i>
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

		<div class="header-main">
			<div class="container header-main__inner">
				<div class="site-branding">
					<?php
					if ( has_custom_logo() ) {
						the_custom_logo();
					} else {
						?>
						<p class="site-title">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
								<?php bloginfo( 'name' ); ?>
							</a>
						</p>
						<?php
					}
					?>
					<?php if ( get_bloginfo( 'description' ) ) : ?>
						<p class="site-description"><?php bloginfo( 'description' ); ?></p>
					<?php endif; ?>
				</div>

				<button class="menu-toggle" type="button" aria-controls="site-navigation" aria-expanded="false">
					<span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'switch-graphics-theme' ); ?></span>
					<span class="menu-toggle__bars" aria-hidden="true">
						<span class="menu-toggle__bar"></span>
						<span class="menu-toggle__bar"></span>
						<span class="menu-toggle__bar"></span>
					</span>
				</button>

				<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'switch-graphics-theme' ); ?>">
					<div class="menu-backdrop" data-menu-close="1"></div>
					<div class="menu-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site menu', 'switch-graphics-theme' ); ?>">
						<div class="menu-panel__head">
							<p class="menu-panel__title"><?php echo esc_html( switch_graphics_get_theme_mod( 'menu_panel_title' ) ); ?></p>
							<button class="menu-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'switch-graphics-theme' ); ?>">
								<i class="fa-solid fa-xmark" aria-hidden="true"></i>
							</button>
						</div>

						<?php
						if ( has_nav_menu( 'primary' ) ) {
							wp_nav_menu(
								array(
									'theme_location' => 'primary',
									'menu_id'        => 'primary-menu',
									'menu_class'     => 'menu-list',
									'container'      => false,
								)
							);
						} else {
							wp_page_menu(
								array(
									'menu_id'    => 'primary-menu',
									'menu_class' => 'menu-list',
									'show_home'  => true,
								)
							);
						}
						?>
					</div>
				</nav>
			</div>
		</div>
	</header>

	<div id="content" class="site-content">
