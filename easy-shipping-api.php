<?php
/**
 * Plugin Name: Easy Shipping API
 * Description: Easy Shipping API.
 * Version: 1.0.0
 * Author: Unax
 * Text Domain: easy-shipping-api
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: Proprietary
 *
 * @package Easy_Shipping_API
 */

namespace Easy_Shipping_API;

use Unax\Helper\Helper;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'EASY_SHIPPING_API_NAME', 'couriers-api' );
define( 'EASY_SHIPPING_API_PATH', plugin_dir_path( __FILE__ ) );
define( 'EASY_SHIPPING_API_URL', plugin_dir_url( __FILE__ ) );
define( 
	'EASY_SHIPPING_API_ERROR_CODES', 
	array(
		1  => 'Invalid endpoint',
		2  => 'Missing endpoint',
		3  => 'Invalid parameter',
		4  => 'Parameter config key not found',
		5  => 'Encode JSON data failed',
		6  => 'Decode JSON data failed',
		7  => 'HTTP request error',
		8  => 'Response no data',
		9  => 'Response body empty',
		10 => 'Response dataset empty',
		11 => 'Request failed',
		12 => 'Response data contain errors',
		90 => 'Configuration error',
		99 => 'Unknown error',
	)
);

// Autoload.
require EASY_SHIPPING_API_PATH . 'vendor/autoload.php';

// Include main class.
require EASY_SHIPPING_API_PATH . 'inc/class-easy-shipping-api.php';

add_action( 'init', array( '\Easy_Shipping_API\Inc\Easy_Shipping_API', 'load_textdomain' ) );
add_action( 'rest_api_init', array( '\Easy_Shipping_API\Inc\Rest_API', 'register_rest_fields' ) );

Helper::set_log_threshold( WP_DEBUG ? 'debug' : 'error' );
Helper::config();
