<?php
/**
 * Title: Portal oder selbst betreiben
 * Slug: bike-maintainer/self-hosting
 * Categories: bike-maintainer
 * Description: Die zwei Wege zur App als Karten, darunter die Installation im Heimnetz per Docker Compose.
 *
 * @package BikeMaintainer
 */

?>
<!-- wp:group {"metadata":{"name":"Portal oder selbst betreiben"},"anchor":"betrieb","align":"full","className":"is-style-glow","style":{"spacing":{"padding":{"top":"clamp(3rem, 8vw, 5rem)","bottom":"clamp(3rem, 8vw, 5rem)","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"top":{"color":"var:preset|color|accent-6","width":"1px","style":"solid"}}},"layout":{"type":"constrained","wideSize":"1024px"}} -->
<div id="betrieb" class="wp-block-group alignfull is-style-glow" style="border-top-color:var(--wp--preset--color--accent-6);border-top-style:solid;border-top-width:1px;padding-top:clamp(3rem, 8vw, 5rem);padding-right:var(--wp--preset--spacing--50);padding-bottom:clamp(3rem, 8vw, 5rem);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"bottom":"3rem"},"blockGap":"0.75rem"}},"layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group alignwide" style="margin-bottom:3rem">
		<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow"} -->
		<p class="has-text-align-center is-style-eyebrow">Zwei Wege</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.75rem, 4vw, 2.5rem)"}}} -->
		<h2 class="wp-block-heading has-text-align-center" style="font-size:clamp(1.75rem, 4vw, 2.5rem)">Im Portal oder selbst betrieben</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","textColor":"accent-4"} -->
		<p class="has-text-align-center has-accent-4-color has-text-color">Dieselbe App, dieselben Funktionen – du entscheidest, wo deine Daten liegen.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"2rem"}}}} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"className":"is-style-card","style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"}},"border":{"color":"var:preset|color|accent-1","width":"2px"}}} -->
		<div class="wp-block-column is-style-card has-border-color" style="border-color:var(--wp--preset--color--accent-1);border-width:2px;padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem">
			<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
			<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/cloud.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading">Im Portal</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.95rem"}}} -->
			<p class="has-accent-4-color has-text-color" style="font-size:0.95rem">Registrieren und loslegen – ohne eigenen Server. Jede Person hat ihr eigenes Konto.</p>
			<!-- /wp:paragraph -->

			<!-- wp:list {"className":"is-style-checklist"} -->
			<ul class="wp-block-list is-style-checklist">
				<!-- wp:list-item -->
				<li>Keine Installation nötig</li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li>Erreichbar von unterwegs</li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li>Konto mit E-Mail-Adresse und Passwort</li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->

			<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
			<div class="wp-block-buttons" style="margin-top:1.5rem">
				<!-- wp:button {"width":100} -->
				<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button" href="#bm-app">Zur App</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"is-style-card","style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"2rem","right":"2rem"}}}} -->
		<div class="wp-block-column is-style-card" style="padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem">
			<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
			<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/server.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading">Selbst betrieben</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.95rem"}}} -->
			<p class="has-accent-4-color has-text-color" style="font-size:0.95rem">Der Haushaltsmodus läuft in deinem Heimnetz – auf PC, Server oder Raspberry Pi. Jede Person wählt ihr Profil, ganz ohne Passwort.</p>
			<!-- /wp:paragraph -->

			<!-- wp:list {"className":"is-style-checklist"} -->
			<ul class="wp-block-list is-style-checklist">
				<!-- wp:list-item -->
				<li>Kostenlos und quelloffen</li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li>Docker-Images für amd64 und arm64</li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li>Deine Daten bleiben bei dir</li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->

			<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
			<div class="wp-block-buttons" style="margin-top:1.5rem">
				<!-- wp:button {"width":100,"className":"is-style-secondary"} -->
				<div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-secondary"><a class="wp-block-button__link wp-element-button" href="https://github.com/stargazer74/bike-maintainer-deploy#selbst-hosten-im-heimnetz">Anleitung auf GitHub</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:paragraph {"align":"center","textColor":"accent-4","style":{"spacing":{"margin":{"top":"3rem"}},"typography":{"fontSize":"0.9rem"}}} -->
	<p class="has-text-align-center has-accent-4-color has-text-color" style="margin-top:3rem;font-size:0.9rem">Im Heimnetz in vier Schritten – auf jedem Rechner mit Docker:</p>
	<!-- /wp:paragraph -->

	<!-- wp:code -->
	<pre class="wp-block-code"><code>mkdir bike-maintainer &amp;&amp; cd bike-maintainer
curl -fsSLO https://raw.githubusercontent.com/stargazer74/bike-maintainer-deploy/main/household/compose.yaml
curl -fsSL -o .env https://raw.githubusercontent.com/stargazer74/bike-maintainer-deploy/main/household/.env.example
docker compose up -d     # vorher in .env ein Passwort für BM_DB_PASSWORD setzen</code></pre>
	<!-- /wp:code -->
</div>
<!-- /wp:group -->
