<?php
/**
 * Speedy Track Request.
 *
 * @package UnaxShipping
 * @author  Unax
 */

namespace UnaxShipping\Lib\Speedy;

/**
 * Track parcels request.
 */
class Track extends Request {

	/**
	 * Object name for the Speedy Endpoint.
	 *
	 * @var string
	 */
	protected static $object = 'Track';

	/**
	 * Request parameters.
	 *
	 * @var array
	 */
	protected static $parameters = array(
		'parcels',
		'language',
	);

	/**
	 * Parcels to track.
	 *
	 * @var array|null
	 */
	public static $parcels = null;

	/**
	 * Language for tracking results.
	 *
	 * @var string|null
	 */
	public static $language = null;

	/**
	 * Get parcels data.
	 *
	 * @return array|null
	 */
	public static function get_parcels() {
		return static::$parcels;
	}

	/**
	 * Set parcels data.
	 *
	 * @param array $parcels Array of parcel numbers to track.
	 *
	 * @return void
	 */
	public static function set_parcels( $parcels ) {
		static::$parcels = $parcels;
	}

	/**
	 * Get language.
	 *
	 * @return string|null
	 */
	public static function get_language() {
		return static::$language;
	}

	/**
	 * Set language.
	 *
	 * @param string $language Language code for tracking results.
	 *
	 * @return void
	 */
	public static function set_language( $language ) {
		static::$language = $language;
	}

	/**
	 * Add parcel to tracking list.
	 *
	 * @param string $parcel_number Parcel number to add.
	 *
	 * @return void
	 */
	public static function add_parcel( $parcel_number ) {
		if ( null === static::$parcels ) {
			static::$parcels = array();
		}
		
		static::$parcels[] = $parcel_number;
	}

	/**
	 * Remove parcel from tracking list.
	 *
	 * @param string $parcel_number Parcel number to remove.
	 *
	 * @return void
	 */
	public static function remove_parcel( $parcel_number ) {
		if ( null === static::$parcels ) {
			return;
		}

		$key = array_search( $parcel_number, static::$parcels, true );
		if ( false !== $key ) {
			unset( static::$parcels[ $key ] );
			static::$parcels = array_values( static::$parcels );
		}
	}
}