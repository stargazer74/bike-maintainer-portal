<?php
/**
 * Title: Features
 * Slug: bike-maintainer/features
 * Categories: bike-maintainer
 * Description: Nine features of the app as cards with icons, in three rows.
 *
 * @package BikeMaintainer
 */

?>
<!-- wp:group {"metadata":{"name":"Funktionen"},"anchor":"funktionen","align":"full","style":{"spacing":{"padding":{"top":"clamp(3rem, 8vw, 5rem)","bottom":"clamp(3rem, 8vw, 5rem)","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"top":{"color":"var:preset|color|accent-6","width":"1px","style":"solid"}}},"backgroundColor":"base","layout":{"type":"constrained","wideSize":"1280px"}} -->
<div id="funktionen" class="wp-block-group alignfull has-base-background-color has-background" style="border-top-color:var(--wp--preset--color--accent-6);border-top-style:solid;border-top-width:1px;padding-top:clamp(3rem, 8vw, 5rem);padding-right:var(--wp--preset--spacing--50);padding-bottom:clamp(3rem, 8vw, 5rem);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"bottom":"3rem"},"blockGap":"0.75rem"}},"layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group alignwide" style="margin-bottom:3rem">
		<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow"} -->
		<p class="has-text-align-center is-style-eyebrow"><?php esc_html_e( 'Made in the workshop, for the workshop', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.75rem, 4vw, 2.5rem)"}}} -->
		<h2 class="wp-block-heading has-text-align-center" style="font-size:clamp(1.75rem, 4vw, 2.5rem)"><?php esc_html_e( 'For everyone who looks after their own vehicles', 'bike-maintainer' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","textColor":"accent-4"} -->
		<p class="has-text-align-center has-accent-4-color has-text-color"><?php esc_html_e( 'Everything you need to document the value and reliability of your vehicles without gaps.', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"bottom":"2rem"},"blockGap":{"left":"2rem"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-bottom:2rem">
			<!-- wp:column {"className":"is-style-card"} -->
			<div class="wp-block-column is-style-card">
				<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
				<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/calendar-clock.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.125rem;font-weight:700"><?php esc_html_e( 'Intervals by mileage or time', 'bike-maintainer' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
				<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'Reminders by mileage or months – whichever comes first. See due and overdue work at a glance, and record extra work right along with the maintenance.', 'bike-maintainer' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"is-style-card"} -->
			<div class="wp-block-column is-style-card">
				<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
				<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/file-text.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.125rem;font-weight:700"><?php esc_html_e( 'Report and checklist as PDF', 'bike-maintainer' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
				<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'A complete maintenance report for your records or when you sell, plus a checklist for your next workshop visit.', 'bike-maintainer' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"is-style-card"} -->
			<div class="wp-block-column is-style-card">
				<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
				<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/receipt-euro.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.125rem;font-weight:700"><?php esc_html_e( 'Parts and costs', 'bike-maintainer' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
				<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'Parts with quantity and price, labour costs and an overview for the current year and in total – in several currencies, too.', 'bike-maintainer' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"bottom":"2rem"},"blockGap":{"left":"2rem"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-bottom:2rem">
			<!-- wp:column {"className":"is-style-card"} -->
			<div class="wp-block-column is-style-card">
				<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
				<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/clipboard-list.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.125rem;font-weight:700"><?php esc_html_e( 'Worksheets for the workshop', 'bike-maintainer' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
				<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'Print a worksheet for valve clearance, brakes, chain or tyres, fill it in by hand at the bike and photograph it with the app. Your phone reads the values – the photo never leaves it.', 'bike-maintainer' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"is-style-card"} -->
			<div class="wp-block-column is-style-card">
				<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
				<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/gauge.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.125rem;font-weight:700"><?php esc_html_e( 'Measured values and targets', 'bike-maintainer' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
				<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'Store the target values from the workshop manual. Values outside them are highlighted, and for shim-adjusted valves the app suggests the new shim.', 'bike-maintainer' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"is-style-card"} -->
			<div class="wp-block-column is-style-card">
				<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
				<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/arrow-left-right.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.125rem;font-weight:700"><?php esc_html_e( 'Take your data with you', 'bike-maintainer' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
				<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'Export a vehicle with its whole history as a file and import it elsewhere – from the portal to your own server or the other way round.', 'bike-maintainer' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"2rem"}}}} -->
	<div class="wp-block-columns alignwide">
			<!-- wp:column {"className":"is-style-card"} -->
			<div class="wp-block-column is-style-card">
				<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
				<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/bike.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.125rem;font-weight:700"><?php esc_html_e( 'Every kind of vehicle', 'bike-maintainer' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
				<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'Motorcycles, cars, bicycles, e-bikes, quads and scooters side by side, each with its own maintenance plan and history.', 'bike-maintainer' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"is-style-card"} -->
			<div class="wp-block-column is-style-card">
				<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
				<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/users.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.125rem;font-weight:700"><?php esc_html_e( 'For the whole household', 'bike-maintainer' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
				<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'In household mode everyone picks their profile – no password, in your own home network.', 'bike-maintainer' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"is-style-card"} -->
			<div class="wp-block-column is-style-card">
				<!-- wp:image {"width":"48px","height":"48px","linkDestination":"none","className":"is-style-icon-tile"} -->
				<figure class="wp-block-image is-resized is-style-icon-tile"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/languages.svg' ) ); ?>" alt="" style="width:48px;height:48px"/></figure>
				<!-- /wp:image -->

				<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
				<h3 class="wp-block-heading" style="font-size:1.125rem;font-weight:700"><?php esc_html_e( 'German and English', 'bike-maintainer' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
				<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'Interface, emails and PDFs are available in both languages, set per account.', 'bike-maintainer' ); ?></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
