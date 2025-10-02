<?php
/**
 * Speedy Get Sites Request.
 *
 * @package UnaxShipping
 * @author  Unax
 */

namespace UnaxShipping\Lib\Speedy;

/**
 * Get sites request.
 */
class GetSites extends Request {

	/**
	 * Object name for the Speedy Endpoint.
	 *
	 * @var string
	 */
	protected static $object = 'GetSites';

	/**
	 * Request parameters.
	 *
	 * @var array
	 */
	protected static $parameters = array(
		'countryId',
		'name',
	);

	/**
	 * Country ID.
	 *
	 * @var int|null
	 */
	public static $countryId = null;

	/**
	 * Site name filter.
	 *
	 * @var string|null
	 */
	public static $name = null;

	/**
	 * Get country ID.
	 *
	 * @return int|null
	 */
	public static function get_country_id() {
		return static::$countryId;
	}

	/**
	 * Set country ID.
	 *
	 * @param int $countryId Country identifier.
	 *
	 * @return void
	 */
	public static function set_country_id( $countryId ) {
		static::$countryId = $countryId;
	}

	/**
	 * Get name filter.
	 *
	 * @return string|null
	 */
	public static function get_name() {
		return static::$name;
	}

	/**
	 * Set name filter.
	 *
	 * @param string $name Name to filter by.
	 *
	 * @return void
	 */
	public static function set_name( $name ) {
		static::$name = $name;
	}
}