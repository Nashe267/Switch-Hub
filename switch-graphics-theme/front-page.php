<?php
/**
 * Front page template.
 *
 * @package Switch_Graphics_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hero_background = switch_graphics_get_theme_mod( 'hero_background' );

if ( ! $hero_background && get_header_image() ) {
	$hero_background = get_header_image();
}

$services = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$services[] = array(
		'icon'  => switch_graphics_get_theme_mod( "service_{$i}_icon" ),
		'title' => switch_graphics_get_theme_mod( "service_{$i}_title" ),
		'text'  => switch_graphics_get_theme_mod( "service_{$i}_text" ),
	);
}

$products = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$products[] = array(
		'title' => switch_graphics_get_theme_mod( "product_{$i}_title" ),
		'text'  => switch_graphics_get_theme_mod( "product_{$i}_text" ),
		'media' => 'sgt-media-' . $i,
	);
}

$testimonials = array(
	array(
		'name' => __( 'Amanda Lee', 'switch-graphics-theme' ),
		'role' => __( 'Marketing Manager', 'switch-graphics-theme' ),
		'text' => __( 'Switch Graphics delivered our campaign materials ahead of schedule. The print quality and finishing were excellent.', 'switch-graphics-theme' ),
	),
	array(
		'name' => __( 'Daniel Brooks', 'switch-graphics-theme' ),
		'role' => __( 'Event Coordinator', 'switch-graphics-theme' ),
		'text' => __( 'We ordered banners, tickets, and promo signage from one place. The team was fast, helpful, and very professional.', 'switch-graphics-theme' ),
	),
	array(
		'name' => __( 'Sophia Martin', 'switch-graphics-theme' ),
		'role' => __( 'Startup Founder', 'switch-graphics-theme' ),
		'text' => __( 'From logo touch-ups to final packaging, this workflow made our launch branding feel polished and premium.', 'switch-graphics-theme' ),
	),
);
?>

<main id="primary" class="site-main switch-homepage">
	<section class="sgt-hero section-space" style="background-image: linear-gradient(120deg, rgba(15, 23, 42, 0.88), rgba(15, 23, 42, 0.62)), url('<?php echo esc_url( $hero_background ); ?>');">
		<div class="container">
			<p class="sgt-eyebrow"><?php echo esc_html( switch_graphics_get_theme_mod( 'hero_eyebrow' ) ); ?></p>
			<h1><?php echo esc_html( switch_graphics_get_theme_mod( 'hero_title' ) ); ?></h1>
			<p class="sgt-hero-text"><?php echo esc_html( switch_graphics_get_theme_mod( 'hero_text' ) ); ?></p>
			<div class="sgt-hero-actions">
				<a class="sgt-btn" href="<?php echo esc_url( switch_graphics_get_theme_mod( 'hero_primary_url' ) ); ?>">
					<?php echo esc_html( switch_graphics_get_theme_mod( 'hero_primary_label' ) ); ?>
				</a>
				<a class="sgt-btn sgt-btn-alt" href="<?php echo esc_url( switch_graphics_get_theme_mod( 'hero_secondary_url' ) ); ?>">
					<?php echo esc_html( switch_graphics_get_theme_mod( 'hero_secondary_label' ) ); ?>
				</a>
			</div>
		</div>
	</section>

	<section id="about" class="sgt-about section-space">
		<div class="container sgt-split">
			<div class="sgt-copy">
				<div class="sgt-section-head">
					<p class="sgt-section-subtitle"><?php esc_html_e( 'About Us', 'switch-graphics-theme' ); ?></p>
					<h2><?php echo esc_html( switch_graphics_get_theme_mod( 'about_title' ) ); ?></h2>
				</div>
				<p><?php echo esc_html( switch_graphics_get_theme_mod( 'about_text' ) ); ?></p>
				<ul class="sgt-feature-list">
					<li>
						<i class="fa-solid fa-check" aria-hidden="true"></i>
						<div>
							<h3><?php echo esc_html( switch_graphics_get_theme_mod( 'about_point_1_title' ) ); ?></h3>
							<p><?php echo esc_html( switch_graphics_get_theme_mod( 'about_point_1_text' ) ); ?></p>
						</div>
					</li>
					<li>
						<i class="fa-solid fa-bolt" aria-hidden="true"></i>
						<div>
							<h3><?php echo esc_html( switch_graphics_get_theme_mod( 'about_point_2_title' ) ); ?></h3>
							<p><?php echo esc_html( switch_graphics_get_theme_mod( 'about_point_2_text' ) ); ?></p>
						</div>
					</li>
				</ul>
			</div>
			<div class="sgt-about-image">
				<img src="<?php echo esc_url( switch_graphics_get_theme_mod( 'about_image' ) ); ?>" alt="<?php esc_attr_e( 'Switch Graphics print production', 'switch-graphics-theme' ); ?>">
			</div>
		</div>
	</section>

	<section id="services" class="sgt-services section-space">
		<div class="container">
			<div class="sgt-section-head sgt-center">
				<p class="sgt-section-subtitle"><?php echo esc_html( switch_graphics_get_theme_mod( 'services_subtitle' ) ); ?></p>
				<h2><?php echo esc_html( switch_graphics_get_theme_mod( 'services_title' ) ); ?></h2>
			</div>
			<div class="sgt-grid sgt-grid-3">
				<?php foreach ( $services as $service ) : ?>
					<article class="sgt-card">
						<span class="sgt-card-icon">
							<i class="<?php echo esc_attr( $service['icon'] ); ?>" aria-hidden="true"></i>
						</span>
						<h3><?php echo esc_html( $service['title'] ); ?></h3>
						<p><?php echo esc_html( $service['text'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section id="products" class="sgt-products section-space">
		<div class="container">
			<div class="sgt-section-head sgt-center">
				<p class="sgt-section-subtitle"><?php echo esc_html( switch_graphics_get_theme_mod( 'products_subtitle' ) ); ?></p>
				<h2><?php echo esc_html( switch_graphics_get_theme_mod( 'products_title' ) ); ?></h2>
			</div>
			<div class="sgt-grid sgt-grid-4">
				<?php foreach ( $products as $product ) : ?>
					<article class="sgt-product-card">
						<div class="sgt-product-media <?php echo esc_attr( $product['media'] ); ?>" aria-hidden="true"></div>
						<div class="sgt-product-copy">
							<h3><?php echo esc_html( $product['title'] ); ?></h3>
							<p><?php echo esc_html( $product['text'] ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section id="testimonials" class="sgt-testimonials section-space">
		<div class="container">
			<div class="sgt-section-head sgt-center">
				<p class="sgt-section-subtitle"><?php esc_html_e( 'Testimonials', 'switch-graphics-theme' ); ?></p>
				<h2><?php esc_html_e( 'What Clients Say', 'switch-graphics-theme' ); ?></h2>
			</div>
			<div class="sgt-grid sgt-grid-3">
				<?php foreach ( $testimonials as $testimonial ) : ?>
					<article class="sgt-testimonial-card">
						<p class="sgt-quote">"<?php echo esc_html( $testimonial['text'] ); ?>"</p>
						<h3><?php echo esc_html( $testimonial['name'] ); ?></h3>
						<p class="sgt-testimonial-role"><?php echo esc_html( $testimonial['role'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section id="gallery" class="sgt-gallery section-space">
		<div class="container">
			<div class="sgt-section-head sgt-center">
				<p class="sgt-section-subtitle"><?php esc_html_e( 'Portfolio', 'switch-graphics-theme' ); ?></p>
				<h2><?php esc_html_e( 'Recent Gallery', 'switch-graphics-theme' ); ?></h2>
			</div>
			<div class="sgt-gallery-grid">
				<?php for ( $i = 1; $i <= 6; $i++ ) : ?>
					<figure class="sgt-gallery-item sgt-gallery-item-<?php echo esc_attr( $i ); ?>">
						<span><?php echo esc_html( sprintf( __( 'Sample Project %d', 'switch-graphics-theme' ), $i ) ); ?></span>
					</figure>
				<?php endfor; ?>
			</div>
		</div>
	</section>

	<section id="blog" class="sgt-blog section-space">
		<div class="container">
			<div class="sgt-section-head sgt-center">
				<p class="sgt-section-subtitle"><?php esc_html_e( 'Latest', 'switch-graphics-theme' ); ?></p>
				<h2><?php esc_html_e( 'News', 'switch-graphics-theme' ); ?></h2>
			</div>
			<div class="sgt-grid sgt-grid-3">
				<?php
				$blog_query = new WP_Query(
					array(
						'post_type'           => 'post',
						'posts_per_page'      => 3,
						'ignore_sticky_posts' => true,
					)
				);

				if ( $blog_query->have_posts() ) :
					while ( $blog_query->have_posts() ) :
						$blog_query->the_post();
						?>
						<article <?php post_class( 'sgt-blog-card' ); ?>>
							<a class="sgt-blog-media" href="<?php the_permalink(); ?>">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'large' ); ?>
								<?php else : ?>
									<span class="sgt-blog-fallback" aria-hidden="true"></span>
								<?php endif; ?>
							</a>
							<div class="sgt-blog-copy">
								<p class="sgt-meta"><?php echo esc_html( get_the_date() ); ?></p>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
								<a class="sgt-read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'switch-graphics-theme' ); ?></a>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
				else :
					?>
					<div class="sgt-empty-posts">
						<p><?php esc_html_e( 'Create your first blog posts and they will appear here automatically.', 'switch-graphics-theme' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section id="newsletter" class="sgt-newsletter section-space">
		<div class="container sgt-newsletter-wrap">
			<div>
				<p class="sgt-section-subtitle"><?php esc_html_e( 'Subscribe', 'switch-graphics-theme' ); ?></p>
				<h2><?php echo esc_html( switch_graphics_get_theme_mod( 'newsletter_title' ) ); ?></h2>
				<p><?php echo esc_html( switch_graphics_get_theme_mod( 'newsletter_text' ) ); ?></p>
			</div>
			<div>
				<?php if ( switch_graphics_get_theme_mod( 'newsletter_shortcode' ) ) : ?>
					<?php echo do_shortcode( wp_kses_post( switch_graphics_get_theme_mod( 'newsletter_shortcode' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<form class="sgt-newsletter-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
						<label class="screen-reader-text" for="sgt-newsletter-email"><?php esc_html_e( 'Email address', 'switch-graphics-theme' ); ?></label>
						<input id="sgt-newsletter-email" type="email" name="sgt_email" placeholder="<?php echo esc_attr( switch_graphics_get_theme_mod( 'newsletter_placeholder' ) ); ?>" required>
						<button type="submit"><?php echo esc_html( switch_graphics_get_theme_mod( 'newsletter_button' ) ); ?></button>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			$content = trim( wp_strip_all_tags( get_the_content() ) );
			if ( ! empty( $content ) ) :
				?>
				<section class="sgt-page-content section-space">
					<div class="container entry-content">
						<?php the_content(); ?>
					</div>
				</section>
				<?php
			endif;
		endwhile;
	endif;
	?>
</main>

<?php
get_footer();
