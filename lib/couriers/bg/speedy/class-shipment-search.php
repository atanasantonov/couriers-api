<?php
/**
 * Speedy Shipment Search Request.
 *
 * @package UnaxShipping
 * @author  Unax
 */

namespace UnaxShipping\Lib\Speedy;

/**
 * Search shipments request.
 */
class ShipmentSearch extends Request {

	/**
	 * Object name for the Speedy Endpoint.
	 *
	 * @var string
	 */
	protected static $object = 'ShipmentSearch';

	/**
	 * Request parameters.
	 *
	 * @var array
	 */
	protected static $parameters = array(
		'clientId',
		'dateFrom',
		'dateTo',
		'ref1',
		'ref2',
	);

	/**
	 * Client ID.
	 *
	 * @var int|null
	 */
	public static $clientId = null;

	/**
	 * Date from.
	 *
	 * @var string|null
	 */
	public static $dateFrom = null;

	/**
	 * Date to.
	 *
	 * @var string|null
	 */
	public static $dateTo = null;

	/**
	 * Reference 1.
	 *
	 * @var string|null
	 */
	public static $ref1 = null;

	/**
	 * Reference 2.
	 *
	 * @var string|null
	 */
	public static $ref2 = null;

	/**
	 * Get client ID.
	 *
	 * @return int|null
	 */
	public static function get_client_id() {
		return static::$clientId;
	}

	/**
	 * Set client ID.
	 *
	 * @param int $clientId Client identifier.
	 *
	 * @return void
	 */
	public static function set_client_id( $clientId ) {
		static::$clientId = $clientId;
	}

	/**
	 * Get date from.
	 *
	 * @return string|null
	 */
	public static function get_date_from() {
		return static::$dateFrom;
	}

	/**
	 * Set date from.
	 *
	 * @param string $dateFrom Start date for search.
	 *
	 * @return void
	 */
	public static function set_date_from( $dateFrom ) {
		static::$dateFrom = $dateFrom;
	}

	/**
	 * Get date to.
	 *
	 * @return string|null
	 */
	public static function get_date_to() {
		return static::$dateTo;
	}

	/**
	 * Set date to.
	 *
	 * @param string $dateTo End date for search.
	 *
	 * @return void
	 */
	public static function set_date_to( $dateTo ) {
		static::$dateTo = $dateTo;
	}

	/**
	 * Get reference 1.
	 *
	 * @return string|null
	 */
	public static function get_ref1() {
		return static::$ref1;
	}

	/**
	 * Set reference 1.
	 *
	 * @param string $ref1 Reference 1 value.
	 *
	 * @return void
	 */
	public static function set_ref1( $ref1 ) {
		static::$ref1 = $ref1;
	}

	/**
	 * Get reference 2.
	 *
	 * @return string|null
	 */
	public static function get_ref2() {
		return static::$ref2;
	}

	/**
	 * Set reference 2.
	 *
	 * @param string $ref2 Reference 2 value.
	 *
	 * @return void
	 */
	public static function set_ref2( $ref2 ) {
		static::$ref2 = $ref2;
	}
}