<?php
/**
 * Theme Customizer helpers and options.
 *
 * @package Switch_Graphics_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return default values for all theme options.
 *
 * @return array<string, string>
 */
function switch_graphics_get_defaults() {
	return array(
		'top_bar_text'         => 'Fast turnaround, premium quality print services',
		'phone'                => '+1 (111) 222-3333',
		'email'                => 'hello@switchgraphics.com',
		'address'              => '308 Berrier Ave, Lexington, New York',
		'menu_panel_title'     => 'Switch Hub',
		'menu_icon_start'      => '#f25743',
		'menu_icon_end'        => '#f9a826',
		'menu_icon_outline_color' => '#8f2300',
		'menu_icon_outline_thickness' => '1',
		'accent_color'         => '#f25743',
		'accent_dark_color'    => '#c9412f',
		'surface_color'        => '#0f172a',
		'text_color'           => '#1f2937',
		'hero_eyebrow'         => 'Creative Print Studio',
		'hero_title'           => 'Design. Print. Deliver.',
		'hero_text'            => 'Switch Graphics Theme helps your print business launch a bold, modern website and customize every section with ease.',
		'hero_primary_label'   => 'Get a Free Quote',
		'hero_primary_url'     => '#contact',
		'hero_secondary_label' => 'View Services',
		'hero_secondary_url'   => '#services',
		'hero_background'      => get_theme_file_uri( '/assets/images/hero-default.svg' ),
		'about_title'          => 'Highly Catchy Green Printing',
		'about_text'           => 'From business cards to custom signage, we blend design expertise with quality materials to create work your clients remember.',
		'about_point_1_title'  => 'Satisfied Service',
		'about_point_1_text'   => 'Friendly communication and reliable project updates at every stage.',
		'about_point_2_title'  => 'Quick Response',
		'about_point_2_text'   => 'Rapid quote turnarounds and fast delivery options for urgent jobs.',
		'about_image'          => get_theme_file_uri( '/assets/images/about-default.svg' ),
		'services_subtitle'    => 'What We Do Best',
		'services_title'       => 'Professional Printing Services',
		'service_1_icon'       => 'fa-solid fa-palette',
		'service_1_title'      => 'Branding & Packaging',
		'service_1_text'       => 'Branded boxes, labels, and packaging systems designed to stand out on shelves.',
		'service_2_icon'       => 'fa-solid fa-id-card',
		'service_2_title'      => 'Business Collateral',
		'service_2_text'       => 'Business cards, brochures, flyers, and stationery with premium finishes.',
		'service_3_icon'       => 'fa-solid fa-bullhorn',
		'service_3_title'      => 'Large Format Print',
		'service_3_text'       => 'Posters, banners, vehicle graphics, and storefront displays for maximum impact.',
		'products_subtitle'    => 'Featured Work',
		'products_title'       => 'Popular Print Categories',
		'product_1_title'      => 'Business Cards',
		'product_1_text'       => 'Classic, matte, gloss, and soft-touch cards with optional spot UV.',
		'product_2_title'      => 'Flyers & Brochures',
		'product_2_text'       => 'High-volume marketing materials for campaigns and events.',
		'product_3_title'      => 'Packaging Labels',
		'product_3_text'       => 'Custom sticker and label solutions in durable materials.',
		'product_4_title'      => 'Event Banners',
		'product_4_text'       => 'Indoor and outdoor banner printing with quick finishing options.',
		'newsletter_title'     => 'Subscribe Our Newsletter',
		'newsletter_text'      => 'Sign up with your email address to receive new offers and design tips.',
		'newsletter_placeholder' => 'Your Email Address',
		'newsletter_button'    => 'Send',
		'newsletter_shortcode' => '',
		'footer_about'         => 'Switch Graphics creates premium print solutions for businesses, creatives, and events of every size.',
		'footer_year'          => gmdate( 'Y' ),
		'footer_powered_label' => 'Designed & Powered By:',
		'footer_brand_text'    => 'Switch Graphics (Pty) Ltd',
		'footer_brand_url'     => 'https://www.switchgraphics.co.za/',
		'footer_copyright'     => 'Copyright ' . gmdate( 'Y' ) . ' Switch Graphics. All Rights Reserved.',
	);
}

