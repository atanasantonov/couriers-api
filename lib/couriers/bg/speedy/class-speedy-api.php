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
	 * @param string $endpoint     Endpoint path (e.g., 'calculate', 'shipment', 'track', 'location/site').
	 * @param array  $params       Request parameters.
	 *
	 * @return array|\WP_Error Response array or WP_Error on failure.
	 */
	private function request( $endpoint, $params = array() ) {
		try {
			// Create new Request instance.
			$request = new Request();

			// Set API URI.
			$url = $this->api_url . '/' . $endpoint;
			$request->set_uri( $url );

			// Validate authorization.
			if ( empty( $this->authorization ) ) {
				throw new \Exception( 'Authorization missing', 45 );
			}

			// Decode authorization.
			$auth_string = base64_decode( $this->authorization );
			if ( false === $auth_string ) {
				throw new \Exception( 'Decode authorization failed', 46 );
			}

			$auth_array = unserialize( $auth_string );
			if ( false === $auth_array ) {
				throw new \Exception( 'Unserialize authorization failed', 46 );
			}

			if ( empty( $auth_array[0] ) ) {
				throw new \Exception( 'API username missing', 46 );
			}

			if ( empty( $auth_array[1] ) ) {
				throw new \Exception( 'API password missing', 46 );
			}

			// Set endpoint and parameters for validation.
			if ( empty( $this->endpoints[ $endpoint ] ) ) {
				return new \WP_Error( 'invalid_endpoint', 'Invalid endpoint: ' . $endpoint, array( 'error_code' => 2, 'status' => 400 ) );
			}

			$request->set_endpoints( $this->endpoints );
			$request->set_endpoint( $endpoint );
			$request->set_parameters( array_keys( $this->endpoints[ $endpoint ] ) );

			// Add authentication to parameters.
			$params['userName'] = $auth_array[0];
			$params['password'] = $auth_array[1];

			// All Speedy API requests are POST with JSON body.
			$result = $request->request( $params, 'POST' );

			return $request->response( $result );
		} catch ( \Exception $e ) {
			$result = new \WP_Error(
				'request_failed',
				sprintf( 'Request to Speedy API failed. Error: %s', $e->getMessage() ),
				array( 'error_code' => $e->getCode(), 'status' => 400 )
			);
			return $request->response( $result );
		}
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
		$result = $this->request( 'location/country', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
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
		$result = $this->request( 'location/site', $params );

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
		$result = $this->request( 'location/office', $params );

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
		// Speedy APT machines are returned from location/office endpoint.
		$result = $this->request( 'location/office', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		// Filter only APT type offices.
		$data = $result['data'];
		if ( isset( $data['offices'] ) && is_array( $data['offices'] ) ) {
			$data['offices'] = array_filter(
				$data['offices'],
				function ( $office ) {
					return isset( $office['type'] ) && 'APT' === $office['type'];
				}
			);
		}

		return $data;
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
		$result = $this->request( 'location/complex', $params );

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
		$result = $this->request( 'location/street', $params );

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
		// Speedy doesn't have a dedicated search endpoint in the documented API.
		// Use track endpoint if searching by parcel number.
		return new \WP_Error( 'not_implemented', 'Search not implemented. Use track_shipment() for tracking by parcel number.', array( 'error_code' => 49, 'status' => 501 ) );
	}


	/**
	 * Calculate shipping price.
	 *
	 * @param array $params Calculation parameters.
	 * @return array|\WP_Error Array with 'cost' key on success, WP_Error on failure.
	 */
	public function calculate_shipping( $params ) {
		$result = $this->request( 'calculate', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
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

		// Another possible structure.
		if ( isset( $data['amount'] ) ) {
			return array(
				'cost'     => $data['amount'],
				'currency' => isset( $data['currency'] ) ? $data['currency'] : 'BGN',
				'details'  => $data,
			);
		}

		return new \WP_Error( 'no_price', 'Price not found in response', array( 'error_code' => 50, 'status' => 500 ) );
	}


	/**
	 * Create shipment.
	 *
	 * @param array $params Shipment parameters.
	 * @return array|\WP_Error Array with shipment data on success, WP_Error on failure.
	 */
	public function create_shipment( $params ) {
		$result = $this->request( 'shipment', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'], $result['data'] );
		}

		return $result['data'];
	}


	/**
	 * Track shipment.
	 *
	 * @param array $params Tracking parameters with 'parcels' array or 'shipment_number'.
	 * @return array|\WP_Error Array with tracking data on success, WP_Error on failure.
	 */
	public function track_shipment( $params ) {
		// Format parameters for Speedy API.
		$track_params = array();

		// Handle different input formats.
		if ( isset( $params['parcels'] ) ) {
			$track_params['parcels'] = $params['parcels'];
		} elseif ( isset( $params['shipment_number'] ) ) {
			$track_params['parcels'] = array( $params['shipment_number'] );
		} else {
			return new \WP_Error( 'missing_params', 'Missing required parameter: parcels or shipment_number', array( 'error_code' => 40, 'status' => 400 ) );
		}

		if ( isset( $params['language'] ) ) {
			$track_params['language'] = $params['language'];
		}

		$result = $this->request( 'track', $track_params );

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
		// Speedy API cancellation is done via DELETE or specific cancel endpoint.
		// This is a placeholder - actual implementation depends on Speedy API documentation.
		return new \WP_Error( 'not_implemented', 'Shipment cancellation not yet implemented for Speedy API', array( 'error_code' => 49, 'status' => 501 ) );
	}
}
