<?php
/**
 * Theme functions and definitions.
 *
 * @package Switch_Graphics_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SWITCH_GRAPHICS_THEME_VERSION' ) ) {
	define( 'SWITCH_GRAPHICS_THEME_VERSION', '1.0.0' );
}

require get_template_directory() . '/inc/customizer.php';

/**
 * Setup theme defaults and WordPress supports.
 */
function switch_graphics_theme_setup() {
	load_theme_textdomain( 'switch-graphics-theme', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 260,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'custom-header',
		array(
			'width'       => 1920,
			'height'      => 1080,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
		)
	);
	add_theme_support(
		'custom-background',
		array(
			'default-color' => 'ffffff',
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'switch-graphics-theme' ),
			'footer'  => __( 'Footer Menu', 'switch-graphics-theme' ),
		)
	);

	add_editor_style( 'assets/css/theme.css' );
}
add_action( 'after_setup_theme', 'switch_graphics_theme_setup' );

/**
 * Set content width.
 */
function switch_graphics_theme_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'switch_graphics_theme_content_width', 900 );
}
add_action( 'after_setup_theme', 'switch_graphics_theme_content_width', 0 );

/**
 * Enqueue frontend assets.
 */
function switch_graphics_theme_scripts() {
	wp_enqueue_style(
		'switch-graphics-google-fonts',
		'https://fonts.googleapis.com/css2?family=Concert+One&family=Roboto:wght@300;400;500;700&family=Yeseva+One&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'switch-graphics-fontawesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
		array(),
		'6.5.2'
	);
	wp_enqueue_style( 'switch-graphics-style', get_stylesheet_uri(), array(), SWITCH_GRAPHICS_THEME_VERSION );
	wp_enqueue_style(
		'switch-graphics-theme',
		get_template_directory_uri() . '/assets/css/theme.css',
		array( 'switch-graphics-style' ),
		SWITCH_GRAPHICS_THEME_VERSION
	);

	$accent_color      = sanitize_hex_color( switch_graphics_get_theme_mod( 'accent_color' ) );
	$accent_dark_color = sanitize_hex_color( switch_graphics_get_theme_mod( 'accent_dark_color' ) );
	$surface_color     = sanitize_hex_color( switch_graphics_get_theme_mod( 'surface_color' ) );
	$text_color        = sanitize_hex_color( switch_graphics_get_theme_mod( 'text_color' ) );

	$accent_color      = $accent_color ? $accent_color : switch_graphics_get_default( 'accent_color' );
	$accent_dark_color = $accent_dark_color ? $accent_dark_color : switch_graphics_get_default( 'accent_dark_color' );
	$surface_color     = $surface_color ? $surface_color : switch_graphics_get_default( 'surface_color' );
	$text_color        = $text_color ? $text_color : switch_graphics_get_default( 'text_color' );

	$inline_css = ":root{--sgt-accent:{$accent_color};--sgt-accent-dark:{$accent_dark_color};--sgt-surface:{$surface_color};--sgt-text:{$text_color};}";
	wp_add_inline_style( 'switch-graphics-theme', $inline_css );

	wp_enqueue_script(
		'switch-graphics-script',
		get_template_directory_uri() . '/assets/js/theme.js',
		array( 'jquery' ),
		SWITCH_GRAPHICS_THEME_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'switch_graphics_theme_scripts' );

/**
 * Register widget areas.
 */
function switch_graphics_theme_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'switch-graphics-theme' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Main sidebar widgets.', 'switch-graphics-theme' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'switch_graphics_theme_widgets_init' );
