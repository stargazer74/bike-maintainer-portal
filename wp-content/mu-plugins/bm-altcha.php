<?php
/**
 * Plugin Name: Bike Maintainer – ALTCHA
 * Description: Self-hosted proof-of-work captcha (ALTCHA, as in the app) on the registration and lost-password forms. No third-party service, no cookie: the browser solves a small task, the server checks it. Needs BM_ALTCHA_HMAC_KEY; without it both forms refuse.
 * Author: Chris Wohlbrecht
 * License: MIT
 *
 * Protocol of the ALTCHA widget 3.x (PBKDF2), same as altcha-org's server library and the app's backend:
 * - Challenge: parameters {algorithm, cost, expiresAt, keyLength, keyPrefix, nonce, salt} with sorted keys, signed with
 *   HMAC-SHA-256 over their JSON.
 * - Solution: counter and derivedKey = PBKDF2-SHA-256(password = nonce bytes + counter as big-endian uint32, salt bytes,
 *   cost, keyLength); the key must start with keyPrefix.
 * - Payload of the form field "altcha": base64 of {"challenge": {...}, "solution": {"counter", "derivedKey"}}.
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

/** Version of bm-altcha/altcha.min.js (also the asset version for caching). */
const BM_ALTCHA_WIDGET_VERSION = '3.5.0';

/** Same work as in the app: about a second on a phone. */
const BM_ALTCHA_COST = 5000;

/** Seconds a challenge stays valid. */
const BM_ALTCHA_VALIDITY = 300;

/**
 * HMAC key from the environment; empty if not configured.
 *
 * @return string
 */
function bm_altcha_key() {
	$key = getenv( 'BM_ALTCHA_HMAC_KEY' );
	return false === $key ? '' : $key;
}

/**
 * JSON of the challenge parameters as the widget library builds it for the signature (sorted keys, no spaces).
 *
 * @param array $parameters Challenge parameters.
 * @return string
 */
function bm_altcha_canonical_json( array $parameters ) {
	ksort( $parameters );
	return (string) wp_json_encode( $parameters, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
}

/**
 * A new signed challenge.
 *
 * @return array{parameters: array, signature: string}
 */
function bm_altcha_create_challenge() {
	$parameters = array(
		'algorithm' => 'PBKDF2/SHA-256',
		'cost'      => BM_ALTCHA_COST,
		'expiresAt' => time() + BM_ALTCHA_VALIDITY,
		'keyLength' => 32,
		'keyPrefix' => '00',
		'nonce'     => bin2hex( random_bytes( 16 ) ),
		'salt'      => bin2hex( random_bytes( 16 ) ),
	);
	ksort( $parameters );
	return array(
		'parameters' => $parameters,
		'signature'  => hash_hmac( 'sha256', bm_altcha_canonical_json( $parameters ), bm_altcha_key() ),
	);
}

/**
 * Checks a solved challenge from the form. Each challenge counts only once.
 *
 * @param string $payload Value of the form field "altcha".
 * @return bool
 */
function bm_altcha_verify( $payload ) {
	$key = bm_altcha_key();
	if ( '' === $key || ! is_string( $payload ) || '' === $payload ) {
		return false;
	}
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- ALTCHA payloads are base64 JSON.
	$data = json_decode( (string) base64_decode( $payload, true ), true );
	if ( ! is_array( $data ) || ! isset( $data['challenge']['parameters'], $data['challenge']['signature'], $data['solution'] ) ) {
		return false;
	}
	$parameters = $data['challenge']['parameters'];
	$signature  = $data['challenge']['signature'];
	$solution   = $data['solution'];
	if ( ! is_array( $parameters ) || ! is_string( $signature ) || ! is_array( $solution ) ) {
		return false;
	}

	// Signature first: everything after it may rely on the parameters being ours.
	if ( ! hash_equals( hash_hmac( 'sha256', bm_altcha_canonical_json( $parameters ), $key ), $signature ) ) {
		return false;
	}
	if ( 'PBKDF2/SHA-256' !== ( $parameters['algorithm'] ?? '' ) || (int) ( $parameters['expiresAt'] ?? 0 ) < time() ) {
		return false;
	}

	$counter     = $solution['counter'] ?? null;
	$derived_key = $solution['derivedKey'] ?? null;
	if ( ! is_int( $counter ) || $counter < 0 || $counter > 4294967295
		|| ! is_string( $derived_key ) || ! preg_match( '/^(?:[0-9a-f]{2})+$/', $derived_key ) ) {
		return false;
	}

	$key_bytes = hash_pbkdf2(
		'sha256',
		hex2bin( (string) $parameters['nonce'] ) . pack( 'N', $counter ),
		hex2bin( (string) $parameters['salt'] ),
		(int) $parameters['cost'],
		(int) $parameters['keyLength'],
		true
	);
	$key_hex   = bin2hex( $key_bytes );
	if ( ! hash_equals( $key_hex, $derived_key ) || 0 !== strpos( $key_hex, strtolower( (string) $parameters['keyPrefix'] ) ) ) {
		return false;
	}

	return bm_altcha_claim( $signature, (int) $parameters['expiresAt'] );
}

/**
 * Marks a solved challenge as used – atomically, so of parallel requests with the same solution exactly one wins.
 * A read-then-write (transients) let N simultaneous registrations through for one proof of work. The row goes into
 * the options table, whose option_name is unique: INSERT IGNORE inserts it once, every other attempt changes nothing.
 * Entries are removed once their challenge has expired anyway.
 *
 * @param string $signature  Signature of the challenge.
 * @param int    $expires_at Unix time the challenge expires.
 * @return bool Whether this request is the first to use it.
 */
function bm_altcha_claim( $signature, $expires_at ) {
	global $wpdb;
	$name = 'bm_altcha_used_' . md5( $signature );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- the options API has no atomic "insert if missing"; no cache involved.
	$inserted = $wpdb->query(
		$wpdb->prepare(
			"INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')",
			$name,
			(string) $expires_at
		)
	);
	if ( 1 !== $inserted ) {
		return false;
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- clean-up of our own expired rows, see above.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(option_value AS UNSIGNED) < %d LIMIT 100",
			$wpdb->esc_like( 'bm_altcha_used_' ) . '%',
			time()
		)
	);
	return true;
}

