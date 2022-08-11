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
class ARVEOLI extends \SCOD_Shipping\API {

	/**
     * Set static data for sandbox api
     *
     * @since   1.0.0
     */
	public static function set_sandbox_data() {

		$api_key = '0o4k0gs4o0kwg8cs840oskwscc4k0g4swwww8804';

		self::$headers = array(
			'access-key' => $api_key
		);

	}

	/**
     * Set static data for live api
     *
     * @since   1.0.0
     */
	public static function set_live_data() {

		$api_key = '0o4k0gs4o0kwg8cs840oskwscc4k0g4swwww8804';

		self::$headers = array(
			'access-key' => $api_key
		);

	}

	/**
     * Set static data based on api environment target
     *
     * @since   1.0.0
     *
     * @return 	(static) return an instance of static class
     */
	public static function set_params( $is_sandbox = false ) {

		self::$headers = [
			'Content-Type' => 'application/x-www-form-urlencoded',
			'Accept' 	   => 'application/json'
		];

		if( $is_sandbox ):

			self::set_sandbox_data();

		else:

			self::set_live_data();

		endif;

		return new static;

	}

	/**
     * Check response from api to determine if request is successful
     *
     * @since   1.0.0
     *
     * @return 	(array|boolean) The response array or false on failure
     */
	public static function get_valid_body_object( $response ) {

		$response_body = $response['body'];

		if( isset( $response_body->status ) && $response_body->status == 'false' ) {
			return false;
		}
	 	
	 	return json_decode( $response_body );

	}

