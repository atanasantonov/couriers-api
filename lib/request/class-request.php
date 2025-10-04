<?php
/**
 * Request abstract class.
 *
 * @package Easy_Shipping
 * @author  Unax
 */

namespace Easy_Shipping\Lib\Request;

/**
 * Request class.
 */
class Request {
	/**
     * API URI.
     *
     * @var string
     */
    public string $uri = '';

	/**
	 * Endpoints.
	 *
	 * @var array
	 */
	protected array $endpoints = array();

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = '';

	/**
	 * Request parameters.
	 *
	 * @var array
	 */
	protected $parameters = array();


	/**
	 * Request data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Request headers.
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Request result.
	 *
	 * @var array
	 */
	public $result = array();

	/**
	 * Set uri.
	 *
	 * @param string $uri The URI to set.
	 */
	public function set_uri( string $uri ) {
		$this->uri = $uri;
	}


	/**
	 * Set endpoints.
	 *
	 * @param array $endpoints The endpoints to set.
	 */
	public function set_endpoints( array $endpoints ): void {
		$this->endpoints = $endpoints;
	}


	/**
	 * Set endpoint.
	 *
	 * @param string $endpoint The endpoint to set.
	 */
	public function set_endpoint( string $endpoint ): void {
		$this->endpoint = $endpoint;
	}


	/**
     * Set parameters.
     *
     * @param array $parameters Parameters to set.
	 *
	 * @throws \Exception If the endpoint is not set or invalid, or if a required property is missing or invalid.
     */
    public function set_parameters( array $parameters ): void {
		$this->parameters = $parameters;
	}


	/**
	 * Set headers.
	 *
	 * @param array $headers The headers to set.
	 */
	public function set_headers( array $headers ): void {
		$this->headers = $headers;
	}


	/**
     * Prepare request data.
	 *
	 * @param array $request_data Data to prepare.
	 *
	 * @return array|\WP_Error The prepared data as a JSON string or WP_Error on failure.
	 *
	 * @throws \Exception If the endpoint is not set or invalid, or if a required property is missing or invalid.
     */
    public function prepare( $data = array() ): array|\WP_Error {
        try {
            // Check endpoint.
            if ( empty( $this->endpoint ) ) {
                throw new \Exception( 'Endpoint not set.', 1 );
            }

            if ( ! isset( $this->endpoints[ $this->endpoint ] ) ) {
                throw new \Exception( sprintf( 'Endpoint "%s" schema not set.', $this->endpoint ), 2 );
            }

            // Get endpoint schema.
            $schema = $this->endpoints[ $this->endpoint ];

            // Set parameters.
            foreach ( $this->parameters as $parameter ) {
                if ( ! isset( $schema[ $parameter ] ) ) {
                    throw new \Exception(
                        sprintf(
                            'Property "%s" for endpoint "%s" not found in schema',
                            $parameter,
                            $this->endpoint
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

                    if ( $data[ $parameter ] === null ) {
                        throw new \Exception(
                            sprintf(
                                "Required property [%s] is null",
                                $parameter,
                            ),
                            3
                        );
                    }
                    if ( $data[ $parameter ] === '' ) {
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
                if ( ! isset( $data[ $parameter ] ) || null === $data[ $parameter ] || '' === $data[ $parameter ] ) {
                    continue;
                }

                if ( ! Request_Helper::validate_parameter( $this->endpoint, $schema, $parameter, $data[ $parameter ] )  ) {
                    throw new \Exception(
                        sprintf(
                            "Invalid parameter, property [%s] value '%s'",
                            $parameter,
                            $data[ $parameter ]
                        ),
                        3
                    );
                }
            }

            return $data;
        } catch ( \Exception $e ) {
            Request_Helper::handle_exception( $e, sprintf( 'Set parameters for endpoint "%s" failed.', $this->endpoint ) );

            return new \WP_Error( 'request_prepare_failed', sprintf( 'Prepare request parameters failed. Error: %s', $e->getMessage() ), 13 );
        }
    }


	/**
	 * Get result.
	 *
	 * @return array
	 */
	public function get_result() {
		return $this->result;
	}


	/**
	 * Set the value of result.
	 *
	 * @param array $result The data to set.
	 *
	 * @return void
	 */
	public function set_result( $result ) {
		$this->result = $result;
	}


	/**
	 * Request.
	 *
	 * @param array  $request_data Data to send in the request.
	 * @param string $method       HTTP method (GET, POST, etc.).
	 *
	 * @return array|\WP_REST_Response The response data or WP_Error on failure.
	 */
	public function request( $data = array(), $method = 'GET' ) {
		$data = $this->prepare( $data );
        if ( is_wp_error( $data ) ) {
			return Request_Helper::handle_wp_error( $data );
		}

		// Default headers.
		$headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);

		// Merge with custom headers if set.
		if ( ! empty( $this->headers ) ) {
			$headers = array_merge( $headers, $this->headers );
		}

		// Set the request body.
		if ( 'GET' !== $method ) {
			$data = json_encode( $data );
			if ( false === $data ) {
				$error = new \WP_Error( 'json_encode_failed', sprintf( 'Request data encode JSON failed: %s (%s)', json_last_error_msg(), json_last_error() ), 5 );
                return Request_Helper::handle_wp_error( $error );
            } 
		}

		// API request.
		$args = array(
			'method'  => $method,
			'headers' => $headers,
			'body'    => $data,
			'timeout' => 30,
		);

		return wp_remote_request( $this->uri, $args );
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
    public function response( $request ) : array {
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
            Request_Helper::handle_exception( $e, 'API response' );
            $response['code']    = $e->getCode();
            $response['message'] = $e->getMessage();

            return $response;
        }
    }
}