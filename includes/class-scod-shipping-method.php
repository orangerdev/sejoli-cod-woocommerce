<?php
namespace SCOD_Shipping;

use \WeDevs\ORM\Eloquent\Facades\DB;
use SCOD_Shipping\Model\State as State;
use SCOD_Shipping\Model\City as City;
use SCOD_Shipping\Model\District as District;
use SCOD_Shipping\Model\JNE\Origin as JNE_Origin;
use SCOD_Shipping\Model\JNE\Destination as JNE_Destination;
use SCOD_Shipping\Model\JNE\Tariff as JNE_Tariff;
use SCOD_Shipping\API\JNE as API_JNE;
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
		 * Supported features.
		 *
		 */
		public $supports = array(
			'shipping-zones',
			'instance-settings',
		);

		/**
	     * Constructor. The instance ID is passed to this.
	     *
	     * @param integer $instance_id default: 0
	     */
	    public function __construct( $instance_id = 0 ) {

	        $this->id 					= 'scod-shipping';
	        $this->instance_id 			= absint( $instance_id );
	        $this->title         		= __( 'Sejoli COD Shipping', 'scod-shipping' );
	        $this->method_title         = __( 'Sejoli COD Shipping', 'scod-shipping' );
	        $this->method_description 	= __( 'Sejoli COD for WooCommerce shipping method', 'scod-shipping' );
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

			$this->enabled		    	= $this->get_option( 'enabled' );

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

	    /**
		 * Init form fields.
		 *
		 * @since 1.0.0
		 */
		public function init_form_fields() {

			if ( 'ID' !== WC()->countries->get_base_country() ) {

				$this->instance_form_fields = array(
					'title' => array(
						'title'       => __( 'Error', 'scod-shipping' ),
						'type'        => 'title',
						'description' => __( 'This plugin only work for Store Address based in Indonesia.', 'scod-shipping' ),
					),
				);

				return;
			}

			$settings = array(
        		'enabled' => array(
        			'title' 		=> __( 'Enable/Disable', 'scod-shipping' ),
        			'type' 			=> 'checkbox',
        			'description' 	=> __( 'This controls whether to activate/disable this shipping method.', 'scod-shipping' ),
        			'label' 		=> __( 'Enable this shipping method', 'scod-shipping' ),
        			'default' 		=> 'yes',
        		),
        		'api_key' => array(
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
        			'title' 		=> __( 'Berat Barang Minimal (kg)', 'scod-shipping' ),
        			'type' 			=> 'text',
        			'description' 	=> __( 'Harus diisi untuk menampilkan pilihan pengiriman ketika checkout.', 'scod-shipping' ),
        			'default' 		=> '',
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

			$option_default = array( '' => __( 'Pilih Origin' ) );
			$option_cities = JNE_Origin::pluck( 'name', 'id' )->toArray();
			return array_merge( $option_default, $option_cities );
		}

		/**
		 * Get origin object
		 *
		 * @since 	1.0.0
		 *
		 * @return 	(Object|false) returns an object on true, or false if fail
		 */
		private function get_origin_info() {

			$origin_option = $this->get_option( 'shipping_origin' );

			if( ! $origin_option ) {
				return false;
			}

			$origin = JNE_Origin::find( $origin_option );

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
		private function get_destination_info( array $destination ) {

			if( $destination['country'] !== 'ID' ) {
				return false;
			}

			$location_data = array(
				'state'			=> NULL,
				'city'			=> NULL,
				'district'		=> NULL
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

			$get_dest = DB::table( (new JNE_Destination)->getTableName() );

			if( empty( $location_data['city'] ) ) {
				$get_dest = $get_dest->whereNull( 'city_id' );
			} else {
				$get_dest = $get_dest->where( 'city_id', $location_data['city']->ID );
			}

			if( empty( $location_data['district'] ) ) {
				$get_dest = $get_dest->whereNull( 'district_id' );
			} else {
				$get_dest = $get_dest->where( 'district_id', $location_data['district']->ID );
			}

			if( $destination = $get_dest->first() ) {
				return $destination;
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
		private function get_tariff_info( $origin, $destination ) {

			$get_tariff = JNE_Tariff::where( 'jne_origin_id', $origin->ID )
							->where( 'jne_destination_id', $destination->ID )
							->first();

			if( ! $get_tariff ) {

	        	$req_tariff_data = API_JNE::set_params()->get_tariff( $origin->code, $destination->code );

	        	if( is_wp_error( $req_tariff_data ) ) {
	        		return false;
	        	}

	        	$get_tariff 					= new JNE_Tariff();
	        	$get_tariff->jne_origin_id 		= $origin->ID;
	        	$get_tariff->jne_destination_id = $destination->ID;
	        	$get_tariff->tariff_data 		= $req_tariff_data;

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
			$cart_weight = WC()->cart->get_cart_contents_weight();
			$wc_weight_unit = get_option( 'woocommerce_weight_unit' );

   			if( $wc_weight_unit != $scod_weight_unit && $cart_weight > 0 ) {
   				$cart_weight = wc_get_weight( $cart_weight, $scod_weight_unit, $wc_weight_unit );
   			}

       		if( $cart_weight == 0 ) {
       			$cart_weight = $this->get_option( 'base_weight' );
       		}

       		if( is_numeric( $cart_weight ) ) {
       			return ceil( $cart_weight );
       		}

			// error_log( __METHOD__ . ' cart_weight '. var_dump( $cart_weight ) );
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
	    	// error_log( __METHOD__ . ' package '. print_r( $package, true ) );

			$origin = $this->get_origin_info();
			error_log( __METHOD__ . ' origin '. print_r( $origin, true ) );
	        if( ! $origin ) {
	        	return false;
	        }

	        $destination = $this->get_destination_info( $package['destination'] );
			error_log( __METHOD__ . ' destination '. print_r( $destination, true ) );
	        if( ! $destination ) {
	        	return false;
	        }

	        $tariff = $this->get_tariff_info( $origin, $destination );
			error_log( __METHOD__ . ' tariff '. print_r( $tariff, true ) );
	        if( ! $tariff ) {
	        	return false;
	        }

	        if( is_array( $tariff->tariff_data ) && count( $tariff->tariff_data ) > 0 ) {

	       		$cart_weight = $this->get_cart_weight();

	       		if( ! $cart_weight ) {
	       			return false;
	       		}

	       		foreach ( $tariff->tariff_data as $rate ) {

					if( \in_array( $rate->service_code, JNE_Tariff::get_available_services() ) ) {

				        $this->add_rate( array(
							'id'    => $this->id . $this->instance_id . $rate->service_code,
							'label' => $tariff->getLabel( $rate ),
							'cost' 	=> $rate->price * $cart_weight
						));
					}
	        	}
	       	}

	    }

	}

}
