<?php
/**
 * Speedy API Implementation
 *
 * @package Easy_Shipping
 */

namespace Easy_Shipping\Lib\Couriers\BG\Speedy;

use Easy_Shipping\Lib\Couriers\Courier_API_Interface;
use Easy_Shipping\Lib\Request\Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Speedy courier API implementation.
 */
class Speedy_API implements Courier_API_Interface {
	/**
	 * Get supported countries.
	 *
	 * @var array Array of country codes supported by the courier.
	 */
	private $supported_countries = array();

	/**
	 * API base URL.
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * Test mode flag.
	 *
	 * @var bool
	 */
	private $test_mode = true;

	/**
	 * API endpoints configuration.
	 *
	 * @var array
	 */
	private $endpoints;

	/**
	 * API authorization.
	 *
	 * @var array
	 */
	private $authorization;

	/**
	 * Constructor.
	 *
	 * @param string $authorization API authorization (username:password base64 encoded).
	 * @param bool   $test_mode     Whether to use test mode.
	 */
	public function __construct( $authorization, $test_mode = true ) {
		require_once __DIR__ . '/config.php';

		$this->supported_countries = $config['supported_countries'];
		$this->api_url             = $test_mode ? $config['test_url'] : $config['live_url'];
		$this->test_mode           = $test_mode;
		$this->endpoints           = $config['endpoints'];
		$this->authorization       = $authorization;
	}

	/**
	 * Make API request.
	 *
	 * @param string $method       Method name (e.g., 'calculate', 'shipment', 'track').
	 * @param array  $params       Request parameters.
	 * @param string $http_method  HTTP method (GET, POST).
	 *
	 * @return array|\WP_Error Response array or WP_Error on failure.
	 */
	private function request( $method, $params = array(), $http_method = 'POST' ) {
		// Create new Request instance.
		$request = new Request();

		// Set API URI.
		$url = $this->api_url . '/' . strtolower( $method );
		$request->set_uri( $url );

		// Set authorization.
		if ( empty( $this->authorization ) ) {
			return new \WP_Error( 'no_auth', 'Authorization missing', array( 'error_code' => 45, 'status' => 400 ) );
		}
		
		if ( empty( $this->authorization['api_key'] ) ) {
			return new \WP_Error( 'no_auth', 'API Key missing', array( 'error_code' => 46, 'status' => 400 ) );
		}

		if ( empty( $this->authorization['api_secret'] ) ) {
			return new \WP_Error( 'no_auth', 'API Secret missing', array( 'error_code' => 46, 'status' => 400 ) );
		}

		$auth_header = array(
			'X-Api-Key'    => $this->authorization['api_key'],
			'X-Api-Secret' => $this->authorization['api_secret'],
		);
		$request->set_headers( $auth_header );

		// Set endpoint and parameters for validation.
		if ( empty( $this->endpoints[ $method ] ) ) {
			return new \WP_Error( 'invalid_endpoint', 'Invalid endpoint: ' . $method, array( 'error_code' => 2, 'status' => 400 ) );
		}

		$request->set_endpoints( $this->endpoints );
		$request->set_endpoint( $method );
		$request->set_parameters( array_keys( $this->endpoints[ $method ] ) );

		// Set request method.
		$http_method = empty( $params ) ? 'GET' : 'POST';

		// Make request.
		$result = $request->request( $params, $http_method );

		// Process response.
		return $request->response( $result );
	}


	/**
	 * Get supported countries.
	 *
	 * @return array Array of country codes supported by the courier.
	 */
	public function get_supported_countries(): array {
		return $this->supported_countries;
	}


	/**
	 * Get courier endpoints.
	 *
	 * @return array Array of endpoints supported by the courier.
	 */
	public function get_endpoints() {
		return $this->endpoints;
	}


	/**
	 * Get countries.
	 *
	 * @param array $params Search parameters.
	 *
	 * @return array|\WP_Error Array of countries on success, WP_Error on failure.
	 */
	public function get_countries( $params = array() ) {
		$result = $this->request( 'GetCountries', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
		}

		return $result['data'];
	}


