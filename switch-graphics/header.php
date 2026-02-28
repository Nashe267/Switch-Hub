<?php
/**
 * Theme header template.
 *
 * @package switch-graphics
 */

if (!defined('ABSPATH')) {
    exit;
}

$menu_title = sanitize_text_field((string) sg_theme_mod('menu_title'));
if ($menu_title === '') {
    $menu_title = 'Switch Hub';
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="sg-header">
    <div class="sg-branding">
        <?php if (has_custom_logo()) : ?>
            <?php the_custom_logo(); ?>
        <?php else : ?>
            <a class="sg-site-title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
        <?php endif; ?>
    </div>

    <button id="sgMenuToggle" class="sg-menu-btn" type="button" aria-label="<?php esc_attr_e('Open menu', 'switch-graphics'); ?>" aria-controls="sgSideMenu" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>
</header>

<div id="sgMenuOverlay" class="sg-menu-overlay"></div>

<aside id="sgSideMenu" class="sg-side-menu" aria-hidden="true">
    <div class="sg-side-menu-header">
        <h2 class="sg-side-menu-title"><?php echo esc_html($menu_title); ?></h2>
        <button id="sgMenuClose" class="sg-side-menu-close" type="button" aria-label="<?php esc_attr_e('Close menu', 'switch-graphics'); ?>">×</button>
    </div>

    <nav aria-label="<?php esc_attr_e('Primary menu', 'switch-graphics'); ?>">
        <?php
        if (has_nav_menu('primary')) {
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'sg-side-menu-list',
                    'fallback_cb' => false,
                )
            );
        } else {
            sg_render_fallback_menu();
        }
        ?>
    </nav>
</aside>

<main class="sg-main">
