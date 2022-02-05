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

		$api_key 	= '0o4k0gs4o0kwg8cs840oskwscc4k0g4swwww8804';

		self::$body = array(
			'ARVEOLI_KEY'  => $api_key
		);

	}

	/**
     * Set static data for live api
     *
     * @since   1.0.0
     */
	public static function set_live_data() {

		$api_key 	= '0o4k0gs4o0kwg8cs840oskwscc4k0g4swwww8804';

		self::$body = array(
			'ARVEOLI_KEY'  => $api_key
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

			self::$endpoint = 'https://apiv2.arveoli.com/origin?keyword='.$city;
			self::$method 	= 'GET';

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $data = self::get_valid_body_object( $get_response ) ) :
						
						if( isset( $data->detail ) ) {

							return $data->detail;

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
	public function get_destination( string $province_name, string $city_name ) {

		try {

			self::$endpoint = 'https://apiv2.arveoli.com/dest/getdistrict';
			self::$method 	= 'POST';
			self::$body 	= array_merge( self::$body, [
				'province_name' => $province_name,
				'city_name'	    => $city_name
			]);

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $data = self::get_valid_body_object( $get_response ) ) :

						if( isset( $data ) ) {

							return $data;

						}

						return new \WP_Error( 'invalid_api_response', 'Invalid destination data.' );
						
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
	public function get_tariff( string $origin, string $destination, int $weight = 1 ) {

		try {

			self::$endpoint = 'https://apiv2.arveoli.com/tarif';
			self::$method 	= 'POST';
			self::$body 	= array_merge( self::$body, [
				'origin' => $origin,
				'dest'	 => $destination,
				'weight' => $weight
			]);

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $tariff = self::get_valid_body_object( $get_response ) ) :

						if( isset( $tariff->data->price ) ) {

							return $tariff->data->price;

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
	public function get_airwaybill( int $order_date, string $shipper_name, string $shipper_phone, string $shipper_addr, string $shipper_city, int $shipper_zip, string $receiver_name, string $receiver_phone, string $receiver_addr, string $receiver_email, string $receiver_city, int $receiver_zip, string $receiver_region, string $receiver_district, string $receiver_subdistrict, string $origin, string $service, int $weight, int $qty, string $goodsdesc, int $goodsvalue, string $insurance, string $note, string $codflag, int $codamount, int $shipping_price ) {
		
		try {

			self::$endpoint = 'https://apiv2.arveoli.com/droptest';
			self::$method 	= 'POST';
			self::$body 	= array_merge( self::$body, [
				'TANGGALORDER'	  => $order_date,
				'SHIPPER_NAME'	  => $shipper_name,
				'SHIPPER_PHONE'	  => $shipper_phone,
				'SHIPPER_ADDRESS' => $shipper_addr,
				'SHIPPER_CITY'	  => $shipper_city,
				'SHIPPER_ZIP'	  => $shipper_zip,
				'CUST_NAMA'	 	  => $receiver_name,
				'CUST_PHONE'	  => $receiver_phone,
				'CUST_ALAMAT'	  => $receiver_addr,
				'CUST_EMAIL'	  => $receiver_email,
				'CUST_KOTA'	 	  => $receiver_city,
				'CUST_KODEPOS'	  => $receiver_zip,
				'CUST_PROPINSI'   => $receiver_region,
				'CUST_KECAMATAN'  => $receiver_district,
				'CUST_KELURAHAN'  => $receiver_subdistrict,
				'ORIGINCODE'	  => $origin,
				'SERVICE'		  => $service,
				'WEIGHT'		  => $weight,
				'QTY'			  => $qty,
				'DESKRIPSI'		  => $goodsdesc,
				'NILAIPAKET'	  => $goodsvalue,
				'ASURANSI'		  => $insurance,
				'CATATAN'		  => $note,
				'IS_COD'		  => $codflag,
				'NILAICOD'		  => $codamount,
				'ONGKOS_KIRIM'	  => $shipping_price
			]);

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $data = self::get_valid_body_object( $get_response ) ) :

						if( isset( $data->detail ) ) {

							return $data->detail;

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
	public function get_tracking( string $tracking_number ) {

		try {

			self::$endpoint = 'http://apiv2.jne.co.id:10101/tracing/api/list/v1/cnote/'.$tracking_number;
			self::$method 	= 'POST';

			$get_response = self::do_request();

			if ( ! is_wp_error( $get_response ) ) :

				if ( self::verify_response_code( $get_response ) ) :

					if( $data = self::get_valid_body_object( $get_response ) ) :
						
						if( isset( $data ) ) {

							return $data;

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