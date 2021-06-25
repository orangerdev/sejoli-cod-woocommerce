<?php
namespace SCOD_Shipping\API;

use SCOD_Shipping\Shipping_Method;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    SCOD_Shipping
 * @subpackage SCOD_Shipping/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    SCOD_Shipping
 * @subpackage SCOD_Shipping/admin
 * @author     Sejoli Team <orangerdigiart@gmail.com>
 */
class SCOD {

	/**
	 * Option name for token.
	 *
	 * @since 1.0.0
	 */
	private $token_option = 'scod_shipping_saas_token';

	/**
	 * Base URL.
	 *66666666
	 * @since 1.0.0
	 */
	private $base_url;

	/**
	 * Endpoint.
	 *
	 * @since 1.0.0
	 */
	private $endpoint;

	/**
	 * Set timeout param for request.
	 *
	 * @since 1.0.0
	 */
	private $timeout = 75;

	/**
	 * Headers.
	 *
	 * @since 1.0.0
	 */
	private $headers;

	/**
	 * Body.
	 *
	 * @since 1.0.0
	 * @var $token
	 */
	private $body;

	/**
     * Construct class.
	 * 
     */
	public function __construct() {
		$this->base_url = 'https://cod.sejoli.co.id';
	}

	/**
     * Get endpoint url.
     *
     * @since   1.0.0
	 * @return (mixed|false)
     */
	public function get_endpoint_url( $endpoint ) {
		return $this->base_url . '/' . $endpoint;
	}

	/**
     * Get token from option data if available.
     *
     * @since   1.0.0
	 * @return (mixed|false)
     */
	public function get_token() {
		return get_option( $this->token_option );
	}

	/**
     * Set token value.
     *
     * @since   1.0.0
     */
	public function set_token( $token ) {
		return update_option( $this->token_option, $token );
	}

	/**
     * Clear existing token. Set token to empty value.
     *
     * @since   1.0.0
     */
	public function reset_token() {
		return update_option( $this->token_option, '' );
	}

	/**
     * Set headers data.
     *
     * @since   1.0.0
     */
	public function set_token_headers( $token ) {
		return $this->headers = [
			'Authorization'	=> 'Bearer ' . $token,
			'Accept' 		=> 'application/json'
		];
	}

	/**
	 * Set body params.
	 *
	 * @since 1.0.0
	 * @param array $options array of options to set.
	 */
	public function set_body_params( $options ) {

		$body = array();
		$this->body = null;

		if( count( $options ) > 0 ) {
			foreach ( $options as $key => $value ) {
				if( $value != NULL)
				$body[$key] = $value;
			}
		}

		if( count( $body ) > 0 ) {
			$this->body = $body;
		}

		return $this;
	}

	/**
     * Container for wp_remote_request function
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function do_request() {
		error_log( 'Doing request ..' );
		$params = array(
			'method'  => $this->method,
			'timeout' => $this->timeout,
			'body' 	  => $this->body
		);

		if( $token = $this->get_token() ) {
			$params['headers'] = $this->set_token_headers( $token );
		}

		return wp_remote_request( $this->get_endpoint_url( $this->endpoint ), $params );
	}

	/**
     * Validate existing token.
     *
     * @since   1.0.0
     */
	public function validate_token( $token ) {
		error_log( 'Validating token ..' );
		$params = array(
			'method'  => 'POST',
			'timeout' => $this->timeout,
			'headers' => $this->set_token_headers( $token ),
		);
		
		$endpoint	  = 'wp-json/jwt-auth/v1/token/validate';
		$get_response = wp_remote_request( $this->get_endpoint_url( $endpoint ), $params );

		if ( ! is_wp_error( $get_response ) ) {
			$response_body = json_decode( $get_response['body'] );
			return $this->validate_body( $response_body );
		}

		return false;
	}

