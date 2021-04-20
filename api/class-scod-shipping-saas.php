<?php
namespace SCOD_Shipping\API;

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
	 * JWT token.
	 *
	 * @since 1.0.0
	 */
	private $token_option = 'scod_shipping_saas_token';

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

	public function get_token() {

		$get_token = get_option( $this->token_option );
		return $get_token;
	}

	/**
     * Set headers data.
     *
     * @since   1.0.0
     */
	public function set_token_headers() {

		if( $token = $this->get_token() ) {

			return $this->headers = [
				'Authorization'	=> 'Bearer ' . $token,
				'Content-Type' 	=> 'application/json',
				'Accept' 		=> 'application/json'
			];

		}
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
	public function do_request(  ) {

		$params = array(
			'method' 	=> $this->method,
			'timeout' 	=> $this->timeout,
			'body' 		=> $this->body
		);

		if( $this->get_token() != NULL ) {
			$params['headers'] = $this->set_token_headers();
		}

		return wp_remote_request( $this->endpoint, $params );
	}

	/**
     * Get new JWT token.
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_new_token( $username, $password ) {
		try {

			//clear existing token
			update_option( $this->token_option, '' );

			$this->endpoint 	= 'https://wordpress.test/wp-json/jwt-auth/v1/token';
			$this->method 		= 'POST';

			$options			= array(
				'username'	=> $username,
				'password'	=> $password,
			);

			$get_response 		= $this->set_body_params( $options )->do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if( $data = $this->get_valid_token_object( $get_response ) ) :

					$token = $data->token;

					if( update_option( $this->token_option, $token ) ) {
						return $token;
					}

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
		try {

			$this->endpoint 	= 'https://wordpress.test/wp-json/scod/v1/stores/' . $store_id .'?key=' .$store_secret_key;
			$this->method 		= 'GET';
			$this->body			= NULL;

			$get_response 		= $this->do_request();

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
     * Local development only, will disable curl error when doing SSL verification.
     *
     * @since   1.0.0
     */
	public function disable_ssl_verify( $r, $url ) {
        $r['sslverify'] = false;
        return $r;
    }

}
