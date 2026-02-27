<?php
/**
 * Main theme template.
 *
 * @package switch-graphics
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('sg-post'); ?>>
            <h1 class="sg-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
            <div class="sg-post-meta"><?php echo esc_html(get_the_date()); ?></div>
            <div class="sg-post-content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>

    <?php the_posts_pagination(); ?>
<?php else : ?>
    <article class="sg-post">
        <h1 class="sg-post-title"><?php esc_html_e('Welcome', 'switch-graphics'); ?></h1>
        <div class="sg-post-content">
            <p><?php esc_html_e('Add content from WordPress admin and assign your menu in Appearance > Menus.', 'switch-graphics'); ?></p>
        </div>
    </article>
<?php endif; ?>

<?php
get_footer();
