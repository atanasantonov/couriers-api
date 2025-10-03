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
use Easy_Shipping\Lib\Request\Request_Helper;
use Easy_Shipping\Lib\Couriers\Courier_Factory;

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
            'callback' => array( 'Easy_Shipping_API\Inc\Rest_API', 'get_countries' ),
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
			return false;
		}

		// TODO: Validate the API key against the stored keys.
		if ( $api_key !== 'test-key' ) {
			return false;
		}

		return true;
	}


	/**
	 * Basic request handler.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response 
	 */	
	public static function handle_request( WP_REST_Request $request, $method ): WP_REST_Response {
		$courier = Courier_Factory::create( $request );
		if ( is_wp_error( $courier ) ) {
			return Request_Helper::handle_wp_error( $courier );
		}

		$params = $request->get_params();
		
		// Remove API params.
		if ( isset( $params['courier_country'] ) ) {
			unset( $params['courier_country'] );
		}

		if ( isset( $params['courier_name'] ) ) {
			unset( $params['courier_name'] );
		}

		if ( isset( $params['authorization'] ) ) {
			unset( $params['authorization'] );
		}

		$items = $courier->$method( $params );
		if ( is_wp_error( $items ) ) {
			return Request_Helper::handle_wp_error( $items );
		}

		return new WP_REST_Response( $items, 200 );
	}


	/**
	 * Get supported countries.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the list of countries.
	 */	
	public static function get_countries( WP_REST_Request $request ): WP_REST_Response {	
		return self::handle_request( $request, 'get_countries' );
	}


	/**
	 * Retrieves a list of cities with their IDs and labels in English and Bulgarian.
	 *
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return WP_REST_Response The REST API response object containing the list of cities.
	 */
	public static function get_cities( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'get_cities' );
	}


	/**
	 * Get offices.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the list of offices.
	 */
	public static function get_offices( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'get_offices' );
	}


	/**
	 * Get machines.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the list of machines.
	 */
	public static function get_machines( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'get_machines' );
	}


	/**
	 * Get mobiles.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the list of mobile stations.
	 */
	public static function get_mobiles( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'get_mobiles' );
	}


	/**
	 * Get quarters.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the list of quarters.
	 */
	public static function get_quarters( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'get_quarters' );
	}


	/**
	 * Get streets.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the list of streets.
	 */
	public static function get_streets( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'get_streets' );
	}


	/**
	 * Search.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the search results.
	 */
	public static function search( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'search' );
	}


	/**
	 * Calculate shipping.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the shipping costs.
	 */
	public static function calculate_shipping( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'calculate_shipping' );
	}


	/**
	 * Create shipment.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the shipment details.
	 */
	public static function create_shipment( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'create_shipment' );
	}


	/**
	 * Track shipment.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the shipment tracking details.
	 */
	public static function track_shipment( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'track_shipment' );
	}


	/**
	 * Cancel shipment.
	 * 
	 * @param \WP_REST_Request $request WP REST API request object.
	 * 
	 * @return WP_REST_Response The REST API response object containing the cancellation result.
	 */
	public static function cancel_shipment( WP_REST_Request $request ): WP_REST_Response {
		return self::handle_request( $request, 'cancel_shipment' );
	}
}