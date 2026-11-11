<?php
/**
 * Helpers shared by the bm-setup scripts (each runs in its own `wp eval-file`, so they load this with require_once).
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

/** Steps run with bm_setup_once(); a site set up before #17 counts them as done. */
const BM_SETUP_ONCE_STEPS = array( 'date-formats', 'registration', 'forum-options', 'forum-appearance' );

/**
 * Starts the bookkeeping of bm_setup_once() – call it before the setup creates anything. A site set up by an older
 * bm-setup has no bm_setup_done yet, but its Polylang languages (only bm-setup creates them; activating Polylang does
 * not). Its settings are the admins' already, so all steps count as done there instead of being applied one last time.
 */
function bm_setup_init() {
	if ( false !== get_option( 'bm_setup_done' ) ) {
		return;
	}
	$languages = get_terms(
		array(
			'taxonomy'   => 'language',
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);
	$set_up    = is_array( $languages ) && array() !== $languages;
	update_option( 'bm_setup_done', $set_up ? BM_SETUP_ONCE_STEPS : array(), false );
	if ( $set_up ) {
		WP_CLI::log( 'Site set up before: its settings stay as they are' );
	}
}

/**
 * Applies defaults that belong to the admins afterwards only once (#17): a later bm-setup run keeps what they changed
 * in the backend. Done steps are remembered in the option bm_setup_done.
 *
 * @param string   $key   Name of the step, one of BM_SETUP_ONCE_STEPS.
 * @param callable $apply Sets the defaults.
 */
function bm_setup_once( $key, callable $apply ) {
	$done = (array) get_option( 'bm_setup_done', array() );
	if ( in_array( $key, $done, true ) ) {
		WP_CLI::log( "Kept the settings of step $key (they belong to the admins now)" );
		return;
	}
	$apply();
	$done[] = $key;
	update_option( 'bm_setup_done', $done, false );
}
