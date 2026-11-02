<?php
/**
 * Plugin Name: Bike Maintainer – Polylang without cookie
 * Description: Polylang sets no language cookie (pll_language, valid for a year); the language comes from the URL (/ German, /en/ English). Visitors arriving from outside on the German start page are sent to /en/ if their browser prefers English – without storing anything.
 * Author: Chris Wohlbrecht
 * License: MIT
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

// Must-use plugins load before plugins, so the constant is in place before Polylang reads it.
if ( ! defined( 'PLL_COOKIE' ) ) {
	define( 'PLL_COOKIE', false );
}

/**
 * First language of the Accept-Language header that the website offers ("de" or "en"), or "" if none.
 *
 * @param string $header Accept-Language, e.g. "en-GB,en;q=0.9,de;q=0.8".
 * @return string
 */
function bm_preferred_language( $header ) {
	$ranked = array();
	foreach ( explode( ',', $header ) as $position => $part ) {
		$pieces   = array_map( 'trim', explode( ';', $part ) );
		$language = strtolower( substr( $pieces[0], 0, 2 ) );
		$quality  = 1.0;
		foreach ( array_slice( $pieces, 1 ) as $parameter ) {
			if ( 0 === strpos( $parameter, 'q=' ) ) {
				$quality = (float) substr( $parameter, 2 );
			}
		}
		if ( $quality > 0 && in_array( $language, array( 'de', 'en' ), true ) ) {
			// Equal quality keeps the browser's order.
			$ranked[] = array( $quality, -$position, $language );
		}
	}
	if ( empty( $ranked ) ) {
		return '';
	}
	rsort( $ranked );
	return $ranked[0][2];
}

/*
 * Browser language without a cookie: only on the German start page and only for visits from outside (typed address,
 * search engine, link on another site). A click from our own pages – e.g. "DE" in the language switcher – carries our
 * host as referrer and is never redirected, so a manual choice sticks without storing it.
 */
add_action(
	'template_redirect',
	static function () {
		if ( ! function_exists( 'pll_current_language' ) || ! is_front_page() || 'de' !== pll_current_language() ) {
			return;
		}
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
		$path   = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
		if ( 'GET' !== $method || '/' !== $path ) {
			return;
		}
		header( 'Vary: Accept-Language, Referer', false );
		$referer = wp_get_raw_referer();
		if ( $referer && wp_parse_url( $referer, PHP_URL_HOST ) === wp_parse_url( home_url(), PHP_URL_HOST ) ) {
			return;
		}
		$accept = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) : '';
		if ( 'en' === bm_preferred_language( $accept ) ) {
			wp_safe_redirect( pll_home_url( 'en' ), 302 );
			exit;
		}
	}
);
