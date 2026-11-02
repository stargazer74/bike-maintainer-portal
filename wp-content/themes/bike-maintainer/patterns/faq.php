<?php
/**
 * Title: Frequently asked questions
 * Slug: bike-maintainer/faq
 * Categories: bike-maintainer
 * Description: Expandable questions and answers (core block “Details”).
 *
 * @package BikeMaintainer
 */

?>
<!-- wp:group {"metadata":{"name":"Häufige Fragen"},"anchor":"faq","align":"full","style":{"spacing":{"padding":{"top":"clamp(3rem, 8vw, 5rem)","bottom":"clamp(3rem, 8vw, 5rem)","left":"var:preset|spacing|50","right":"var:preset|spacing|50"},"blockGap":"1rem"},"border":{"top":{"color":"var:preset|color|accent-6","width":"1px","style":"solid"}}},"backgroundColor":"base","layout":{"type":"constrained","contentSize":"800px"}} -->
<div id="faq" class="wp-block-group alignfull has-base-background-color has-background" style="border-top-color:var(--wp--preset--color--accent-6);border-top-style:solid;border-top-width:1px;padding-top:clamp(3rem, 8vw, 5rem);padding-right:var(--wp--preset--spacing--50);padding-bottom:clamp(3rem, 8vw, 5rem);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.5rem, 3vw, 2rem)"},"spacing":{"margin":{"bottom":"2rem"}}}} -->
	<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:2rem;font-size:clamp(1.5rem, 3vw, 2rem)"><?php esc_html_e( 'Frequently asked questions', 'bike-maintainer' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'Which vehicles can I manage?', 'bike-maintainer' ); ?></summary>
		<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
		<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'Motorcycles and cars, as many as you like. Bicycles, e-bikes, quads and scooters are coming next.', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'How do the intervals work?', 'bike-maintainer' ); ?></summary>
		<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
		<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'For each job you set after how many kilometres and/or months it is due. Whichever is reached first counts. The app shows you what is due soon or already overdue.', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'What is in the PDF report?', 'bike-maintainer' ); ?></summary>
		<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
		<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'The selected maintenance entries with date, mileage, work done, parts and costs – for your records or when you sell your vehicle.', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'What is the difference between the portal and household mode?', 'bike-maintainer' ); ?></summary>
		<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
		<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'In the portal everyone has their own account with email address and password. Household mode is meant for your home network: no login, everyone picks their profile. That is why it must not be reachable from the internet.', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'Is Bike Maintainer open source?', 'bike-maintainer' ); ?></summary>
		<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
		<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'Yes, under the MIT licence. The source code is on GitHub.', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary><?php esc_html_e( 'Which languages are available?', 'bike-maintainer' ); ?></summary>
		<!-- wp:paragraph {"textColor":"accent-4","style":{"typography":{"fontSize":"0.9rem"}}} -->
		<p class="has-accent-4-color has-text-color" style="font-size:0.9rem"><?php esc_html_e( 'German and English – for the interface, the emails and the PDFs. You choose the language in your account.', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->
</div>
<!-- /wp:group -->
