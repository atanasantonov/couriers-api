<?php
/**
 * API Helper
 *
 * @package Easy_Shipping
 */

namespace Easy_Shipping\Lib\Request;

use Unax\Helper\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Helper class.
 */
class Request_Helper {
	/**
     * Errors.
     *
     * @var array
     */
    public static array $error_codes = array();


    /**
     * Settings.
     *
     * @var array
     */
    public static array $settings = array();


    /**
     * Region.
     *
     * @var string
     */
    public static string $region = '';


    /**
     * Courier.
     *
     * @var string
     */
    public static string $courier = '';


	/**
     * Endpoints/entities.
     *
     * @var array
     */
    public static array $endpoints = array();

	

	/**
	 * API Helper init.
	 *
	 * @param array $settings Plugin settings.
	 *
	 * @return void
	 */
	public static function init( $settings = array() ) {
		require_once __DIR__ . '/error-codes.php';

		self::$error_codes  = $error_codes;
        self::$settings     = $settings;        
	}


    /** 
     * Set region.
     *
     * @param string $region The region.
     *
     * @return void
     */
    public static function set_region( string $region ) {
        self::$region = $region;
    }


    /**
     * Set courier.
     *
     * @param string $courier_id The courier ID.
     *
     * @return void
     */
    public static function set_courier( string $courier ) {
        self::$courier = $courier;
    }


    /**
	 * Set endpoints.
	 *
	 * @param array $endpoints The endpoints to set.
     * 
     * @return array
	 */
	public static function get_endpoints() {
        try {
            if ( empty( self::$settings[self::$region][self::$courier] ) ) {
                throw new \Exception( 'Courier configuration path not set', 90 );
            }

            if ( ! file_exists( self::$settings[self::$region][self::$courier] . '/config.php' ) ) {
                throw new \Exception( 'Courier configuration file not found', 90 );
            }

            require_once self::$settings[self::$region][self::$courier] . '/config.php';

            return $endpoints;
        } catch ( \Exception $e ) {
            self::handle_exception( $e, 'Get endpoints failed', false );
            return array();
        }
	}


	/**
     * Validate endpoint.
     *
     * @return bool
     */
    public static function validate_endpoint( $endpoint ) : bool {
        return in_array( $endpoint, array_keys( self::get_endpoints() ), true );
    }

