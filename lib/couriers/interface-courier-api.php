<?php
/**
 * Courier API Interface
 *
 * @package Easy_Shipping
 */

namespace Easy_Shipping\Lib\Couriers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface for courier API implementations.
 */
interface Courier_API_Interface {

	public function __construct( $authorization, $test_mode = true );

	/**
	 * Get courier endpoints.
	 *
	 * @return array Array of endpoints supported by the courier.
	 */
	public function get_endpoints();


	/**
	 * Get supported countries.
	 *
	 * @return array Array of country codes supported by the courier.
	 */
	public function get_countries();


	/**
	 * Get cities list.
	 *
	 * @param array $params Search parameters.
	 * 
	 * @return array|\WP_Error Array of cities on success, WP_Error on failure.
	 */
	public function get_cities( $params = array() );


	/**
	 * Get offices.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 * 
	 * @return array|\WP_Error Array of offices on success, WP_Error on failure.
	 */
	public function get_offices( $params = array() );


	/**
	 * Get pickup points.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 * 
	 * @return array|\WP_Error Array of pickup points on success, WP_Error on failure.
	 */
	public function get_machines( $params = array() );


	/**
	 * Get streets.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 * 
	 * @return array|\WP_Error Array of quarters on success, WP_Error on failure.
	 */
	public function get_quarters( $params = array() );


	/**
	 * Get streets.
	 *
	 * @param array $params Search parameters (city, country, etc.).
	 * 
	 * @return array|\WP_Error Array of streets on success, WP_Error on failure.
	 */
	public function get_streets( $params = array() );


	/**
	 * Search.
	 *
	 * @param array $params Search parameters (search fields like city, street, etc.).
	 * 
	 * @return array|\WP_Error Results on success, WP_Error on failure.
	 */
	public function search( $params = array() );


	/**
	 * Calculate shipping price.
	 *
	 * @param array $params Calculation parameters.
	 * @return array|\WP_Error Array with 'cost' key on success, WP_Error on failure.
	 */
	public function calculate_shipping( $params );


	/**
	 * Create shipment.
	 *
	 * @param array $params Shipment parameters.
	 * 
	 * @return array|\WP_Error Array with shipment data on success, WP_Error on failure.
	 */
	public function create_shipment( $params );
	

	/**
	 * Track shipment.
	 *
	 * @param array $params Shipment tracking parameters
	 * .
	 * @return array|\WP_Error Array with tracking data on success, WP_Error on failure.
	 */
	public function track_shipment( $params );


	/**
	 * Cancel shipment.
	 *
	 * @param array $params Shipment cancellation parameters.
	 * 
	 * @return array|\WP_Error Array with cancellation data on success, WP_Error on failure.
	 */
	public function cancel_shipment( $params );
}