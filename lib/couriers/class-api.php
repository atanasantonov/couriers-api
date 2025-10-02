<?php
/**
 * Speedy connector.
 *
 * @package UnaxShipping
 * @author  Unax
 */

namespace UnaxShipping\Lib\Speedy;

/**
 * Speedy Connector.
 */
class Connector {

	/**
	 * Username for API authentication.
	 *
	 * @var string
	 */
	public static $username = '';

	/**
	 * Password for API authentication.
	 *
	 * @var string
	 */
	public static $password = '';

	/**
	 * Language for API requests.
	 *
	 * @var string
	 */
	public static $language = '';

	/**
	 * Client system ID.
	 *
	 * @var string
	 */
	public static $client_system_id = '';

	/**
	 * Objects schema configuration.
	 *
	 * @var array
	 */
	public static $objects = array();

	/**
	 * Error codes.
	 *
	 * @var array
	 */
	public static $error_codes = array();

	/**
	 * Initialize connector.
	 *
	 * @return void
	 */
	public static function init() {
		require_once EASY_SHIPPING_LIB_DIR . 'speedy/interface-request.php';
		require_once EASY_SHIPPING_LIB_DIR . 'speedy/abstract-class-request.php';

		self::$objects = defined( 'SPEEDY_OBJECTS' ) ? SPEEDY_OBJECTS : array();
		self::$error_codes = defined( 'SPEEDY_ERROR_CODES' ) ? SPEEDY_ERROR_CODES : array();
	}

	/**
	 * Set credentials.
	 *
	 * @param string $username API username.
	 * @param string $password API password.
	 *
	 * @return void
	 */
	public static function set_credentials( $username, $password ) {
		self::$username = $username;
		self::$password = $password;
	}

	/**
	 * Set language.
	 *
	 * @param string $language Language code.
	 *
	 * @return void
	 */
	public static function set_language( $language ) {
		self::$language = $language;
	}

	/**
	 * Set client system ID.
	 *
	 * @param string $client_system_id Client system identifier.
	 *
	 * @return void
	 */
	public static function set_client_system_id( $client_system_id ) {
		self::$client_system_id = $client_system_id;
	}

	/**
	 * Get username.
	 *
	 * @return string
	 */
	public static function get_username() {
		return self::$username;
	}

	/**
	 * Get password.
	 *
	 * @return string
	 */
	public static function get_password() {
		return self::$password;
	}

	/**
	 * Get language.
	 *
	 * @return string
	 */
	public static function get_language() {
		return self::$language;
	}

	/**
	 * Get client system ID.
	 *
	 * @return string
	 */
	public static function get_client_system_id() {
		return self::$client_system_id;
	}

	/**
	 * Validate object.
	 *
	 * @param string $object Object name.
	 *
	 * @return bool
	 */
	public static function validate_object( $object ) {
		return in_array( $object, array_keys( self::$objects ), true );
	}

