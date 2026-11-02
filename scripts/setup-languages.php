<?php
/**
 * Polylang setup for bm-setup (run with `wp eval-file`): German and English, German without prefix, English under
 * /en/, and the start page in both languages. Idempotent.
 *
 * No browser language detection and no language cookie (bm-polylang.php): the language comes from the URL alone, as
 * the most privacy-friendly option – visitors switch with DE | EN, search engines use hreflang.
 *
 * Polylang has no WP-CLI commands of its own, so this uses its PHP API.
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_set_post_language' ) ) {
	WP_CLI::error( 'Polylang is not active.' );
}

$bm_model = PLL()->model;
foreach (
	array(
		array(
			'locale'     => 'de_DE',
			'slug'       => 'de',
			'name'       => 'Deutsch',
			'flag'       => 'de',
			'term_group' => 0,
		),
		array(
			'locale'     => 'en_US',
			'slug'       => 'en',
			'name'       => 'English',
			'flag'       => 'us',
			'term_group' => 1,
		),
	) as $bm_language
) {
	if ( ! $bm_model->get_language( $bm_language['slug'] ) ) {
		$bm_result = $bm_model->languages->add( $bm_language );
		if ( is_wp_error( $bm_result ) ) {
			WP_CLI::error( 'Language ' . $bm_language['slug'] . ': ' . $bm_result->get_error_message() );
		}
		WP_CLI::log( 'Added language ' . $bm_language['slug'] );
	}
}

// URL: language as directory (/en/), none for the default language.
$bm_options = PLL()->options;
foreach (
	array(
		'default_lang'  => 'de',
		'force_lang'    => 1,
		'hide_default'  => true,
		'rewrite'       => true,
		// The start page of a language is /en/, not /en/home/.
		'redirect_lang' => true,
		'browser'       => false,
	) as $bm_key => $bm_value
) {
	$bm_options->set( $bm_key, $bm_value );
}
$bm_options->save();

// Content created before Polylang (e.g. the German start page) is German.
foreach ( get_posts(
	array(
		'post_type'      => array( 'post', 'page' ),
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'lang'           => '',
	)
) as $bm_post_id ) {
	if ( ! pll_get_post_language( $bm_post_id ) ) {
		pll_set_post_language( $bm_post_id, 'de' );
	}
}

// English start page next to the German one, both made of the landing page pattern.
$bm_start = (int) get_option( 'page_on_front' );
if ( $bm_start && ! pll_get_post( $bm_start, 'en' ) ) {
	$bm_home = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Home',
			'post_name'    => 'home',
			'post_content' => '<!-- wp:pattern {"slug":"bike-maintainer/landing"} /-->',
		),
		true
	);
	if ( is_wp_error( $bm_home ) ) {
		WP_CLI::error( $bm_home->get_error_message() );
	}
	pll_set_post_language( $bm_home, 'en' );
	pll_save_post_translations(
		array(
			'de' => $bm_start,
			'en' => $bm_home,
		)
	);
	WP_CLI::log( 'Created English start page ' . $bm_home );
}

// Polylang caches each language's start page when the language is created; refresh it now that both exist.
$bm_model->languages->clean_cache();

flush_rewrite_rules();
WP_CLI::success( 'Languages: ' . implode( ', ', pll_languages_list() ) );
