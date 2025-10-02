<?php
/**
 * Plugin
 *
 * @package UnaxPlugin
 * @author  Unax
 */

namespace Easy_Shipping_API\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin main class.
 */
class Easy_Shipping_API {
	
	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'easy-shipping', false, EASY_SHIPPING_API_SLUG . '/languages' );
	}


	/**
	 * Activate plugin.
	 *
	 * @return void
	 */
	public static function activate() {}


	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 */
	public static function deactivate() {}
}