	/**
	 * Validate a parameter value against its definition in the SPEEDY_OBJECTS schema.
	 *
	 * @param mixed  $value           The value to validate.
	 * @param string $object          The object name.
	 * @param bool   $strict          Strict validation mode.
	 * @param string ...$config_key   The parameter path.
	 *
	 * @return bool True if valid, false if invalid.
	 */
	public static function validate_parameter( $value, $object, $strict, ...$config_key ) {
		try {
			if ( empty( $object ) ) {
				throw new \Exception( self::$error_codes[2], 2 );
			}

			if ( ! self::validate_object( $object ) ) {
				throw new \Exception( self::$error_codes[1] . " '{$object}'", 1 );
			}

			// Traverse the parameter schema using the path.
			$schema = self::$objects[ $object ];
			foreach ( $config_key as $segment ) {
				if ( ! isset( $schema[ $segment ] ) ) {
					throw new \Exception( self::$error_codes[4] . ' ' . implode( '.', $config_key ), 4 );
				}

				$schema = $schema[ $segment ];
			}

			$type            = $schema['type'] ?? '';
			$required        = $schema['required'] ?? false;
			$comma_separated = $schema['comma_separated'] ?? false;
			$min_size        = $schema['min_size'] ?? null;
			$max_size        = $schema['max_size'] ?? null;

			if ( false === $required && ( $value === null || $value === '' ) ) {
				return true;
			}

			// Required check.
			if ( true === $required ) {
				if ( $value === null ) {
					throw new \Exception(
						sprintf(
							'%s, Object %s property [%s] parameter is null',
							self::$error_codes[3],
							$object,
							implode( '.', $config_key )
						),
						3
					);
				}
				if ( $value === '' ) {
					throw new \Exception(
						sprintf(
							'%s (parameter is empty), Object %s property [%s]',
							self::$error_codes[3],
							$object,
							implode( '.', $config_key )
						),
						3
					);
				}
			}

			// Comma-separated values.
			if ( true === $comma_separated && is_string( $value ) ) {
				$items = explode( ',', $value );
				foreach ( $items as $item ) {
					if ( ! self::validate_type( trim( $item ), $type ) ) {
						throw new \Exception(
							sprintf(
								'Comma-separated item invalid type, Object %s property [%s] value "%s", expected type "%s"',
								$object,
								$item,
								implode( '.', $config_key ),
								$type
							),
							3
						);
					}

					if ( ! self::validate_size( $item, $type, $min_size, $max_size ) ) {
						throw new \Exception(
							sprintf(
								'Comma-separated item invalid size, Object %s property [%s] value "%s, expected type "%s"',
								$object,
								$item,
								implode( '.', $config_key ),
								$type
							),
							3
						);
					}
				}
				return true;
			}

			if ( ! self::validate_type( $value, $type ) ) {
				throw new \Exception(
					sprintf(
						'Invalid type, Object %s property [%s] value "%s", expected type "%s"',
						$object,
						implode( '.', $config_key ),
						$value,
						$type
					),
					3
				);
			}

			if ( ! self::validate_size( $value, $type, $min_size, $max_size ) ) {
				throw new \Exception(
					sprintf(
						'Invalid size, Object %s property [%s] value "%s"',
						$object,
						implode( '.', $config_key ),
						$value,
					),
					3
				);
			}

			return true;
		} catch ( \Exception $e ) {
			Connector::handle_exception( $e, 'Validate parameters', $strict );
			return false;
		}
	}

	/**
	 * Validate type.
	 *
	 * @param mixed  $value The value to validate.
	 * @param string $type  The expected type.
	 *
	 * @return bool
	 */
	public static function validate_type( $value, $type ) {
		// This is fine with leading 0, but does not allow negative numbers in string.
		if ( 'int' === $type ) {
			return ( is_int( $value ) || ( is_string( $value ) && ctype_digit( $value ) ) );
		}

		// Leading zero is valid, allows negative numbers in string.
		if ( 'float' === $type ) {
			return is_float( $value ) || ( is_string( $value ) && is_numeric( $value ) );
		}

		if ( 'string' === $type ) {
			return is_string( $value );
		}

		if ( 'date' === $type || 'datetime' === $type ) {
			return self::is_valid_datetime( $value );
		}

		if ( 'array' === $type ) {
			return is_array( $value );
		}

		if ( 'bool' === $type ) {
			return is_bool( $value );
		}

		return false;
	}

