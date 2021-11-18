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
class SICEPAT extends \SCOD_Shipping\API {

	/**
     * Set static data for sandbox api
     *
     * @since   1.0.0
     */
	public static function set_sandbox_data() {

		$api_key = 'ed5689029973fb138ade6a2db1c1b789';

		self::$headers = array(
			'api-key' => $api_key
		);

	}

	/**
     * Set static data for live api
     *
     * @since   1.0.0
     */
	public function set_live_data() {
		
		$api_key = 'ed5689029973fb138ade6a2db1c1b789';

		self::$headers = array(
			'api-key' => $api_key
		);

	}

	/**
     * Set static data based on api environment target
     *
     * @since   1.0.0
     *
     * @return 	(static) return an instance of static class
     */
	public static function set_params( $is_sandbox = true ) {
		
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
     * Get origin city data from SICEPAT API
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public static function get_origin() {
	
		try {
			self::$endpoint = 'https://apitrek.sicepat.com/customer/origin';
			self::$method 	= 'GET';

			$get_response 	= self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $data = self::get_valid_body_object( $get_response ) ) :
						
						if( isset( $data->sicepat->results ) ) {

							return $data->sicepat->results;
						}

						return new \WP_Error( 'invalid_api_response', 'Invalid tariff data.' );

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
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from SICEPAT API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		}
	
	}

	/**
     * Get destination data from SICEPAT API
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_destination() {

		try {
			self::$endpoint = 'https://apitrek.sicepat.com/customer/destination';
			self::$method 	= 'GET';

			$get_response 	= self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $data = self::get_valid_body_object( $get_response ) ) :
						
						if( isset( $data->sicepat->results ) ) {

							return $data->sicepat->results;
						}

						return new \WP_Error( 'invalid_api_response', 'Invalid tariff data.' );

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
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from SICEPAT API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		}

	}

	/**
     * Get tariff data from SICEPAT API
     *
     * @since   1.0.0
     *
     * @param 	$origin 		jne origin code
     * @param 	$destination 	jne destination code
     * @param 	$weight			weight of goods in Kg
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_tariff( string $origin, string $destination, int $weight = 1 ) {

		try {
			self::$endpoint = 'https://apitrek.sicepat.com/customer/tariff';
			self::$method 	= 'GET';
			self::$body 	= array(
				'origin'	  => $origin,
				'destination' => $destination,
				'weight'	  => $weight
			);

			$get_response 	= self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $data = self::get_valid_body_object( $get_response ) ) :

						if( isset( $data->sicepat->results ) ) {

							return $data->sicepat->results;
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
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from SICEPAT API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		}

	}

	/**
     * Get airway bill or number resi data from SICEPAT API
     *
     * @since   1.0.0
     *
     * @param 	$origin 		jne origin code
     * @param 	$destination 	jne destination code
     * @param 	$weight			weight of goods in Kg
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_airwaybill( $pickupParams ) {
		try {
			self::$endpoint = 'http://pickup.sicepat.com:8087/api/partner/requestpickuppackage';
			self::$method 	= 'POST';

			$auth_key = 'DE7085E652524385971725E39E8805DE';
	    	$pickupDataArray = array (
	    		"auth_key"              => $auth_key,
				"reference_number"      => "SICEPAT-TEST-SCPT".$pickupParams['orderID'],
				"pickup_request_date"   => date('Y-m-d H:i:s'),
				"pickup_merchant_name"  => $pickupParams['pickup_merchant_name'],
				"pickup_address"        => $pickupParams['pickup_address'],
				"pickup_city" 	        => $pickupParams['pickup_city'],
				"pickup_merchant_phone" => $pickupParams['pickup_merchant_phone'],
				"pickup_merchant_email" => $pickupParams['pickup_merchant_email'],
				'PackageList' => [ array (
					"receipt_number"      => "999888777666",
					"origin_code"         => $pickupParams['origin_code'],
					"delivery_type"       => $pickupParams['delivery_type'],
					"parcel_category"     => $pickupParams['parcel_category'],
					"parcel_content"      => $pickupParams['parcel_content'],
					"parcel_qty"          => $pickupParams['parcel_qty'],
					"parcel_uom"          => "Items",
					"parcel_value"        => $pickupParams['parcel_value'],
					"cod_value"           => $pickupParams['cod_value'],
					"total_weight"        => $pickupParams['total_weight'],
					"shipper_name"        => $pickupParams['shipper_name'],
					"shipper_address"     => $pickupParams['shipper_address'],
					"shipper_province"    => $pickupParams['shipper_province'],
					"shipper_city"        => $pickupParams['shipper_city'],
					"shipper_district"    => $pickupParams['shipper_district'],
					"shipper_zip"         => $pickupParams['shipper_zip'],
					"shipper_phone"       => $pickupParams['shipper_phone'],
					"shipper_longitude"   => "00000000",
					"shipper_latitude"    => "00000000",
					"recipient_title"     => "Bapak/Ibu",
					"recipient_name"      => $pickupParams['recipient_name'],
					"recipient_address"   => $pickupParams['recipient_address'],
					"recipient_province"  => $pickupParams['recipient_province'],
					"recipient_city"      => $pickupParams['recipient_city'],
					"recipient_district"  => $pickupParams['recipient_district'],
					"recipient_zip" 	  => $pickupParams['recipient_zip'],
					"recipient_phone"     => $pickupParams['recipient_phone'],
					"recipient_longitude" => "00000000",
					"recipient_latitude"  => "00000000",
					"destination_code"    => $pickupParams['destination_code']
				) ]
			);

			self::$body = $pickupDataArray;

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $data = self::get_valid_body_object( $get_response ) ) :
						
						if( isset( $data ) ) {

							return $data;
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
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from JNE API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		}
	}

	/**
     * Get Tracking history of status based airwaybill number from SICEPAT API
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public function get_tracking( string $tracking_number ) {
		try {
			self::$endpoint = 'https://apitrek.sicepat.com/customer/waybill';
			self::$method 	= 'GET';
			self::$body 	= array(
				'waybill' => '002589984055' //$tracking_number // 002589984055
			);

			$get_response 	= self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $data = self::get_valid_body_object( $get_response ) ) :
						return $data->sicepat->result;
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
			return new \WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from JNE API</strong>: %s', 'scod-shipping' ), $e->getMessage() ) );
		}
	}

}