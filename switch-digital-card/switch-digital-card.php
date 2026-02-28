<?php
/**
 * Plugin Name: Switch Digital Card
 * Plugin URI:  https://www.switchgraphics.co.za/
 * Description: Mobile-first digital card with slideshow, share actions, custom links, menu links, and viewport-fit layout.
 * Version:     1.0.2
 * Author:      Switch Graphics (Pty) Ltd
 * Author URI:  https://www.switchgraphics.co.za/
 * Text Domain: switch-digital-card
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Switch_Digital_Card {
    const OPTION_KEY = 'sdc_options';
    const VERSION = '1.0.2';

    /**
     * @var Switch_Digital_Card|null
     */
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_shortcode('switch_digital_card', array($this, 'render_shortcode'));

        add_action('admin_menu', array($this, 'register_admin_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_assets() {
        wp_register_style(
            'sdc-frontend',
            plugin_dir_url(__FILE__) . 'assets/css/frontend.css',
            array(),
            self::VERSION
        );

        wp_register_script(
            'sdc-frontend',
            plugin_dir_url(__FILE__) . 'assets/js/frontend.js',
            array(),
            self::VERSION,
            true
        );
    }

    private function defaults() {
        return array(
            'company_name' => 'Switch Graphics (Pty) Ltd',
            'email' => 'info@switchgraphics.co.za',
            'phone' => '+27681474232',
            'phone_display' => '(+27) 068 147 4232',
            'website_url' => 'https://www.switchgraphics.co.za/',
            'whatsapp_url' => 'https://wa.me/27681474232',
            'facebook_url' => 'https://www.facebook.com/switchgraphics.co.za',
            'instagram_url' => 'https://www.instagram.com/switchgraphics.co.za/',
            'tiktok_url' => 'https://www.tiktok.com/@switchgraphics',
            'menu_location' => 'primary',
            'custom_links' => "Website|https://www.switchgraphics.co.za/\nWhatsApp|https://wa.me/27681474232\nFacebook|https://www.facebook.com/switchgraphics.co.za",
            'slide_images' => "https://www.switchgraphics.co.za/wp-content/uploads/2026/02/3cc75b7cf07efe7bdb278911916bdf81.jpg\nhttps://www.switchgraphics.co.za/wp-content/uploads/2026/02/71f36ac3d6b335755baeab7f26839158.jpg",
            'autoplay_interval' => 3800,
            'cover_height_percent' => 50,
            'wave_height' => 94,
            'cover_gradient_start' => '#000000',
            'cover_gradient_end' => '#000000',
            'wave_color' => '#000000',
            'top_fade_start' => 'rgba(0,0,0,0.85)',
            'top_fade_end' => 'rgba(0,0,0,0)',
            'top_fade_height' => 92,
            'shape_gradient_path' => 'M0,190 L0,132 C95,108 240,156 395,152 C548,148 650,123 748,84 C844,46 918,40 1000,52 L1000,190 Z',
            'shape_black_path' => 'M620,190 L620,170 C700,145 782,97 856,64 C916,38 966,52 1000,78 L1000,190 Z',
            'actions_shift_down' => 36,
            'button_height' => 50,
            'button_font_size' => 13,
            'button_font_weight' => 900,
            'icon_size' => 38,
            'social_icon_size' => 46,
            'save_button_width' => 170,
            'design_width' => 390,
            'design_height' => 860,
            'lock_page_scroll' => 0,
            'fit_between_header_footer' => 1,
        );
    }

    private function get_options() {
        $saved = get_option(self::OPTION_KEY, array());
        if (!is_array($saved)) {
            $saved = array();
        }

        return wp_parse_args($saved, $this->defaults());
    }

    public function register_settings() {
        register_setting(
            'sdc_settings_group',
            self::OPTION_KEY,
            array($this, 'sanitize_options')
        );
    }

    public function sanitize_options($input) {
        $defaults = $this->defaults();
        $input = is_array($input) ? $input : array();
        $out = array();

        $out['company_name'] = sanitize_text_field($input['company_name'] ?? $defaults['company_name']);
        $out['email'] = sanitize_email($input['email'] ?? $defaults['email']);
        $out['phone'] = sanitize_text_field($input['phone'] ?? $defaults['phone']);
        $out['phone_display'] = sanitize_text_field($input['phone_display'] ?? $defaults['phone_display']);
        $out['website_url'] = esc_url_raw($input['website_url'] ?? $defaults['website_url']);
        $out['whatsapp_url'] = esc_url_raw($input['whatsapp_url'] ?? $defaults['whatsapp_url']);
        $out['facebook_url'] = esc_url_raw($input['facebook_url'] ?? $defaults['facebook_url']);
        $out['instagram_url'] = esc_url_raw($input['instagram_url'] ?? $defaults['instagram_url']);
        $out['tiktok_url'] = esc_url_raw($input['tiktok_url'] ?? $defaults['tiktok_url']);
        $out['menu_location'] = sanitize_key($input['menu_location'] ?? $defaults['menu_location']);
        $out['custom_links'] = isset($input['custom_links']) ? wp_kses_post(trim((string) $input['custom_links'])) : $defaults['custom_links'];
        $out['slide_images'] = isset($input['slide_images']) ? wp_kses_post(trim((string) $input['slide_images'])) : $defaults['slide_images'];

        $out['autoplay_interval'] = $this->sanitize_int_in_range($input['autoplay_interval'] ?? $defaults['autoplay_interval'], 1000, 15000, $defaults['autoplay_interval']);
        $out['cover_height_percent'] = $this->sanitize_int_in_range($input['cover_height_percent'] ?? $defaults['cover_height_percent'], 20, 70, $defaults['cover_height_percent']);
        $out['wave_height'] = $this->sanitize_int_in_range($input['wave_height'] ?? $defaults['wave_height'], 30, 200, $defaults['wave_height']);

        $out['cover_gradient_start'] = $this->sanitize_css_color($input['cover_gradient_start'] ?? $defaults['cover_gradient_start'], $defaults['cover_gradient_start']);
        $out['cover_gradient_end'] = $this->sanitize_css_color($input['cover_gradient_end'] ?? $defaults['cover_gradient_end'], $defaults['cover_gradient_end']);
        $out['wave_color'] = $this->sanitize_css_color($input['wave_color'] ?? $defaults['wave_color'], $defaults['wave_color']);
        $out['top_fade_start'] = $this->sanitize_css_color($input['top_fade_start'] ?? $defaults['top_fade_start'], $defaults['top_fade_start']);
        $out['top_fade_end'] = $this->sanitize_css_color($input['top_fade_end'] ?? $defaults['top_fade_end'], $defaults['top_fade_end']);
        $out['top_fade_height'] = $this->sanitize_int_in_range($input['top_fade_height'] ?? $defaults['top_fade_height'], 20, 220, $defaults['top_fade_height']);
        $out['shape_gradient_path'] = $this->sanitize_svg_path($input['shape_gradient_path'] ?? $defaults['shape_gradient_path'], $defaults['shape_gradient_path']);
        $out['shape_black_path'] = $this->sanitize_svg_path($input['shape_black_path'] ?? $defaults['shape_black_path'], $defaults['shape_black_path']);
        $out['actions_shift_down'] = $this->sanitize_int_in_range($input['actions_shift_down'] ?? $defaults['actions_shift_down'], 0, 220, $defaults['actions_shift_down']);

        $out['button_height'] = $this->sanitize_int_in_range($input['button_height'] ?? $defaults['button_height'], 36, 80, $defaults['button_height']);
        $out['button_font_size'] = $this->sanitize_int_in_range($input['button_font_size'] ?? $defaults['button_font_size'], 10, 24, $defaults['button_font_size']);
        $out['button_font_weight'] = $this->sanitize_int_in_range($input['button_font_weight'] ?? $defaults['button_font_weight'], 400, 900, $defaults['button_font_weight']);
        $out['icon_size'] = $this->sanitize_int_in_range($input['icon_size'] ?? $defaults['icon_size'], 26, 64, $defaults['icon_size']);
        $out['social_icon_size'] = $this->sanitize_int_in_range($input['social_icon_size'] ?? $defaults['social_icon_size'], 30, 72, $defaults['social_icon_size']);
        $out['save_button_width'] = $this->sanitize_int_in_range($input['save_button_width'] ?? $defaults['save_button_width'], 120, 260, $defaults['save_button_width']);
        $out['design_width'] = $this->sanitize_int_in_range($input['design_width'] ?? $defaults['design_width'], 320, 520, $defaults['design_width']);
        $out['design_height'] = $this->sanitize_int_in_range($input['design_height'] ?? $defaults['design_height'], 640, 1200, $defaults['design_height']);

        $out['lock_page_scroll'] = empty($input['lock_page_scroll']) ? 0 : 1;
        $out['fit_between_header_footer'] = empty($input['fit_between_header_footer']) ? 0 : 1;

        return $out;
    }

    private function sanitize_int_in_range($value, $min, $max, $fallback) {
        if (!is_numeric($value)) {
            return (int) $fallback;
        }
        $value = (int) $value;
        if ($value < $min) {
            return (int) $min;
        }
        if ($value > $max) {
            return (int) $max;
        }
        return $value;
    }

    private function sanitize_css_color($value, $fallback) {
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }
        if (strtolower($value) === 'transparent') {
            return 'transparent';
        }
        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $value)) {
            return $value;
        }
        if (!preg_match('/^rgba?\((.+)\)$/i', $value, $matches)) {
            return $fallback;
        }
        $parts = array_map('trim', explode(',', $matches[1]));
        if (count($parts) !== 3 && count($parts) !== 4) {
            return $fallback;
        }
        for ($i = 0; $i < 3; $i++) {
            if (!is_numeric($parts[$i])) {
                return $fallback;
            }
            $chan = (int) $parts[$i];
            if ($chan < 0 || $chan > 255) {
                return $fallback;
            }
            $parts[$i] = (string) $chan;
        }
        if (count($parts) === 3) {
            return 'rgb(' . implode(',', $parts) . ')';
        }
        if (!is_numeric($parts[3])) {
            return $fallback;
        }
        $alpha = (float) $parts[3];
        if ($alpha < 0 || $alpha > 1) {
            return $fallback;
        }
        $alpha_text = rtrim(rtrim(sprintf('%.3F', $alpha), '0'), '.');
        return 'rgba(' . $parts[0] . ',' . $parts[1] . ',' . $parts[2] . ',' . $alpha_text . ')';
    }

    private function sanitize_svg_path($value, $fallback) {
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }

        // Allow only SVG path command letters, separators, signs and decimals.
        $clean = preg_replace('/[^MmZzLlHhVvCcSsQqTtAa0-9,\.\-\s]/', '', $value);
        $clean = trim((string) $clean);
        if ($clean === '') {
            return $fallback;
        }

        return $clean;
    }

    public function register_admin_page() {
        add_menu_page(
            __('Switch Digital Card', 'switch-digital-card'),
            __('Switch Digital Card', 'switch-digital-card'),
            'manage_options',
            'switch-digital-card',
            array($this, 'render_admin_page'),
            'dashicons-id-alt',
            58
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $opts = $this->get_options();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Switch Digital Card', 'switch-digital-card'); ?></h1>
            <p><?php esc_html_e('Use shortcode:', 'switch-digital-card'); ?> <code>[switch_digital_card]</code></p>
            <form method="post" action="options.php">
                <?php settings_fields('sdc_settings_group'); ?>
                <h2><?php esc_html_e('Content & Links', 'switch-digital-card'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->text_input_row('company_name', 'Company name', $opts['company_name']); ?>
                    <?php $this->text_input_row('email', 'Email', $opts['email']); ?>
                    <?php $this->text_input_row('phone', 'Phone (international)', $opts['phone']); ?>
                    <?php $this->text_input_row('phone_display', 'Phone display text', $opts['phone_display']); ?>
                    <?php $this->text_input_row('website_url', 'Website URL', $opts['website_url']); ?>
                    <?php $this->text_input_row('whatsapp_url', 'WhatsApp URL', $opts['whatsapp_url']); ?>
                    <?php $this->text_input_row('facebook_url', 'Facebook URL', $opts['facebook_url']); ?>
                    <?php $this->text_input_row('instagram_url', 'Instagram URL', $opts['instagram_url']); ?>
                    <?php $this->text_input_row('tiktok_url', 'TikTok URL', $opts['tiktok_url']); ?>
                    <?php $this->text_input_row('menu_location', 'Theme menu location slug', $opts['menu_location']); ?>
                    <?php $this->textarea_row('custom_links', 'Custom links', $opts['custom_links'], 'One per line: Label|URL'); ?>
                    <?php $this->textarea_row('slide_images', 'Slide image URLs', $opts['slide_images'], 'One image URL per line'); ?>
                </table>

                <h2><?php esc_html_e('Mobile Fit Behavior', 'switch-digital-card'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->number_input_row('autoplay_interval', 'Slideshow interval (ms)', $opts['autoplay_interval']); ?>
                    <?php $this->number_input_row('design_width', 'Design width (px)', $opts['design_width']); ?>
                    <?php $this->number_input_row('design_height', 'Design height (px)', $opts['design_height']); ?>
                    <?php $this->checkbox_row('lock_page_scroll', 'Lock page scroll (optional)', $opts['lock_page_scroll']); ?>
                    <?php $this->checkbox_row('fit_between_header_footer', 'Fit between header and theme footer', $opts['fit_between_header_footer']); ?>
                </table>

                <h2><?php esc_html_e('Colors & Shape', 'switch-digital-card'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->text_input_row('cover_gradient_start', 'Cover gradient start color', $opts['cover_gradient_start']); ?>
                    <?php $this->text_input_row('cover_gradient_end', 'Cover gradient end color', $opts['cover_gradient_end']); ?>
                    <?php $this->text_input_row('wave_color', 'Wave shape color', $opts['wave_color']); ?>
                    <?php $this->text_input_row('top_fade_start', 'Top fade start color', $opts['top_fade_start']); ?>
                    <?php $this->text_input_row('top_fade_end', 'Top fade end color', $opts['top_fade_end']); ?>
                    <?php $this->number_input_row('top_fade_height', 'Top fade height (px)', $opts['top_fade_height']); ?>
                    <?php $this->number_input_row('cover_height_percent', 'Cover height (% of image area)', $opts['cover_height_percent']); ?>
                    <?php $this->number_input_row('wave_height', 'Wave height (px)', $opts['wave_height']); ?>
                    <?php $this->textarea_row('shape_gradient_path', 'Shape path (gradient)', $opts['shape_gradient_path'], 'SVG path d-attribute for the main shape'); ?>
                    <?php $this->textarea_row('shape_black_path', 'Shape path (black overlay)', $opts['shape_black_path'], 'SVG path d-attribute for black overlay hump'); ?>
                    <?php $this->number_input_row('actions_shift_down', 'Actions shift down (px)', $opts['actions_shift_down']); ?>
                </table>

                <h2><?php esc_html_e('Button Sizing & Typography', 'switch-digital-card'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->number_input_row('button_height', 'Button height (px)', $opts['button_height']); ?>
                    <?php $this->number_input_row('button_font_size', 'Button text size (px)', $opts['button_font_size']); ?>
                    <?php $this->number_input_row('button_font_weight', 'Button text weight', $opts['button_font_weight']); ?>
                    <?php $this->number_input_row('icon_size', 'Main icon size (px)', $opts['icon_size']); ?>
                    <?php $this->number_input_row('social_icon_size', 'Social icon size (px)', $opts['social_icon_size']); ?>
                    <?php $this->number_input_row('save_button_width', 'Save button width (px)', $opts['save_button_width']); ?>
                </table>

                <?php submit_button(__('Save Settings', 'switch-digital-card')); ?>
            </form>
        </div>
        <?php
    }

    private function field_name($name) {
        return self::OPTION_KEY . '[' . $name . ']';
    }

    private function text_input_row($name, $label, $value) {
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label></th>
            <td><input class="regular-text" type="text" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($this->field_name($name)); ?>" value="<?php echo esc_attr($value); ?>"></td>
        </tr>
        <?php
    }

    private function number_input_row($name, $label, $value) {
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label></th>
            <td><input class="small-text" type="number" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($this->field_name($name)); ?>" value="<?php echo esc_attr($value); ?>"></td>
        </tr>
        <?php
    }

    private function textarea_row($name, $label, $value, $description = '') {
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label></th>
            <td>
                <textarea class="large-text code" rows="6" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($this->field_name($name)); ?>"><?php echo esc_textarea($value); ?></textarea>
                <?php if ($description !== '') : ?>
                    <p class="description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    private function checkbox_row($name, $label, $value) {
        ?>
        <tr>
            <th scope="row"><?php echo esc_html($label); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr($this->field_name($name)); ?>" value="1" <?php checked((int) $value, 1); ?>>
                    <?php esc_html_e('Enabled', 'switch-digital-card'); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    public function render_shortcode($atts = array()) {
        $opts = $this->get_options();

        wp_enqueue_style('sdc-frontend');
        wp_enqueue_script('sdc-frontend');

        $id = 'sdc-card-' . wp_rand(1000, 999999);
        $css_vars = $this->build_css_vars($opts);

        $config = array(
            'id' => $id,
            'companyName' => $opts['company_name'],
            'email' => $opts['email'],
            'phone' => $opts['phone'],
            'phoneDisplay' => $opts['phone_display'],
            'websiteUrl' => $opts['website_url'],
            'whatsappUrl' => $opts['whatsapp_url'],
            'facebookUrl' => $opts['facebook_url'],
            'instagramUrl' => $opts['instagram_url'],
            'tiktokUrl' => $opts['tiktok_url'],
            'slides' => $this->parse_lines_to_urls($opts['slide_images']),
            'customLinks' => $this->parse_custom_links($opts['custom_links']),
            'menuLinks' => $this->get_menu_links($opts['menu_location']),
            'autoplayInterval' => (int) $opts['autoplay_interval'],
            'designWidth' => (int) $opts['design_width'],
            'designHeight' => (int) $opts['design_height'],
            'lockPageScroll' => (int) $opts['lock_page_scroll'] === 1,
            'fitBetweenHeaderFooter' => (int) $opts['fit_between_header_footer'] === 1,
        );

        $script = 'window.SDC_CARDS = window.SDC_CARDS || {}; window.SDC_CARDS[' . wp_json_encode($id) . '] = ' . wp_json_encode($config) . ';';
        wp_add_inline_script('sdc-frontend', $script, 'before');

        $grad_id = sanitize_html_class($id . '-grad');
        $shape_gradient_path = esc_attr($opts['shape_gradient_path']);
        $shape_black_path = esc_attr($opts['shape_black_path']);

        ob_start();
        ?>
        <div id="<?php echo esc_attr($id); ?>" class="sdc-root" style="<?php echo esc_attr($css_vars); ?>">
            <div class="sdc-stage">
                <div class="sdc-hero">
                    <div class="sdc-slides"></div>
                    <div class="sdc-dots"></div>
                </div>

                <section class="sdc-cover">
                    <svg class="sdc-cover-wave" viewBox="0 0 1000 190" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="<?php echo esc_attr($grad_id); ?>" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0%" stop-color="<?php echo esc_attr($opts['cover_gradient_start']); ?>"></stop>
                                <stop offset="100%" stop-color="<?php echo esc_attr($opts['cover_gradient_end']); ?>"></stop>
                            </linearGradient>
                        </defs>
                        <path d="<?php echo $shape_gradient_path; ?>" fill="url(#<?php echo esc_attr($grad_id); ?>)"></path>
                        <path d="<?php echo $shape_black_path; ?>" fill="<?php echo esc_attr($opts['wave_color']); ?>"></path>
                    </svg>

                    <div class="sdc-content">
                        <div class="sdc-actions">
                            <a class="sdc-pill sdc-email" href="#">
                                <span class="sdc-icon sdc-dark">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill="currentColor" d="M3 5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V5.25Zm1.5.55 7.5 5.1 7.5-5.1v-.05a.75.75 0 0 0-.75-.75H5.25a.75.75 0 0 0-.75.75v.05Zm15 1.8-6.6 4.5a1.5 1.5 0 0 1-1.7 0l-6.7-4.5v11.15c0 .41.34.75.75.75h13.5c.41 0 .75-.34.75-.75V7.6Z"/>
                                    </svg>
                                </span>
                                <span class="sdc-text sdc-email-text"></span>
                            </a>

                            <div class="sdc-pill sdc-wa-pill">
                                <a class="sdc-wa-main" href="#" target="_blank" rel="noopener">
                                    <span class="sdc-icon sdc-wa">
                                        <img src="https://cdn.simpleicons.org/whatsapp/ffffff" alt="WhatsApp">
                                    </span>
                                    <span class="sdc-text sdc-wa-text"></span>
                                </a>
                                <button class="sdc-share-btn" type="button" aria-label="<?php esc_attr_e('Share number', 'switch-digital-card'); ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill="currentColor" d="M12.75 3a.75.75 0 0 0 0 1.5h5.69l-8.72 8.72a.75.75 0 1 0 1.06 1.06l8.72-8.72v5.69a.75.75 0 0 0 1.5 0V3.75A.75.75 0 0 0 20.25 3h-7.5ZM5.25 6A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75v-5.25a.75.75 0 0 0-1.5 0v5.25c0 .41-.34.75-.75.75H5.25a.75.75 0 0 1-.75-.75V8.25c0-.41.34-.75.75-.75h5.25a.75.75 0 0 0 0-1.5H5.25Z"/>
                                    </svg>
                                </button>
                            </div>

                            <button class="sdc-pill sdc-other" type="button">
                                <span class="sdc-icon sdc-dark">
                                    <img src="https://cdn.simpleicons.org/linktree/ffffff" alt="Links">
                                </span>
                                <span class="sdc-text"><?php esc_html_e('OTHER LINKS', 'switch-digital-card'); ?></span>
                            </button>
                        </div>

                        <div class="sdc-bottom">
                            <div class="sdc-socials">
                                <a class="sdc-social sdc-fb" href="#" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Facebook', 'switch-digital-card'); ?>"><img src="https://cdn.simpleicons.org/facebook/ffffff" alt="Facebook"></a>
                                <a class="sdc-social sdc-ig" href="#" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Instagram', 'switch-digital-card'); ?>"><img src="https://cdn.simpleicons.org/instagram/ffffff" alt="Instagram"></a>
                                <a class="sdc-social sdc-tt" href="#" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('TikTok', 'switch-digital-card'); ?>"><img src="https://cdn.simpleicons.org/tiktok/ffffff" alt="TikTok"></a>
                            </div>
                            <a class="sdc-save" href="#">
                                <span class="sdc-save-copy"><span><?php esc_html_e('SAVE TO', 'switch-digital-card'); ?></span><span><?php esc_html_e('CONTACTS', 'switch-digital-card'); ?></span></span>
                                <span class="sdc-plus">+</span>
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="sdc-modal" hidden>
            <div class="sdc-modal-card" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Other links', 'switch-digital-card'); ?>">
                <button type="button" class="sdc-modal-close" aria-label="<?php esc_attr_e('Close', 'switch-digital-card'); ?>">×</button>
                <div class="sdc-links-list"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function build_css_vars($opts) {
        $vars = array(
            '--sdc-cover-height:' . ((int) $opts['cover_height_percent']) . '%',
            '--sdc-wave-height:' . ((int) $opts['wave_height']) . 'px',
            '--sdc-cover-gradient-start:' . $opts['cover_gradient_start'],
            '--sdc-cover-gradient-end:' . $opts['cover_gradient_end'],
            '--sdc-wave-color:' . $opts['wave_color'],
            '--sdc-top-fade-start:' . $opts['top_fade_start'],
            '--sdc-top-fade-end:' . $opts['top_fade_end'],
            '--sdc-top-fade-height:' . ((int) $opts['top_fade_height']) . 'px',
            '--sdc-actions-shift:' . ((int) $opts['actions_shift_down']) . 'px',
            '--sdc-pill-height:' . ((int) $opts['button_height']) . 'px',
            '--sdc-pill-font-size:' . ((int) $opts['button_font_size']) . 'px',
            '--sdc-pill-font-weight:' . ((int) $opts['button_font_weight']),
            '--sdc-icon-size:' . ((int) $opts['icon_size']) . 'px',
            '--sdc-social-size:' . ((int) $opts['social_icon_size']) . 'px',
            '--sdc-save-width:' . ((int) $opts['save_button_width']) . 'px',
        );

        return implode(';', $vars) . ';';
    }

    private function parse_lines_to_urls($text) {
        $lines = preg_split('/\r\n|\r|\n/', (string) $text);
        $urls = array();
        foreach ($lines as $line) {
            $url = esc_url_raw(trim($line));
            if (!empty($url)) {
                $urls[] = $url;
            }
        }
        return array_values(array_unique($urls));
    }

    private function parse_custom_links($text) {
        $lines = preg_split('/\r\n|\r|\n/', (string) $text);
        $links = array();
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = explode('|', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $label = sanitize_text_field(trim($parts[0]));
            $url = esc_url_raw(trim($parts[1]));
            if ($label !== '' && $url !== '') {
                $links[] = array('label' => $label, 'href' => $url);
            }
        }
        return $links;
    }

    private function get_menu_links($location_slug) {
        $locations = get_nav_menu_locations();
        $menu_id = 0;

        if ($location_slug && isset($locations[$location_slug])) {
            $menu_id = (int) $locations[$location_slug];
        }

        if (!$menu_id && is_array($locations)) {
            foreach ($locations as $loc_menu_id) {
                if (!empty($loc_menu_id)) {
                    $menu_id = (int) $loc_menu_id;
                    break;
                }
            }
        }

        if (!$menu_id) {
            $menus = wp_get_nav_menus();
            if (!empty($menus) && !is_wp_error($menus)) {
                $first_menu = reset($menus);
                if ($first_menu && !empty($first_menu->term_id)) {
                    $menu_id = (int) $first_menu->term_id;
                }
            }
        }

        if (!$menu_id) {
            return array();
        }

        $items = wp_get_nav_menu_items($menu_id, array('update_post_term_cache' => false));
        if (empty($items) || is_wp_error($items)) {
            return array();
        }

        usort($items, function($a, $b) {
            return (int) $a->menu_order <=> (int) $b->menu_order;
        });

        $links = array();
        foreach ($items as $item) {
            if (empty($item->title) || empty($item->url)) {
                continue;
            }
            $links[] = array(
                'label' => wp_strip_all_tags($item->title),
                'href' => $item->url,
            );
        }

        return $links;
    }
}

Switch_Digital_Card::instance();
