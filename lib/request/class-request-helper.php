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
     * @param string $endpoint       The endpoint name (e.g 'GetCountries' or 'GetOffices').
     * @param mixed  $schema         The parameter schema.
     * @param string $parameter      The parameter (e.g. 'countryCode' or 'idType').
     * @param mixed  $value          The value to validate.
     *
     * @return bool True if valid, false if invalid.
     */
    public static function validate_parameter( string $endpoint, array $schema, string $parameter, $value ) : bool {
        try {
            foreach ( $schema as $config_key => $rule ) {
                if ( is_array( $rule ) ) {
                   self::validate_parameter( $endpoint, $schema[$config_key], $config_key, $value[$config_key] );
                   continue;
                }
            }

            $type     = isset( $schema['type'] )             ? $schema['type'] : 'string';
            $required = isset( $schema['required'] )         ? $schema['required'] : false;
            if ( false === $required && ( $value === null || $value === '' ) ) {
                return true;
            }

            // Check required.
            if ( $required ) {
                if ( $value === null ) {
                    throw new \Exception( sprintf( 'Endpoint "%s" required parameter [%s] is null', $endpoint, $parameter ), 3 );
                }

                if ( $value === '' ) {
                    throw new \Exception( sprintf( 'Endpoint "%s" required parameter [%s] is empty string', $endpoint, $parameter ), 3 );
                }
            }

            if ( ! self::validate_type( $value, $type ) ) {
                throw new \Exception( sprintf( 'Endpoint "%s" required parameter [%s] not type "%s"', $endpoint, $parameter, $type ), 3 );
            }

            if ( isset( $schema['min_size'] ) && ! self::validate_min_size( $value, $type, $schema['min_size'] ) ) {
                throw new \Exception( sprintf( 'Endpoint "%s" required parameter [%s] min size "%s"', $endpoint, $parameter, $schema['min_size'] ), 3 );
            }

            if ( isset( $schema['max_size'] ) && ! self::validate_max_size( $value, $type, $schema['max_size'] ) ) {
                throw new \Exception( sprintf( 'Endpoint "%s" required parameter [%s] max size "%s"', $endpoint, $parameter, $schema['max_size'] ), 3 );
            }

            if ( isset( $schema['allowed_values'] ) && ! self::validate_allowed_values( $value, $schema['allowed_values'] ) ) {
                throw new \Exception( sprintf( 'Endpoint "%s" required parameter [%s] invalid value "%s"', $endpoint, $parameter, $value ), 3 );                
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
     * @param mixed $value Value to validate.
     * @param string $type Type of the value.
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
     * Validate min size.
     *
     * @param mixed    $value      Value to validate.
     * @param string   $type       Type of the value.
     * @param int|null $min_size   Minimum allowed size.
     *
     * @return bool
     */
    public static function validate_min_size( $value, $type, $min_size = null ) {
        $min_size = null !== $min_size ? (int) $min_size : null;
        if ( null === $min_size ) {
            return true;
        }

        $length = $value;
        if ( is_array( $value ) ) {
            $length = count( $value );
        } 

        if ( in_array( $type, array( 'string', 'date', 'datetime' ), true ) ) {
            $length = strlen( $value );
        }

        if ( $length < $min_size ) {
            return false;
        }

        return true;
    }


    /**
     * Validate max size.
     *
     * @param mixed    $value      Value to validate.
     * @param string   $type       Type of the value.
     * @param int|null $max_size   Maximum allowed size.
     *
     * @return bool
     */
    public static function validate_max_size( $value, $type, $max_size = null ) {      
        $max_size = null !== $max_size ? (int) $max_size : null;
        if ( null === $max_size ) {
            return true;
        }

        $length = $value;
        if ( is_array( $value ) ) {
            $length = count( $value );
        } 

        if ( in_array( $type, array( 'string', 'date', 'datetime' ), true ) ) {
            $length = strlen( $value );
        }

        if ( $length > $max_size ) {
            return false;
        }

        return true;
    }


    /**
     * Validate allowed values.
     *
     * @param mixed        $value
     * @param string       $type
     * @param mixed|null   $allowed_values
     *
     * @return bool
     */
    public static function validate_allowed_values( $value, $allowed_values = null ) {
        if ( null === $allowed_values ) {
            return true;
        }

        if ( is_string( $allowed_values ) ) {
            $allowed_values = explode( ',', $allowed_values );
        }

        if ( empty( $allowed_values ) ) {
            return false;
        }

        return in_array( $value, $allowed_values, true );
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
        $status     = 200;

        $error_data = $wp_error->get_error_data();
        if ( isset( $error_data['status'] ) ) {
            $status = $error_data['status'];
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
            $status
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
