<?php
/**
 * Courier Factory
 *
 * @package Easy_Shipping
 */

namespace Easy_Shipping\Lib\Couriers;

use \Easy_Shipping\Lib\Couriers\Courier_API_Interface;

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

		if ( ! is_courier_supported( $courier ) ) {
			return new \WP_Error(
				'invalid_courier',
				sprintf( 
					'Courier "%s" is not supported. Supported couriers: %s', $courier, implode( ', ', self::$supported_couriers ) ),
				array( 'status' => 400 )
			);
		}		

		$courier_class_file = sprintf( '%s/lib/couriers/%s/%s/class-%s-api.php', EASY_SHIPPING_API_PATH, $country, $courier, $courier );
		if ( ! file_exists( $courier_class_file ) ) {
			return new \WP_Error(
				'missing_courier_file',
				'Courier not found.',
				array( 
					'error_code' => 42,
					'status' 	 => 404 
				)
			);
		}

		require_once $courier_class_file;
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
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return bool
	 */
	public static function is_supported( WP_REST_Request $request ): bool {
		$api_key = $request->get_header( 'X-API-Key' );
		$country = $request->get_param( 'courier_country' );
		$courier = $request->get_param( 'courier_name' );

		// TODO: Check if the courier is supported by the license.
		return true;
	}
}
