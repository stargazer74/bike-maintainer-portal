<?php
/**
 * Polylang setup for bm-setup (run with `wp eval-file`): German and English, German without prefix, English under
 * /en/, and the start page in both languages. Idempotent.
 *
 * Polylang's own browser detection is off because it needs its language cookie; bm-polylang.php detects the browser
 * language without a cookie instead.
 *
 * Polylang has no WP-CLI commands of its own, so this uses its PHP API.
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/setup-lib.php';

if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_set_post_language' ) ) {
	WP_CLI::error( 'Polylang is not active.' );
}

bm_setup_init();

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
		'browser'       => false, // Done without a cookie in bm-polylang.php.
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

// Date and time formats: German as the site default (WordPress was installed in English first), English via
// Polylang's translation of the WordPress formats. Once only: afterwards they are the admins' settings.
bm_setup_once(
	'date-formats',
	static function () use ( $bm_model ) {
		update_option( 'date_format', 'j. F Y' );
		update_option( 'time_format', 'G:i' );
		$english = $bm_model->get_language( 'en' );
		if ( $english && class_exists( 'PLL_MO' ) ) {
			$mo = new PLL_MO();
			$mo->import_from_db( $english );
			$mo->add_entry( $mo->make_entry( 'j. F Y', 'F j, Y' ) );
			$mo->add_entry( $mo->make_entry( 'G:i', 'g:i a' ) );
			$mo->export_to_db( $english );
		}
	}
);

flush_rewrite_rules();
WP_CLI::success( 'Languages: ' . implode( ', ', pll_languages_list() ) );
