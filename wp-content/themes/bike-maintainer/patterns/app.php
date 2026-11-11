<?php
/**
 * Title: Android app
 * Slug: bike-maintainer/app
 * Categories: bike-maintainer
 * Description: The Android app: what it does as a card, how to get it as a second card with a link to the releases.
 *
 * @package BikeMaintainer
 */

?>
<!-- wp:group {"metadata":{"name":"App"},"anchor":"app","align":"full","style":{"spacing":{"padding":{"top":"clamp(3rem, 8vw, 5rem)","bottom":"clamp(3rem, 8vw, 5rem)","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"top":{"color":"var:preset|color|accent-6","width":"1px","style":"solid"}}},"backgroundColor":"base","layout":{"type":"constrained","wideSize":"1024px"}} -->
<div id="app" class="wp-block-group alignfull has-base-background-color has-background" style="border-top-color:var(--wp--preset--color--accent-6);border-top-style:solid;border-top-width:1px;padding-top:clamp(3rem, 8vw, 5rem);padding-right:var(--wp--preset--spacing--50);padding-bottom:clamp(3rem, 8vw, 5rem);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"bottom":"3rem"},"blockGap":"0.75rem"}},"layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group alignwide" style="margin-bottom:3rem">
		<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow"} -->
		<p class="has-text-align-center is-style-eyebrow"><?php esc_html_e( 'Android app', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.75rem, 4vw, 2.5rem)"}}} -->
		<h2 class="wp-block-heading has-text-align-center" style="font-size:clamp(1.75rem, 4vw, 2.5rem)"><?php esc_html_e( 'Your service book in your pocket', 'bike-maintainer' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","textColor":"accent-4"} -->
		<p class="has-text-align-center has-accent-4-color has-text-color"><?php esc_html_e( 'The app works with the portal and with your own server – and gets by without Google services.', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"2rem"}}}} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"className":"is-style-card","style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"}}}} -->
		<div class="wp-block-column is-style-card" style="padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem">
			<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
			<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/smartphone.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading"><?php esc_html_e( 'At the bike, not at the desk', 'bike-maintainer' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:list {"className":"is-style-checklist"} -->
			<ul class="wp-block-list is-style-checklist">
				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Reminders of due maintenance as notifications', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Record maintenance with parts, costs and measured values', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Photograph filled-in worksheets – the values are read on the phone', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'For accounts in the portal and for household mode', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"is-style-card","style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"}}}} -->
		<div class="wp-block-column is-style-card" style="padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem">
			<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
			<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/file-text.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading"><?php esc_html_e( 'Installation', 'bike-maintainer' ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.95rem"}}} -->
			<p class="has-accent-4-color has-text-color" style="font-size:0.95rem"><?php esc_html_e( 'The signed APK is on GitHub, for Android 10 or newer. Updates come automatically with Obtainium, or you install each new version by hand. You create your portal account in the web app; the app only signs in.', 'bike-maintainer' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:list {"className":"is-style-checklist"} -->
			<ul class="wp-block-list is-style-checklist">
				<!-- wp:list-item -->
				<li><?php esc_html_e( 'No Google services needed', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Open source (MIT licence)', 'bike-maintainer' ); ?></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->

			<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
			<div class="wp-block-buttons" style="margin-top:1.5rem">
				<!-- wp:button {"width":100,"className":"is-style-secondary"} -->
				<div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-secondary"><a class="wp-block-button__link wp-element-button" href="https://github.com/stargazer74/bike-maintainer-app/releases"><?php esc_html_e( 'Download on GitHub', 'bike-maintainer' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