/**
 * Get a single default value.
 *
 * @param string $key Default key.
 * @return string
 */
function switch_graphics_get_default( $key ) {
	$defaults = switch_graphics_get_defaults();
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * Get a theme mod with its corresponding default value.
 *
 * @param string $key Theme mod key without prefix.
 * @return string
 */
function switch_graphics_get_theme_mod( $key ) {
	return get_theme_mod( 'switch_graphics_' . $key, switch_graphics_get_default( $key ) );
}

/**
 * Sanitize icon class list.
 *
 * @param string $input Raw class string.
 * @return string
 */
function switch_graphics_sanitize_icon_class( $input ) {
	return trim( preg_replace( '/[^a-zA-Z0-9\-\s]/', '', (string) $input ) );
}

/**
 * Sanitize menu icon outline thickness.
 *
 * @param mixed $input Raw value.
 * @return int
 */
function switch_graphics_sanitize_outline_thickness( $input ) {
	$value = absint( $input );

	if ( $value > 8 ) {
		$value = 8;
	}

	return $value;
}

/**
 * Register customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function switch_graphics_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'switch_graphics_theme_options',
		array(
			'title'       => __( 'Switch Graphics Theme Options', 'switch-graphics-theme' ),
			'description' => __( 'Customize homepage sections, colors, and business details.', 'switch-graphics-theme' ),
			'priority'    => 30,
		)
	);

	$wp_customize->add_section(
		'switch_graphics_colors',
		array(
			'title'    => __( 'Brand Colors', 'switch-graphics-theme' ),
			'panel'    => 'switch_graphics_theme_options',
			'priority' => 1,
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_accent_color',
		array(
			'default'           => switch_graphics_get_default( 'accent_color' ),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'switch_graphics_accent_color',
			array(
				'label'   => __( 'Accent Color', 'switch-graphics-theme' ),
				'section' => 'switch_graphics_colors',
			)
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_accent_dark_color',
		array(
			'default'           => switch_graphics_get_default( 'accent_dark_color' ),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'switch_graphics_accent_dark_color',
			array(
				'label'   => __( 'Accent Hover Color', 'switch-graphics-theme' ),
				'section' => 'switch_graphics_colors',
			)
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_surface_color',
		array(
			'default'           => switch_graphics_get_default( 'surface_color' ),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'switch_graphics_surface_color',
			array(
				'label'   => __( 'Dark Surface Color', 'switch-graphics-theme' ),
				'section' => 'switch_graphics_colors',
			)
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_text_color',
		array(
			'default'           => switch_graphics_get_default( 'text_color' ),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'switch_graphics_text_color',
			array(
				'label'   => __( 'Body Text Color', 'switch-graphics-theme' ),
				'section' => 'switch_graphics_colors',
			)
		)
	);

	$wp_customize->add_section(
		'switch_graphics_header',
		array(
			'title'    => __( 'Header Details', 'switch-graphics-theme' ),
			'panel'    => 'switch_graphics_theme_options',
			'priority' => 2,
		)
	);

	$header_settings = array(
		'top_bar_text' => array(
			'label'    => __( 'Top Bar Text', 'switch-graphics-theme' ),
			'type'     => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'phone'        => array(
			'label'    => __( 'Phone Number', 'switch-graphics-theme' ),
			'type'     => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'email'        => array(
			'label'    => __( 'Email Address', 'switch-graphics-theme' ),
			'type'     => 'email',
			'sanitize' => 'sanitize_email',
		),
		'address'      => array(
			'label'    => __( 'Business Address', 'switch-graphics-theme' ),
			'type'     => 'text',
			'sanitize' => 'sanitize_text_field',
		),
	);

	foreach ( $header_settings as $key => $config ) {
		$wp_customize->add_setting(
			'switch_graphics_' . $key,
			array(
				'default'           => switch_graphics_get_default( $key ),
				'sanitize_callback' => $config['sanitize'],
			)
		);
		$wp_customize->add_control(
			'switch_graphics_' . $key,
			array(
				'label'   => $config['label'],
				'section' => 'switch_graphics_header',
				'type'    => $config['type'],
			)
		);
	}

	$wp_customize->add_section(
		'switch_graphics_menu',
		array(
			'title'    => __( 'Menu Styles', 'switch-graphics-theme' ),
			'panel'    => 'switch_graphics_theme_options',
			'priority' => 3,
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_menu_panel_title',
		array(
			'default'           => switch_graphics_get_default( 'menu_panel_title' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'switch_graphics_menu_panel_title',
		array(
			'label'   => __( 'Menu Panel Title', 'switch-graphics-theme' ),
			'section' => 'switch_graphics_menu',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_menu_icon_start',
		array(
			'default'           => switch_graphics_get_default( 'menu_icon_start' ),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'switch_graphics_menu_icon_start',
			array(
				'label'   => __( 'Menu Icon Fill (Gradient Start)', 'switch-graphics-theme' ),
				'section' => 'switch_graphics_menu',
			)
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_menu_icon_end',
		array(
			'default'           => switch_graphics_get_default( 'menu_icon_end' ),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'switch_graphics_menu_icon_end',
			array(
				'label'   => __( 'Menu Icon Fill (Gradient End)', 'switch-graphics-theme' ),
				'section' => 'switch_graphics_menu',
			)
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_menu_icon_outline_color',
		array(
			'default'           => switch_graphics_get_default( 'menu_icon_outline_color' ),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'switch_graphics_menu_icon_outline_color',
			array(
				'label'   => __( 'Menu Icon Outline Color', 'switch-graphics-theme' ),
				'section' => 'switch_graphics_menu',
			)
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_menu_icon_outline_thickness',
		array(
			'default'           => switch_graphics_get_default( 'menu_icon_outline_thickness' ),
			'sanitize_callback' => 'switch_graphics_sanitize_outline_thickness',
		)
	);
	$wp_customize->add_control(
		'switch_graphics_menu_icon_outline_thickness',
		array(
			'label'       => __( 'Menu Icon Outline Thickness (px)', 'switch-graphics-theme' ),
			'section'     => 'switch_graphics_menu',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 8,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_section(
		'switch_graphics_hero',
		array(
			'title'    => __( 'Hero Section', 'switch-graphics-theme' ),
			'panel'    => 'switch_graphics_theme_options',
			'priority' => 4,
		)
	);

	$hero_settings = array(
		'hero_eyebrow'         => array( __( 'Eyebrow Text', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'hero_title'           => array( __( 'Main Heading', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'hero_text'            => array( __( 'Description', 'switch-graphics-theme' ), 'textarea', 'sanitize_textarea_field' ),
		'hero_primary_label'   => array( __( 'Primary Button Label', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'hero_primary_url'     => array( __( 'Primary Button URL', 'switch-graphics-theme' ), 'url', 'esc_url_raw' ),
		'hero_secondary_label' => array( __( 'Secondary Button Label', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'hero_secondary_url'   => array( __( 'Secondary Button URL', 'switch-graphics-theme' ), 'url', 'esc_url_raw' ),
	);

	foreach ( $hero_settings as $key => $config ) {
		$wp_customize->add_setting(
			'switch_graphics_' . $key,
			array(
				'default'           => switch_graphics_get_default( $key ),
				'sanitize_callback' => $config[2],
			)
		);
		$wp_customize->add_control(
			'switch_graphics_' . $key,
			array(
				'label'   => $config[0],
				'section' => 'switch_graphics_hero',
				'type'    => $config[1],
			)
		);
	}

	$wp_customize->add_setting(
		'switch_graphics_hero_background',
		array(
			'default'           => switch_graphics_get_default( 'hero_background' ),
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'switch_graphics_hero_background',
			array(
				'label'   => __( 'Hero Background Image', 'switch-graphics-theme' ),
				'section' => 'switch_graphics_hero',
			)
		)
	);

	$wp_customize->add_section(
		'switch_graphics_about',
		array(
			'title'    => __( 'About Section', 'switch-graphics-theme' ),
			'panel'    => 'switch_graphics_theme_options',
			'priority' => 4,
		)
	);

	$about_settings = array(
		'about_title'         => array( __( 'About Heading', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'about_text'          => array( __( 'About Description', 'switch-graphics-theme' ), 'textarea', 'sanitize_textarea_field' ),
		'about_point_1_title' => array( __( 'Point 1 Title', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'about_point_1_text'  => array( __( 'Point 1 Description', 'switch-graphics-theme' ), 'textarea', 'sanitize_textarea_field' ),
		'about_point_2_title' => array( __( 'Point 2 Title', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'about_point_2_text'  => array( __( 'Point 2 Description', 'switch-graphics-theme' ), 'textarea', 'sanitize_textarea_field' ),
	);

	foreach ( $about_settings as $key => $config ) {
		$wp_customize->add_setting(
			'switch_graphics_' . $key,
			array(
				'default'           => switch_graphics_get_default( $key ),
				'sanitize_callback' => $config[2],
			)
		);
		$wp_customize->add_control(
			'switch_graphics_' . $key,
			array(
				'label'   => $config[0],
				'section' => 'switch_graphics_about',
				'type'    => $config[1],
			)
		);
	}

	$wp_customize->add_setting(
		'switch_graphics_about_image',
		array(
			'default'           => switch_graphics_get_default( 'about_image' ),
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'switch_graphics_about_image',
			array(
				'label'   => __( 'About Image', 'switch-graphics-theme' ),
				'section' => 'switch_graphics_about',
			)
		)
	);

	$wp_customize->add_section(
		'switch_graphics_services',
		array(
			'title'    => __( 'Services Section', 'switch-graphics-theme' ),
			'panel'    => 'switch_graphics_theme_options',
			'priority' => 5,
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_services_subtitle',
		array(
			'default'           => switch_graphics_get_default( 'services_subtitle' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'switch_graphics_services_subtitle',
		array(
			'label'   => __( 'Services Subtitle', 'switch-graphics-theme' ),
			'section' => 'switch_graphics_services',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_services_title',
		array(
			'default'           => switch_graphics_get_default( 'services_title' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'switch_graphics_services_title',
		array(
			'label'   => __( 'Services Heading', 'switch-graphics-theme' ),
			'section' => 'switch_graphics_services',
			'type'    => 'text',
		)
	);

	for ( $i = 1; $i <= 3; $i++ ) {
		$wp_customize->add_setting(
			"switch_graphics_service_{$i}_icon",
			array(
				'default'           => switch_graphics_get_default( "service_{$i}_icon" ),
				'sanitize_callback' => 'switch_graphics_sanitize_icon_class',
			)
		);
		$wp_customize->add_control(
			"switch_graphics_service_{$i}_icon",
			array(
				'label'       => sprintf( __( 'Service %d Icon Class', 'switch-graphics-theme' ), $i ),
				'description' => __( 'Example: fa-solid fa-palette', 'switch-graphics-theme' ),
				'section'     => 'switch_graphics_services',
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			"switch_graphics_service_{$i}_title",
			array(
				'default'           => switch_graphics_get_default( "service_{$i}_title" ),
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			"switch_graphics_service_{$i}_title",
			array(
				'label'   => sprintf( __( 'Service %d Title', 'switch-graphics-theme' ), $i ),
				'section' => 'switch_graphics_services',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			"switch_graphics_service_{$i}_text",
			array(
				'default'           => switch_graphics_get_default( "service_{$i}_text" ),
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);
		$wp_customize->add_control(
			"switch_graphics_service_{$i}_text",
			array(
				'label'   => sprintf( __( 'Service %d Description', 'switch-graphics-theme' ), $i ),
				'section' => 'switch_graphics_services',
				'type'    => 'textarea',
			)
		);
	}

	$wp_customize->add_section(
		'switch_graphics_products',
		array(
			'title'    => __( 'Products Section', 'switch-graphics-theme' ),
			'panel'    => 'switch_graphics_theme_options',
			'priority' => 6,
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_products_subtitle',
		array(
			'default'           => switch_graphics_get_default( 'products_subtitle' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'switch_graphics_products_subtitle',
		array(
			'label'   => __( 'Products Subtitle', 'switch-graphics-theme' ),
			'section' => 'switch_graphics_products',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'switch_graphics_products_title',
		array(
			'default'           => switch_graphics_get_default( 'products_title' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'switch_graphics_products_title',
		array(
			'label'   => __( 'Products Heading', 'switch-graphics-theme' ),
			'section' => 'switch_graphics_products',
			'type'    => 'text',
		)
	);

	for ( $i = 1; $i <= 4; $i++ ) {
		$wp_customize->add_setting(
			"switch_graphics_product_{$i}_title",
			array(
				'default'           => switch_graphics_get_default( "product_{$i}_title" ),
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			"switch_graphics_product_{$i}_title",
			array(
				'label'   => sprintf( __( 'Product %d Title', 'switch-graphics-theme' ), $i ),
				'section' => 'switch_graphics_products',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			"switch_graphics_product_{$i}_text",
			array(
				'default'           => switch_graphics_get_default( "product_{$i}_text" ),
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);
		$wp_customize->add_control(
			"switch_graphics_product_{$i}_text",
			array(
				'label'   => sprintf( __( 'Product %d Description', 'switch-graphics-theme' ), $i ),
				'section' => 'switch_graphics_products',
				'type'    => 'textarea',
			)
		);
	}

	$wp_customize->add_section(
		'switch_graphics_newsletter',
		array(
			'title'    => __( 'Newsletter Section', 'switch-graphics-theme' ),
			'panel'    => 'switch_graphics_theme_options',
			'priority' => 7,
		)
	);

	$newsletter_settings = array(
		'newsletter_title'       => array( __( 'Newsletter Heading', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'newsletter_text'        => array( __( 'Newsletter Description', 'switch-graphics-theme' ), 'textarea', 'sanitize_textarea_field' ),
		'newsletter_placeholder' => array( __( 'Email Placeholder', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'newsletter_button'      => array( __( 'Button Label', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'newsletter_shortcode'   => array( __( 'Newsletter Shortcode (optional)', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
	);

	foreach ( $newsletter_settings as $key => $config ) {
		$wp_customize->add_setting(
			'switch_graphics_' . $key,
			array(
				'default'           => switch_graphics_get_default( $key ),
				'sanitize_callback' => $config[2],
			)
		);
		$wp_customize->add_control(
			'switch_graphics_' . $key,
			array(
				'label'   => $config[0],
				'section' => 'switch_graphics_newsletter',
				'type'    => $config[1],
			)
		);
	}

	$wp_customize->add_section(
		'switch_graphics_footer',
		array(
			'title'    => __( 'Footer Content', 'switch-graphics-theme' ),
			'panel'    => 'switch_graphics_theme_options',
			'priority' => 8,
		)
	);

	$footer_settings = array(
		'footer_about'         => array( __( 'Footer About Text', 'switch-graphics-theme' ), 'textarea', 'sanitize_textarea_field' ),
		'footer_year'          => array( __( 'Footer Year', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'footer_powered_label' => array( __( 'Footer Label Before Brand', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'footer_brand_text'    => array( __( 'Footer Brand Text', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
		'footer_brand_url'     => array( __( 'Footer Brand Link URL', 'switch-graphics-theme' ), 'url', 'esc_url_raw' ),
		'footer_copyright'     => array( __( 'Legacy Copyright Text (fallback)', 'switch-graphics-theme' ), 'text', 'sanitize_text_field' ),
	);

	foreach ( $footer_settings as $key => $config ) {
		$wp_customize->add_setting(
			'switch_graphics_' . $key,
			array(
				'default'           => switch_graphics_get_default( $key ),
				'sanitize_callback' => $config[2],
			)
		);
		$wp_customize->add_control(
			'switch_graphics_' . $key,
			array(
				'label'   => $config[0],
				'section' => 'switch_graphics_footer',
				'type'    => $config[1],
			)
		);
	}
}
add_action( 'customize_register', 'switch_graphics_customize_register' );
