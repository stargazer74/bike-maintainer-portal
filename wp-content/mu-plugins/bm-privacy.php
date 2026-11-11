<?php
/**
 * Plugin Name: Bike Maintainer – Privacy
 * Description: Avatars never come from gravatar.com (that would send each visitor's IP address and a hash of the members' email addresses to a third party); every avatar is a local placeholder – also where plugins such as Asgaros Forum force avatars on. Login names never appear publicly: members get a neutral display name and URL name.
 * Author: Chris Wohlbrecht
 * License: MIT
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'pre_get_avatar_data',
	/**
	 * Local placeholder instead of Gravatar for every avatar request.
	 *
	 * @param array $args Avatar arguments.
	 * @return array
	 */
	static function ( $args ) {
		$args['url']          = WPMU_PLUGIN_URL . '/bm-privacy/avatar.svg';
		$args['found_avatar'] = true;
		return $args;
	}
);

/*
 * No login names in public (#15). WordPress makes the login name the display name and, slightly changed, the URL name
 * (user_nicename); Asgaros Forum shows the display name at every post and uses the URL name in profile links and
 * @mentions. That publishes half of the credentials and often the real name. Members get "Mitglied #<ID>" and
 * "member-<ID>" instead; a nickname can be chosen in the profile, just not the login name.
 */

/**
 * Whether a public name of the account is (derived from) its login name.
 *
 * @param WP_User $user Account.
 * @return bool
 */
function bm_shows_login_name( WP_User $user ) {
	$login = strtolower( $user->user_login );
	return strtolower( $user->display_name ) === $login
		|| strtolower( (string) $user->nickname ) === $login
		|| sanitize_title( $user->user_login ) === $user->user_nicename;
}

/**
 * Replaces the public names of an account by neutral ones, if they show the login name.
 *
 * @param int $user_id Account ID.
 * @return bool Whether something was changed.
 */
function bm_neutralize_public_names( $user_id ) {
	$user = get_userdata( $user_id );
	if ( ! $user || ! bm_shows_login_name( $user ) ) {
		return false;
	}
	/* translators: %d: user ID */
	$name = sprintf( __( 'Member #%d', 'bike-maintainer' ), $user->ID );
	$data = array( 'ID' => $user->ID );
	if ( strtolower( $user->display_name ) === strtolower( $user->user_login ) ) {
		$data['display_name'] = $name;
	}
	if ( strtolower( (string) $user->nickname ) === strtolower( $user->user_login ) ) {
		$data['nickname'] = $name;
	}
	if ( sanitize_title( $user->user_login ) === $user->user_nicename ) {
		$data['user_nicename'] = 'member-' . $user->ID;
	}
	return ! is_wp_error( wp_update_user( $data ) );
}

add_action( 'user_register', 'bm_neutralize_public_names' );

add_action(
	'user_profile_update_errors',
	/**
	 * Refuses the login name as nickname or display name in the profile.
	 *
	 * @param WP_Error $errors Profile errors.
	 * @param bool     $update Whether an existing account is updated.
	 * @param stdClass $user   Submitted data.
	 */
	static function ( $errors, $update, $user ) {
		$login = $update ? get_userdata( $user->ID )->user_login : ( $user->user_login ?? '' );
		foreach ( array( 'nickname', 'display_name' ) as $field ) {
			if ( isset( $user->$field ) && '' !== $login && strtolower( $user->$field ) === strtolower( $login ) ) {
				$errors->add(
					'bm_login_name_public',
					__( 'Your username must not be shown publicly. Please choose another nickname and display name.', 'bike-maintainer' )
				);
				return;
			}
		}
	},
	10,
	3
);
