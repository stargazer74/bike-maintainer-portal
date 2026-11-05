<?php
/**
 * Legal pages for bm-setup (run with `wp eval-file` after setup-languages.php): legal notice and privacy policy in
 * German and English, at the addresses the theme footer and the app link to (bike_maintainer_legal_url(), app:
 * websiteUrl). Idempotent.
 *
 * Missing pages are created as drafts with placeholders in [brackets]; the operator fills them in in the block editor
 * and publishes the page. Existing pages are never changed – the real text belongs to the operator and never lives in
 * this repo.
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'pll_set_post_language' ) ) {
	WP_CLI::error( 'Polylang must be active.' );
}

// Page content as blocks: array( 'h' or 'p', HTML ). Placeholders in [brackets] are filled in by the operator; the
// legal notice gets the usual structure, the privacy policy only a note (its facts: docs/datenschutz-hinweise.md).
$bm_legal_pages = array(
	'imprint' => array(
		'de' => array(
			'slug'   => 'impressum',
			'title'  => 'Impressum',
			'blocks' => array(
				array( 'p', 'Angaben gemäß § 5 DDG' ),
				array( 'p', '[Vorname Nachname]<br>[Straße Hausnummer]<br>[PLZ Ort]<br>Deutschland' ),
				array( 'h', 'Kontakt' ),
				array( 'p', 'E-Mail: [E-Mail-Adresse]<br>Telefon: [Telefonnummer]' ),
				array( 'h', 'Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV' ),
				array( 'p', '[Vorname Nachname]<br>Anschrift wie oben' ),
				array( 'p', '[Nur falls vorhanden, sonst diesen Absatz löschen: Umsatzsteuer-Identifikationsnummer nach § 27a UStG, Registergericht und Registernummer, vertretungsberechtigte Person.]' ),
				array( 'h', 'Forum' ),
				array( 'p', 'Beiträge im Forum geben die Meinung ihrer Verfasser wieder. Rechtswidrige Inhalte bitte über die Meldefunktion des Forums oder per E-Mail an die oben genannte Adresse melden.' ),
			),
		),
		'en' => array(
			'slug'   => 'legal-notice',
			'title'  => 'Legal notice',
			'blocks' => array(
				array( 'p', 'Information pursuant to § 5 DDG (German Digital Services Act)' ),
				array( 'p', '[First name last name]<br>[Street and number]<br>[Postcode city]<br>Germany' ),
				array( 'h', 'Contact' ),
				array( 'p', 'Email: [email address]<br>Phone: [phone number]' ),
				array( 'h', 'Responsible for the content according to § 18 (2) MStV' ),
				array( 'p', '[First name last name]<br>Address as above' ),
				array( 'p', '[Only if applicable, otherwise delete this paragraph: VAT identification number according to § 27a UStG, register court and number, authorised representative.]' ),
				array( 'h', 'Forum' ),
				array( 'p', 'Forum posts reflect the opinions of their authors. Please report illegal content using the report function of the forum or by email to the address above.' ),
			),
		),
	),
	'privacy' => array(
		'de' => array(
			'slug'   => 'datenschutz',
			'title'  => 'Datenschutzerklärung',
			'blocks' => array(
				array( 'p', '[Platzhalter: Datenschutzerklärung für Website, Forum und App eintragen (Grundlage: docs/datenschutz-hinweise.md im Repo bike-maintainer-portal) und die Seite veröffentlichen.]' ),
			),
		),
		'en' => array(
			'slug'   => 'privacy',
			'title'  => 'Privacy policy',
			'blocks' => array(
				array( 'p', '[Placeholder: enter the privacy policy and publish the page.]' ),
			),
		),
	),
);

/**
 * Block markup as the block editor saves it, so the editor does not flag the blocks as invalid.
 *
 * @param array $blocks List of array( 'h' or 'p', HTML ).
 * @return string
 */
function bm_legal_blocks( $blocks ) {
	$markup = array();
	foreach ( $blocks as $block ) {
		$html     = wp_kses( $block[1], array( 'br' => array() ) );
		$markup[] = 'h' === $block[0]
			? "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$html</h2>\n<!-- /wp:heading -->"
			: "<!-- wp:paragraph -->\n<p>$html</p>\n<!-- /wp:paragraph -->";
	}
	return implode( "\n\n", $markup );
}

/**
 * Page with the given slug in the given language, in any status.
 *
 * @param string $slug     Page slug.
 * @param string $language Polylang language slug.
 * @return int Page ID or 0.
 */
function bm_legal_find_page( $slug, $language ) {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'name'           => $slug,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'lang'           => $language,
		)
	);
	return $pages ? (int) $pages[0] : 0;
}

$bm_ids = array();
foreach ( $bm_legal_pages as $bm_key => $bm_languages ) {
	$bm_translations = array();
	foreach ( $bm_languages as $bm_language => $bm_page ) {
		$bm_id = bm_legal_find_page( $bm_page['slug'], $bm_language );
		if ( ! $bm_id ) {
			$bm_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'draft',
					'post_title'   => $bm_page['title'],
					'post_name'    => $bm_page['slug'],
					'post_content' => bm_legal_blocks( $bm_page['blocks'] ),
				),
				true
			);
			if ( is_wp_error( $bm_id ) ) {
				WP_CLI::error( $bm_page['slug'] . ': ' . $bm_id->get_error_message() );
			}
			WP_CLI::log( "Created draft {$bm_page['slug']} ($bm_language) $bm_id" );
		}
		pll_set_post_language( $bm_id, $bm_language );
		$bm_translations[ $bm_language ] = $bm_id;
	}
	pll_save_post_translations( $bm_translations );
	$bm_ids[ $bm_key ] = $bm_translations;
}

// WordPress' own privacy page (linked on registration and login; Polylang translates it per language). The sample
// draft "Privacy Policy" that WordPress creates on installation is replaced, as long as nobody published it.
$bm_sample = (int) get_option( 'wp_page_for_privacy_policy' );
if ( $bm_sample && ! in_array( $bm_sample, $bm_ids['privacy'], true ) && 'draft' === get_post_status( $bm_sample )
	&& 'privacy-policy' === get_post_field( 'post_name', $bm_sample ) ) {
	wp_delete_post( $bm_sample, true );
	WP_CLI::log( "Deleted WordPress' sample privacy page $bm_sample" );
}
update_option( 'wp_page_for_privacy_policy', $bm_ids['privacy']['de'] );

WP_CLI::success(
	sprintf(
		'Legal pages: imprint de %d, en %d; privacy de %d, en %d',
		$bm_ids['imprint']['de'],
		$bm_ids['imprint']['en'],
		$bm_ids['privacy']['de'],
		$bm_ids['privacy']['en']
	)
);
