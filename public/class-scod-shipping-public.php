<?php
namespace SCOD_Shipping;

use SCOD_Shipping\Model\State as State;
use SCOD_Shipping\Model\City as City;
use SCOD_Shipping\Model\District as District;
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

		$params = wp_parse_args($_POST, array(
            'state_id'  => NULL,
            'nonce'     => NULL
        ));

        $data = [];

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

		$params = wp_parse_args($_POST, array(
            'city_id'  => NULL,
            'nonce'     => NULL
        ));

        $data = [];

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

}
