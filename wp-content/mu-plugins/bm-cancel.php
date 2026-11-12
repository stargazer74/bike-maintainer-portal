<?php
/**
 * Plugin Name: Bike Maintainer – Cancellation button
 * Description: Form "Cancel contracts here" for the subscription of the portal (§ 312k BGB): reachable without login, button "Cancel now", immediate confirmation by email with date and time, a copy goes to the operator. Nothing is stored in the database. Shortcode [bm_cancel_form].
 * Author: Chris Wohlbrecht
 * License: MIT
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

/** Submissions per client IP within BM_CANCEL_WINDOW. */
const BM_CANCEL_MAX_PER_IP = 5;
/** Submissions of all visitors per day; protects the mailbox and the sending quota against floods. */
const BM_CANCEL_MAX_PER_DAY = 50;
const BM_CANCEL_WINDOW      = HOUR_IN_SECONDS;

/**
 * Transient key of the per-IP counter; only a hash of the IP is stored (REMOTE_ADDR comes from mod_remoteip).
 *
 * @return string
 */
function bm_cancel_ip_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return 'bm_cancel_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 32 );
}

/**
 * Counts a submission; false if the client or all visitors are over the limit.
 *
 * @return bool
 */
function bm_cancel_allowed() {
	$ip_key = bm_cancel_ip_key();
	$by_ip  = (int) get_transient( $ip_key );
	$by_day = (int) get_transient( 'bm_cancel_day' );
	if ( $by_ip >= BM_CANCEL_MAX_PER_IP || $by_day >= BM_CANCEL_MAX_PER_DAY ) {
		return false;
	}
	set_transient( $ip_key, $by_ip + 1, BM_CANCEL_WINDOW );
	set_transient( 'bm_cancel_day', $by_day + 1, DAY_IN_SECONDS );
	return true;
}

/**
 * Link to the legal notice of the current language (contact for anything the form cannot do).
 *
 * @return string
 */
function bm_cancel_imprint_link() {
	$url = function_exists( 'bike_maintainer_legal_url' ) ? bike_maintainer_legal_url( 'imprint' ) : home_url( '/' );
	return '<a href="' . esc_url( $url ) . '">' . esc_html__( 'legal notice', 'bike-maintainer' ) . '</a>';
}

/**
 * Values of the form, trimmed; empty strings for missing fields.
 *
 * @return array{name: string, email: string, type: string, reason: string, when: string, date: string, trap: string}
 */
function bm_cancel_input() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- checked in bm_cancel_handle() before use.
	$field = static function ( $name, $multiline = false ) {
		if ( ! isset( $_POST[ $name ] ) || ! is_string( $_POST[ $name ] ) ) {
			return '';
		}
		return trim(
			$multiline
				? sanitize_textarea_field( wp_unslash( $_POST[ $name ] ) )
				: sanitize_text_field( wp_unslash( $_POST[ $name ] ) )
		);
	};
	// phpcs:enable
	return array(
		'name'   => $field( 'bm_cancel_name' ),
		'email'  => $field( 'bm_cancel_email' ),
		'type'   => 'extraordinary' === $field( 'bm_cancel_type' ) ? 'extraordinary' : 'ordinary',
		'reason' => $field( 'bm_cancel_reason', true ),
		'when'   => 'date' === $field( 'bm_cancel_when' ) ? 'date' : 'next',
		'date'   => $field( 'bm_cancel_date' ),
		'trap'   => $field( 'bm_cancel_website' ),
	);
}

/**
 * Checks the input; error messages per field.
 *
 * @param array $input Values from bm_cancel_input().
 * @return array<string, string>
 */
function bm_cancel_errors( $input ) {
	$errors = array();
	if ( '' === $input['name'] || mb_strlen( $input['name'] ) > 200 ) {
		$errors['name'] = __( 'Please enter your name.', 'bike-maintainer' );
	}
	if ( ! is_email( $input['email'] ) ) {
		$errors['email'] = __( 'Please enter a valid email address.', 'bike-maintainer' );
	}
	if ( 'date' === $input['when'] ) {
		$date  = DateTimeImmutable::createFromFormat( '!Y-m-d', $input['date'], wp_timezone() );
		$today = new DateTimeImmutable( 'today', wp_timezone() );
		if ( false === $date || $date->format( 'Y-m-d' ) !== $input['date'] || $date < $today ) {
			$errors['date'] = __( 'Please choose today or a later date.', 'bike-maintainer' );
		}
	}
	if ( mb_strlen( $input['reason'] ) > 2000 ) {
		$errors['reason'] = __( 'Please shorten the reason to 2000 characters.', 'bike-maintainer' );
	}
	return $errors;
}

