<?php
/**
 * Bike Maintainer child theme: app link and pattern category. Design lives in theme.json and styles/.
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Address of the app (BM_APP_URL), e.g. https://app.bike-maintainer.de; no trailing slash.
 *
 * @return string
 */
function bike_maintainer_app_url() {
	$url = getenv( 'BM_APP_URL' );
	return untrailingslashit( false === $url || '' === $url ? 'https://app.bike-maintainer.de' : $url );
}

/*
 * Links to the app are written as href="#bm-app" in patterns and content, so the same content works in development and
 * operation. They are replaced with BM_APP_URL when a block is rendered.
 */
add_filter(
	'render_block',
	/**
	 * Replaces the app placeholder link in the rendered block.
	 *
	 * @param string $content Rendered block.
	 * @return string
	 */
	static function ( $content ) {
		if ( false === strpos( $content, '#bm-app' ) ) {
			return $content;
		}
		return str_replace( 'href="#bm-app"', 'href="' . esc_url( bike_maintainer_app_url() ) . '"', $content );
	}
);

add_action(
	'wp_head',
	/** Loads the text font early, so the page does not jump when it arrives (layout shift). */
	static function () {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( 'assets/fonts/InterVariable.woff2' ) )
		);
	},
	1
);

/*
 * Favicon and app icons: the app's logo (orange square with the motorcycle) instead of the WordPress "W". Used while no
 * site icon is set in the admin; one set there takes precedence. WordPress then prints the icon links on the website,
 * in the admin and on the login page, and redirects /favicon.ico to the 32 px icon.
 */
add_filter(
	'get_site_icon_url',
	/**
	 * Theme icon in the requested size if the site has no icon of its own.
	 *
	 * @param string $url  Icon URL, empty without a site icon.
	 * @param int    $size Requested size in pixels.
	 * @return string
	 */
	static function ( $url, $size ) {
		if ( '' !== $url ) {
			return $url;
		}
		if ( 180 === (int) $size ) {
			// iOS rounds the corners itself and fills transparent areas with black, so this one has no rounded corners.
			return get_theme_file_uri( 'assets/images/apple-touch-icon.png' );
		}
		foreach ( array( 32, 192, 512 ) as $available ) {
			if ( (int) $size <= $available ) {
				return get_theme_file_uri( "assets/images/favicon-{$available}.png" );
			}
		}
		return get_theme_file_uri( 'assets/images/favicon-512.png' );
	},
	10,
	2
);

add_action(
	'do_faviconico',
	/** /favicon.ico: WordPress passes its own "W" as fallback there, so redirect to the theme icon explicitly. */
	static function () {
		if ( get_option( 'site_icon' ) ) {
			return;
		}
		wp_safe_redirect( get_theme_file_uri( 'assets/images/favicon-32.png' ) );
		exit;
	}
);

/** Sharp at any size in browsers that support it; only while the theme icon is used. */
function bike_maintainer_svg_icon() {
	if ( get_option( 'site_icon' ) ) {
		return;
	}
	printf(
		'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
		esc_url( get_theme_file_uri( 'assets/images/favicon.svg' ) )
	);
}
add_action( 'wp_head', 'bike_maintainer_svg_icon', 98 );
add_action( 'admin_head', 'bike_maintainer_svg_icon', 98 );
add_action( 'login_head', 'bike_maintainer_svg_icon', 98 );

add_action(
	'after_setup_theme',
	/** Translations of the theme (languages/bike-maintainer-{locale}.mo); German and English, see README. */
	static function () {
		load_child_theme_textdomain( 'bike-maintainer', get_stylesheet_directory() . '/languages' );
	}
);

/**
 * Current language of the website: "de" or "en" (Polylang), "de" without Polylang.
 *
 * @return string
 */
function bike_maintainer_language() {
	$language = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
	return 'en' === $language ? 'en' : 'de';
}

/**
 * URL on the start page of the current language, e.g. "#betrieb" -> /#betrieb or /en/#betrieb.
 *
 * @param string $fragment Optional fragment without "#".
 * @return string
 */
function bike_maintainer_home_url( $fragment = '' ) {
	$home = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
	return trailingslashit( $home ) . ( '' === $fragment ? '' : '#' . $fragment );
}

/**
 * Legal pages per language. The pages themselves are created by the operator (Epic #48).
 *
 * @param string $page "imprint" or "privacy".
 * @return string
 */
function bike_maintainer_legal_url( $page ) {
	$slugs    = array(
		'de' => array(
			'imprint' => 'impressum',
			'privacy' => 'datenschutz',
		),
		'en' => array(
			'imprint' => 'legal-notice',
			'privacy' => 'privacy',
		),
	);
	$language = bike_maintainer_language();
	return home_url( ( 'en' === $language ? '/en/' : '/' ) . $slugs[ $language ][ $page ] . '/' );
}

/*
 * Block templates run shortcodes before the blocks inside them are rendered, so a shortcode block inside a pattern
 * (like the language switcher in the header) would stay raw text. Run them when the block itself is rendered.
 */
add_filter( 'render_block_core/shortcode', 'do_shortcode' );

add_shortcode(
	'bm_language_switcher',
	/**
	 * "DE | EN" switcher like in the app; links to the translation of the current page if there is one, otherwise to
	 * the start page of the language. Empty without Polylang.
	 *
	 * @return string
	 */
	static function () {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return '';
		}
		$languages = pll_the_languages(
			array(
				'raw'                    => 1,
				'hide_if_no_translation' => 0,
			)
		);
		if ( empty( $languages ) ) {
			return '';
		}
		$links = '';
		foreach ( $languages as $language ) {
			$links .= sprintf(
				'<a href="%1$s" hreflang="%2$s" lang="%2$s"%3$s>%4$s</a>',
				esc_url( $language['url'] ),
				esc_attr( $language['locale'] ? str_replace( '_', '-', $language['locale'] ) : $language['slug'] ),
				$language['current_lang'] ? ' aria-current="true"' : '',
				esc_html( strtoupper( $language['slug'] ) )
			);
		}
		return sprintf(
			'<nav class="bm-language-switcher" aria-label="%1$s">%2$s</nav>',
			esc_attr__( 'Language', 'bike-maintainer' ),
			$links
		);
	}
);

add_action(
	'init',
	/** Groups our patterns in the inserter. */
	static function () {
		register_block_pattern_category(
			'bike-maintainer',
			array( 'label' => __( 'Bike Maintainer', 'bike-maintainer' ) )
		);
	}
);
