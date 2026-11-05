<?php
/**
 * Legal pages for bm-setup (run with `wp eval-file` after setup-languages.php): legal notice and privacy policy in
 * German and English, at the addresses the theme footer and the app link to (bike_maintainer_legal_url(), app:
 * websiteUrl). Idempotent.
 *
 * Missing pages are created as drafts with a placeholder; the operator writes the text in the block editor and
 * publishes it. Existing pages are never changed – the text belongs to the operator and never lives in this repo.
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'pll_set_post_language' ) ) {
	WP_CLI::error( 'Polylang must be active.' );
}

$bm_legal_pages = array(
	'imprint' => array(
		'de' => array(
			'slug'  => 'impressum',
			'title' => 'Impressum',
			'note'  => 'Platzhalter: Angaben nach § 5 DDG (Name, ladungsfähige Anschrift, Kontakt) eintragen und die Seite veröffentlichen.',
		),
		'en' => array(
			'slug'  => 'legal-notice',
			'title' => 'Legal notice',
			'note'  => 'Placeholder: enter the legal notice and publish the page.',
		),
	),
	'privacy' => array(
		'de' => array(
			'slug'  => 'datenschutz',
			'title' => 'Datenschutzerklärung',
			'note'  => 'Platzhalter: Datenschutzerklärung für Website, Forum und App eintragen (Grundlage: docs/datenschutz-hinweise.md im Repo bike-maintainer-portal) und die Seite veröffentlichen.',
		),
		'en' => array(
			'slug'  => 'privacy',
			'title' => 'Privacy policy',
			'note'  => 'Placeholder: enter the privacy policy and publish the page.',
		),
	),
);

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
					'post_content' => "<!-- wp:paragraph -->\n<p>" . esc_html( $bm_page['note'] ) . "</p>\n<!-- /wp:paragraph -->",
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
