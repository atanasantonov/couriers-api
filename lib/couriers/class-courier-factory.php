<?php
/**
 * Courier Factory
 *
 * @package Easy_Shipping
 */

namespace Easy_Shipping\Lib\Couriers;

use Easy_Shipping\Lib\Couriers\Courier_API_Interface;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Factory class for creating courier API instances.
 */
class Courier_Factory {
	/**
	 * Create courier API instance.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return Courier_API_Interface|\WP_Error Courier API instance or WP_Error on failure.
	 */
	public static function create( WP_REST_Request $request ) {
		$country = $request->get_param( 'courier_country' );
		if ( empty( $country ) ) {
			return new \WP_Error(
				'missing_country',
				'Parameter "courier_country" is required.',
				array( 
					'error_code' => 40,
					'status' 	 => 400 
				)
			);
		}

		$courier = $request->get_param( 'courier_name' );
		if ( empty( $courier ) ) {
			return new \WP_Error(
				'missing_courier',
				'Parameter "courier_name" is required.',
				array( 
					'error_code' => 41,
					'status' 	 => 400 
				)
			);
		}

		if ( ! self::is_supported( $country, $courier ) ) {
			return new \WP_Error(
				'invalid_courier',
				sprintf( 'Courier "%s" is not supported.', $courier ),
				array( 
					'error_code' => 42,
					'status' 	 => 400 
				)
			);
		}		

		$courier_class_file = sprintf( '%1$s/lib/couriers/%2$s/%3$s/class-%3$s-api.php', EASY_SHIPPING_API_PATH, $country, $courier );
		if ( ! file_exists( $courier_class_file ) ) {
			return new \WP_Error(
				'missing_courier',
				'Courier not found.',
				array( 
					'error_code' => 43,
					'status' 	 => 404 
				)
			);
		}

		// Include the courier file.
		require_once $courier_class_file;

		$courier_config_file = sprintf( '%1$s/lib/couriers/%2$s/%3$s/config.php', EASY_SHIPPING_API_PATH, $country, $courier );
		if ( ! file_exists( $courier_config_file ) ) {
			return new \WP_Error(
				'missing_courier_config',
				'Courier configuration not found.',
				array( 
					'error_code' => 44,
					'status' 	 => 404 
				)
			);
		}

		// Get authorization parameters.
		$authorization = $request->get_param( 'authorization' );
		if ( empty( $authorization ) || ! is_array( $authorization ) ) {
			return new \WP_Error(
				'missing_authorization',
				'Authorization parameters are required and must be an array.',
				array( 
					'error_code' => 45,
					'status' 	 => 400 
				)
			);
		}

		// Get mode (test/live).
		$mode = $request->get_param( 'mode' );
		if ( empty( $mode ) || ! is_bool( $mode ) ) {
			return new \WP_Error(
				'missing_mode',
				'Courier mode is required.',
				array( 
					'error_code' => 46,
					'status' 	 => 400 
				)
			);
		}

		if ( ! in_array( $mode, array( 'test', 'live' ), true ) ) {
			return new \WP_Error(
				'invalid_mode',
				'Courier mode not valid.',
				array( 
					'error_code' => 47,
					'status' 	 => 400 
				)
			);
		}

		$test_mode = ( 'test' === $mode );

		// Include the courier class file.
		require_once $courier_class_file;

		// Get courier class instance.
		$courier_class = sprintf( '\Easy_Shipping\Lib\Couriers\%1$s\%2$s\%2$s_API', strtoupper( $country ), ucfirst( $courier ) );

		return new $courier_class( $authorization, $test_mode );
	}

	/**
	 * Get list of supported couriers.
	 *
	 * @return array Array of supported courier names.
	 */
	public static function get_supported_couriers() {
		// Loop through the directories in lib/couriers to find supported couriers.
		$couriers_path = EASY_SHIPPING_API_PATH . 'lib/couriers/';
		$couriers 	   = array();

		if ( is_dir( $couriers_path ) ) {
			$dir = opendir( $couriers_path );
			while ( ( $country = readdir( $dir ) ) !== false ) {
				if ( $country !== '.' && $country !== '..' && is_dir( $couriers_path . $country ) ) {
					$subdir = opendir( $couriers_path . $country );
					while ( ( $file = readdir( $subdir ) ) !== false ) {
						if ( $file !== '.' && $file !== '..' && is_dir( $couriers_path . $country . '/' . $file ) ) {
							$couriers[] = sprintf( '%s/%s', $country, $file );
						}
					}
					closedir( $subdir );
				}
			}
			closedir( $dir );
		}

		return $couriers;
	}


	/**
	 * Check if courier is supported by the license.
	 *
	 * @param string $courier_country Courier country code.
	 * @param string $courier_name Courier name.
	 *
	 * @return bool
	 */
	public static function is_supported( string $courier_country = '', string $courier_name = '' ): bool {
		if ( empty( $courier_country ) || empty( $courier_name ) ) {
			return false;
		}

		$courier = sprintf( '%s/%s', $courier_country, $courier_name );
		if ( in_array( $courier, self::get_supported_couriers(), true ) ) {
			return true;
		}

		return false;
	}
}