/**
 * The declaration as lines "label: value", for the mails and the confirmation page.
 *
 * @param array  $input       Valid values.
 * @param string $received_at Date and time of receipt.
 * @return array<string, string>
 */
function bm_cancel_summary( $input, $received_at ) {
	$summary = array(
		__( 'Contract', 'bike-maintainer' )             => __( 'Subscription to the Bike Maintainer portal', 'bike-maintainer' ),
		__( 'Name', 'bike-maintainer' )                 => $input['name'],
		__( 'Email address', 'bike-maintainer' )        => $input['email'],
		__( 'Type of cancellation', 'bike-maintainer' ) => 'extraordinary' === $input['type']
			? __( 'Extraordinary cancellation (for good cause)', 'bike-maintainer' )
			: __( 'Ordinary cancellation', 'bike-maintainer' ),
		__( 'Cancel as of', 'bike-maintainer' )         => 'date' === $input['when']
			? wp_date( get_option( 'date_format' ), DateTimeImmutable::createFromFormat( '!Y-m-d', $input['date'], wp_timezone() )->getTimestamp() )
			: __( 'Next possible date', 'bike-maintainer' ),
	);
	if ( '' !== $input['reason'] ) {
		$summary[ __( 'Reason', 'bike-maintainer' ) ] = $input['reason'];
	}
	$summary[ __( 'Received on', 'bike-maintainer' ) ] = $received_at;
	return $summary;
}

/**
 * Plain text of the summary for the mails.
 *
 * @param array<string, string> $summary Lines.
 * @return string
 */
function bm_cancel_text( $summary ) {
	$lines = array();
	foreach ( $summary as $label => $value ) {
		$lines[] = $label . ': ' . $value;
	}
	return implode( "\n", $lines );
}

/**
 * Processes a submitted form.
 *
 * @return array{state: string, errors?: array<string, string>, input?: array, summary?: array<string, string>, confirmed?: bool}
 */
function bm_cancel_handle() {
	$nonce = isset( $_POST['bm_cancel_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['bm_cancel_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'bm_cancel' ) ) {
		return array(
			'state'  => 'form',
			'errors' => array( 'form' => __( 'The form has expired. Please submit it again.', 'bike-maintainer' ) ),
			'input'  => bm_cancel_input(),
		);
	}
	$input = bm_cancel_input();
	if ( '' !== $input['trap'] ) {
		// Filled in by a bot (the field is invisible to people): pretend success, send nothing.
		return array( 'state' => 'ignored' );
	}
	$errors = bm_cancel_errors( $input );
	if ( $errors ) {
		return array(
			'state'  => 'form',
			'errors' => $errors,
			'input'  => $input,
		);
	}
	if ( ! bm_cancel_allowed() ) {
		return array(
			'state'  => 'form',
			'errors' => array(
				/* translators: %s: link to the legal notice */
				'form' => sprintf( __( 'Too many cancellations were sent from your connection. Please try again later or write to us by email (see %s).', 'bike-maintainer' ), bm_cancel_imprint_link() ),
			),
			'input'  => $input,
		);
	}

	$received_at = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) . ' T' );
	$summary     = bm_cancel_summary( $input, $received_at );
	$text        = bm_cancel_text( $summary );

	$to_operator = wp_mail(
		get_option( 'admin_email' ),
		/* translators: %s: email address of the customer */
		sprintf( __( 'Cancellation of a subscription: %s', 'bike-maintainer' ), $input['email'] ),
		$text,
		array( 'Reply-To: ' . $input['email'] )
	);
	if ( ! $to_operator ) {
		return array(
			'state'  => 'form',
			'errors' => array(
				/* translators: %s: link to the legal notice */
				'form' => sprintf( __( 'Your cancellation could not be sent. Please send it to us by email (see %s).', 'bike-maintainer' ), bm_cancel_imprint_link() ),
			),
			'input'  => $input,
		);
	}
	$confirmed = wp_mail(
		$input['email'],
		__( 'Confirmation of your cancellation', 'bike-maintainer' ),
		__( 'We have received your cancellation:', 'bike-maintainer' ) . "\n\n" . $text . "\n\n"
			. __( 'You do not need to do anything else. Your data in the app stays readable after the subscription ends.', 'bike-maintainer' )
	);
	return array(
		'state'     => 'done',
		'summary'   => $summary,
		'confirmed' => $confirmed,
		'email'     => $input['email'],
	);
}

