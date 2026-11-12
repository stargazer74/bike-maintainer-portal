<?php
/**
 * Pages made of theme patterns for bm-setup (run with `wp eval-file` after setup-languages.php): the feature page in
 * German and English (/funktionen/, /en/features/; linked by the header and the start page via
 * bike_maintainer_features_url()). Idempotent.
 *
 * Missing pages are created and published (template without title, the pattern brings its own heading), both
 * languages linked. Existing pages are never changed – once created,
 * their content belongs to the admins (block editor).
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'pll_set_post_language' ) ) {
	WP_CLI::error( 'Polylang must be active.' );
}

$bm_pages = array(
	'features' => array(
		'pattern' => 'bike-maintainer/feature-tour',
		'de'      => array(
			'slug'  => 'funktionen',
			'title' => 'Funktionen',
		),
		'en'      => array(
			'slug'  => 'features',
			'title' => 'Features',
		),
	),
);

foreach ( $bm_pages as $bm_key => $bm_page ) {
	$bm_ids = array();
	foreach ( array( 'de', 'en' ) as $bm_lang ) {
		$bm_existing = get_posts(
			array(
				'post_type'      => 'page',
				'name'           => $bm_page[ $bm_lang ]['slug'],
				'post_status'    => 'any',
				'lang'           => $bm_lang,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( $bm_existing ) {
			$bm_ids[ $bm_lang ] = (int) $bm_existing[0];
			continue;
		}
		$bm_id = wp_insert_post(
			array(
				'post_type'     => 'page',
				'post_status'   => 'publish',
				'post_title'    => $bm_page[ $bm_lang ]['title'],
				'post_name'     => $bm_page[ $bm_lang ]['slug'],
				'post_content'  => '<!-- wp:pattern {"slug":"' . $bm_page['pattern'] . '"} /-->',
				'page_template' => 'page-without-title',
			),
			true
		);
		if ( is_wp_error( $bm_id ) ) {
			WP_CLI::error( $bm_id->get_error_message() );
		}
		pll_set_post_language( $bm_id, $bm_lang );
		$bm_ids[ $bm_lang ] = $bm_id;
		WP_CLI::log( "Created page {$bm_page[ $bm_lang ]['slug']} ($bm_lang): $bm_id" );
	}
	pll_save_post_translations( $bm_ids );
}
