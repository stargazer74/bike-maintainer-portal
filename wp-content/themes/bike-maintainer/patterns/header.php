<?php
/**
 * Title: Header
 * Slug: bike-maintainer/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * @package BikeMaintainer
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"0.9rem","bottom":"0.9rem","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"bottom":{"color":"var:preset|color|accent-6","width":"1px","style":"solid"}}},"backgroundColor":"accent-2","layout":{"type":"constrained","wideSize":"1280px"}} -->
<div class="wp-block-group alignfull has-accent-2-background-color has-background" style="border-bottom-color:var(--wp--preset--color--accent-6);border-bottom-style:solid;border-bottom-width:1px;padding-top:0.9rem;padding-right:var(--wp--preset--spacing--50);padding-bottom:0.9rem;padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:group {"style":{"spacing":{"blockGap":"0.6rem"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<div class="wp-block-group">
			<!-- wp:image {"width":"36px","height":"36px","linkDestination":"custom","className":"bm-logo"} -->
			<figure class="wp-block-image is-resized bm-logo"><a href="<?php echo esc_url( bike_maintainer_home_url() ); ?>"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo.svg' ) ); ?>" alt="<?php esc_attr_e( 'Bike Maintainer – home page', 'bike-maintainer' ); ?>" style="width:36px;height:36px"/></a></figure>
			<!-- /wp:image -->

			<!-- wp:paragraph {"className":"bm-brand","style":{"typography":{"fontWeight":"800","letterSpacing":"-0.02em","fontSize":"1.125rem"},"elements":{"link":{"color":{"text":"var:preset|color|white"}}}}} -->
			<p class="bm-brand has-link-color" style="font-size:1.125rem;font-weight:800;letter-spacing:-0.02em"><a href="<?php echo esc_url( bike_maintainer_home_url() ); ?>">bike-maintainer<mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-accent-1-color">.de</mark></a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"style":{"spacing":{"blockGap":"1.5rem"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
		<div class="wp-block-group">
			<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"},"ariaLabel":"<?php echo esc_html__( 'Main navigation', 'bike-maintainer' ); ?>"} -->
				<!-- wp:navigation-link {"label":"<?php echo esc_html__( 'Features', 'bike-maintainer' ); ?>","url":"<?php echo esc_url( bike_maintainer_home_url( 'funktionen' ) ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"<?php echo esc_html__( 'Self-hosting', 'bike-maintainer' ); ?>","url":"<?php echo esc_url( bike_maintainer_home_url( 'betrieb' ) ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"<?php echo esc_html__( 'FAQ', 'bike-maintainer' ); ?>","url":"<?php echo esc_url( bike_maintainer_home_url( 'faq' ) ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"<?php echo esc_html__( 'Forum', 'bike-maintainer' ); ?>","url":"<?php echo esc_url( bike_maintainer_forum_url() ); ?>","kind":"custom"} /-->
			<!-- /wp:navigation -->

			<!-- wp:shortcode -->
			[bm_language_switcher]
			<!-- /wp:shortcode -->

			<!-- wp:buttons {"className":"bm-header-app"} -->
			<div class="wp-block-buttons bm-header-app">
				<!-- wp:button {"style":{"spacing":{"padding":{"top":"0.55rem","bottom":"0.55rem","left":"1.1rem","right":"1.1rem"}},"typography":{"fontSize":"0.9rem"}}} -->
				<div class="wp-block-button"><a class="wp-block-button__link has-custom-font-size wp-element-button" href="#bm-app" style="padding-top:0.55rem;padding-right:1.1rem;padding-bottom:0.55rem;padding-left:1.1rem;font-size:0.9rem"><?php esc_html_e( 'Open the app', 'bike-maintainer' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