/**
 * Error message of a field, if any.
 *
 * @param array  $errors Errors.
 * @param string $field  Field name.
 * @return string
 */
function bm_cancel_error( $errors, $field ) {
	return isset( $errors[ $field ] )
		? '<p class="bm-cancel__error" id="bm-cancel-' . esc_attr( $field ) . '-error">' . esc_html( $errors[ $field ] ) . '</p>'
		: '';
}

/**
 * The form.
 *
 * @param array $input  Previous values.
 * @param array $errors Errors of the previous submission.
 * @return string
 */
function bm_cancel_form( $input, $errors ) {
	$input   = wp_parse_args(
		$input,
		array(
			'name'   => '',
			'email'  => '',
			'type'   => 'ordinary',
			'reason' => '',
			'when'   => 'next',
			'date'   => '',
		)
	);
	$invalid = static function ( $field ) use ( $errors ) {
		return isset( $errors[ $field ] )
			? ' aria-invalid="true" aria-describedby="bm-cancel-' . esc_attr( $field ) . '-error"'
			: '';
	};
	$checked = static function ( $value, $expected ) {
		return $value === $expected ? ' checked' : '';
	};

	ob_start();
	?>
	<form class="bm-cancel" method="post" action="" novalidate>
		<?php
		if ( isset( $errors['form'] ) ) {
			echo '<p class="bm-cancel__error bm-cancel__error--form" role="alert">' . wp_kses( $errors['form'], array( 'a' => array( 'href' => array() ) ) ) . '</p>';
		}
		wp_nonce_field( 'bm_cancel', 'bm_cancel_nonce', false );
		?>
		<div class="bm-cancel__field">
			<label for="bm-cancel-name"><?php esc_html_e( 'Name', 'bike-maintainer' ); ?> *</label>
			<input type="text" id="bm-cancel-name" name="bm_cancel_name" required maxlength="200" autocomplete="name" value="<?php echo esc_attr( $input['name'] ); ?>"<?php echo $invalid( 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?>>
			<?php echo bm_cancel_error( $errors, 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function. ?>
		</div>
		<div class="bm-cancel__field">
			<label for="bm-cancel-email"><?php esc_html_e( 'Email address of your account', 'bike-maintainer' ); ?> *</label>
			<input type="email" id="bm-cancel-email" name="bm_cancel_email" required maxlength="254" autocomplete="email" value="<?php echo esc_attr( $input['email'] ); ?>"<?php echo $invalid( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?>>
			<?php echo bm_cancel_error( $errors, 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function. ?>
		</div>
		<fieldset>
			<legend><?php esc_html_e( 'Type of cancellation', 'bike-maintainer' ); ?></legend>
			<label><input type="radio" name="bm_cancel_type" value="ordinary"<?php echo esc_attr( $checked( $input['type'], 'ordinary' ) ); ?>> <?php esc_html_e( 'Ordinary cancellation', 'bike-maintainer' ); ?></label>
			<label><input type="radio" name="bm_cancel_type" value="extraordinary"<?php echo esc_attr( $checked( $input['type'], 'extraordinary' ) ); ?>> <?php esc_html_e( 'Extraordinary cancellation (for good cause)', 'bike-maintainer' ); ?></label>
		</fieldset>
		<div class="bm-cancel__field">
			<label for="bm-cancel-reason"><?php esc_html_e( 'Reason (optional, for an extraordinary cancellation)', 'bike-maintainer' ); ?></label>
			<textarea id="bm-cancel-reason" name="bm_cancel_reason" rows="3" maxlength="2000"<?php echo $invalid( 'reason' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?>><?php echo esc_textarea( $input['reason'] ); ?></textarea>
			<?php echo bm_cancel_error( $errors, 'reason' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function. ?>
		</div>
		<fieldset>
			<legend><?php esc_html_e( 'Cancel as of', 'bike-maintainer' ); ?></legend>
			<label><input type="radio" name="bm_cancel_when" value="next"<?php echo esc_attr( $checked( $input['when'], 'next' ) ); ?>> <?php esc_html_e( 'Next possible date', 'bike-maintainer' ); ?></label>
			<label><input type="radio" name="bm_cancel_when" value="date"<?php echo esc_attr( $checked( $input['when'], 'date' ) ); ?>> <?php esc_html_e( 'On this date:', 'bike-maintainer' ); ?>
				<input type="date" name="bm_cancel_date" aria-label="<?php esc_attr_e( 'Date of the cancellation', 'bike-maintainer' ); ?>" value="<?php echo esc_attr( $input['date'] ); ?>"<?php echo $invalid( 'date' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure. ?>></label>
			<?php echo bm_cancel_error( $errors, 'date' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function. ?>
		</fieldset>
		<div class="bm-cancel__trap" aria-hidden="true">
			<label for="bm-cancel-website">Website</label>
			<input type="text" id="bm-cancel-website" name="bm_cancel_website" tabindex="-1" autocomplete="off" value="">
		</div>
		<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
			<div class="wp-block-button"><button type="submit" class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Cancel now', 'bike-maintainer' ); ?></button></div>
		</div>
	</form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Confirmation after a submission; the visitor can save or print it (§ 312k para. 4 BGB).
 *
 * @param array $result Result of bm_cancel_handle().
 * @return string
 */
function bm_cancel_done( $result ) {
	$rows = '';
	foreach ( $result['summary'] as $label => $value ) {
		$rows .= '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $value ) ) . '</td></tr>';
	}
	$note = $result['confirmed']
		/* translators: %s: email address */
		? sprintf( __( 'We have sent a confirmation to %s. Please also save or print this page.', 'bike-maintainer' ), $result['email'] )
		: __( 'The confirmation email could not be sent. Please save or print this page as proof of your cancellation.', 'bike-maintainer' );
	return '<div class="bm-cancel bm-cancel--done" role="status">'
		. '<h2>' . esc_html__( 'Your cancellation has been received', 'bike-maintainer' ) . '</h2>'
		. '<table class="bm-cancel__summary"><tbody>' . $rows . '</tbody></table>'
		. '<p>' . esc_html( $note ) . '</p></div>';
}

/**
 * Whether the current request shows a page with the form.
 *
 * @return bool
 */
function bm_cancel_is_form_page() {
	return is_singular() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'bm_cancel_form' );
}

/**
 * Result of the submission in this request; null without one. Set once in template_redirect, so a shortcode that is
 * rendered more than once (excerpts, descriptions) never sends the mails twice.
 *
 * @param array|null $result New result to keep.
 * @return array|null
 */
function bm_cancel_result( $result = null ) {
	static $kept = null;
	if ( null !== $result ) {
		$kept = $result;
	}
	return $kept;
}

add_action(
	'template_redirect',
	/** Handles a submission and keeps the page out of every cache (it holds a nonce and a result). */
	static function () {
		if ( ! bm_cancel_is_form_page() ) {
			return;
		}
		nocache_headers();
		$posted = isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['bm_cancel_nonce'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in bm_cancel_handle().
		if ( $posted ) {
			bm_cancel_result( bm_cancel_handle() );
		}
	}
);

add_shortcode(
	'bm_cancel_form',
	/**
	 * Form, or the result of the submission in this request.
	 *
	 * @return string
	 */
	static function () {
		$result = bm_cancel_result();
		if ( null === $result ) {
			return bm_cancel_form( array(), array() );
		}
		if ( 'ignored' === $result['state'] ) {
			return '<div class="bm-cancel bm-cancel--done" role="status"><h2>' . esc_html__( 'Your cancellation has been received', 'bike-maintainer' ) . '</h2></div>';
		}
		return 'done' === $result['state'] ? bm_cancel_done( $result ) : bm_cancel_form( $result['input'], $result['errors'] );
	}
);
