<?php
/**
 * Rest_API.
 *
 * @package Easy_Shipping_API
 */

namespace Easy_Shipping_API\Inc;

defined( 'ABSPATH' ) || exit;

use WP_REST_Request;
use WP_REST_Response;
use Exception;
use Easy_Shipping_API\Inc\Request\Request_Helper;
use Easy_Shipping\Lib\Courier_Factory;

/**
 * Class Rest API.
 */
class Rest_API {
	/**
	 * Registers the REST API routes for the plugin.
	 *
	 * @return void
	 */
	public static function register_rest_fields() {
		register_rest_route( 'api/v1', '/countries', array(
            'methods' => 'GET',
            'callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'get_supported_countries' ),
            'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));

        register_rest_route( 'api/v1', '/cities', array(
            'methods' => 'GET',
            'callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'get_cities' ),
            'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));

		register_rest_route( 'api/v1', '/offices', array(
			'methods'             => 'GET',
			'callback'            => array( 'Easy_Shipping_API\Inc\Rest_API', 'get_offices' ),
			'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));

		register_rest_route( 'api/v1', '/machines', array(
			'methods'             => 'GET',
			'callback'            => array( 'Easy_Shipping_API\Inc\Rest_API', 'get_machines' ),
			'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));

		register_rest_route( 'api/v1', '/mobiles', array(
			'methods'             => 'GET',
			'callback'            => array( 'Easy_Shipping_API\Inc\Rest_API', 'get_mobiles' ),
			'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));

		register_rest_route( 'api/v1', '/quarters', array(
			'methods'             => 'GET',
			'callback'            => array( 'Easy_Shipping_API\Inc\Rest_API', 'get_quarters' ),
			'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));

		register_rest_route( 'api/v1', '/streets', array(
			'methods'             => 'GET',
			'callback'            => array( 'Easy_Shipping_API\Inc\Rest_API', 'get_streets' ),
			'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));

		register_rest_route( 'api/v1', '/search', array(
			'methods'             => 'GET',
			'callback'            => array( 'Easy_Shipping_API\Inc\Rest_API', 'search' ),
			'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));
 
		register_rest_route( 'api/v1', '/calculate', array(
			'methods'             => 'GET',
			'callback'            => array( 'Easy_Shipping_API\Inc\Rest_API', 'calculate_shipping' ),
			'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));

		register_rest_route( 'api/v1', '/create', array(
			'methods'             => 'POST',
			'callback'            => array( 'Easy_Shipping_API\Inc\Rest_API', 'create_shipment' ),
			'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));

		register_rest_route( 'api/v1', '/track', array(
			'methods'             => 'POST',
			'callback'            => array( 'Easy_Shipping_API\Inc\Rest_API', 'track_shipment' ),
			'permission_callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'authorize' ),
		));
    }


	/**
	 * Authorize request.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return bool
	 */
	public static function authorize( WP_REST_Request $request ): bool {
		// Check if the request has a valid API key.
		$api_key = $request->get_header( 'X-API-Key' );
		if ( empty( $api_key ) ) {
			// return false;
		}

		return true;
	}


	/**
	 * Get courier instance from request.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return \Easy_Shipping\Lib\Courier_API\Courier_API_Interface|\WP_Error
	 */
	private static function get_courier_instance( WP_REST_Request $request ): \Easy_Shipping\Lib\Courier_API\Courier_API_Interface|\WP_Error {
		return Courier_Factory::create( $request );
	}


	/**
	 * Retrieves a list of cities with their IDs and labels in English and Bulgarian.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return WP_REST_Response The REST API response object with status code 200 and
	 *                          an array containing city data in the format:
	 *                                  ['id' => int, 'label' => string, 'labelBg' => string],
	 */
	public static function get_cities( WP_REST_Request $request ): WP_REST_Response {
		try {
			$courier = self::get_courier_instance( $request );
			if ( is_wp_error( $courier ) ) {
				return Request_Helper::handle_wp_error( $courier );
			}

			$params = $request->get_params();
			$items  = $courier->get_cities( $params );

			if ( is_wp_error( $items ) ) {
				$error_data = $items->get_error_data();
				$status_code = 500;

				if ( isset( $error_data['status'] ) ) {
					$status_code = $error_data['status'];
				}

				return new WP_REST_Response(
					array( 'message' => $items->get_error_message() ),
					$status_code
				);
			}

			return new WP_REST_Response( $items, 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getCode() );
		}
	}


	/**
	 * Get offices.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */
	public static function get_offices( WP_REST_Request $request ): WP_REST_Response {
		return self::get_offices_by_type( $request, 'real' );
	}


	/**
	 * Get machines.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */
	public static function get_machines( WP_REST_Request $request ): WP_REST_Response {
		return self::get_offices_by_type( $request, 'machine' );
	}


	/**
	 * Get mobiles.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */
	public static function get_mobiles( WP_REST_Request $request ): WP_REST_Response {
		return self::get_offices_by_type( $request, 'mobile' );
	}


	/**
	 * Get offices by type.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * @param string           $type    Type of office to retrieve ('real', 'machine', 'mobile').
	 * 
	 * @return WP_REST_Response 
	 */
	public static function get_offices_by_type( WP_REST_Request $request, $type = 'real' ): WP_REST_Response  {
		try {
			$courier = self::get_courier_instance( $request );

			if ( is_wp_error( $courier ) ) {
				$error_data = $courier->get_error_data();
				$status_code = 400;

				if ( isset( $error_data['status'] ) ) {
					$status_code = $error_data['status'];
				}

				return new WP_REST_Response(
					array( 'message' => $courier->get_error_message() ),
					$status_code
				);
			}

			$params = $request->get_params();

			if ( $type === 'machine' ) {
				$items = $courier->get_machines( $params );
			} else {
				$items = $courier->get_offices( $params );
			}

			if ( is_wp_error( $items ) ) {
				$error_data = $items->get_error_data();
				$status_code = 500;

				if ( isset( $error_data['status'] ) ) {
					$status_code = $error_data['status'];
				}

				return new WP_REST_Response(
					array( 'message' => $items->get_error_message() ),
					$status_code
				);
			}

			return new WP_REST_Response( $items, 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getCode() );
		}
	}


	/**
	 * Get quarters.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */
	public static function get_quarters( WP_REST_Request $request ): WP_REST_Response {
		try {
			$items = array();
	
			return new WP_REST_Response( $items, 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getCode() );
		}
	}


	/**
	 * Get streets.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */
	public static function get_streets( WP_REST_Request $request ): WP_REST_Response {
		try {
			$items = array();
	
			return new WP_REST_Response( $items, 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getCode() );
		}
	}


	/**
	 * Search.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */
	public static function search( WP_REST_Request $request ): WP_REST_Response {
		try {
			$items = array();
	
			return new WP_REST_Response( $items, 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getCode() );
		}
	}


	/**
	 * Calculate shipping.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */
	public static function calculate_shipping( WP_REST_Request $request ): WP_REST_Response {
		try {
			$data = array();

			return new WP_REST_Response( $data, 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getCode() );
		}
	}


	/**
	 * Create shipment.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */
	public static function create_shipment( WP_REST_Request $request ): WP_REST_Response {
		try {
			$data = array();

			return new WP_REST_Response( $data, 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getCode() );
		}
	}


	/**
	 * Track shipment.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */
	public static function track_shipment( WP_REST_Request $request ): WP_REST_Response {
		try {
			$data = array();

			return new WP_REST_Response( $data, 200 );
		} catch ( Exception $e ) {
			return new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getCode() );
		}
	}
}