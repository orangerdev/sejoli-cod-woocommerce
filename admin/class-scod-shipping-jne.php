<?php
namespace Scod_Shipping\API;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Scod_Shipping
 * @subpackage Scod_Shipping/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Scod_Shipping
 * @subpackage Scod_Shipping/admin
 * @author     Sejoli Team <orangerdigiart@gmail.com>
 */
class JNE {

	private $username;

	private $api_key;

	private $body;

	private $endpoint;

	private $timeout;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {

		$this->username 	= 'TESTAPI';
		$this->api_key 		= '25c898a9faea1a100859ecd9ef674548';
		$this->body 		= [
								'username'	=> $this->username,
								'api_key'	=> $this->api_key
							];
		$this->timeout 		= 75;
	}

	private function do_request( $method = 'GET' ) {
		
		return wp_remote_request( $this->endpoint, [
			'headers' 	=> [
				'Content-Type' 	=> 'application/x-www-form-urlencoded',
				'Accept' 		=> 'application/json'
			],
			'method' 	=> $method,
			'timeout' 	=> $this->timeout,				    
			'body' 		=> $this->body
		]);	
	}

	public function get_origin() {

		$this->endpoint		= 'http://apiv2.jne.co.id:10102/insert/getorigin';

		$response = $this->do_request( 'POST' );

		$response_code = wp_remote_retrieve_response_code( $response );

		if( $response_code == 200 ) {

			$body = json_decode( $response['body'] );
			
			if( isset( $body->status ) && $body->status == 'false' ) {

				return false;

			} elseif( isset( $body->detail ) ) {

		 		return $body->detail;
			}

		} elseif ( $response_code == 500 ) {

			return false;
		}
	}

	public function get_destination() {

		$this->endpoint		= 'http://apiv2.jne.co.id:10102/insert/getdestination';

		$response = $this->do_request( 'POST' );

		$response_code = wp_remote_retrieve_response_code( $response );

		if( $response_code == 200 ) {

			$body = json_decode( $response['body'] );
			
			if( isset( $body->status ) && $body->status == 'false' ) {

				return false;

			} elseif( isset( $body->detail ) ) {

		 		return $body->detail;
			}

		} elseif ( $response_code == 500 ) {

			return false;
		}
	}

	public function get_tariff( $origin, $destination, $weight = 1 ) {

		$this->endpoint		= 'http://apiv2.jne.co.id:10102/tracing/api/pricedev';

		$this->body = array_merge( $this->body, [
			'from'		=> $origin,
			'thru'		=> $destination,
			'weight'	=> $weight
		]);

		$response = $this->do_request( 'POST' );

		$response_code = wp_remote_retrieve_response_code( $response );

		if( $response_code == 200 ) {

			$body = json_decode( $response['body'] );
			
			if( isset( $body->status ) && $body->status == 'false' ) {

				return false;

			} elseif( isset( $body->price ) ) {

		 		return $body->price;
			}

		} elseif ( $response_code == 500 ) {

			return false;
		}
	}

}
