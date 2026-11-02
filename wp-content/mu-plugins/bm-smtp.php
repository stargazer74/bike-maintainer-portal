<?php
/**
 * Plugin Name: Bike Maintainer – Mail via SMTP
 * Description: Sends all mail through the SMTP server from the environment (BM_SMTP_*), e.g. noreply@bike-maintainer.de at IONOS or Mailpit in development. Without BM_SMTP_HOST no mail is sent at all.
 * Author: Chris Wohlbrecht
 * License: MIT
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

/**
 * A setting from the container environment, or the default.
 *
 * @param string $name    Variable name.
 * @param string $fallback Value if the variable is not set or empty.
 * @return string
 */
function bm_smtp_env( $name, $fallback = '' ) {
	$value = getenv( $name );
	return false === $value || '' === $value ? $fallback : $value;
}

if ( '' === bm_smtp_env( 'BM_SMTP_HOST' ) ) {
	// No fallback to PHP's mail(): the container has no mail server, and mail must never leave unauthenticated.
	add_filter( 'pre_wp_mail', '__return_false' );

	add_action(
		'admin_notices',
		/** Tells administrators that no mail goes out (registrations, password resets, notifications). */
		static function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'Es werden keine E-Mails versendet: BM_SMTP_HOST ist nicht gesetzt.', 'bike-maintainer' )
			);
		}
	);
	return;
}

add_action(
	'phpmailer_init',
	/**
	 * Configures PHPMailer for the SMTP server. Port 465 uses implicit TLS, 587 STARTTLS (required), any other port
	 * (e.g. 1025 for Mailpit) no encryption; BM_SMTP_SECURE (ssl, tls or none) overrides this.
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer $mailer The mailer used by wp_mail().
	 */
	static function ( $mailer ) {
		$port   = (int) bm_smtp_env( 'BM_SMTP_PORT', '587' );
		$secure = bm_smtp_env( 'BM_SMTP_SECURE', 465 === $port ? 'ssl' : ( 587 === $port ? 'tls' : 'none' ) );
		$user   = bm_smtp_env( 'BM_SMTP_USER' );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer API.
		$mailer->isSMTP();
		$mailer->Host        = bm_smtp_env( 'BM_SMTP_HOST' );
		$mailer->Port        = $port;
		$mailer->SMTPSecure  = 'none' === $secure ? '' : $secure;
		$mailer->SMTPAutoTLS = 'tls' === $secure;
		$mailer->SMTPAuth    = '' !== $user;
		$mailer->Username    = $user;
		$mailer->Password    = bm_smtp_env( 'BM_SMTP_PASSWORD' );
		$mailer->Timeout     = 10;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
);

add_filter( 'wp_mail_from', static fn() => bm_smtp_env( 'BM_SMTP_FROM', 'noreply@bike-maintainer.de' ) );
add_filter( 'wp_mail_from_name', static fn() => 'Bike Maintainer' );