add_action(
	'rest_api_init',
	/** GET /wp-json/bike-maintainer/v1/altcha-challenge – fetched by the widget. */
	static function () {
		register_rest_route(
			'bike-maintainer/v1',
			'/altcha-challenge',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => static function () {
					if ( '' === bm_altcha_key() ) {
						return new WP_Error( 'bm_altcha_unconfigured', 'ALTCHA is not configured.', array( 'status' => 503 ) );
					}
					$response = rest_ensure_response( bm_altcha_create_challenge() );
					$response->header( 'Cache-Control', 'no-store' );
					return $response;
				},
			)
		);
	}
);

/**
 * Widget texts in the current language (same wording as the app).
 *
 * @return array<string, string>
 */
function bm_altcha_strings() {
	return array(
		'ariaLinkLabel'        => __( 'Visit altcha.org', 'bike-maintainer' ),
		'error'                => __( 'Verification failed, please try again later.', 'bike-maintainer' ),
		'expired'              => __( 'Verification expired, please try again.', 'bike-maintainer' ),
		'footer'               => sprintf(
			/* translators: %s: link to altcha.org with the name ALTCHA. */
			__( 'Protected by %s', 'bike-maintainer' ),
			'<a href="https://altcha.org/" target="_blank" rel="noopener noreferrer">ALTCHA</a>'
		),
		'label'                => __( "I'm not a robot", 'bike-maintainer' ),
		'loading'              => __( 'Loading …', 'bike-maintainer' ),
		'reload'               => __( 'Reload', 'bike-maintainer' ),
		'verify'               => __( 'Verify', 'bike-maintainer' ),
		'verificationRequired' => __( 'Verification required!', 'bike-maintainer' ),
		'verified'             => __( 'Verified', 'bike-maintainer' ),
		'verifying'            => __( 'Verifying …', 'bike-maintainer' ),
		'waitAlert'            => __( 'Verifying … please wait.', 'bike-maintainer' ),
	);
}

add_action(
	'login_enqueue_scripts',
	/** Widget script on the login page (registration and lost password), served from our own server. */
	static function () {
		wp_enqueue_script( 'bm-altcha', WPMU_PLUGIN_URL . '/bm-altcha/altcha.min.js', array(), BM_ALTCHA_WIDGET_VERSION, true );
		$language = substr( determine_locale(), 0, 2 );
		wp_add_inline_script(
			'bm-altcha',
			sprintf( 'globalThis.$altcha.i18n.set(%s, %s);', wp_json_encode( $language ), wp_json_encode( bm_altcha_strings() ) )
		);
	}
);

/** Prints the widget into a form on the login page. */
function bm_altcha_widget() {
	if ( '' === bm_altcha_key() ) {
		printf( '<p class="message">%s</p>', esc_html__( 'This form is not available at the moment.', 'bike-maintainer' ) );
		return;
	}
	printf(
		'<div class="bm-altcha" style="margin: 0 0 16px"><altcha-widget challenge="%1$s" auto="onload" name="altcha" language="%2$s"></altcha-widget></div>',
		esc_url( rest_url( 'bike-maintainer/v1/altcha-challenge' ) ),
		esc_attr( substr( determine_locale(), 0, 2 ) )
	);
}
add_action( 'register_form', 'bm_altcha_widget' );
add_action( 'lostpassword_form', 'bm_altcha_widget' );

/**
 * Whether the current form submission carries a valid solution.
 *
 * @return bool
 */
function bm_altcha_request_is_valid() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- the login forms have no nonce; ALTCHA is the check.
	$payload = isset( $_POST['altcha'] ) ? sanitize_text_field( wp_unslash( $_POST['altcha'] ) ) : '';
	return bm_altcha_verify( $payload );
}

add_filter(
	'registration_errors',
	/**
	 * Refuses a registration without a valid solution.
	 *
	 * @param WP_Error $errors Registration errors.
	 * @return WP_Error
	 */
	static function ( $errors ) {
		if ( ! bm_altcha_request_is_valid() ) {
			$errors->add( 'bm_altcha', __( 'Please confirm that you are not a robot.', 'bike-maintainer' ) );
		}
		return $errors;
	}
);

add_action(
	'lostpassword_post',
	/**
	 * Refuses a password reset request without a valid solution (it would send a mail).
	 *
	 * @param WP_Error $errors Errors of the request.
	 */
	static function ( $errors ) {
		// Only the public form on the login page; administrators can also send reset links from the user list.
		if ( 'wp-login.php' !== ( $GLOBALS['pagenow'] ?? '' ) ) {
			return;
		}
		if ( ! bm_altcha_request_is_valid() ) {
			$errors->add( 'bm_altcha', __( 'Please confirm that you are not a robot.', 'bike-maintainer' ) );
		}
	}
);
