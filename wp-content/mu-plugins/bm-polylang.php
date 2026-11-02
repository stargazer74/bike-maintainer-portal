<?php
/**
 * Plugin Name: Bike Maintainer – Polylang without cookie
 * Description: Polylang must not set its language cookie (pll_language, valid for a year) – the language comes from the URL alone (/ German, /en/ English). Must-use plugins load before plugins, so the constant is in place in time.
 * Author: Chris Wohlbrecht
 * License: MIT
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PLL_COOKIE' ) ) {
	define( 'PLL_COOKIE', false );
}
