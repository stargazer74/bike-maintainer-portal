<?php
/**
 * Plugin Name: Bike Maintainer – Hardening
 * Description: No XML-RPC, no application passwords, no user enumeration for guests, no version in the page, no file editor in the admin, and a per-IP limit for failed logins.
 * Author: Chris Wohlbrecht
 * License: MIT
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

/** Failed logins per client IP within the window before further attempts are refused (same values as the app). */
const BM_LOGIN_MAX_FAILURES = 10;
const BM_LOGIN_WINDOW       = 15 * MINUTE_IN_SECONDS;

// Theme and plugin editor in the admin: code changes come with the image, never through the browser.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// XML-RPC: not used by anything we run; a classic target for password guessing and pingback abuse.
if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
	status_header( 403 );
	exit;
}
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
add_filter( 'wp_headers', static fn( $headers ) => array_diff_key( $headers, array( 'X-Pingback' => true ) ) );

// Application passwords would allow logins to the REST API that bypass the login form and its limit.
add_filter( 'wp_is_application_passwords_available', '__return_false' );

// No WordPress version in the page head and feeds.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/*
 * User enumeration: guests must not be able to list accounts – neither through the REST API, nor through
 * ?author=N (which redirects to the user's slug), nor through the user sitemap.
 */
add_filter(
	'rest_endpoints',
	/**
	 * Removes the user endpoints for anyone who may not list users.
	 *
	 * @param array $endpoints Registered REST routes.
	 * @return array
	 */
	static function ( $endpoints ) {
		if ( current_user_can( 'list_users' ) ) {
			return $endpoints;
		}
		foreach ( array_keys( $endpoints ) as $route ) {
			if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
				unset( $endpoints[ $route ] );
			}
		}
		return $endpoints;
	}
);

add_action(
	'template_redirect',
	/** Answers ?author=N with 404 for guests instead of redirecting to the author's slug. */
	static function () {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only check of a public query var.
		if ( isset( $_GET['author'] ) && ! is_user_logged_in() ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	},
	1
);

add_filter(
	'wp_sitemaps_add_provider',
	/**
	 * Leaves the users out of the sitemap.
	 *
	 * @param WP_Sitemaps_Provider|false $provider Sitemap provider.
	 * @param string                     $name     Provider name.
	 * @return WP_Sitemaps_Provider|false
	 */
	static fn( $provider, $name ) => 'users' === $name ? false : $provider,
	10,
	2
);

/*
 * Failed logins per client IP. The nginx-proxy-manager in front has no per-path limit, so WordPress does it itself.
 * REMOTE_ADDR is the real client: Apache's mod_remoteip takes it from X-Forwarded-For of the proxy (see Dockerfile).
 */

/**
 * Transient key for the failed logins of the current client.
 *
 * @return string
 */
function bm_login_failures_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return 'bm_login_failures_' . md5( $ip );
}

add_filter(
	'authenticate',
	/**
	 * Refuses any login attempt while the client is over the limit – even with the right password.
	 *
	 * @param WP_User|WP_Error|null $user Result so far.
	 * @return WP_User|WP_Error|null
	 */
	static function ( $user ) {
		if ( (int) get_transient( bm_login_failures_key() ) >= BM_LOGIN_MAX_FAILURES ) {
			return new WP_Error(
				'bm_too_many_attempts',
				__( 'Zu viele fehlgeschlagene Anmeldungen. Bitte versuche es in 15 Minuten erneut.', 'bike-maintainer' )
			);
		}
		return $user;
	},
	30
);

add_action(
	'wp_login_failed',
	/** Counts a failed login; every failure extends the window, so steady guessing stays blocked. */
	static function () {
		$key      = bm_login_failures_key();
		$failures = (int) get_transient( $key );
		set_transient( $key, $failures + 1, BM_LOGIN_WINDOW );
	}
);

add_action(
	'wp_login',
	/** A successful login clears the counter of this client. */
	static function () {
		delete_transient( bm_login_failures_key() );
	}
);