	/**
     * Get new JWT token.
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_new_token( $username, $password ) {
		error_log( 'Getting new token ..' );
		try {
			$this->reset_token();

			$this->endpoint 	= 'wp-json/jwt-auth/v1/token';
			$this->method 		= 'POST';

			$options = array(
				'username'	=> $username,
				'password'	=> $password,
			);

			$get_response 		= $this->set_body_params( $options )->do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if( $data = $this->get_valid_token_object( $get_response ) ) :
					$token = $data->token;

					if( ! $this->set_token( $token ) ) {
						return new \WP_Error( 'invalid_api_response', 'Invalid response token.' );
					}

					return $token;

				endif;

				return new \WP_Error( 'invalid_api_response', 'Invalid response token.' );

			else :
				return $get_response;
			endif;

		} catch ( Exception $e ) {
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from SCOD API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		}
	}

	/**
     * Get store detail.
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_store_detail( $store_id, $store_secret_key  ) {
		error_log( 'Getting store data ..' );
		try {

			$this->endpoint = 'wp-json/scod/v1/stores/' . $store_id .'?key=' .$store_secret_key;
			$this->method 	= 'GET';
			$this->body		= NULL;

			$get_response 	= $this->do_request();

			if ( ! is_wp_error( $get_response ) ) :

				$body = json_decode( $get_response['body'] );

				if( isset( $body->data->status ) && ( $body->data->status != 200 ) ) :
					return new \WP_Error( 'invalid_api_response', $body->message );
				endif;

				return $body;
			else :
				return $get_response;
			endif;

		} catch ( Exception $e ) {
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from SCOD API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		}
	}

	/**
     * API to create new order data.
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function post_create_order( $params ) {
		error_log( 'Creating new order data ..' );
		try {

			$this->endpoint = 'wp-json/scod/v1/orders';
			$this->method 	= 'POST';

			$body_params = wp_parse_args( $params, array(
				'store_id'		  => NULL,
				'store_secret'	  => NULL,
				'buyer_name'	  => NULL,
				'buyer_email'	  => NULL,
				'buyer_phone'	  => NULL,
				'courier_name'	  => NULL,
				'invoice_number'  => NULL,
				'shipper_name'    => NULL,
		        'shipper_addr1'   => NULL,
		        'shipper_addr2'   => NULL,
		        'shipper_city'    => NULL,
		        'shipper_region'  => NULL,
		        'shipper_zip'     => NULL,
		        'shipper_phone'   => NULL,
		        'receiver_name'   => NULL,
		        'receiver_addr1'  => NULL,
		        'receiver_addr2'  => NULL,
		        'receiver_city'   => NULL,
		        'receiver_region' => NULL,
		        'receiver_zip'    => NULL,
		        'receiver_phone'  => NULL,
		        'qty'             => NULL,
		        'weight'          => NULL,
		        'goods_desc'      => NULL,
		        'goods_value'     => NULL,
		        'goods_type'      => NULL,
		        'insurance'       => NULL,
		        'origin'          => NULL,
		        'destination'     => NULL,
		        'service'         => NULL,
		        'codflag'         => NULL,
		        'codamount'       => NULL,
				'invoice_total'   => NULL,
				'shipping_fee'	  => NULL,
				'shipping_number' => NULL,
				'shipping_status' => NULL,
				'notes'			  => NULL,
				'order'			  => NULL
			));

			if( \is_null( $body_params['store_id'] ) || \is_null( $body_params['secret_key'] ) ) {
				return new \WP_Error( 'invalid_api_params', 'Store account is invalid.' );
			}

			//Always validate token first before doing request
			if( $token = $this->get_token() ) {

				// Validate token if set
				if( $token ) {
					$validate_token = $this->validate_token( $token );
	
					// Get new token
					if( ! $validate_token ) {
						
						// Get order shipping instance
						$order = $body_params['order'];
						$shipping_method_instance_id = null;

						foreach( $order->get_items( 'shipping' ) as $item_id => $item ){
							$shipping_method_instance_id = $item->get_instance_id(); // The instance ID
						}

						if( $shipping_method_instance_id ) {
							
							$shipping_instance = new Shipping_Method( $shipping_method_instance_id );
							$username = $shipping_instance->get_option( 'scod_username' );
							$password = $shipping_instance->get_option( 'scod_password' );

							if( $username && $password ) {
								$token = $this->get_new_token( $username, $password );

								if( is_wp_error( $token ) ) {
									return new \WP_Error( 'invalid_token', 'Token is invalid.' );
								}
							}
						} else {
							return new \WP_Error( 'invalid_token', 'Failed to get new token.' );
						}
					}

					$this->set_token( $token );
				}
			}

			unset( $body_params['order'] );
			$set_body 	  = $this->set_body_params( $body_params );
			$get_response = $this->do_request();

			if ( ! is_wp_error( $get_response ) ) :
				$body = json_decode( $get_response['body'] );
				$response_code = wp_remote_retrieve_response_code( $get_response );

				if( $response_code != 200 ) {
					return new \WP_Error( 'invalid_response' );
				}

				if( $response_code == 409 ) {
					$conflict_msg = 'Order already registered.';
					
					if( isset( $body->message ) ) {
						$conflict_msg = $body->message;
					}

					return new \WP_Error( 'duplicate_order_data', $conflict_msg );
				}
				
				if( isset( $body->data->status ) && ( $body->data->status != 200 ) ) :
					return new \WP_Error( 'invalid_api_response', $body->message );
				endif;

				return $body;
			else :
				return $get_response;
			endif;

		} catch ( Exception $e ) {
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from SCOD API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		}
	}

	/**
     * API to create new order data.
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function post_update_order( $order_id, $status, $shipNumber ) {
		error_log( 'Updating order data ..' );
		try {

			$this->endpoint = 'wp-json/scod/v1/orders/update/' . $order_id. '/' .$status. '/' .$shipNumber;
			$this->method 	= 'PUT';
			$this->body		= NULL;

			$get_response 	= $this->do_request();

			if ( ! is_wp_error( $get_response ) ) :

				$body = json_decode( $get_response['body'] );
				if( isset( $body->data->status ) && ( $body->data->status != 200 ) ) :
					return new \WP_Error( 'invalid_api_response', $body->message );
				endif;

				return $body;
			else :
				return $get_response;
			endif;

		} catch ( Exception $e ) {
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from SCOD API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		}
	}

	/**
     * Check response from api to determine if request is successful
     *
     * @since   1.0.0
     *
     * @return 	(array|boolean) The response array or false on failure
     */
	public function get_valid_token_object( $response ) {
		$response_body = json_decode( $response['body'] );

		if( ! isset( $response_body->token ) ) {
			return false;
		}

	 	return $response_body;
	}

	/**
     * Validate body response.
     *
     * @since   1.0.0
     */
	public function validate_body( $body ) {
		$error_codes = array( 'jwt_auth_failed', 'jwt_auth_invalid_token' );
		
		if( $body->code && in_array( $body->code, $error_codes ) ) {
			return false;
		}

		return true;
    }

	/**
     * Local development only, will suppress curl error on SSL verification.
     *
     * @since   1.0.0
     */
	public function disable_ssl_verify( $r, $url ) {

		if ( 'local' == wp_get_environment_type() ) {
	        $r['sslverify'] = false;
		}

		return $r;
    }

}
