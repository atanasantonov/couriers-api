<?php
/**
 * Request abstract class.
 *
 * @package Easy_Shipping
 * @author  Unax
 */

namespace Easy_Shipping\Lib\Courier_API;

/**
 * Request class.
 */
class Request {
	/**
     * API URI.
     *
     * @var string
     */
    public static string $uri = '';

	/**
	 * Endpoints.
	 *
	 * @var array
	 */
	protected static array $endpoints = array();

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected static $endpoint = '';

	/**
	 * Request parameters.
	 *
	 * @var array
	 */
	protected static $parameters = array();


	/**
	 * Request data.
	 *
	 * @var array
	 */	
	protected static $data = array();

	/**
	 * Request result.
	 *
	 * @var array
	 */
	public static $result = array();

	/**
	 * Set uri.
	 *
	 * @param string $uri The URI to set.
	 */
	public static function set_uri( string $uri ) {
		static::$uri = $uri;
	}


	/**
	 * Set endpoints.
	 *
	 * @param string $endpoint The endpoint to set.
	 */
	public static function set_endpoints( array $endpoints ): void {
		static::$endpoints = $endpoints;
	}


	/**
	 * Set endpoint.
	 *
	 * @param string $endpoint The endpoint to set.
	 */
	public static function set_endpoint( string $endpoint ): void {
		self::$endpoint = $endpoint;
	}


	/**
     * Set parameters.
     *
     * @param string $property Property name.
     * @param mixed  $value    Property value.
	 * 
	 * @throws \Exception If the endpoint is not set or invalid, or if a required property is missing or invalid.
     */
    public static function set_parameters( array $parameters ): void {
		self::$parameters = $parameters;
	}


	/**
     * Prepare request data.
	 * 
	 * @param array $request_data Data to prepare.
	 * 
	 * @return string
	 * 
	 * @throws \Exception If the endpoint is not set or invalid, or if a required property is missing or invalid.
     */
    public static function prepare( $request_data = array() ): string {
		$data = '';

        try {
            $endpoints = self::$endpoints;

            // Check endpoint.
            if ( empty( self::$endpoint ) ) {
                throw new \Exception(
                    sprintf(
                        'Endpoint "%s" not set',
                        self::$endpoint
                    ),
                    1
                );
            }

            if ( ! isset( $endpoints[ self::$endpoint ] ) ) {
                throw new \Exception(
                    sprintf(
                        'Endpoint "%s" not valid',
                        self::$endpoint
                    ),
                    2
                );
            }

            // Get endpoint schema.
            $schema = $endpoints[ self::$endpoint ];

            // Set parameters.
            $data = array();
            foreach ( self::$parameters as $parameter ) {
                if ( ! isset( $schema[ $parameter ] ) ) {
                    throw new \Exception(
                        sprintf(
                            'Property "%s" for endpoint "%s" not found in schema',
                            $parameter,
                            self::$endpoint
                        ),
                        3
                    );
                }

                $required = isset( $schema[ $parameter ]['required'] ) ? $schema[ $parameter ]['required'] : false;
                if ( true === $required ) {
					if ( ! isset( $request_data[ $parameter ] ) ) {
                        throw new \Exception(
                            sprintf(
                                "Required property [%s] not set",
                                $parameter,
                            ),
                            3
                        );
                    }

                    if ( $request_data[ $parameter ] === null ) {
                        throw new \Exception(
                            sprintf(
                                "Required property [%s] is null",
                                $parameter,
                            ),
                            3
                        );
                    }
                    if ( $request_data[ $parameter ] === '' ) {
                        throw new \Exception(
                            sprintf(
                                "Required property [%s] is empty",
                                $parameter,
                            ),
                            3
                        );
                    }
                }

                // Check if the property is set and if is not set do not add it to parameters array.
                if ( null === $request_data[ $parameter ] || '' === $request_data[ $parameter ] ) {
                    continue;
                }

                if ( ! API_Helper::validate_parameter( $data[ $parameter ], self::$endpoint, $parameter )  ) {
                    throw new \Exception(
                        sprintf(
                            "Invalid parameter, property [%s] value '%s'",
                            $parameter,
                            $request_data[ $parameter ]
                        ),
                        3
                    );
                }
            }

			// Convert to JSON.
			$data = json_encode( $request_data );
			if ( false === $data ) {
                throw new \Exception( sprintf( 'Request data encode JSON failed: %s (%s)', json_last_error_msg(), json_last_error() ), 5 );
            }
        } catch ( \Exception $e ) {
            API_Helper::handle_exception( $e, sprintf( 'Set parameters for endpoint "%s" failed.', self::$endpoint ) );
			return '';
        } finally {
			return $data;
		}
    }


	/**
	 * Get result.
	 *
	 * @return array
	 */
	public static function get_result() {
		return self::$result;
	}


	/**
	 * Set the value of $dataSet.
	 *
	 * @param array $dataSet The data to set.
	 *
	 * @return void
	 */
	public static function set_result( $result ) {
		self::$result = $result;
	}


	/**
	 * Request.
	 * 
	 * @param array  $request_data Data to send in the request.
	 *
	 * @return array|WP_Error The response data or WP_Error on failure.
	 */
	public static function request( $request_data = array(), $method = 'GET' ) {
		$data = self::prepare( $request_data );

		// API request.
		$args = array(
			'method'  => $method,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => $data,
			'timeout' => 30,
		);

		return wp_remote_request( self::$uri, $args );
	}


	/**
     * Response.
     *
     * @param array|WP_Error $request The request response.
     *
     * @return array {
     *     @type bool       $success Indicates if the response was successful.
     *     @type int|string $code    Error code or response code.
     *     @type string     $message Response or error message.
     *     @type array      $data    Response data (usually an array of results).
     * }
     */
    public static function response( $request ) : array {
        // Initial values.
        $response = array(
            'success' => false,
            'code'    => 0,
            'message' => '',
            'data'    => array(),
        );

        try {
			// Check if request is WP Error.
			if ( is_wp_error( $request ) ) {
				throw new \Exception( $request->get_error_message(), 11 );
			}

            // Get response body.
            $response_body = wp_remote_retrieve_body( $request );
            if ( '' === $response_body ) {
                throw new \Exception( 'Response body empty', 9 );
            }

            // Decode response body.
            $body = json_decode( $response_body, true );
            if ( empty( $body ) ) {
                throw new \Exception( sprintf( 'Decode JSON failed: %s (%s)', json_last_error_msg(), json_last_error() ), 6 );
            }

            $response['success'] = true;
            $response['data']    = $body;

            return $response;
        } catch ( \Throwable $e ) {
            API_Helper::handle_exception( $e, 'API response' );
            $response['code']    = $e->getCode();
            $response['message'] = $e->getMessage();

            return $response;
        }
    }
}