   /**
     * Validate a parameter value against its definition in the ZERON_OPERATIONS schema.
     *
     * @param mixed  $value           The value to validate.
     * @param string $endpoint       The endpoint name (e.g 'GetInventory') or (e.g. 'PutNewSalesDoc').
     * @param string ...$config_key   The parameter path (e.g 'PageNo')       or (e.g. 'OrderLines', 'WEBDocID').
     *
     * @return bool True if valid, false if invalid.
     */
    public static function validate_parameter( $value, string $endpoint, string ...$config_key ) {
        try {
            if ( empty( $endpoint ) ) {
                throw new \Exception( self::$error_codes[2], 2 );
            }

            if ( ! self::validate_endpoint( $endpoint ) ) {
                throw new \Exception( self::$error_codes[1] . " '{$endpoint}'", 1 );
            }

            $endpoints = self::get_endpoints();
            if ( empty( $endpoints ) ) {
                throw new \Exception( self::$error_codes[90] . " '{$endpoint}'", 90 );
            }

            // Traverse the parameter schema using the path
            $schema = $endpoints[ $endpoint ];
            foreach ( $config_key as $segment ) {
                if ( ! isset( $schema[ $segment ] ) ) {
                    throw new \Exception( self::$error_codes[4] . " " . implode( '.', $config_key ), 4 );
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

            // Required check
            if ( true === $required ) {
                if ( $value === null ) {
                    throw new \Exception(
                        sprintf(
                            '%s, Operation %s property [%s] parameter is null',
                            self::$error_codes[3],
                            $endpoint,
                            implode( '.', $config_key )
                        ),
                        3
                    );
                }
                if ( $value === '' ) {
                    throw new \Exception(
                        sprintf(
                            '%s (parameter is empty), Operation %s property [%s]',
                            self::$error_codes[3],
                            $endpoint,
                            implode( '.', $config_key )
                        ),
                        3
                    );
                }
            }

            // Comma-separated values
            if ( true === $comma_separated && is_string( $value ) ) {
                $items = explode( ',', $value );
                foreach ( $items as $item ) {
                    if ( ! self::validate_type( trim( $item ), $type ) ) {
                        throw new \Exception(
                            sprintf(
                                "Comma-separated item invalid type, Operation %s property [%s] value '%s', expected type '%s'",
                                $endpoint,
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
                                "Comma-separated item invalid size, Operation %s property [%s] value '%s, expected type '%s'",
                                $endpoint,
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
                        "Invalid type, Operation %s property [%s] value '%s', expected type '%s'",
                        $endpoint,
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
                        "Invalid size, Operation %s property [%s] value '%s'",
                        $endpoint,
                        implode( '.', $config_key ),
                        $value,
                    ),
                    3
                );
            }

            return true;
        } catch ( \Exception $e ) {
            self::handle_exception( $e, "Validate parameters" );
            return false;
        }
    }


    /**
     * Validate type.
     *
     * @param mixed $value
     * @param string $type
     *
     * @return bool
     */
    public static function validate_type( $value, $type ) {

        // This is fine with leading 0, but does not allow negative numbers in string ('-2' not valid, but -2 is valid).
        if ( 'int' === $type ) {
            return ( is_int( $value ) || ( is_string( $value ) && ctype_digit( $value ) ) );
        }

        // Leading zero is valid, allows negative numbers in string ('-2' and -2 are valid).
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

        return false;
    }


    /**
     * Check if a string value is a valid datetime.
     *
     * @param string $value
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
     * Validate size.
     *
     * @param mixed $value
     * @param string $type
     * @param int|null $min_size
     * @param int|null $max_size
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
     * Handle WP_Error instances.
     *
     * @param mixed $wp_error The WP_Error instance.
     *  
     * @return \WP_REST_Response
     */
    public static function handle_wp_error( $wp_error ) : \WP_REST_Response {
        $error_code = '';
        $status_code = 400;

        $error_data = $wp_error->get_error_data();
        if ( isset( $error_data['status'] ) ) {
            $status_code = $error_data['status'];
        }

        if ( isset( $error_data['error_code'] ) ) {
            $error_code = $error_data['error_code'];
        }

        return new \WP_REST_Response(
            array(
                'success'    => false,
                'error_code' => $error_code,
                'message'    => $wp_error->get_error_message()
            ),
            $status_code
        );
    }


    /**
	 * Handle exception.
     * 
	 * @param \Throwable $exception     The exception to handle.
	 * @param string     $context       Context for the exception, e.g. 'GetInventory'.
     * @param bool       $strict        If true, will throw an exception if the exception is not an instance of Throwable.
	 *
	 * @return void
	 */
	public static function  handle_exception( $exception, $context = '', $strict = true ) {
		if ( ! $exception instanceof \Throwable ) {
			Helper::log(
				'Connector exception handler',
				'Exception not an instance of Throwable',
				array(),
				__FILE__,
				__LINE__
			);
		}

		$message = $exception instanceof \Throwable ? $exception->getMessage() : '';
		$code = $exception instanceof \Throwable ? $exception->getCode() : '';
        if ( array_key_exists( $code, self::$error_codes ) ) {
            $error_code = (string) $code;
        } else {
            if ( $code !== '' && $message !== '' ) {
                $error_code = $code;
                self::$error_codes[ $code ] = $message;
            }
        }
        $error_code = str_pad( $error_code, 2, '0', STR_PAD_LEFT );
        $file = $exception instanceof \Throwable ? $exception->getFile() : '';
        $line = $exception instanceof \Throwable ? $exception->getLine() : '';

        $context = 'Easy Shipping Courier API ' . $context;

		// Log.
        Helper::log( $context, sprintf( '[%s] %s', $error_code, $message ), array(), $file, $line, 'error' );

		// Send email.
        $email_message = sprintf(
            "%s: %s<br><strong>%s:</strong> %s<br><strong>%s:</strong> %s<br><strong>%s:</strong> %d",
            esc_html__( 'Context', 'easy-shipping' ),
            esc_html( $context ),
            esc_html__( 'Description', 'easy-shipping' ),
            esc_html( $message ),
            esc_html__( 'File', 'easy-shipping' ),
            esc_html( $file ),
            esc_html__( 'Line', 'easy-shipping' ),
            esc_html( $line )
        );

        if ( $strict ) {
            Helper::admin_notification( 'Courier API error', $email_message, Helper::$administrator_email );
        }
	}
}
