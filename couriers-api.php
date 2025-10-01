<?php
/**
 * Plugin Name: Couriers API
 * Description: Couriers API for Easy Shipping.
 * Version: 1.0.0
 * Author: Unax
 * Text Domain: couriers-api
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: Proprietary
 *
 * @package Couriers_API
 */

namespace Couriers_API;

use Unax\Helper\Helper;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'COURIERS_API_VERSION', '1.0.0' );
define( 'COURIERS_API_NAME', 'couriers-api' );
define( 'COURIERS_API_PATH', plugin_dir_path( __FILE__ ) );
define( 'COURIERS_API_URL', plugin_dir_url( __FILE__ ) );
define( 
	'COURIERS_API_ERROR_CODES', 
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

require COURIERS_API_PATH . 'vendor/autoload.php';

Helper::set_log_threshold( 'debug' );
Helper::config();