	/**
	 * Get cities list.
	 *
	 * @param array $params Search parameters.
	 *
	 * @return array|\WP_Error Array of cities on success, WP_Error on failure.
	 */
	public function get_cities( $params = array() ) {
		$result = $this->request( 'GetSites', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		return $result['data'];
	}


	/**
	 * Get offices.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 *
	 * @return array|\WP_Error Array of offices on success, WP_Error on failure.
	 */
	public function get_offices( $params = array() ) {
		$result = $this->request( 'GetOffices', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		return $result['data'];
	}


	/**
	 * Get pickup points (APT machines).
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 *
	 * @return array|\WP_Error Array of pickup points on success, WP_Error on failure.
	 */
	public function get_machines( $params = array() ) {
		// Speedy uses type parameter to filter office types.
		$params['type'] = 'APT';

		$result = $this->request( 'GetOffices', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		return $result['data'];
	}


	/**
	 * Get mobile stations.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 *
	 * @return array|\WP_Error Array of mobile stations on success, WP_Error on failure.
	 */
	public function get_mobiles( $params = array() ) {
		// Speedy doesn't have mobile stations, return empty array.
		return array();
	}


	/**
	 * Get quarters.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 *
	 * @return array|\WP_Error Array of quarters on success, WP_Error on failure.
	 */
	public function get_quarters( $params = array() ) {
		$result = $this->request( 'GetQuarters', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		return $result['data'];
	}


	/**
	 * Get streets.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 *
	 * @return array|\WP_Error Array of streets on success, WP_Error on failure.
	 */
	public function get_streets( $params = array() ) {
		$result = $this->request( 'GetStreets', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		return $result['data'];
	}


	/**
	 * Search shipments.
	 *
	 * @param array $params Search parameters (search fields like ref1, ref2, dates, etc.).
	 *
	 * @return array|\WP_Error Results on success, WP_Error on failure.
	 */
	public function search( $params = array() ) {
		$result = $this->request( 'ShipmentSearch', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		return $result['data'];
	}


	/**
	 * Calculate shipping price.
	 *
	 * @param array $params Calculation parameters.
	 * @return array|\WP_Error Array with 'cost' key on success, WP_Error on failure.
	 */
	public function calculate_shipping( $params ) {
		$result = $this->request( 'Calculate', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
		}

		// Extract price from response.
		$data = $result['data'];
		if ( isset( $data['price']['total'] ) ) {
			return array(
				'cost'     => $data['price']['total'],
				'currency' => isset( $data['price']['currency'] ) ? $data['price']['currency'] : 'BGN',
				'details'  => $data,
			);
		}

		// Alternative response structure.
		if ( isset( $data['totalPrice'] ) ) {
			return array(
				'cost'     => $data['totalPrice'],
				'currency' => isset( $data['currency'] ) ? $data['currency'] : 'BGN',
				'details'  => $data,
			);
		}

		return new \WP_Error( 'no_price', 'Price not found in response' );
	}


	/**
	 * Create shipment.
	 *
	 * @param array $params Shipment parameters.
	 * @return array|\WP_Error Array with shipment data on success, WP_Error on failure.
	 */
	public function create_shipment( $params ) {
		$result = $this->request( 'Shipment', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		return $result['data'];
	}


	/**
	 * Track shipment.
	 *
	 * @param array $params Tracking parameters with 'parcels' array.
	 * @return array|\WP_Error Array with tracking data on success, WP_Error on failure.
	 */
	public function track_shipment( $params ) {
		// Format parameters for Speedy API.
		$track_params = array(
			'parcels' => isset( $params['parcels'] ) ? $params['parcels'] : array( $params['shipment_number'] ),
		);

		if ( isset( $params['language'] ) ) {
			$track_params['language'] = $params['language'];
		}

		$result = $this->request( 'Track', $track_params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		return $result['data'];
	}


	/**
	 * Cancel shipment.
	 *
	 * @param array $params Shipment cancellation parameters.
	 * @return array|\WP_Error Array with cancellation data on success, WP_Error on failure.
	 */
	public function cancel_shipment( $params ) {
		return new \WP_Error( 'not_implemented', 'Shipment cancellation not yet implemented for Speedy API', array( 'error_code' => 49, 'status' => 501 ) );
	}
}
