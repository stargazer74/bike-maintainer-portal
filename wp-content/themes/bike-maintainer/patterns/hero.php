<?php
/**
 * Title: Hero mit App-Vorschau
 * Slug: bike-maintainer/hero
 * Categories: bike-maintainer
 * Description: Einstieg der Landing Page: Überschrift, Text, zwei Buttons, Vorteile und eine Vorschau der App.
 *
 * @package BikeMaintainer
 */

?>
<!-- wp:group {"align":"full","className":"is-style-glow","style":{"spacing":{"padding":{"top":"clamp(3rem, 8vw, 6rem)","bottom":"clamp(4rem, 10vw, 8rem)","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","wideSize":"1280px"}} -->
<div class="wp-block-group alignfull is-style-glow" style="padding-top:clamp(3rem, 8vw, 6rem);padding-right:var(--wp--preset--spacing--50);padding-bottom:clamp(4rem, 10vw, 8rem);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide","style":{"spacing":{"blockGap":{"left":"clamp(2rem, 5vw, 4rem)"}}}} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"58%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%">
			<!-- wp:paragraph {"className":"is-style-badge"} -->
			<p class="is-style-badge">Open Source · im Portal oder auf eigener Hardware</p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(2.25rem, 5vw, 3.75rem)","fontWeight":"900"}}} -->
			<h1 class="wp-block-heading" style="font-size:clamp(2.25rem, 5vw, 3.75rem);font-weight:900">Behalte die Kontrolle über dein <mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-accent-1-color">Bike und seine Wartung</mark>.</h1>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"style":{"typography":{"fontSize":"1.125rem","fontWeight":"300","lineHeight":"1.7"}}} -->
			<p style="font-size:1.125rem;font-weight:300;line-height:1.7">Schluss mit vergessenen Ölwechseln und Zettelwirtschaft. Bike Maintainer erinnert nach Kilometerstand oder Zeit – was zuerst kommt – und führt dein Wartungsheft: <strong>im Portal</strong> oder kostenlos <strong>auf deinem eigenen Rechner</strong>, zum Beispiel einem Raspberry Pi.</p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"2rem"},"blockGap":"1rem"}}} -->
			<div class="wp-block-buttons" style="margin-top:2rem">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#bm-app">Zur App</a></div>
				<!-- /wp:button -->

				<!-- wp:button {"className":"is-style-secondary"} -->
				<div class="wp-block-button is-style-secondary"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/#betrieb' ) ); ?>">Selbst betreiben</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->

			<!-- wp:list {"className":"is-style-checklist","style":{"spacing":{"margin":{"top":"2rem"}}}} -->
			<ul class="wp-block-list is-style-checklist" style="margin-top:2rem">
				<!-- wp:list-item -->
				<li>Keine Werbung, kein Tracking</li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li>Quellcode offen (MIT-Lizenz)</li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li>Wartungsbericht als PDF</li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"is-style-preview"} -->
			<figure class="wp-block-image size-full is-style-preview"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/app-preview.webp' ) ); ?>" alt="Fahrzeugseite der App: Kilometerstand und fällige Wartungen eines Motorrads"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
