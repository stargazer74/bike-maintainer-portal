<?php
/**
 * Plugin Name: Bike Maintainer – Privacy
 * Description: Avatars never come from gravatar.com (that would send each visitor's IP address and a hash of the members' email addresses to a third party); every avatar is a local placeholder – also where plugins such as Asgaros Forum force avatars on.
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