	/**
     * Get origin data from Arveoli API
     *
     * @since   1.0.0
     *
     * @param 	$city 		 city name
     * 
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public static function get_origin( string $city ) {

		try {

			self::$endpoint = 'https://sandbox.arveoli.com/api/region/origins/jne?query='.$city;
			self::$method 	= 'GET';

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $origin = self::get_valid_body_object( $get_response ) ) :
						
						if( isset( $origin ) ) {

							return $origin;

						}

						return new \WP_Error( 'invalid_api_response', 'Invalid origin data.' );

					else:
						
						return new \WP_Error( 'invalid_api_response', 'Invalid response body.' );

					endif;

				else :

					return new \WP_Error( 'invalid_api_response', 'Invalid response code.' );

				endif;

			else :

				return $get_response;

			endif;

		} catch ( Exception $e ) {
			
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from Arveoli API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		
		}

	}

	/**
     * Get destination data from Arveoli API
     *
     * @since   1.0.0
     *
     * @param 	$province_name 		   province name
     * @param 	$city_name 			   city name
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_destination( string $city, string $district ) {

		try {

			self::$endpoint = 'https://sandbox.arveoli.com/api/region/destinations/jne/'.$city.'/'.$district;
			self::$method 	= 'GET';

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $destination = self::get_valid_body_object( $get_response ) ) :
						
						if( isset( $destination ) ) {

							return $destination;

						}

						return new \WP_Error( 'invalid_api_response', 'Invalid destination data.' );

					else:
						
						return new \WP_Error( 'invalid_api_response', 'Invalid response body.' );

					endif;

				else :

					return new \WP_Error( 'invalid_api_response', 'Invalid response code.' );

				endif;

			else :

				return $get_response;

			endif;

		} catch ( Exception $e ) {
			
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from Arveoli API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		
		}

	}

	/**
     * Get tariff data from Arveoli API
     *
     * @since   1.0.0
     *
     * @param 	$origin 			origin code
     * @param 	$destination 		destination code
     * @param 	$weight				weight of goods in Kg
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_tariff( string $expedition, string $origin, string $destination, int $weight = 1 ) {

		try {

			self::$endpoint = 'https://sandbox.arveoli.com/api/tariff/check';
			self::$method 	= 'POST';

			$tariffDataArray = array(
				"expedition"  => $expedition,
			  	"origin" 	  => $origin,
			  	"destination" => $destination,
				"weight" 	  => $weight
			);

			self::$body = $tariffDataArray;

			$get_response = self::do_request();

			if ( ! is_wp_error( $weight ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $tariff = self::get_valid_body_object( $get_response ) ) :

						if( isset( $tariff ) ) {

							return $tariff;

						}

						return new \WP_Error( 'invalid_api_response', 'Invalid tariff data.' );
						
					else:

						return new \WP_Error( 'invalid_api_response', 'Invalid response body.' );

					endif;

				else :

					return new \WP_Error( 'invalid_api_response', 'Invalid response code.' );

				endif;

			else :

				return new \WP_Error( 'invalid_api_response', 'Invalid response.' );

			endif;

		} catch ( Exception $e ) {

			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from Arveoli API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		
		}

	}

	/**
     * Get airway bill or number resi data from Arveoli API
     *
     * @since   1.0.0
     *
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_airwaybill( $pickupParams ) {
		
		try {

			self::$endpoint = 'https://sandbox.arveoli.com/api/orders?ordertype=pickup';
			self::$method 	= 'POST';

			// $pickupDataArray = array(
			// 	"expedition" => $pickupParams['expedition'], // new
			//   	"cod" 		 => $pickupParams['codflag'],
			//   	"insurance"  => $pickupParams['insurance'],
			//   	"sender" 	 => [ array(
			//     	"name"		 => $pickupParams['shipperName'],
			//     	"phone" 	 => $pickupParams['shipperPhone'],
			//     	"address" 	 => $pickupParams['shipperAddress'],
			//     	"city" 		 => $pickupParams['shipperCity'],
			//     	"postal" 	 => $pickupParams['shipperZip'],
			//     	"origincode" => $pickupParams['origin'],
			//     	"branchcode" => $pickupParams['branch'] // new
			//   	) ],
			//   	"recipient" => [ array(
			//     	"name"			  => $pickupParams['receiverName'],
			// 	    "phone"			  => $pickupParams['receiverPhone'],
			// 	    "address"		  => $pickupParams['receiverAddress'],
			// 	    "address2"		  => "",
			// 	    "address3"	 	  => "",
			// 	    "district"		  => $pickupParams['receiverDistrict'],
			// 	    "city"			  => $pickupParams['receiverCity'],
			// 	    "province"		  => $pickupParams['receiverProvince'],
			// 	    "postal"		  => $pickupParams['receiverZip'],
			// 	    "service"		  => $pickupParams['service'],
			// 	    "cost"			  => $pickupParams['shippingPrice'],
			// 	    "destinationcode" => $pickupParams['destination']
			// 	) ],
			//   	"goods" => [ array(
			// 	    "description" => $pickupParams['description'],
			// 	    "quantity" 	  => $pickupParams['qty'],
			// 	    "weight"	  => $pickupParams['weight'],
			// 	    "value"		  => $pickupParams['packageAmount'], // new
			// 	    "notes"		  => $pickupParams['note'],
			// 	    "category"	  => $pickupParams['productCategory'], // new
			// 	    "cod"		  => $pickupParams['codAmount']
			//   	) ],
			//   	"pickup" => [ array(
			// 	    "name"     => "arya",
			// 	    "date"	   => date('d-m-Y'),
			// 	    "time"	   => date('H:i'),
			// 	    "pic"	   => "TEAS",
			// 	    "picphone" => "6289100000002",
			// 	    "address"  => "JAKARTA",
			// 	    "district" => "JAKARTA",
			// 	    "city"     => "JAKARTA",
			// 	    "province" => "DKI JAKARTA",
			// 	    "service"  => "Reguler",
			// 	    "vehicle"  => "MOTOR"
			//   	) ]
			// );

			$pickupDataArray = array(
				"expedition" => "sicepat",
			  	"cod" 		 => $pickupParams['codflag'],
			  	"insurance"  => $pickupParams['insurance'],
			  	"sender" 	 => array(
			    	"name"		 => $pickupParams['shipperName'],
			    	"phone" 	 => $pickupParams['shipperPhone'],
			    	"address" 	 => $pickupParams['shipperAddress'],
			    	"city" 		 => $pickupParams['shipperCity'],
			    	"postal" 	 => $pickupParams['shipperZip'],
			    	"origincode" => "CGK",
			    	"branchcode" => "CGK000"
			  	),
			  	"recipient" => array(
			    	"name"			  => $pickupParams['receiverName'],
				    "phone"			  => $pickupParams['receiverPhone'],
				    "address"		  => $pickupParams['receiverAddress'],
				    "address2"		  => "",
				    "address3"	 	  => "",
				    "district"		  => $pickupParams['receiverDistrict'],
				    "city"			  => $pickupParams['receiverCity'],
				    "province"		  => $pickupParams['receiverProvince'],
				    "postal"		  => $pickupParams['receiverZip'],
				    "service"		  => "SIUNT",
				    "cost"			  => $pickupParams['shippingPrice'],
				    "destinationcode" => "BDO10000"
				),
			  	"goods" => array(
				    "description" => $pickupParams['description'],
				    "quantity" 	  => $pickupParams['qty'],
				    "weight"	  => $pickupParams['weight'],
				    "value"		  => $pickupParams['packageAmount'], // new
				    "notes"		  => "Mohon Segera Diproses, Terima Kasih",
				    "category"	  => $pickupParams['category'], // new
				    "cod"		  => $pickupParams['codAmount']
			  	),
			  	"pickup" => array(
				    "name"     => "arya",
				    "date"	   => date('d-m-Y'),
				    "time"	   => date('H:i'),
				    "pic"	   => "TEAS",
				    "picphone" => "6289100000002",
				    "address"  => "JAKARTA",
				    "district" => "JAKARTA",
				    "city"     => "JAKARTA",
				    "province" => "DKI JAKARTA",
				    "service"  => "Reguler",
				    "vehicle"  => "MOTOR"
			  	)
			);

			self::$body = $pickupDataArray;

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $pickup = self::get_valid_body_object( $get_response ) ) :

						if( isset( $pickup ) ) {

							return $pickup;

						}

						return new \WP_Error( 'invalid_api_response', 'Invalid airwaybill data.' );
						
					else:

						return new \WP_Error( 'invalid_api_response', 'Invalid response body.' );
					
					endif;

				else :

					return new \WP_Error( 'invalid_api_response', 'Invalid response code.' );
				
				endif;

			else :
				
				return new \WP_Error( 'invalid_api_response', 'Invalid response.' );
			
			endif;

		} catch ( Exception $e ) {

			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from Arveoli API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		
		}

	}

	/**
     * Get Tracking history of status based airwaybill number from Arveoli API
     *
     * @since   1.0.0
     *
     * * @param 	$tracking_number 		airwaybill code
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_tracking( string $expedition, string $tracking_number ) {

		try {

			self::$endpoint = 'https://sandbox.arveoli.com/api/orders/track';
			self::$method 	= 'POST';

			$trackingDataArray = array(
				"expedition" => $expedition,
			  	"cnote" 	 => $tracking_number
			);

			self::$body = $trackingDataArray;

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $tracking = self::get_valid_body_object( $get_response ) ) :
						
						if( isset( $tracking ) ) {

							return $tracking;

						}

						return new \WP_Error( 'invalid_api_response', 'Invalid tracking history data.' );

					else:

						return new \WP_Error( 'invalid_api_response', 'Invalid response body.' );

					endif;

				else :

					return new \WP_Error( 'invalid_api_response', 'Invalid response code.' );
				
				endif;

			else :

				return $get_response;
			
			endif;

		} catch ( Exception $e ) {
			
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from Arveoli API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		
		}

	}

}