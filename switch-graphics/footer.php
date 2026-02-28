<?php
/**
 * Theme footer template.
 *
 * @package switch-graphics
 */

if (!defined('ABSPATH')) {
    exit;
}

$footer_year = sanitize_text_field((string) sg_theme_mod('footer_year'));
if ($footer_year === '') {
    $footer_year = gmdate('Y');
}

$footer_company = sanitize_text_field((string) sg_theme_mod('footer_company'));
if ($footer_company === '') {
    $footer_company = 'Switch Graphics (Pty) Ltd';
}

$footer_link = esc_url((string) sg_theme_mod('footer_link'));
if ($footer_link === '') {
    $footer_link = 'https://www.switchgraphics.co.za/';
}
?>
    </main>

    <footer class="sg-footer">
        <p>
            <strong><?php echo esc_html($footer_year); ?></strong>
            <strong>&copy;</strong>
            Designed &amp; Powered By:
            <a href="<?php echo esc_url($footer_link); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($footer_company); ?></a>
        </p>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>
