<?php
/**
 * Zeron connector.
 *
 * @package UnaxPlugin
 * @author  Unax
 */

namespace Unax\Lib\Couriers;

use Unax\Lib\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Couriers Connector.
 */
class Connector {
    /**
     * Endpoints/entities.
     *
     * @var array
     */
    public static array $endpoints = array();


    /**
     * Errors.
     *
     * @var array
     */
    public static array $error_codes = array();



    /**
     * Get the endpoint/entity.
     *
     * @return string
     */
    public static function get_endpoint() : string {
        return self::$endpoint;
    }


    /**
     * Set the endpoint/entity.
     *
     * @param string $endpoint
     */
    public static function set_endpoint( string $endpoint ) {
        self::$endpoint = $endpoint;
    }


    /**
     * Get the parameters.
     *
     * @return array
     */
    public static function get_parameters() : array {
        return self::$parameters;
    }


    /**
     * Set the parameters.
     *
     * @param array $parameters
     */
    public static function set_parameters( array $parameters ) {
        self::$parameters = $parameters;
    }


    /**
     * Get the error codes.
     *
     * @return array
     */
    public static function get_error_codes() : array {
        return self::$error_codes;
    }


    /**
     * Set the error codes.
     *
     * @param array $error_codes
     */
    public static function set_error_codes( array $error_codes ) {
        self::$error_codes = $error_codes;
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
            $operations = Connector::$operations;

            // Check operation.
            if ( empty( static::$operation ) ) {
                throw new \Exception(
                    sprintf(
                        'Operation "%s" not set',
                        static::$operation
                    ),
                    1
                );
            }

            if ( ! isset( $operations[ static::$operation ] ) ) {
                throw new \Exception(
                    sprintf(
                        'Operation "%s" not valid',
                        static::$operation
                    ),
                    2
                );
            }

            // Get operation schema.
            $schema = $operations[ static::$operation ];

            // Set parameters.
            $parameters = array();
            $class_parameters = ! empty( static::$parameters ) ? static::$parameters : self::$parameters;
            foreach ( $class_parameters as $property ) {
                // Check if the property is set.
                if ( ! isset( $schema[ $property ] ) ) {
                    throw new \Exception(
                        sprintf(
                            'Property "%s" for operation "%s" not found in schema',
                            $property,
                            static::$operation
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

                if ( ! Connector::validate_parameter( static::${$property}, static::$operation, true, $property )  ) {
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
            Connector::handle_exception( $e, static::$operation . '::set_parameters()' );
            return false;
        }
    }


    /**
     * Request.
     *
     * @param string $operation
     * @param array  $parameters
     *
     * @return array|false
     */
    public static function request() {
        try {
            if ( ! self::validate_endpoint( self::$endpoint ) ) {
                throw new \Exception( 'Invalid endpoint', 1 );
            }

            if ( ! self::$uri ) {
                throw new \Exception( 'API URI not set', 2 );
            }

            // JSON encode data.
            $json_data = json_encode( self::$parameters );
            if ( ! $json_data ) {
                throw new \Exception( sprintf( 'Encode JSON failed: %s (%s)', json_last_error_msg(), json_last_error() ), 5 );
            }

            // Make remote request.
            $request = wp_remote_post(
                self::$uri,
                array(
                    'user-agent' => 'Easy Shipping/' . get_bloginfo( 'version' ),
                    'headers'    => array(
                        'Content-Type' => 'application/json; charset=utf-8',
                    ),
                    'body'    => $json_data,
                    'timeout' => 30,
                )
            );

            // Check if request is WP Error.
            if ( is_wp_error( $request ) ) {
                throw new \Exception( $request->get_error_message(), 7 );
            }

            return $request;
        } catch ( \Exception $e ) {
            $context = sprintf(
                'Connector request operation %s failed',
                self::$endpoint 
            );

            self::handle_exception( $e, $context ); 
            
            return false;
        }
    }


    /**
     * Response.
     *
     * @param array $request
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
            if ( false === $request ) {
                $response['code']    = 11;
                $response['message'] = 'Request failed';

                return $response;
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

            // Check response.
            if ( empty( $body['Response'] ) ) {
                throw new \Exception( 'Response no data', 8 );
            }

            // Check response result.
            if ( empty( $body['Response']['Success'] ) || 'true' !== $body['Response']['Success'] ) {
                $response['ErrorNumber'] = isset( $body['Response']['ErrorNumber'] ) ? sprintf( ' ErrorNumber: %s.', $body['Response']['ErrorNumber'] ) : '';
                $response['ErrorMessage'] = isset( $body['Response']['ErrorMessage'] ) ? sprintf( ' ErrorMessage: %s.', $body['Response']['ErrorMessage'] ) : '';

                throw new \Exception(
                    sprintf( 'Request failed.%s%s', $response['ErrorNumber'], $response['ErrorMessage'] ),
                    11
                );
            }

            $response_data = null;
            if ( isset( $body['Response']['Destination']['Endpoint/entity']['DataSet']['Table1'] ) ) {
                $response_data = $body['Response']['Destination']['Endpoint/entity']['DataSet']['Table1'];
            }

            if ( isset( $body['Response']['Destination']['Endpoint/entity']['DataSet']['Table'] ) ) {
                $response_data = $body['Response']['Destination']['Endpoint/entity']['DataSet']['Table'];
            }

            $response['success'] = true;
            $response['data']    = $response_data;

            return $response;
        } catch ( \Throwable $e ) {
            self::handle_exception( $e, 'Connector response' );
            $response['code']    = $e->getCode();
            $response['message'] = $e->getMessage();

            return $response;
        }
    }


	
}
