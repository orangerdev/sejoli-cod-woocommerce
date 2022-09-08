<?php
namespace SCOD_Shipping;

use \WeDevs\ORM\Eloquent\Facades\DB;
use SCOD_Shipping\Model\State as State;
use SCOD_Shipping\Model\City as City;
use SCOD_Shipping\Model\District as District;
use SCOD_Shipping\Model\JNE\Tariff as JNE_Tariff;
use SCOD_Shipping\API\ARVEOLI as API_ARVEOLI;
use SCOD_Shipping\Model\SiCepat\Tariff as SICEPAT_Tariff;
use SCOD_Shipping\API\SCOD as API_SCOD;
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
function scod_shipping_init() {

	class Shipping_Method extends \WC_Shipping_Method {
		/**
		 * Woongkir_API API Class Object
		 *
		 * @since 1.0.0
		 * @var API_SCOD
		 */
		private $api;

		/**
		 * Supported features.
		 *
		 */
		public $supports = array(
			'shipping-zones',
			'instance-settings',
		);

		/**
		 * Array of supported country code.
		 *
		 */
		public $available_countries = array( 'ID' );

		/**
	     * Constructor. The instance ID is passed to this.
	     *
	     * @param integer $instance_id default: 0
	     */
	    public function __construct( $instance_id = 0 ) {

			$this->api                = new API_SCOD();
	        $this->id 				  = 'scod-shipping';
	        $this->instance_id 		  = absint( $instance_id );
	        $this->title         	  = __( 'Sejoli Shipping', 'scod-shipping' );
	        $this->method_title       = __( 'Sejoli Shipping', 'scod-shipping' );
	        $this->method_description = __( 'Sejoli Shipping for WooCommerce shipping method', 'scod-shipping' );

			$this->init();
			
	    }

	    /**
		 * Initialize user set variables.
		 *
		 * @since 1.0.0
		 */
		public function init() {

			$this->init_form_fields();
			$this->init_settings();

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

		}

	    /**
		 * Init form fields.
		 *
		 * @since 1.0.0
		 */
		public function init_form_fields() {

			if ( ! $this->validate_supported_country( WC()->countries->get_base_country() ) ) {
				$this->instance_form_fields = array(
					'title' => array(
						'title'       => __( 'Plugin Unavailable', 'scod-shipping' ),
						'type'        => 'title',
						'description' => __( 'This plugin only work for Store Address based in Indonesia.', 'scod-shipping' ),
					),
				);

				return;
			}

			$settings = array(
        		'scod_username' => array(
        			'title' 		=> __( 'Username', 'scod-shipping' ),
        			'type' 			=> 'text',
        			'description' 	=> __( 'Please enter your account username.', 'scod-shipping' ),
        		),
        		'scod_password' => array(
        			'title' 		=> __( 'Password', 'scod-shipping' ),
        			'type' 			=> 'password',
        			'description' 	=> __( 'Please enter your account password.', 'scod-shipping' ),
        		),
        		'store_id' => array(
        			'title' 		=> __( 'Store ID', 'scod-shipping' ),
        			'type' 			=> 'text',
        			'description' 	=> __( 'Please enter your store ID.', 'scod-shipping' ),
        			'default' 		=> '',
        		),
        		'store_secret_key' => array(
        			'title' 		=> __( 'Store Secret Key', 'scod-shipping' ),
        			'type' 			=> 'text',
        			'description' 	=> __( 'Please enter your store secret key.', 'scod-shipping' ),
        			'default' 		=> '',
        		),
        		'shipping_origin'  	=> array(
					'title'   		=> __( 'Shipping Origin', 'scod-shipping' ),
        			'description' 	=> __( 'Please select your shipping origin location.', 'scod-shipping' ),
					'type'    		=> 'select',
					'default' 		=> '',
					'options' 		=> $this->generate_origin_dropdown(),
				),
        		'base_weight' => array(
        			'title' 		=> __( 'Default Item Weight (Kg)', 'scod-shipping' ),
        			'type' 			=> 'text',
        			'description' 	=> __( 'Berat default yang digunakan ketika berat per barang tidak ada.', 'scod-shipping' ),
        			'default' 		=> '',
        		),
        		'arveoli_jne_settings' => array(
					'title' 		=> __( 'PENGATURAN SHIPPING JNE', 'scod-shipping' ),
        			'label'			=> __( 'YES', 'scod-shipping' ),
        			'type' 			=> 'title',
        		),
        		'arveoli_jne_service_yes' => array(
					'title' 		=> __( 'JNE Services', 'scod-shipping' ),
        			'label'			=> __( 'YES', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'arveoli_jne_service_reg' => array(
        			'label'			=> __( 'Regular (COD Available)', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'arveoli_jne_service_oke' => array(
        			'label'			=> __( 'OKE', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'arveoli_jne_service_jtr' => array(
        			'label'			=> __( 'JNE Trucking', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'arveoli_jne_label_markup_cod' => array(
        			'title' 		=> __( 'Label Biaya Markup COD JNE', 'scod-shipping' ),
        			'type' 			=> 'text',
        			'description' 	=> '',
        			'default' 		=> __( 'Biaya COD', 'scod-shipping' ),
        		),
        		'arveoli_jne_biaya_markup' => array(
        			'title' 		=> __( 'Biaya COD JNE Termasuk ke Ongkir?', 'scod-shipping' ),
        			'label'			=> __( 'Aktifkan', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'no',
        		),
        		'sicepat_settings' => array(
					'title' 		=> __( 'PENGATURAN SHIPPING SICEPAT', 'scod-shipping' ),
        			'label'			=> __( 'YES', 'scod-shipping' ),
        			'type' 			=> 'title',
        		),
        		'sicepat_service_cargo' => array(
					'title' 		=> __( 'SiCepat Services', 'scod-shipping' ),
        			'label'			=> __( 'Cargo', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'sicepat_service_best' => array(
        			'label'			=> __( 'BEST', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'sicepat_service_gokil' => array(
        			'label'			=> __( 'GOKIL', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'sicepat_service_kepo' => array(
        			'label'			=> __( 'KEPO', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'sicepat_service_halu' => array(
        			'label'			=> __( 'Halu', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'sicepat_service_reg' => array(
        			'label'			=> __( 'Regular', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'sicepat_service_sds' => array(
        			'label'			=> __( 'SDS', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'sicepat_service_siunt' => array(
        			'label'			=> __( 'SI UNTUNG (COD Available)', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'yes',
        		),
        		'sicepat_label_markup_cod' => array(
        			'title' 		=> __( 'Label Biaya Markup COD SiCepat', 'scod-shipping' ),
        			'type' 			=> 'text',
        			'description' 	=> '',
        			'default' 		=> __( 'Biaya COD', 'scod-shipping' ),
        		),
        		'sicepat_biaya_markup' => array(
        			'title' 		=> __( 'Biaya COD SiCepat Termasuk ke Ongkir?', 'scod-shipping' ),
        			'label'			=> __( 'Aktifkan', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
					'default'		=> 'no',
        		),
			);

			$this->instance_form_fields = $settings;

		}

		/**
		 * Generate options for origin dropdown
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		private function generate_origin_dropdown() {

			$option_default = array( '' => __( '--- Pilih Origin ---' ) );
			// $option_cities  = JNE_Origin::pluck( 'name', 'id' )->toArray();
			$option_cities  = City::pluck( 'name', 'id' )->toArray();

			return $option_default + $option_cities;

		}

		/**
		 * Validate if current value of country code is supported.
		 *
		 * @param $country_code (string) country code to check.
		 *
		 * @since 1.0.0
		 */
		public function validate_supported_country( string $country_code ) {

			$supported_countries = $this->available_countries;

			return \in_array( $country_code, $supported_countries );

		}

		/**
		 * Validate username & password settings field.
		 *
		 * @since 1.0.0
		 * @param string $key Input field key.
		 * @param string $value Input field current value.
		 * @throws Exception Error message.
		 */
		public function validate_scod_username_field( $key, $value ) {

			error_log( 'Validating scod account ..' );
			$error_msg 		  = wp_sprintf( __( '%s is not valid. Please use a valid account.', 'scod-shipping' ), 'Username or password' );
			$posted 		  = $this->get_post_data();
			$current_username = $this->get_option( 'scod_username' );
			$current_password = $this->get_option( 'scod_password' );
			$username 		  = $posted[ $this->get_field_key( 'scod_username' ) ];
			$password 		  = $posted[ $this->get_field_key( 'scod_password' ) ];

			if( $current_password != $password || $current_username != $username ) {

				if ( ! $username || ! $password ) {
					throw new \Exception( $error_msg );
				}

				$get_token = $this->api->get_new_token( $username, $password );

				if( is_wp_error( $get_token ) ) {
					throw new \Exception( $error_msg );
				}
			}

			return $value;

		}

		/**
		 * Validate store account fields.
		 *
		 * @since 1.0.0
		 * @param string $key Input field key.
		 * @param string $value Input field current value.
		 * @throws Exception Error message.
		 */
		public function validate_store_secret_key_field( $key, $value ) {

			error_log( 'Validating scod store account ..' );
			$error_msg 				  = wp_sprintf( __( '%s is not valid. Please use a valid account.', 'scod-shipping' ), 'Store ID or Store secret key' );
			$posted    				  = $this->get_post_data();
			$current_store_id 		  = $this->get_option( 'store_id' );
			$current_store_secret_key = $this->get_option( 'store_secret_key' );
			$store_id 				  = $posted[ $this->get_field_key( 'store_id' ) ];
			$store_secret_key 		  = $posted[ $this->get_field_key( 'store_secret_key' ) ];

			if( $current_store_id != $store_id || $current_store_secret_key != $store_secret_key ) {

				if ( ! $store_id || ! $store_secret_key ) {
					throw new \Exception( $error_msg );
				}

				$validate_store = $this->api->get_store_detail( $store_id, $store_secret_key );

				if( is_wp_error( $validate_store ) ) {
					throw new \Exception( $error_msg );
				}
			}

			return $value;

		}

		/**
		 * Generate options for origin dropdown
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		private function get_arveoli_jne_services() {

			$services = array();

			if( $this->get_option('arveoli_jne_service_yes') === 'yes' ) {
				$services[] = 'YES19';
			}

			if( $this->get_option('arveoli_jne_service_oke') === 'yes' ) {
				$services[] = 'OKE19';
			}

			if( $this->get_option('arveoli_jne_service_reg') === 'yes' ) {
				$services[] = 'REG19';
			}

			if( $this->get_option('arveoli_jne_service_jtr') === 'yes' ) {
				$codes = array( 'JTR18', 'JTR250', 'JTR<150', 'JTR>250' );
				$services = array_merge( $services, $codes );
			}

			return $services;
			
		}

		/**
		 * Generate options for origin dropdown
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		private function get_sicepat_services() {

			$services = array();

			if( $this->get_option('sicepat_service_cargo') === 'yes' ) {
				$services[] = 'CARGO';
			}

			if( $this->get_option('sicepat_service_best') === 'yes' ) {
				$services[] = 'BEST';
			}

			if( $this->get_option('sicepat_service_gokil') === 'yes' ) {
				$services[] = 'GOKIL';
			}

			if( $this->get_option('sicepat_service_kepo') === 'yes' ) {
				$services[] = 'KEPO';
			}

			if( $this->get_option('sicepat_service_halu') === 'yes' ) {
				$services[] = 'HALU';
			}

			if( $this->get_option('sicepat_service_reg') === 'yes' ) {
				$services[] = 'REG';
			}

			if( $this->get_option('sicepat_service_sds') === 'yes' ) {
				$services[] = 'SDS';
			}

			if( $this->get_option('sicepat_service_siunt') === 'yes' ) {
				$services[] = 'SIUNT';
			}

			return $services;

		}

		/**
	     * Get origin detail
	     * @since   1.0.0
	     * @param   integer     $subdistrict_id     District ID
	     * @return  array|null  District detail
	     */
	    public function get_origin( $expedition, $city ) {

	        if( $city && $expedition === 'jne' ) :

	            ob_start();

	            require SCOD_SHIPPING_DIR . 'json/json_origin_JNE.json';
	            $json_data = ob_get_contents();
	            
	            ob_end_clean();

	            $origins = json_decode( $json_data, true );
	            $current_origin = array();

	            foreach( $origins as $data ):
	                if( \str_contains( strtolower( $city ), strtolower( $data['originname'] ) ) !== false ) {
	                    $current_origin = $data;

	                    break;
	                } else {
	                    $current_origin = null;
	                }
	            endforeach;

	            return $current_origin;

	        endif;

	        if( $city && $expedition === 'sicepat' ) :

	            ob_start();

	            require SCOD_SHIPPING_DIR . 'json/json_origin_sicepat.json';
	            $json_data = ob_get_contents();
	            
	            ob_end_clean();

	            $origins = json_decode( $json_data, true );
	            $current_origin = array();

	            foreach( $origins as $data ):
	                if( \str_contains( strtolower( $city ), strtolower( $data['origin_name'] ) ) !== false ) {
	                    $current_origin = $data;

	                    break;
	                } else {
	                    $current_origin = null;
	                }
	            endforeach;

	            return $current_origin;

	        endif;

	        return NULL;

	    }

	    /**
	     * Get destination detail
	     * @since   1.0.0
	     * @param   integer     $subdistrict_id     District ID
	     * @return  array|null  District detail
	     */
	    public function get_destination( $expedition, $district ) {

	        if( $district && $expedition === 'jne' ) :

	            ob_start();

	            require SCOD_SHIPPING_DIR . 'json/json_dest_jne.json';
	            $json_data = ob_get_contents();
	            
	            ob_end_clean();

	            $destinations   = json_decode( $json_data, true );

	            foreach( $destinations as $data ):
	                if( \str_contains( strtolower( $district ), strtolower( $data['district_name'] ) ) !== false ) {
	                    $current_destination = $data;

	                    break;
	                } else {
	                    $current_destination = null;
	                }
	            endforeach;

	            return $current_destination;

	        endif;

	        if( $district && $expedition === 'sicepat' ) :

	            ob_start();

	            require SCOD_SHIPPING_DIR . 'json/json_dest_sicepat.json';
	            $json_data = ob_get_contents();
	            
	            ob_end_clean();

	            $destinations   = json_decode( $json_data, true );

	            foreach( $destinations as $data ):
	                if( \str_contains( strtolower( $district ), strtolower( $data['subdistrict'] ) ) !== false ) {
	                    $current_destination = $data;

	                    break;
	                } else {
	                    $current_destination = null;
	                }
	            endforeach;

	            return $current_destination;

	        endif;

	        return NULL;

	    }

		/**
		 * Get origin object
		 *
		 * @since 	1.0.0
		 *
		 * @return 	(Object|false) returns an object on true, or false if fail
		 */
		public function get_origin_info() {

			$origin_option = $this->get_option( 'shipping_origin' );

	        $cod_origin_city = DB::table( 'scod_shipping_city' )
	                ->where( 'ID', $origin_option )
	                ->get();      

	        $getOriginCode = $this->get_origin( $expedition = 'jne', $cod_origin_city[0]->name );  

			if( ! $getOriginCode ) {
				return false;
			}

			$origin = $getOriginCode['origincode'];

			if( ! $origin ) {
				return false;
			}

			return $origin;

		}

		/**
		 * Get origin object
		 *
		 * @since 	1.0.0
		 *
		 * @return 	(Object|false) returns an object on true, or false if fail
		 */
		public function get_branch_info() {

			$branch_option = $this->get_option( 'shipping_origin' );

	        $cod_branch_city = DB::table( 'scod_shipping_city' )
	                ->where( 'ID', $branch_option )
	                ->get();      

	        $getBranchCode = $this->get_origin( $expedition = 'jne', $cod_branch_city[0]->name );  

			if( ! $getBranchCode ) {
				return false;
			}

			$branch = $getBranchCode['branchcode'];

			if( ! $branch ) {
				return false;
			}

			return $branch;

		}

		/**
		 * Get origin object
		 *
		 * @since 	1.0.0
		 *
		 * @return 	(Object|false) returns an object on true, or false if fail
		 */
		public function get_sicepat_origin_info() {

			$origin_option = $this->get_option( 'shipping_origin' );

	        $cod_origin_city = DB::table( 'scod_shipping_city' )
	                ->where( 'ID', $origin_option )
	                ->get();      

	        $getOriginCode = $this->get_origin( $expedition = 'sicepat', $cod_origin_city[0]->name );  

			if( ! $getOriginCode ) {
				return false;
			}

			$origin = $getOriginCode['origin_code'];

			if( ! $origin ) {
				return false;
			}

			return $origin;

		}

		/**
		 * Get destination object
		 *
		 * @since 	1.0.0
		 *
		 * @param array $destination destination array with country, state, postcode, city, address, address_1, address_2
		 *
		 * @return 	(Object|false) returns an object on true, or false if fail
		 */
		public function get_destination_info( array $destination ) {

			if( ! $this->validate_supported_country( $destination['country'] ) ) {
				return false;
			}

			$location_data = array(
				'state'	   => NULL,
				'city'	   => NULL,
				'district' => NULL
			);

			if( $destination['state'] ) {
				$state = State::find( $destination['state'] );

				if( $state ) {
					$location_data[ 'state' ] = $state;
				}
			}

			if( $destination['city'] ) {
				$city = City::find( $destination['city'] );

				if( $city ) {
					$location_data[ 'city' ] = $city;
				}
			}

			if( $destination['address_2'] ) {
				$district = District::find( $destination['address_2'] );

				if( $district ) {
					$location_data[ 'district' ] = $district;
				}
			}

			$getDestCode  = $this->get_destination( $expedition = 'jne', $location_data['district']->name );
			
			if( $destination = $getDestCode ) {
				return $destination['tariff_code'];
			}
			
			return false;

		}

		/**
		 * Get destination object
		 *
		 * @since 	1.0.0
		 *
		 * @param array $destination destination array with country, state, postcode, city, address, address_1, address_2
		 *
		 * @return 	(Object|false) returns an object on true, or false if fail
		 */
		public function get_sicepat_destination_info( array $destination ) {

			if( ! $this->validate_supported_country( $destination['country'] ) ) {
				return false;
			}

			$location_data = array(
				'state'	   => NULL,
				'city'	   => NULL,
				'district' => NULL
			);

			if( $destination['state'] ) {
				$state = State::find( $destination['state'] );

				if( $state ) {
					$location_data[ 'state' ] = $state;
				}
			}

			if( $destination['city'] ) {
				$city = City::find( $destination['city'] );

				if( $city ) {
					$location_data[ 'city' ] = $city;
				}
			}

			if( $destination['address_2'] ) {
				$district = District::find( $destination['address_2'] );

				if( $district ) {
					$location_data[ 'district' ] = $district;
				}
			}

			$getDestCode  = $this->get_destination( $expedition = 'sicepat', $location_data['district']->name );
			
			if( $destination = $getDestCode ) {
				return $destination['destination_code'];
			}
			
			return false;

		}

		/**
		 * Get tariff object
		 *
		 * @since 	1.0.0
		 *
	     * @param 	$origin 		origin object to find
	     * @param 	$destination 	destination object to find
	     *
		 * @return 	(Object|false) 	returns an object on true, or false if fail
		 */
		private function get_arveoli_tariff_info( $expedition, $origin, $destination, $weight ) {

			$get_tariff = JNE_Tariff::where( 'jne_origin_id', $origin )
							->where( 'jne_destination_id', $destination )
							->first();

			if( ! $get_tariff ) {

	        	$req_tariff_data = API_ARVEOLI::set_params()->get_tariff( $expedition, $origin, $destination, $weight );

				if( is_wp_error( $req_tariff_data ) ) {

	        		return false;

	        	}

	        	$get_tariff 					= new JNE_Tariff();
	        	$get_tariff->jne_origin_id 		= $origin;
	        	$get_tariff->jne_destination_id = $destination;
	        	$get_tariff->tariff_data 		= $req_tariff_data;

	        	if( ! $get_tariff->save() ) {

	        		return false;

	        	}

	        }

			return $get_tariff;

		}

		/**
		 * Get tariff object
		 *
		 * @since 	1.0.0
		 *
	     * @param 	$origin 		origin object to find
	     * @param 	$destination 	destination object to find
	     *
		 * @return 	(Object|false) 	returns an object on true, or false if fail
		 */
		private function get_sicepat_tariff_info( $expedition, $origin, $destination, $weight ) {
			
			$get_tariff = SICEPAT_Tariff::where( 'sicepat_origin_id', $origin )
							->where( 'sicepat_destination_id', $destination )
							->first();

			if( ! $get_tariff ) {
	        	$req_tariff_data = API_ARVEOLI::set_params()->get_tariff( $expedition, $origin, $destination, $weight );
	        	
				if( is_wp_error( $req_tariff_data ) ) {
	        		return false;
	        	}

	        	$get_tariff 					    = new SICEPAT_Tariff();
	        	$get_tariff->sicepat_origin_id 		= $origin;
	        	$get_tariff->sicepat_destination_id = $destination;
	        	$get_tariff->tariff_data 		    = $req_tariff_data;

	        	if( ! $get_tariff->save() ) {
	        		return false;
	        	}
	        }

			return $get_tariff;

		}

		/**
		 * Get cart package total weight
		 *
		 * @since 	1.0.0
	     *
		 * @return 	(Double|false) 	returns double type number, or false if fail
		 */
		private function get_cart_weight() {

			$scod_weight_unit = 'kg';
			$cart_weight 	  = WC()->cart->get_cart_contents_weight();
			$wc_weight_unit   = get_option( 'woocommerce_weight_unit' );

   			if( $wc_weight_unit != $scod_weight_unit && $cart_weight > 0 ) {
   				$cart_weight = wc_get_weight( $cart_weight, $scod_weight_unit, $wc_weight_unit );
   			}

       		if( $cart_weight == 0 ) {
       			$cart_weight = $this->get_option( 'base_weight' );
       		}

       		if( is_numeric( $cart_weight ) ) {
       			return ceil( $cart_weight );
       		}

			return false;

		}

	    /**
	     * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
	     *
		 * @since 	1.0.0
		 *
	     * @param 	array $package default: array()
	     *
	     * @return 	boolean|rate returns false if fail, add rate to wc if available
	     */
	    public function calculate_shipping( $package = array() ) {

	    	if (is_admin() && !defined('DOING_AJAX')) {
				return;
			}

	       	$arveoli_jne_origin 	 = $this->get_origin_info();
			$arveoli_jne_destination = $this->get_destination_info( $package['destination'] );
			$cart_weight 		     = $this->get_cart_weight();

			if( ! $arveoli_jne_origin ) {
	        	return false;
	        }

			if( ! $arveoli_jne_destination ) {
	        	return false;
	        }

			$arveoli_jne_tariff = $this->get_arveoli_tariff_info( $expedition = 'jne', $arveoli_jne_origin, $arveoli_jne_destination, $cart_weight );

			if( ! $arveoli_jne_tariff ) {
	        	return false;
	        }

	        if( $arveoli_jne_tariff ) {

	       		if( ! $cart_weight ) {
	       			return false;
	       		}

	       		foreach ( $arveoli_jne_tariff->tariff_data->data as $key => $rate ) {

					if( \in_array( $rate->service_name, $this->get_arveoli_jne_services() ) ) {

						$chosen_shipping_method = WC()->session->get('chosen_shipping_methods');
						$chosen_payment_method  = WC()->session->get( 'chosen_payment_method' );
						$option_biaya_markup    = $this->get_option( 'arveoli_jne_biaya_markup' );

						$shipping_methods = WC()->shipping->get_shipping_methods();
						$rate_table[] = '';

						foreach($shipping_methods as $shipping_method){
						    $shipping_method->init();

						    foreach($shipping_method->rates as $key=>$val)
						        $rate_table[$key] = $val->id;
						}

						$percentage     = 0.04;
						$percentage_fee = WC()->cart->get_cart_contents_total() * $percentage;
					 	
						if($option_biaya_markup === 'yes') {

							if($chosen_payment_method === 'cod') {

						        if (strpos( $chosen_shipping_method[0], 'scod-shipping_jne_reg19' ) !== false) {
								       
								        $this->add_rate( array(
											'id'    => $arveoli_jne_tariff->getRateID( $this->id, $rate ),
											'label' => $arveoli_jne_tariff->getLabel( $rate ),
											'cost' 	=> ($rate->price + $percentage_fee) * $cart_weight
										));

								}

							} else {

								$this->add_rate( array(
									'id'    => $arveoli_jne_tariff->getRateID( $this->id, $rate ),
									'label' => $arveoli_jne_tariff->getLabel( $rate ),
									'cost' 	=> $rate->price * $cart_weight
								));

							}

					 	} else {

					 		$this->add_rate( array(
								'id'    => $arveoli_jne_tariff->getRateID( $this->id, $rate ),
								'label' => $arveoli_jne_tariff->getLabel( $rate ),
								'cost' 	=> $rate->price * $cart_weight
							));

					 	}

					}
	        	}
	       	}
	    	
	       	$arveoli_sicepat_origin 	 = $this->get_sicepat_origin_info();
			$arveoli_sicepat_destination = $this->get_sicepat_destination_info( $package['destination'] );

			if( ! $arveoli_sicepat_origin ) {
	        	return false;
	        }

			if( ! $arveoli_sicepat_destination ) {
	        	return false;
	        }

			$arveoli_sicepat_tariff = $this->get_sicepat_tariff_info( $expedition = 'sicepat', $arveoli_sicepat_origin, $arveoli_sicepat_destination, $cart_weight );

			if( ! $arveoli_sicepat_tariff ) {
	        	return false;
	        }

	        if( $arveoli_sicepat_tariff ) {

	       		if( ! $cart_weight ) {
	       			return false;
	       		}

	       		foreach ( $arveoli_sicepat_tariff->tariff_data->data as $key => $rate ) {

					if( \in_array( $rate->service_code, $this->get_sicepat_services() ) ) {

						$chosen_shipping_method = WC()->session->get('chosen_shipping_methods');
						$chosen_payment_method  = WC()->session->get( 'chosen_payment_method' );
						$option_biaya_markup    = $this->get_option( 'sicepat_biaya_markup' );

						$percentage     = 0.04;
						$percentage_fee = WC()->cart->get_cart_contents_total() * $percentage;
						$total_order    = WC()->cart->get_cart_contents_total();
					 	
						if($option_biaya_markup === 'yes') {

							if($chosen_payment_method === 'cod') {

						        if (strpos( $chosen_shipping_method[0], 'scod-shipping_sicepat_siunt' ) !== false) {

								        $this->add_rate( array(
											'id'    => $arveoli_sicepat_tariff->getRateID( $this->id, $rate ),
											'label' => $arveoli_sicepat_tariff->getLabel( $rate ),
											'cost' 	=> ($rate->price + $percentage_fee) * $cart_weight
										));

								}

							} else {

								$this->add_rate( array(
									'id'    => $arveoli_sicepat_tariff->getRateID( $this->id, $rate ),
									'label' => $arveoli_sicepat_tariff->getLabel( $rate ),
									'cost' 	=> $rate->price * $cart_weight
								));

							}

					 	} else {

					 		$this->add_rate( array(
								'id'    => $arveoli_sicepat_tariff->getRateID( $this->id, $rate ),
								'label' => $arveoli_sicepat_tariff->getLabel( $rate ),
								'cost' 	=> $rate->price * $cart_weight
							));

					 	}

					}
	        	}
	       	}

	    }

	}
}