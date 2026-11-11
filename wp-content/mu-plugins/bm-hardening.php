<?php
/**
 * Plugin Name: Bike Maintainer – Hardening
 * Description: No XML-RPC, no application passwords, no user enumeration for guests (also not through the login, registration and lost password forms), no version in the page, no file editor in the admin, and a per-IP limit for failed logins.
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
				__( 'Too many failed logins. Please try again in 15 minutes.', 'bike-maintainer' )
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

/*
 * No account enumeration (#14): the public forms on wp-login.php answer the same for known and unknown accounts –
 * same text, same status, same redirect. Otherwise anyone can find out who has an account, ALTCHA only slows that down.
 */

add_filter(
	'login_errors',
	/**
	 * "Unknown user" and "wrong password" become one message on the login form.
	 *
	 * @param string $message Error HTML of the login form.
	 * @return string
	 */
	static function ( $message ) {
		global $errors;
		if ( ! $errors instanceof WP_Error ) {
			return $message;
		}
		$revealing = array( 'invalid_username', 'invalid_email', 'incorrect_password' );
		if ( array() === array_intersect( $errors->get_error_codes(), $revealing ) ) {
			return $message;
		}
		return '<strong>' . esc_html__( 'Error:', 'bike-maintainer' ) . '</strong> '
			. esc_html__( 'The email address, username or password is not correct.', 'bike-maintainer' );
	}
);

add_filter(
	'lostpassword_errors',
	/**
	 * A reset request for an unknown account ends like a successful one: on "check your email". No mail is sent.
	 * Other errors (empty field, failed ALTCHA) are still shown – they say nothing about accounts.
	 *
	 * @param WP_Error      $errors    Errors of the request.
	 * @param WP_User|false $user_data The account, or false if there is none.
	 * @return WP_Error
	 */
	static function ( $errors, $user_data ) {
		if ( 'wp-login.php' !== ( $GLOBALS['pagenow'] ?? '' ) || $user_data ) {
			return $errors;
		}
		$codes = array_diff( $errors->get_error_codes(), array( 'invalid_email', 'invalidcombo' ) );
		if ( array() !== $codes ) {
			return $errors;
		}
		bm_redirect_like_wp_login( 'wp-login.php?checkemail=confirm' );
	},
	10,
	2
);

add_filter(
	'registration_errors',
	/**
	 * A registration with an address that already has an account ends like a successful one. The account gets a
	 * notice instead (log in or reset the password). Runs after ALTCHA (priority 10), so it costs a proof of work.
	 *
	 * @param WP_Error $errors      Registration errors.
	 * @param string   $login       Requested username.
	 * @param string   $email       Requested email address.
	 * @return WP_Error
	 */
	static function ( $errors, $login, $email ) {
		if ( array( 'email_exists' ) !== $errors->get_error_codes() ) {
			return $errors;
		}
		$user = get_user_by( 'email', $email );
		if ( $user ) {
			bm_send_registration_exists_notice( $user );
		}
		bm_redirect_like_wp_login( 'wp-login.php?checkemail=registered' );
	},
	20,
	3
);

/**
 * Redirects as wp-login.php does after success (honouring a safe redirect_to) and stops.
 *
 * @param string $fallback Target if the form sent no redirect_to.
 * @return never
 */
function bm_redirect_like_wp_login( $fallback ) {
	// The login forms have no nonce (same as core); wp_safe_redirect only allows our own host.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$redirect_to = isset( $_REQUEST['redirect_to'] ) ? wp_unslash( $_REQUEST['redirect_to'] ) : '';
	wp_safe_redirect( '' !== $redirect_to ? $redirect_to : $fallback );
	exit;
}

/**
 * Tells the owner of an existing account that someone tried to register with their address.
 *
 * @param WP_User $user The existing account.
 */
function bm_send_registration_exists_notice( WP_User $user ) {
	$switched = switch_to_user_locale( $user->ID );
	$site     = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$message  = sprintf(
		/* translators: 1: site name, 2: login URL, 3: lost password URL */
		__( "Hello,\n\nsomeone tried to register at %1\$s with your email address. You already have an account.\n\nLog in: %2\$s\nForgot your password: %3\$s\n\nIf this was not you, you can ignore this email. Nothing was changed on your account.", 'bike-maintainer' ),
		$site,
		wp_login_url(),
		wp_lostpassword_url()
	);
	/* translators: %s: site name */
	wp_mail( $user->user_email, sprintf( __( '[%s] Registration with your email address', 'bike-maintainer' ), $site ), $message );
	if ( $switched ) {
		restore_previous_locale();
	}
}
