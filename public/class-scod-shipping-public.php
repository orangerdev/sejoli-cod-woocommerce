<?php
namespace SCOD_Shipping;

use SCOD_Shipping\Model\State as State;
use SCOD_Shipping\Model\City as City;
use SCOD_Shipping\Model\District as District;
use SCOD_Shipping\API\SCOD as API_SCOD;
use SCOD_Shipping\Shipping_Method;
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    SCOD_Shipping
 * @subpackage SCOD_Shipping/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    SCOD_Shipping
 * @subpackage SCOD_Shipping/public
 * @author     Sejoli Team <orangerdigiart@gmail.com>
 */
class Front {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/scod-shipping-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/scod-shipping-public.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'scods_object', array(
        	'ajax_url'	=> admin_url('admin-ajax.php'),
        	'checkout'	=> array(
	        	'target_country' => 'ID',
	            'billing_country' => WC()->customer->get_billing_country(),
	            'shipping_country' => WC()->customer->get_shipping_country(),
        	),
        	'locations' => array(
        		'city'	=> array(
        			'action' => 'scods-get-city-by-state',
					'nonce' => wp_create_nonce('scods-get-city-by-state'),
        		),
        		'district'	=> array(
        			'action' => 'scods-get-district-by-city',
					'nonce' => wp_create_nonce('scods-get-district-by-city'),
        		)
			)
        ));
	}

	/**
	 * Make state field dropdown in checkout fields for Indonesia country code ID.
	 *
	 * @since    1.0.0
	 */
	public function checkout_state_dropdown( $states ) {
		$states['ID'] = State::pluck( 'name', 'id' )->toArray();
		return $states;
	}

	/**
	 * Custom checkout fields
	 *
	 * @since    1.0.0
	 */
	public function scod_checkout_fields( $fields ) {
		// error_log( __METHOD__ . ' fields '. print_r( $fields, true ) );
		$shipping_country = WC()->customer->get_shipping_country();

		if( $shipping_country != 'ID' ) {
			return $fields;
		}

		// Custom fields
	    $fields['billing']['billing_city2'] = $fields['shipping']['shipping_city2'] = array(
	        'label'		  	=> $fields['billing']['billing_city']['label'],
		    'type'         	=> 'select',
	        'required'     	=> true,
	        'options'      	=> array( '' => '' ),
	        'autocomplete' 	=> 'address-level2',
			'placeholder'	=> __( 'Select an option...', 'scod-shipping' ),
	        'class'		   	=> array( 'address-field', 'form-row-wide', 'hidden' ),
	    );

	    $fields['billing']['billing_address_2'] = $fields['shipping']['shipping_address_2'] = array(
	        'label'     	=> __( 'District', 'scod-shipping' ),
		    'type'			=> 'select',
		    'required'  	=> false,
	    	'options'		=> array( '' => '' ),
			'placeholder'	=> __( 'Select an option...', 'scod-shipping' ),
    		'class'     	=> array( 'form-row-wide', 'address-field', 'hidden' ),
	    );

	    // Sort fields
	    $fields['billing']['billing_first_name']['priority'] 	= 1;
	    $fields['billing']['billing_last_name']['priority'] 	= 2;
	    $fields['billing']['billing_company']['priority'] 		= 3;
	    $fields['billing']['billing_country']['priority'] 		= 4;
	    $fields['billing']['billing_state']['priority'] 		= 5;
	    $fields['billing']['billing_city']['priority'] 			= 6;
	    $fields['billing']['billing_city2']['priority'] 		= 6;
	    $fields['billing']['billing_address_2']['priority'] 	= 7;
	    $fields['billing']['billing_address_1']['priority'] 	= 8;
	    $fields['billing']['billing_postcode']['priority'] 		= 9;
	    $fields['billing']['billing_email']['priority'] 		= 10;
	    $fields['billing']['billing_phone']['priority'] 		= 11;

	    $fields['shipping']['shipping_first_name']['priority'] 	= 1;
	    $fields['shipping']['shipping_last_name']['priority'] 	= 2;
	    $fields['shipping']['shipping_company']['priority'] 	= 3;
	    $fields['shipping']['shipping_country']['priority'] 	= 4;
	    $fields['shipping']['shipping_state']['priority'] 		= 5;
	    $fields['shipping']['shipping_city']['priority'] 		= 6;
	    $fields['shipping']['shipping_city2']['priority'] 		= 6;
	    $fields['shipping']['shipping_address_2']['priority'] 	= 7;
	    $fields['shipping']['shipping_address_1']['priority'] 	= 8;
	    $fields['shipping']['shipping_postcode']['priority'] 	= 9;

	    return $fields;
	}

	/**
	 * Override checkout fields locale
	 *
	 * @since    1.0.0
	 */
	public function override_locale_fields( $fields ) {
		$shipping_country = WC()->customer->get_shipping_country();

		if( $shipping_country != 'ID' ) {
			return $fields;
		}

		$fields['state']['priority'] 		= 5;
	    $fields['city']['priority'] 		= 6;
	    $fields['address_2']['priority'] 	= 7; //custom district
	    $fields['address_1']['priority'] 	= 8;
	    $fields['postcode']['priority'] 	= 9;

	    return $fields;
	}

	/**
	 * Get cities data by state for dropdown options
	 * Hook via wp_ajax_scods-get-city-by-state
	 * Hook via wp_ajax_nopriv_scods-get-city-by-state
	 *
	 * @since    1.0.0
	 */
	public function get_city_by_state() {
		$data = [];

		$params = wp_parse_args($_POST, array(
            'state_id'  => NULL,
            'nonce'     => NULL
        ));

        if( wp_verify_nonce( $params[ 'nonce' ], 'scods-get-city-by-state' ) ) :

        	if( ! empty( $params['state_id'] ) ) :
        		$state = State::find( $params['state_id'] );

	            if ( $state && count( $state->cities ) > 0 ) :
	        		$data = $state->cities()->pluck( 'name', 'ID' )->toArray();
	            endif;

        	endif;

        endif;

        echo wp_send_json( $data );
	}

	/**
	 * Get cities data by state for dropdown options
	 * Hook via wp_ajax_scods-get-district-by-city
	 * Hook via wp_ajax_nopriv_scods-get-district-by-city
	 *
	 * @since    1.0.0
	 */
	public function get_district_by_city() {
		$data = [];

		$params = wp_parse_args($_POST, array(
            'city_id'  => NULL,
            'nonce'     => NULL
        ));

        if( wp_verify_nonce( $params[ 'nonce' ], 'scods-get-district-by-city' ) ) :

        	if( ! empty( $params['city_id'] ) ) :
        		$city = City::find( $params['city_id'] );

	            if ( $city && count( $city->districts ) > 0 ) :
	        		$data = $city->districts()->pluck( 'name', 'ID' )->toArray();
	            endif;

        	endif;

        endif;

        echo wp_send_json( $data );
	}

	/**
	 * WooCommerce checkout disable COD payment if JNE YES shipping is selected.
	 *
	 * @since    1.0.0
	 */
	public function scods_checkout_available_payments( $available_gateways ) {

		if( isset( WC()->session ) ):

			$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

			if( ! empty( $chosen_shipping_methods ) ) :
				if( $chosen_shipping_methods[0] == 'scod-shipping_jne_yes19' ) :

					if( isset( $available_gateways['cod'] ) ) :
						unset( $available_gateways['cod'] );
					endif;

				endif;
			endif;

			return $available_gateways;

		endif;
	}

	/**
	 * WooCommerce checkout disable JNE YES if COD payment is selected.
	 *
	 * @since    1.0.0
	 */
	public function scods_checkout_available_shippings( $rates, $package ) {

		if( isset( WC()->session ) ):

			$chosen_payment_method = WC()->session->get( 'chosen_payment_method' );

			if( $chosen_payment_method == 'cod' ) :

				if( isset( $rates['scod-shipping_jne_yes19'] ) ) :
					unset( $rates['scod-shipping_jne_yes19'] );
				endif;

			endif;

			return $rates;

		endif;
	}

	/**
	 * WooCommerce action to send newly created order data to API.
	 *
	 * @since    1.0.0
	 */
	public function send_order_data_to_api( $order_id ) {
	    if ( ! $order_id ) return;

	    // Allow code execution only once
	    if( ! get_post_meta( $order_id, '_sync_order_action_scod_done', true ) ) {

			// Get an instance of the WC_Order object
	        $order = wc_get_order( $order_id );

			// Check payment method
			if( $order->get_payment_method() != 'cod' ) {
				return;
			}

			// Get shipping method
			$shipping_methods	= $order->get_shipping_methods();
			$shipping_method_id = NULL;
			$shipping_instance_id = NULL;
			$courier_name = NULL;

			foreach ($shipping_methods as $shipping_method) {
				$shipping_name = $shipping_method['name'];
				$shipping_method_id = $shipping_method->get_method_id();
				$shipping_instance_id = $shipping_method->get_instance_id();

				if( \str_contains( strtolower( $shipping_name ), 'jne' ) ):
					$courier_name = 'jne';
				endif;
			}

			// Check selected shipping
			if( $shipping_method_id != 'scod-shipping' ) {
				return;
			}

			// Get store account data
			$store_id = NULL;
			$store_secret_key = NULL;

			if( $shipping_instance_id ) {
				$shipping_class = new Shipping_Method( $shipping_instance_id );
				$store_id = $shipping_class->get_option( 'store_id' );
				$store_secret_key = $shipping_class->get_option( 'store_secret_key' );
			}

			// Default params
			$order_params = array(
				'store_id'			=> $store_id,
				'secret_key'		=> $store_secret_key,
				'buyer_name'		=> $order->get_billing_first_name() .' '. $order->get_billing_last_name(),
				'buyer_email'		=> $order->get_billing_email(),
				'buyer_phone'		=> $order->get_billing_phone(),
				'courier_name'		=> $courier_name,
				'invoice_number'	=> $order->get_order_number(),
				'invoice_total' 	=> $order->get_total(),
				'shipping_fee'		=> $order->get_total_shipping(),
				'shipping_status'	=> 'pickup',
				'notes'				=> $order->get_customer_note(),
				'order'				=> $order
			);

			// Send data to API
			$api_scod = new API_SCOD();
			$create_order = $api_scod->post_create_order( $order_params );

			if( ! is_wp_error( $create_order ) ) {
				// Flag the action as done (to avoid repetitions on reload for example)
				$order->update_meta_data( '_sync_order_action_scod_done', true );
				$order->save();
			}
			
			error_log( 'Done processing for order ID '. $order_id );
	    }
	}

}
