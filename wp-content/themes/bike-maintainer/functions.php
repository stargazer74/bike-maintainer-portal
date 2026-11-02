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
