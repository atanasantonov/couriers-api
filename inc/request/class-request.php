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
	 * Request result data set.
	 *
	 * @var array
	 */
	public static $dataSet = array();

	/**
	 * Set uri.
	 *
	 * @param string $uri The URI to set.
	 */
	public static function set_uri( string $uri ) {
		static::$uri = $uri;
	}


	/**
	 * Set endpoint.
	 *
	 * @param string $endpoint The endpoint to set.
	 */
	public static function set_endpoint( string $endpoint ) {
		static::$endpoint = $endpoint;
	}


	/**
     * Set the value of a property.
     *
     * @param string $property Property name.
     * @param mixed  $value    Property value.
     *
     * @return array|bool
     */
    public static function set_parameters() {
        try {
            $endpoints = self::$endpoints;

            // Check endpoint.
            if ( empty( static::$endpoint ) ) {
                throw new \Exception(
                    sprintf(
                        'Endpoint "%s" not set',
                        static::$endpoint
                    ),
                    1
                );
            }

            if ( ! isset( $endpoints[ static::$endpoint ] ) ) {
                throw new \Exception(
                    sprintf(
                        'Endpoint "%s" not valid',
                        static::$endpoint
                    ),
                    1
                );
            }

            if ( ! isset( $endpoints[ static::$endpoint ] ) ) {
                throw new \Exception(
                    sprintf(
                        'Operation "%s" not valid',
                        static::$endpoint
                    ),
                    2
                );
            }

            // Get endpoint schema.
            $schema = $endpoints[ static::$endpoint ];

            // Set parameters.
            $parameters = array();
            $class_parameters = ! empty( static::$parameters ) ? static::$parameters : self::$parameters;
            foreach ( $class_parameters as $property ) {
                // Check if the property is set.
                if ( ! isset( $schema[ $property ] ) ) {
                    throw new \Exception(
                        sprintf(
                            'Property "%s" for endpoint "%s" not found in schema',
                            $property,
                            static::$endpoint
                        ),
                        3
                    );
                }

                $required = isset( $schema[ $property ]['required'] ) ? $schema[ $property ]['required'] : false;
                if ( true === $required ) {
                    if ( static::${$property} === null ) {
                        throw new \Exception(
                            sprintf(
                                "Required property [%s] is null",
                                $property,
                            ),
                            3
                        );
                    }
                    if ( static::${$property} === '' ) {
                        throw new \Exception(
                            sprintf(
                                "Required property [%s] is empty",
                                $property,
                            ),
                            3
                        );
                    }
                }

                // Check if the property is set and if is not set do not add it to parameters array.
                if ( null === static::${$property} || '' === static::${$property} ) {
                    continue;
                }

                if ( ! API_Helper::validate_parameter( static::${$property}, static::$endpoint, true, $property )  ) {
                    throw new \Exception(
                        sprintf(
                            "Invalid parameter, property [%s] value '%s'",
                            $property,
                            static::${$property}
                        ),
                        3
                    );
                }

                $parameters[ $property ] = static::${$property};
            }

            return $parameters;
        } catch ( \Exception $e ) {
            API_Helper::handle_exception( $e, sprintf( 'Set parameters for endpoint "%s" failed.', static::$endpoint ) );
            return false;
        }
    }


	/**
	 * Get the value of $dataSet.
	 *
	 * @return array
	 */
	public static function getDataSet() {
		return static::$dataSet;
	}

	/**
	 * Set the value of $dataSet.
	 *
	 * @param array $dataSet The data to set.
	 *
	 * @return void
	 */
	public static function setDataSet( $dataSet ) {
		static::$dataSet = $dataSet;
	}

	/**
	 * Prepare request data with validation.
	 *
	 * @return array|bool
	 */
	public static function prepare() {
		try {
			$objects = Connector::$objects;

			// Check object.
			if ( empty( static::$object ) ) {
				throw new \Exception(
					sprintf(
						'Object "%s" not set',
						static::$object
					),
					1
				);
			}

			if ( ! isset( $objects[ static::$object ] ) ) {
				throw new \Exception(
					sprintf(
						'Object "%s" not valid',
						static::$object
					),
					2
				);
			}

			// Get object schema.
			$schema = $objects[ static::$object ];

			// Set parameters.
			$parameters = array();
			$class_parameters = ! empty( static::$parameters ) ? static::$parameters : self::$parameters;
			foreach ( $class_parameters as $property ) {
				// Check if the property is set.
				if ( ! isset( $schema[ $property ] ) ) {
					throw new \Exception(
						sprintf(
							'Property "%s" for object "%s" not found in schema',
							$property,
							static::$object
						),
						3
					);
				}

				$required = isset( $schema[ $property ]['required'] ) ? $schema[ $property ]['required'] : false;
				if ( true === $required ) {
					if ( static::${$property} === null ) {
						throw new \Exception(
							sprintf(
								'Required property [%s] is null',
								$property,
							),
							3
						);
					}
					if ( static::${$property} === '' ) {
						throw new \Exception(
							sprintf(
								'Required property [%s] is empty',
								$property,
							),
							3
						);
					}
				}

				// Check if the property is set and if is not set do not add it to parameters array.
				if ( null === static::${$property} || '' === static::${$property} ) {
					continue;
				}

				if ( ! Connector::validate_parameter( static::${$property}, static::$object, true, $property ) ) {
					throw new \Exception(
						sprintf(
							'Invalid parameter, property [%s] value "%s"',
							$property,
							static::${$property}
						),
						3
					);
				}

				$parameters[ $property ] = static::${$property};
			}

			return wp_json_encode( $parameters );
		} catch ( \Exception $e ) {
			Connector::handle_exception( $e, static::$object . '::prepare()' );
			return false;
		}
	}

	/**
	 * Request.
	 *
	 * @return array|bool
	 */
	public static function request() {
		// Prepare request data.
		$prepared_data = static::prepare();
		if ( false === $prepared_data ) {
			return false;
		}

		// API request.
		$request = Connector::request( static::$object, $prepared_data );

		// Get response.
		$response = Connector::response( $request );
		if ( false === $response['success'] ) {
			return false;
		}

		self::setDataSet( $response['data'] );

		return $response;
	}
}