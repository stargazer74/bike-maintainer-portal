<?php
/**
 * Plugin Name: Bike Maintainer – Automatic updates
 * Description: WordPress keeps core (including major versions), plugins, themes and translations up to date itself. Our own child theme is excluded: it comes with the image.
 * Author: Chris Wohlbrecht
 * License: MIT
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

/** Slug (directory) of our child theme; it is not on wordpress.org and must never be "updated" from there. */
const BM_OWN_THEME = 'bike-maintainer';

add_filter( 'allow_major_auto_core_updates', '__return_true' );
add_filter( 'allow_minor_auto_core_updates', '__return_true' );
add_filter( 'auto_update_plugin', '__return_true' );
add_filter( 'auto_update_translation', '__return_true' );

add_filter(
	'auto_update_theme',
	/**
	 * Enables automatic updates for every theme except ours.
	 *
	 * @param bool|null $update Whether to update.
	 * @param object    $item   The update offer.
	 * @return bool
	 */
	static function ( $update, $item ) {
		return ! ( isset( $item->theme ) && BM_OWN_THEME === $item->theme );
	},
	10,
	2
);

/**
 * WordPress matches themes with wordpress.org by directory name. Should a theme called like ours ever appear there,
 * it would be offered – and auto-installed – as an "update" over our theme. Remove it from every update check.
 */
add_filter(
	'site_transient_update_themes',
	/**
	 * Drops our theme from the update offers.
	 *
	 * @param mixed $transient Cached update data.
	 * @return mixed
	 */
	static function ( $transient ) {
		if ( is_object( $transient ) ) {
			unset( $transient->response[ BM_OWN_THEME ], $transient->no_update[ BM_OWN_THEME ] );
		}
		return $transient;
	}
);

add_filter(
	'http_request_args',
	/**
	 * Does not even send our theme to the wordpress.org update API.
	 *
	 * @param array  $args HTTP request arguments.
	 * @param string $url  Request URL.
	 * @return array
	 */
	static function ( $args, $url ) {
		if ( false === strpos( $url, '://api.wordpress.org/themes/update-check/' ) || empty( $args['body']['themes'] ) ) {
			return $args;
		}
		$themes = json_decode( $args['body']['themes'], true );
		if ( is_array( $themes ) ) {
			unset( $themes['themes'][ BM_OWN_THEME ] );
			$args['body']['themes'] = wp_json_encode( $themes );
		}
		return $args;
	},
	10,
	2
);
