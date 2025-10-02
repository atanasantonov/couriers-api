<?php
/**
 * Speedy Calculate Request.
 *
 * @package UnaxShipping
 * @author  Unax
 */

namespace UnaxShipping\Lib\Speedy;

/**
 * Calculate shipping cost request.
 */
class Calculate extends Request {

	/**
	 * Object name for the Speedy Endpoint.
	 *
	 * @var string
	 */
	protected static $object = 'Calculate';

	/**
	 * Request parameters.
	 *
	 * @var array
	 */
	protected static $parameters = array(
		'sender',
		'recipient', 
		'service',
		'content',
		'payment',
	);

	/**
	 * Sender data.
	 *
	 * @var array|null
	 */
	public static $sender = null;

	/**
	 * Recipient data.
	 *
	 * @var array|null
	 */
	public static $recipient = null;

	/**
	 * Service data.
	 *
	 * @var array|null
	 */
	public static $service = null;

	/**
	 * Content data.
	 *
	 * @var array|null
	 */
	public static $content = null;

	/**
	 * Payment data.
	 *
	 * @var array|null
	 */
	public static $payment = null;

	/**
	 * Get sender data.
	 *
	 * @return array|null
	 */
	public static function get_sender() {
		return static::$sender;
	}

	/**
	 * Set sender data.
	 *
	 * @param array $sender Sender information.
	 *
	 * @return void
	 */
	public static function set_sender( $sender ) {
		static::$sender = $sender;
	}

	/**
	 * Get recipient data.
	 *
	 * @return array|null
	 */
	public static function get_recipient() {
		return static::$recipient;
	}

	/**
	 * Set recipient data.
	 *
	 * @param array $recipient Recipient information.
	 *
	 * @return void
	 */
	public static function set_recipient( $recipient ) {
		static::$recipient = $recipient;
	}

	/**
	 * Get service data.
	 *
	 * @return array|null
	 */
	public static function get_service() {
		return static::$service;
	}

	/**
	 * Set service data.
	 *
	 * @param array $service Service information.
	 *
	 * @return void
	 */
	public static function set_service( $service ) {
		static::$service = $service;
	}

	/**
	 * Get content data.
	 *
	 * @return array|null
	 */
	public static function get_content() {
		return static::$content;
	}

	/**
	 * Set content data.
	 *
	 * @param array $content Content information.
	 *
	 * @return void
	 */
	public static function set_content( $content ) {
		static::$content = $content;
	}

	/**
	 * Get payment data.
	 *
	 * @return array|null
	 */
	public static function get_payment() {
		return static::$payment;
	}

	/**
	 * Set payment data.
	 *
	 * @param array $payment Payment information.
	 *
	 * @return void
	 */
	public static function set_payment( $payment ) {
		static::$payment = $payment;
	}
}