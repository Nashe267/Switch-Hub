<?php
/**
 * Switch Graphics theme functions.
 *
 * @package switch-graphics
 */

if (!defined('ABSPATH')) {
    exit;
}

function sg_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');

    register_nav_menus(
        array(
            'primary' => __('Primary Menu', 'switch-graphics'),
        )
    );
}
add_action('after_setup_theme', 'sg_theme_setup');

function sg_theme_defaults() {
    return array(
        'menu_title' => 'Switch Hub',
        'menu_icon_fill_start' => '#FF6600',
        'menu_icon_fill_end' => '#ff8533',
        'menu_icon_outline_color' => '#666666',
        'menu_icon_outline_width' => '1',
        'footer_year' => gmdate('Y'),
        'footer_company' => 'Switch Graphics (Pty) Ltd',
        'footer_link' => 'https://www.switchgraphics.co.za/',
    );
}

function sg_theme_mod($name) {
    $defaults = sg_theme_defaults();
    $default = isset($defaults[$name]) ? $defaults[$name] : '';
    return get_theme_mod($name, $default);
}

function sg_sanitize_outline_width($value) {
    $value = is_numeric($value) ? (float) $value : 1;
    $value = max(0, min(8, $value));
    return (string) $value;
}

function sg_enqueue_assets() {
    wp_enqueue_style('switch-graphics-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
    wp_enqueue_script('switch-graphics-theme', get_template_directory_uri() . '/assets/theme.js', array(), wp_get_theme()->get('Version'), true);

    $fill_start = sanitize_hex_color(sg_theme_mod('menu_icon_fill_start'));
    $fill_end = sanitize_hex_color(sg_theme_mod('menu_icon_fill_end'));
    $outline_color = sanitize_hex_color(sg_theme_mod('menu_icon_outline_color'));
    $outline_width = sg_sanitize_outline_width(sg_theme_mod('menu_icon_outline_width'));

    $inline_css = ':root{' .
        '--sg-menu-fill-start:' . ($fill_start ? $fill_start : '#FF6600') . ';' .
        '--sg-menu-fill-end:' . ($fill_end ? $fill_end : '#ff8533') . ';' .
        '--sg-menu-outline-color:' . ($outline_color ? $outline_color : '#666666') . ';' .
        '--sg-menu-outline-width:' . $outline_width . 'px;' .
    '}';

    wp_add_inline_style('switch-graphics-style', $inline_css);
}
add_action('wp_enqueue_scripts', 'sg_enqueue_assets');

function sg_render_fallback_menu() {
    echo '<ul class="sg-side-menu-list">';
    wp_list_pages(
        array(
            'title_li' => '',
        )
    );
    echo '</ul>';
}

function sg_customize_register($wp_customize) {
    $wp_customize->add_section(
        'sg_menu_section',
        array(
            'title' => __('Menu (burger)', 'switch-graphics'),
            'priority' => 30,
        )
    );

    $wp_customize->add_setting(
        'menu_title',
        array(
            'default' => sg_theme_defaults()['menu_title'],
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'menu_title',
        array(
            'type' => 'text',
            'label' => __('Menu title', 'switch-graphics'),
            'section' => 'sg_menu_section',
        )
    );

    $wp_customize->add_setting(
        'menu_icon_fill_start',
        array(
            'default' => sg_theme_defaults()['menu_icon_fill_start'],
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'menu_icon_fill_start',
            array(
                'label' => __('Menu icon fill (gradient start)', 'switch-graphics'),
                'section' => 'sg_menu_section',
            )
        )
    );

    $wp_customize->add_setting(
        'menu_icon_fill_end',
        array(
            'default' => sg_theme_defaults()['menu_icon_fill_end'],
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'menu_icon_fill_end',
            array(
                'label' => __('Menu icon fill (gradient end)', 'switch-graphics'),
                'section' => 'sg_menu_section',
            )
        )
    );

    $wp_customize->add_setting(
        'menu_icon_outline_color',
        array(
            'default' => sg_theme_defaults()['menu_icon_outline_color'],
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'menu_icon_outline_color',
            array(
                'label' => __('Menu icon outline color', 'switch-graphics'),
                'section' => 'sg_menu_section',
            )
        )
    );

    $wp_customize->add_setting(
        'menu_icon_outline_width',
        array(
            'default' => sg_theme_defaults()['menu_icon_outline_width'],
            'sanitize_callback' => 'sg_sanitize_outline_width',
        )
    );
    $wp_customize->add_control(
        'menu_icon_outline_width',
        array(
            'type' => 'number',
            'label' => __('Menu icon outline thickness (px)', 'switch-graphics'),
            'section' => 'sg_menu_section',
            'input_attrs' => array(
                'min' => 0,
                'max' => 8,
                'step' => 0.1,
            ),
        )
    );

    $wp_customize->add_section(
        'sg_footer_section',
        array(
            'title' => __('Footer Content', 'switch-graphics'),
            'priority' => 31,
        )
    );

    $wp_customize->add_setting(
        'footer_year',
        array(
            'default' => sg_theme_defaults()['footer_year'],
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'footer_year',
        array(
            'type' => 'text',
            'label' => __('Footer year', 'switch-graphics'),
            'section' => 'sg_footer_section',
        )
    );

    $wp_customize->add_setting(
        'footer_company',
        array(
            'default' => sg_theme_defaults()['footer_company'],
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    $wp_customize->add_control(
        'footer_company',
        array(
            'type' => 'text',
            'label' => __('Footer company name', 'switch-graphics'),
            'section' => 'sg_footer_section',
        )
    );

    $wp_customize->add_setting(
        'footer_link',
        array(
            'default' => sg_theme_defaults()['footer_link'],
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        'footer_link',
        array(
            'type' => 'url',
            'label' => __('Footer company link', 'switch-graphics'),
            'section' => 'sg_footer_section',
        )
    );
}
add_action('customize_register', 'sg_customize_register');
