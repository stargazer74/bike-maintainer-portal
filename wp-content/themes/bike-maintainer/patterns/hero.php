<?php
/**
 * Title: Hero with app preview
 * Slug: bike-maintainer/hero
 * Categories: bike-maintainer
 * Description: Start of the landing page: heading, text, two buttons, benefits and a preview of the app.
 *
 * @package BikeMaintainer
 */

$bm_heading = wp_kses(
	sprintf(
		/* translators: %s: highlighted part of the heading, "your bike and its maintenance". */
		__( 'Keep control of %s.', 'bike-maintainer' ),
		'<mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-accent-1-color">' . esc_html__( 'your bike and its maintenance', 'bike-maintainer' ) . '</mark>'
	),
	array(
		'mark' => array(
			'style' => true,
			'class' => true,
		),
	)
);

?>
<!-- wp:group {"align":"full","className":"is-style-glow bm-hero","style":{"spacing":{"padding":{"top":"clamp(3rem, 8vw, 6rem)","bottom":"clamp(4rem, 10vw, 8rem)","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","wideSize":"1280px"}} -->
<div class="wp-block-group alignfull is-style-glow bm-hero" style="padding-top:clamp(3rem, 8vw, 6rem);padding-right:var(--wp--preset--spacing--50);padding-bottom:clamp(4rem, 10vw, 8rem);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide","style":{"spacing":{"blockGap":{"left":"clamp(2rem, 5vw, 4rem)"}}}} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"58%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%">
			<!-- wp:paragraph {"className":"is-style-badge"} -->
			<p class="is-style-badge"><?php esc_html_e( 'Open source · in the portal, on your own hardware and as an Android app', 'bike-maintainer' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(2.25rem, 5vw, 3.75rem)","fontWeight":"900"}}} -->
			<h1 class="wp-block-heading" style="font-size:clamp(2.25rem, 5vw, 3.75rem);font-weight:900"><?php echo $bm_heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with wp_kses() above. ?></h1>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"style":{"typography":{"fontSize":"1.125rem","fontWeight":"300","lineHeight":"1.7"}}} -->
			<p style="font-size:1.125rem;font-weight:300;line-height:1.7"><?php echo wp_kses( __( 'No more forgotten oil changes and piles of paper. Bike Maintainer reminds you by mileage or time – whichever comes first – and keeps your service book: <strong>in the portal</strong> or for free <strong>on your own computer</strong>, for example a Raspberry Pi.', 'bike-maintainer' ), array( 'strong' => array() ) ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"2rem"},"blockGap":"1rem"}}} -->
			<div class="wp-block-buttons" style="margin-top:2rem">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#bm-app"><?php esc_html_e( 'Open the app', 'bike-maintainer' ); ?></a></div>
				<!-- /wp:button -->

				<!-- wp:button {"className":"is-style-secondary"} -->
				<div class="wp-block-button is-style-secondary"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( bike_maintainer_home_url( 'betrieb' ) ); ?>"><?php esc_html_e( 'Host it yourself', 'bike-maintainer' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->

			<!-- wp:list {"className":"is-style-checklist","style":{"spacing":{"margin":{"top":"2rem"}}}} -->
			<ul class="wp-block-list is-style-checklist" style="margin-top:2rem">
				<!-- wp:list-item -->
				<li><?php esc_html_e( 'No ads, no tracking', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Open source code (MIT licence)', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Maintenance report as PDF', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Android app without Google services', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"is-style-preview"} -->
			<figure class="wp-block-image size-full is-style-preview"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/app-preview.webp' ) ); ?>" alt="<?php esc_attr_e( 'Vehicle page of the app: mileage and maintenance due for a motorcycle', 'bike-maintainer' ); ?>"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"bm-hero-watermark"} -->
	<figure class="wp-block-image size-full bm-hero-watermark"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-emblem.webp' ) ); ?>" alt=""/></figure>
	<!-- /wp:image -->
</div>
<!-- /wp:group -->