	/**
	 * Check if a string value is a valid datetime.
	 *
	 * @param string $value The value to check.
	 *
	 * @return bool
	 */
	public static function is_valid_datetime( $value ) {
		try {
			new \DateTime( $value );
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Validate size constraints.
	 *
	 * @param mixed    $value    The value to validate.
	 * @param string   $type     The value type.
	 * @param int|null $min_size Minimum size.
	 * @param int|null $max_size Maximum size.
	 *
	 * @return bool
	 */
	public static function validate_size( $value, $type, $min_size = null, $max_size = null ) {
		if ( 'int' === $type ) {
			if ( $min_size !== null && $value < $min_size ) {
				return false;
			}
			if ( $max_size !== null && $value > $max_size ) {
				return false;
			}
		}

		if ( 'string' === $type ) {
			$length = strlen( $value );
			if ( $min_size !== null && $length < $min_size ) {
				return false;
			}
			if ( $max_size !== null && $length > $max_size ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Prepare request data with authentication.
	 *
	 * @param string $prepared_data JSON encoded request parameters.
	 *
	 * @return string JSON encoded request data.
	 */
	public static function request( $object, $prepared_data ) {
		try {
			if ( ! self::validate_object( $object ) ) {
				throw new \Exception( 'Invalid object', 1 );
			}

			if ( ! self::$username ) {
				throw new \Exception( 'Speedy username not set', 90 );
			}

			if ( ! self::$password ) {
				throw new \Exception( 'Speedy password not set', 90 );
			}

			// Decode prepared data to add authentication.
			$parameters = json_decode( $prepared_data, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new \Exception( 'Invalid JSON in prepared data: ' . json_last_error_msg(), 5 );
			}

			// Add authentication to parameters.
			$request_data = array(
				'userName' => self::$username,
				'password' => self::$password,
			);

			if ( self::$language ) {
				$request_data['language'] = self::$language;
			}

			if ( self::$client_system_id ) {
				$request_data['clientSystemId'] = self::$client_system_id;
			}

			$request_data = array_merge( $request_data, $parameters );

			// JSON encode final request data.
			$json_data = wp_json_encode( $request_data );
			if ( ! $json_data ) {
				throw new \Exception( sprintf( 'Encode JSON failed: %s (%s)', json_last_error_msg(), json_last_error() ), 5 );
			}

			return $json_data;
		} catch ( \Exception $e ) {
			$context = sprintf(
				'Connector request object %s failed',
				$object
			);

			self::handle_exception( $e, $context );
			
			return false;
		}
	}

	/**
	 * Process response.
	 *
	 * @param string $response_body Raw response body.
	 *
	 * @return array {
	 *     @type bool       $success Indicates if the response was successful.
	 *     @type int|string $code    Error code or response code.
	 *     @type string     $message Response or error message.
	 *     @type array      $data    Response data.
	 * }
	 */
	public static function response( $response_body ) {
		$response = array(
			'success' => false,
			'code'    => 0,
			'message' => '',
			'data'    => array(),
		);

		try {
			if ( false === $response_body ) {
				$response['code']    = 11;
				$response['message'] = 'Request failed';

				return $response;
			}

			if ( '' === $response_body ) {
				throw new \Exception( 'Response body empty', 9 );
			}

			// Decode response body.
			$body = json_decode( $response_body, true );
			if ( empty( $body ) ) {
				throw new \Exception( sprintf( 'Decode JSON failed: %s (%s)', json_last_error_msg(), json_last_error() ), 6 );
			}

			// Check for API errors in response.
			if ( isset( $body['error'] ) && ! empty( $body['error'] ) ) {
				$error = $body['error'];
				$error_message = isset( $error['message'] ) ? $error['message'] : 'Unknown API error';
				$error_code = isset( $error['code'] ) ? $error['code'] : 'UNKNOWN';
				
				throw new \Exception( "Speedy API Error [{$error_code}]: {$error_message}", 12 );
			}

			$response['success'] = true;
			$response['data']    = $body;

			return $response;
		} catch ( \Exception $e ) {
			self::handle_exception( $e, 'Connector response' );
			$response['code']    = $e->getCode();
			$response['message'] = $e->getMessage();

			return $response;
		}
	}

	/**
	 * Handle exception.
	 * 
	 * @param \Throwable $exception The exception to handle.
	 * @param string     $context   Context for the exception.
	 * @param bool       $strict    If true, will throw an exception.
	 *
	 * @return void
	 */
	public static function handle_exception( $exception, $context = '', $strict = true ) {
		if ( ! $exception instanceof \Throwable ) {
			return;
		}

		$message = $exception->getMessage();
		$code = $exception->getCode();

		if ( array_key_exists( $code, self::$error_codes ) ) {
			$error_code = (string) $code;
		} else {
			if ( $code !== '' && $message !== '' ) {
				$error_code = $code;
				self::$error_codes[ $code ] = $message;
			}
		}

		$error_code = str_pad( $error_code, 2, '0', STR_PAD_LEFT );
		$file = $exception->getFile();
		$line = $exception->getLine();

		$context = 'Speedy ' . $context;

		// For WordPress environment, you can add logging here.
		if ( function_exists( 'error_log' ) ) {
			error_log( sprintf( '[%s] %s in %s:%d - Context: %s', $error_code, $message, $file, $line, $context ) );
		}
	}
}