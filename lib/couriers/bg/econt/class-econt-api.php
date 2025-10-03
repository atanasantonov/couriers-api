<?php
/**
 * Econt API Implementation
 *
 * @package Easy_Shipping
 */

namespace Easy_Shipping\Lib\Couriers\BG\Econt;

use Easy_Shipping\Lib\Couriers\Courier_API_Interface;
use Easy_Shipping\Lib\Request\Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Econt courier API implementation.
 */
class Econt_API implements Courier_API_Interface {
	/**
	 * Get supported countries.
	 *
	 * @var array Array of country codes supported by the courier.
	 */
	private $supported_countries = array();

	/**
	 * API base URL .
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
	 * @param array $config API configuration (username, password, test_mode, etc.).
	 */
	public function __construct( $authorization, $test_mode = true ) {
		require_once __DIR__ . '/config.php';

		$this->supported_countries = $config['supported_countries'];
		$this->api_url       	   = $test_mode ? $config['test_url'] : $config['live_url'];
		$this->test_mode     	   = $test_mode;
		$this->endpoints     	   = $endpoints;
		$this->authorization       = $authorization;
	}

	/**
	 * Make API request.
	 *
	 * @param string $service      Service name (e.g., 'Nomenclatures', 'Shipments').
	 * @param string $method       Method name (e.g., 'GetCities', 'CreateLabel').
	 * @param array  $params       Request parameters.
	 * @param string $http_method  HTTP method (GET, POST).
	 *
	 * @return array|\WP_Error Response array or WP_Error on failure.
	 */
	private function request( $service, $method, $params = array(), $http_method = 'GET' ) {
		// Create new Request instance.
		$request = new Request();

		// Set API URI.
		$url = $this->api_url . '/' . $service . '/' . $service . 'Service.' . $method . '.json';
		$request->set_uri( $url );

		// Set Basic Auth header.
		$auth_header = array(
			'Authorization' => 'Basic ' . $this->authorization,
		);
		$request->set_headers( $auth_header );

		// Set endpoint and parameters for validation.
		if ( isset( $this->endpoints[ $method ] ) ) {
			$request->set_endpoints( $this->endpoints );
			$request->set_endpoint( $method );
			$request->set_parameters( array_keys( $params ) );
		}

		// Make request.
		$response = $request->request( $params, $http_method );

		// Process response.
		return $request->response( $response );
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
	 * Get supported countries.
	 *
	 * @return array Array of country codes supported by the courier.
	 */
	public function get_countries( $params = array() ) {
		$result = $this->request( 'Nomenclatures', 'GetCountries', $params );

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
		$result = $this->request( 'Nomenclatures', 'GetCities', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
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
		$result = $this->request( 'Nomenclatures', 'GetOffices', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
		}

		return $result['data'];
	}


	/**
	 * Get pickup points.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 *
	 * @return array|\WP_Error Array of pickup points on success, WP_Error on failure.
	 */
	public function get_machines( $params = array() ) {
		// Econt doesn't have separate machines/pickup points API.
		// Machines are included in GetOffices response with type filter.
		$params['type'] = 'APT'; // Automated Pickup Terminal.

		$result = $this->request( 'Nomenclatures', 'GetOffices', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
		}

		return $result['data'];
	}


	/**
	 * Get quarters.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 *
	 * @return array|\WP_Error Array of quarters on success, WP_Error on failure.
	 */
	public function get_quarters( $params = array() ) {
		$result = $this->request( 'Nomenclatures', 'GetQuarters', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
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
		$result = $this->request( 'Nomenclatures', 'GetStreets', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
		}

		return $result['data'];
	}


	/**
	 * Search.
	 *
	 * @param array $params Search parameters (search fields like city, street, etc.).
	 *
	 * @return array|\WP_Error Results on success, WP_Error on failure.
	 */
	public function search( $params = array() ) {
		// Econt uses AddressService.validateAddress for search/validation.
		$result = $this->request( 'Address', 'validateAddress', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
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
		// Use CreateLabel with mode 'calculate'.
		$label_params         = $params;
		$label_params['mode'] = 'calculate';

		$result = $this->request( 'Shipments', 'CreateLabel', $label_params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
		}

		// Extract price from response.
		$data = $result['data'];
		if ( isset( $data['label']['totalPrice'] ) ) {
			return array(
				'cost'     => $data['label']['totalPrice'],
				'currency' => isset( $data['label']['currency'] ) ? $data['label']['currency'] : 'BGN',
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
		// Use CreateLabel with mode 'create'.
		$label_params         = $params;
		$label_params['mode'] = 'create';

		$result = $this->request( 'Shipments', 'CreateLabel', $label_params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
		}

		return $result['data'];
	}


	/**
	 * Track shipment.
	 *
	 * @param string $shipment_number Shipment tracking number.
	 * @return array|\WP_Error Array with tracking data on success, WP_Error on failure.
	 */
	public function track_shipment( $params ) {
		$params = array(
			'shipmentNumbers' => array( $params['shipment_number'] ),
		);

		$result = $this->request( 'Shipments', 'GetShipmentStatuses', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
		}

		return $result['data'];
	}


	/**
	 * Cancel shipment.
	 *
	 * @param string $shipment_number Shipment tracking number.
	 * @return array|\WP_Error Array with cancellation data on success, WP_Error on failure.
	 */	
	public function cancel_shipment( $params ) {
		$params = array(
			'shipmentNumber' => $params['shipment_number'],
		);

		$result = $this->request( 'Shipments', 'CancelShipment', $params );

		if ( ! $result['success'] ) {
			return new \WP_Error( $result['code'], $result['message'] );
		}

		return $result['data'];
	}
}
