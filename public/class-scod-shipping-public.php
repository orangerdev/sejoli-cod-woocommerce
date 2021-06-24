<?php
namespace SCOD_Shipping;

use \WeDevs\ORM\Eloquent\Facades\DB;
use SCOD_Shipping\Model\State as State;
use SCOD_Shipping\Model\City as City;
use SCOD_Shipping\Model\District as District;
use SCOD_Shipping\API\SCOD as API_SCOD;
use SCOD_Shipping\API\JNE as API_JNE;
use SCOD_Shipping\Model\JNE\Origin as JNE_Origin;
use SCOD_Shipping\Model\JNE\Destination as JNE_Destination;
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
		$this->version 	   = $version;
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
	        	'target_country'   => 'ID',
	            'billing_country'  => WC()->customer->get_billing_country(),
	            'shipping_country' => WC()->customer->get_shipping_country(),
        	),
        	'locations' => array(
        		'city'	=> array(
        			'action' => 'scods-get-city-by-state',
					'nonce'  => wp_create_nonce('scods-get-city-by-state'),
        		),
        		'district'	 => array(
        			'action' => 'scods-get-district-by-city',
					'nonce'  => wp_create_nonce('scods-get-district-by-city'),
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
	 * Custom Order to Formatted Shipping Address
	 * Hook via woocommerce_order_formatted_shipping_address
	 * @since    1.0.0
	 */
	public function woo_custom_order_formatted_shipping_address( $address , $order_id) {
<<<<<<< HEAD
		$order 				 	 = wc_get_order( $order_id );
		$order_id  				 = $order->get_id(); // Get the order ID
		$order_data 			 = $order->get_data(); // The Order data
		$order_shipping_state 	 = $order_data['shipping']['state'];
		$order_shipping_city 	 = $order_data['shipping']['city'];
=======
		$order = wc_get_order( $order_id );
		$order_id  = $order->get_id(); // Get the order ID
		$order_data = $order->get_data(); // The Order data
		$order_shipping_state = $order_data['shipping']['state'];
		$order_shipping_city = $order_data['shipping']['city'];
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
		$order_shipping_district = $order_data['shipping']['address_2'];

		$getStatesName = DB::table( 'scod_shipping_state' )
                ->where( 'ID', $order_shipping_state )
                ->get();
        $getCityName = DB::table( 'scod_shipping_city' )
                ->where( 'ID', $order_shipping_city )
                ->get();
        $getDistrictName = DB::table( 'scod_shipping_district' )
                ->where( 'ID', $order_shipping_district )
                ->get();
<<<<<<< HEAD

	    $address = array(
	        'first_name' => $order_data['shipping']['first_name'],
	        'last_name'  => $order_data['shipping']['last_name'],
	        'company'    => $order_data['shipping']['company'],
	        'address_1'  => $order_data['shipping']['address_1'],
	        'address_2'  => isset($getDistrictName[0]->name) ? $getDistrictName[0]->name : $order_data['shipping']['address_2'],
	        'city'       => isset($getCityName[0]->name) ? $getCityName[0]->name : $order_data['shipping']['city'],
	        'state'      => isset($getStatesName[0]->name) ? $getStatesName[0]->name : $order_data['shipping']['state'],
	        'postcode'   => $order_data['shipping']['postcode'],
	        'country'    => $order_data['shipping']['country']
=======
	    $address = array(
	        'first_name'    => $order_data['shipping']['first_name'],
	        'last_name'     => $order_data['shipping']['last_name'],
	        'company'       => $order_data['shipping']['company'],
	        'address_1'     => $order_data['shipping']['address_1'],
	        'address_2'     => isset($getDistrictName[0]->name) ? $getDistrictName[0]->name : $order_data['shipping']['address_2'],
	        'city'          => isset($getCityName[0]->name) ? $getCityName[0]->name : $order_data['shipping']['city'],
	        'state'         => isset($getStatesName[0]->name) ? $getStatesName[0]->name : $order_data['shipping']['state'],
	        'postcode'      => $order_data['shipping']['postcode'],
	        'country'       => $order_data['shipping']['country']
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	    );

	    return $address;
	}

	/**
	 * Custom Order to Formatted Billing Address
	 * Hook via woocommerce_order_formated_shipping_address
	 * @since    1.0.0
	 */
	public function woo_custom_order_formatted_billing_address( $address, $order_id ) {
<<<<<<< HEAD
		$order 				 	= wc_get_order( $order_id );
		$order_id  			 	= $order->get_id(); // Get the order ID
		$order_data 		 	= $order->get_data(); // The Order data
		$order_billing_state 	= $order_data['billing']['state'];
		$order_billing_city 	= $order_data['billing']['city'];
=======
		$order = wc_get_order( $order_id );
		$order_id  = $order->get_id(); // Get the order ID
		$order_data = $order->get_data(); // The Order data
		$order_billing_state = $order_data['billing']['state'];
		$order_billing_city = $order_data['billing']['city'];
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
		$order_billing_district = $order_data['billing']['address_2'];

		$getStatesName = DB::table( 'scod_shipping_state' )
                ->where( 'ID', $order_billing_state )
                ->get();
        $getCityName = DB::table( 'scod_shipping_city' )
                ->where( 'ID', $order_billing_city )
                ->get();
        $getDistrictName = DB::table( 'scod_shipping_district' )
                ->where( 'ID', $order_billing_district )
                ->get();
<<<<<<< HEAD

	    $address = array(
	        'first_name' => $order_data['billing']['first_name'],
	        'last_name'  => $order_data['billing']['last_name'],
	        'company'    => $order_data['billing']['company'],
	        'address_1'  => $order_data['billing']['address_1'],
	        'address_2'  => isset($getDistrictName[0]->name) ? $getDistrictName[0]->name : $order_data['billing']['address_2'] ,
	        'city'       => isset($getCityName[0]->name) ? $getCityName[0]->name : $order_data['billing']['city'],
	        'state'      => isset($getStatesName[0]->name) ? $getStatesName[0]->name : $order_data['billing']['state'],
	        'postcode'   => $order_data['billing']['postcode'],
	        'country'    => $order_data['billing']['country']
=======
	    $address = array(
	        'first_name'    => $order_data['billing']['first_name'],
	        'last_name'     => $order_data['billing']['last_name'],
	        'company'       => $order_data['billing']['company'],
	        'address_1'     => $order_data['billing']['address_1'],
	        'address_2'     => isset($getDistrictName[0]->name) ? $getDistrictName[0]->name : $order_data['billing']['address_2'] ,
	        'city'          => isset($getCityName[0]->name) ? $getCityName[0]->name : $order_data['billing']['city'],
	        'state'         => isset($getStatesName[0]->name) ? $getStatesName[0]->name : $order_data['billing']['state'],
	        'postcode'      => $order_data['billing']['postcode'],
	        'country'       => $order_data['billing']['country']
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	    );

	    return $address;
	}

	/**
	 * Filter Woocommerce My Account Formatted Address (Billing and Shipping)
	 * Hook via woocommerce_my_account_my_address_formatted_address
	 * @since    1.0.0
	 */
	public function filter_woocommerce_my_account_my_address_formatted_address( $address, $customer_id, $name ) {
<<<<<<< HEAD
		$order_billing_state 	= get_user_meta($customer_id, $name . '_state', true);
		$order_billing_city 	= get_user_meta($customer_id, $name . '_city', true);
=======
		$order_billing_state = get_user_meta($customer_id, $name . '_state', true);
		$order_billing_city = get_user_meta($customer_id, $name . '_city', true);
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
		$order_billing_district = get_user_meta($customer_id, $name . '_address_2', true);

		$getStatesName = DB::table( 'scod_shipping_state' )
                ->where( 'ID', $order_billing_state )
                ->get();
        $getCityName = DB::table( 'scod_shipping_city' )
                ->where( 'ID', $order_billing_city )
                ->get();
        $getDistrictName = DB::table( 'scod_shipping_district' )
                ->where( 'ID', $order_billing_district )
                ->get();

	    $address = array( 
	    	'first_name' => get_user_meta($customer_id, $name . '_first_name', true), 
<<<<<<< HEAD
	    	'last_name'  => get_user_meta($customer_id, $name . '_last_name', true), 
	    	'company' 	 => get_user_meta($customer_id, $name . '_company', true), 
	    	'address_1'  => get_user_meta($customer_id, $name . '_address_1', true), 
	    	'address_2'  => isset($getDistrictName[0]->name) ? $getDistrictName[0]->name : get_user_meta($customer_id, $name . '_address_2', true), 
	    	'city' 		 => isset($getCityName[0]->name) ? $getCityName[0]->name : get_user_meta($customer_id, $name . '_city', true), 
	    	'state' 	 => isset($getStateName[0]->name) ? $getStateName[0]->name : get_user_meta($customer_id, $name . '_state', true), 
	    	'postcode' 	 => get_user_meta($customer_id, $name . '_postcode', true), 
	    	'country' 	 => get_user_meta($customer_id, $name . '_country', true) 
	    );

=======
	    	'last_name' => get_user_meta($customer_id, $name . '_last_name', true), 
	    	'company' => get_user_meta($customer_id, $name . '_company', true), 
	    	'address_1' => get_user_meta($customer_id, $name . '_address_1', true), 
	    	'address_2' => isset($getDistrictName[0]->name) ? $getDistrictName[0]->name : get_user_meta($customer_id, $name . '_address_2', true), 
	    	'city' => isset($getCityName[0]->name) ? $getCityName[0]->name : get_user_meta($customer_id, $name . '_city', true), 
	    	'state' => isset($getStateName[0]->name) ? $getStateName[0]->name : get_user_meta($customer_id, $name . '_state', true), 
	    	'postcode' => get_user_meta($customer_id, $name . '_postcode', true), 
	    	'country' => get_user_meta($customer_id, $name . '_country', true) 
	    );
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	    return $address; 
	}

	/**
	 * Ajax Send Custom Package When Checkout Actions
	 * Hook via wp_footer
	 * @since    1.0.0
	 */
	public function checkout_send_custom_package_via_ajax_js() {
	    if ( is_checkout() && ! is_wc_endpoint_url() ) :
<<<<<<< HEAD
	    ?>
		<script type="text/javascript">
=======
	    ?><script type="text/javascript">
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	    jQuery( function($){
	        if (typeof wc_checkout_params === 'undefined')
	            return false;

	        // Function that send the Ajax request
	        function sendAjaxRequest( value, fieldset = 'billing' ) {
	            $.ajax({
	                type: 'POST',
	                url: wc_checkout_params.ajax_url,
	                data: {
	                    'action': 'city2',
	                    'city2': value,
	                    'fieldset' : fieldset
	                },
	                success: function (result) {
	                    // $(document.body).trigger('update_checkout'); // Update checkout processes
	                    console.log( result ); // For testing (output data sent)
	                }
	            });
	        }

	        function sendAjaxRequestDistrict( value, fieldset = 'billing' ) {
	            $.ajax({
	                type: 'POST',
	                url: wc_checkout_params.ajax_url,
	                data: {
	                    'action': 'district',
	                    'district': value,
	                    'fieldset' : fieldset
	                },
	                success: function (result) {
	                    // $(document.body).trigger('update_checkout'); // Update checkout processes
	                    console.log( result ); // For testing (output data sent)
	                }
	            });
	        }

	        $(window).load(function() {	 
				$('select#billing_city2').on('select2:select', function (e) {
				    // var data = e.params.data;
					var selectedCity = $(this).find("option:selected").val();
				  	sendAjaxRequest( selectedCity );
				});

				$('select#shipping_city2').on('select2:select', function (e) {
				    // var data = e.params.data;
					var selectedCity = $(this).find("option:selected").val();
				  	sendAjaxRequest( selectedCity, 'shipping' );
				});

				$('select#billing_district').on('select2:select', function (e) {
				    // var data = e.params.data;
					var selectedDistrict = $(this).find("option:selected").val();
				  	sendAjaxRequestDistrict( selectedDistrict );
				});

				$('select#shipping_district').on('select2:select', function (e) {
				    // var data = e.params.data;
					var selectedDistrict = $(this).find("option:selected").val();
				  	sendAjaxRequestDistrict( selectedDistrict, 'shipping' );
				});
			});
	    });
	    </script>
	    <?php
	    endif;
	}

	/**
	 * Set City2 to WC Session
	 * Hook via wp_ajax_city2
	 * @since    1.0.0
	 */
	public function set_city2_to_wc_session() {
	    $field_key = 'city2';
	    if ( isset($_POST[$field_key]) && isset($_POST['fieldset']) ){
	        // Get data from custom session variable
	        $values = (array) WC()->session->get($field_key);
	        // Initializing when empty
	        if( ! empty($values) ) {
	            $values = array(
<<<<<<< HEAD
	                'billing'  => WC()->customer->get_meta('billing_'.$field_key),
=======
	                'billing' => WC()->customer->get_meta('billing_'.$field_key),
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	                'shipping' => WC()->customer->get_meta('shipping_'.$field_key)
	            );
	        }

	        // Sanitizing data sent
<<<<<<< HEAD
	        $fieldset = esc_attr($_POST['fieldset']);
	        $city2 	  = sanitize_text_field($_POST[$field_key]);
=======
	        $fieldset  = esc_attr($_POST['fieldset']);
	        $city2 = sanitize_text_field($_POST[$field_key]);
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee

	        // Set / udpate custom WC_Session variable
	        $values[$fieldset] = $city2;
	        WC()->session->set($field_key, wc_clean($values));

	        // Send back to javascript the data received as an array (json encoded)
	        echo json_encode(array($fieldset.'_'.$field_key => $city2));
	        wp_die(); // always use die() or wp_die() at the end to avoird errors
	    }
	}

	/**
	 * Save Update Field City2 from Checkout
	 * Hook via woocommerce_checkout_get_value
	 * @since    1.0.0
	 */
	public function update_city2_checkout_fields_values( $value, $input ) {
	    $field_key = 'city2';
<<<<<<< HEAD

=======
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	    // Get data from custom session variable
	    $values = (array) WC()->session->get($field_key);

	    if ( ! empty($values) ) {
	        if ( 'billing_'.$field_key === $input ) {
	            $value = $values['billing'];
	        }
	        if ( 'shipping_'.$field_key === $input ) {
	            $value = isset($values['shipping']) ? $values['shipping'] : '';
	        }
	    }
	    return $value;
	}

	/**
	 * Add City2 to Destination Shipping Package
	 * Hook via woocommerce_cart__shipping_packages
	 * @since    1.0.0
	 */
	public function add_city2_to_destination_shipping_package( $packages ) {
	    $customer   = WC()->customer; // The WC_Customer Object

	    // Get 'city2' data from customer meta data
	    $main_key   = 'city2';
	    $meta_value = $customer->get_meta('shipping_'.$main_key);
	    $meta_value = empty($meta_value) ? $customer->get_meta('billing_'.$main_key) : $meta_value;

	    // Get data from custom session variable
	    $values = (array) WC()->session->get($main_key);

	    if ( ! empty($values) ) {
	        $session_value = isset($values['shipping']) ? $values['shipping'] : '';
<<<<<<< HEAD
=======

>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	        if ( $session_value === $meta_value ) {
	            $session_value = $values['billing'];

	            if ( $session_value !== $meta_value ) {
	                $meta_value = $values['billing'];
	            }
	        } else {
	            $meta_value = $session_value;
	        }
	    }

	    // Loop through shipping packages
	    foreach ( $packages as $key => $package ) {
	        // Set to destination package the "city2"
	        $packages[$key]['destination'][$main_key] = $meta_value;
	    }
	    return $packages;
	}

	/**
	 * Remove City2 From WC Session Variable
	 * Hook via woocommerce_checkout_order_created
	 * @since    1.0.0
	 */
	public function remove_city2_custom_wc_session_variable() {
	    // Remove the custom WC_Session variable
	    WC()->session->__unset('city2');
	}

	/**
	 * Set District to WC Session
	 * Hook via wp_ajax_district
	 * @since    1.0.0
	 */
	public function set_district_to_wc_session() {
	    $field_key = 'district';
	    if ( isset($_POST[$field_key]) && isset($_POST['fieldset']) ){
	        // Get data from custom session variable
	        $values = (array) WC()->session->get($field_key);

	        // Initializing when empty
	        if( ! empty($values) ) {
	            $values = array(
<<<<<<< HEAD
	                'billing'  => WC()->customer->get_meta('billing_'.$field_key),
=======
	                'billing' => WC()->customer->get_meta('billing_'.$field_key),
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	                'shipping' => WC()->customer->get_meta('shipping_'.$field_key)
	            );
	        }

	        // Sanitizing data sent
<<<<<<< HEAD
	        $fieldset = esc_attr($_POST['fieldset']);
=======
	        $fieldset  = esc_attr($_POST['fieldset']);
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	        $district = sanitize_text_field($_POST[$field_key]);

	        // Set / udpate custom WC_Session variable
	        $values[$fieldset] = $district;
	        WC()->session->set($field_key, wc_clean($values));

	        // Send back to javascript the data received as an array (json encoded)
	        echo json_encode(array($fieldset.'_'.$field_key => $district));
	        wp_die(); // always use die() or wp_die() at the end to avoird errors
	    }
	}

	/**
	 * Save Update District Field On Checkout
	 * Hook via woocommerce_checkout_get_value
	 * @since    1.0.0
	 */
	public function update_district_checkout_fields_values( $value, $input ) {
	    $field_key = 'district';

	    // Get data from custom session variable
	    $values = (array) WC()->session->get($field_key);

	    if ( ! empty($values) ) {
	        if ( 'billing_'.$field_key === $input ) {
	            $value = $values['billing'];
	        }
	        if ( 'shipping_'.$field_key === $input ) {
	            $value = isset($values['shipping']) ? $values['shipping'] : '';
	        }
	    }
	    return $value;
	}

	/**
	 * Add District to Destination Shipping Package
	 * Hook via woocommerce_cart_shipping_packages
	 * @since    1.0.0
	 */
	public function add_district_to_destination_shipping_package( $packages ) {
	    $customer   = WC()->customer; // The WC_Customer Object

	    // Get 'district' data from customer meta data
	    $main_key   = 'district';
	    $meta_value = $customer->get_meta('shipping_'.$main_key);
	    $meta_value = empty($meta_value) ? $customer->get_meta('billing_'.$main_key) : $meta_value;

	    // Get data from custom session variable
	    $values = (array) WC()->session->get($main_key);

	    if ( ! empty($values) ) {
	        $session_value = isset($values['shipping']) ? $values['shipping'] : '';

	        if ( $session_value === $meta_value ) {
	            $session_value = $values['billing'];

	            if ( $session_value !== $meta_value ) {
	                $meta_value = $values['billing'];
	            }
	        } else {
	            $meta_value = $session_value;
	        }
	    }

	    // Loop through shipping packages
	    foreach ( $packages as $key => $package ) {
	        // Set to destination package the "district"
	        $packages[$key]['destination'][$main_key] = $meta_value;
	    }
	    return $packages;
	}

	/**
	 * Remove District From WC Session Variable
	 * Hook via woocommerce_checkout_order_created
	 * @since    1.0.0
	 */
	public function remove_district_custom_wc_session_variable() {
	    // Remove the custom WC_Session variable
	    WC()->session->__unset('district');
	}

<<<<<<< HEAD
=======
	/**
	 * Custom Order Status
	 * Hook via wc_order_statuses
	 * @since    1.0.0
	 */
	// Add to list of WC Order statuses
	public function add_custom_order_statuses( $order_statuses ) {
	    $new_order_statuses = array();
	 
	    // add new order status after processing
	    foreach ( $order_statuses as $key => $status ) {
	        $new_order_statuses[ $key ] = $status;

	        if ( 'wc-processing' === $key ) {
	        	$new_order_statuses['wc-pickup-shipping'] = __( 'Pickup', 'scod-shipping' );
	            $new_order_statuses['wc-in-shipping'] = __( 'In-Shipping', 'scod-shipping' );
	        }
	    }
	 
	    return $new_order_statuses;
	}

	/**
	 * Add Shipping Number Meta Box in Order Detail Admin
	 * Hook via add_meta_boxes
	 * @since    1.0.0
	 */
	// Adding Meta container admin shop_order pages
    public function add_order_shipping_number_meta_boxes()
    {
        add_meta_box(
	       'sejoli_shipping_number',
	       __('Shipping Number', 'scod-shipping'),
	       array( $this, 'add_other_fields_for_shipping_number' ),
	       'shop_order',
	       'side',
	       'core'
	   	);
    }

    /**
	 * Add Shipping Number Field Meta Box Container in Order Detail Side Admin
	 * @since    1.0.0
	 */
    // Adding Meta field in the meta container admin shop_order pages
    public function add_other_fields_for_shipping_number($post)
    {
	    $value = get_post_meta( $post->ID, '_sejoli_shipping_number', true );
	    $text = ! empty( $value ) ? esc_attr( $value ) : '';
	    echo '<input type="text" readonly class="input-text" name="sejoli_shipping_number" id="sejoli_shipping_number" value="' . $text . '" style="width:100%" />';
	    echo '<input type="hidden" name="sejoli_shipping_number_nonce" value="' . wp_create_nonce() . '">';

	    $order = wc_get_order( $post->ID );
	    $order_status  = $order->get_status();
	   	if ($order_status == 'pickup-shipping') {
	   		echo '<a href="#" 
	   		data-id="'.$post->ID.'"
	   		data-shipper-name="Zakky"
	   		data-shipper-addr1="JL Rusa 2"
	   		data-shipper-addr2="Cibodas"
	   		data-shipper-city="Jakarta"
	   		data-shipper-zip="15189"
	   		data-shipper-phone="+628569082338"
	   		data-receiver-name="Isti"
	   		data-receiver-addr1="JL Darmawangsa"
	   		data-receiver-addr2="Arcamanik"
	   		data-receiver-city="Kota Bandung"
	   		data-receiver-zip="14465"
	   		data-receiver-phone="+628569082338"
	   		data-qty="1"
	   		data-weight="1"
	   		data-goodsdesc="TEST"
	   		data-goodsvalue="1000"
	   		data-goodstype="1"
	   		data-origin="CGK10000"
	   		data-destination="BDO10000"
	   		data-service="REG"
	   		data-codamount="11000"
	   		class="generate-airwaybill">Generate Number Resi</a>';
	   	}

	   	// $origin_option = $this->get_option( 'shipping_origin' );
		// $origin = JNE_Origin::find( $origin_option );
		// print_r($origin);

	   	// print_r($order);
	   	
	   	// $shipping_method = new Shipping_Method();
		// $get_origin = $shipping_method->get_origin_info();
	   	
	   	// print_r($get_origin);
    }

    /**
	 * Save Custom Meta Box Shipping Number Field
	 * Hook via save_post
	 * @since    1.0.0
	 */
    // Save the data of the Meta field
    public function save_wc_order_shipping_number_fields( $post_id ) {
	    // Only for shop order
	    if ( 'shop_order' != $_POST[ 'post_type' ] )
	        return $post_id;

	    // Check if our nonce is set (and our cutom field)
	    if ( ! isset( $_POST[ 'sejoli_shipping_number_nonce' ] ) && isset( $_POST['sejoli_shipping_number'] ) )
	        return $post_id;

	    $nonce = $_POST[ 'sejoli_shipping_number_nonce' ];

	    // Verify that the nonce is valid.
	    if ( ! wp_verify_nonce( $nonce ) )
	        return $post_id;

	    // Checking that is not an autosave
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
	        return $post_id;

	    // Check the user’s permissions (for 'shop_manager' and 'administrator' user roles)
	    if ( ! current_user_can( 'edit_shop_order', $post_id ) && ! current_user_can( 'edit_shop_orders', $post_id ) )
	        return $post_id;

	    // Saving the data
	    update_post_meta( $post_id, '_sejoli_shipping_number', sanitize_text_field( $_POST[ 'sejoli_shipping_number' ] ) );
    }

>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
    /**
	 * Display Custom Shipping Number Meta Box Value on the My Account View Order Detail
	 * Hook via woocommerce_order_details_after_order_table
	 * @since    1.0.0
	 */
    // Display To My Account view Order
	public function display_sejoli_shipping_number_in_order_view( $order )
	{
	    $shipping_number = get_post_meta( $order->get_id(), '_sejoli_shipping_number', true );
	    if ( ! empty( $shipping_number ) ) {
<<<<<<< HEAD
	        // echo '<p><strong>'. __("Shipping Number", "scod-shipping").':</strong><mark class="order-status">' . get_post_meta( $order->get_id(), '_sejoli_shipping_number', true ) . '</mark></p>';
	        echo '<h2 class="woocommerce-order-details__title">'. __("Shipping Information", "scod-shipping").'</h2>';
	        
	        $shipping_methods	  = $order->get_shipping_methods();
			$shipping_method_id   = NULL;
			$shipping_instance_id = NULL;
			$courier_name 		  = NULL;

			foreach ($shipping_methods as $shipping_method) {
				$shipping_name 		  = $shipping_method['name'];
				$shipping_total	 	  = $shipping_method['total'];
				$shipping_method_id   = $shipping_method->get_method_id();
				$shipping_instance_id = $shipping_method->get_instance_id();
			}

	        $trace_tracking = API_JNE::set_params()->get_tracking( $shipping_number );
	    	// print_r($trace_tracking->history);
	    	
	    	echo '<h6>'.__('Number Resi:', 'scod-shipping').'</h6>';
	    	echo '<div class="shipping-number" style="font-size:20px;">'.$shipping_number.'</div>';

		   	echo '<h6>'.__('Shipping Details:', 'scod-shipping').'</h6>';
		   	echo '<table style="text-align: left;">';
		   	echo '<tr>';
		   		echo '<th>'.__('Courier:', 'scod-shipping').'</th>';
		   		echo '<td>'.$shipping_name.'</td>';
		   	echo '</tr>';
		   	echo '<tr>';
		   		echo '<th>'.__('Receiver:', 'scod-shipping').'</th>';
		   		echo '<td>'.$trace_tracking->cnote->cnote_receiver_name.' - ('.$trace_tracking->cnote->keterangan.')</td>';
		   	echo '</tr>';
		   	echo '<tr>';
		   		echo '<th>'.__('Last Status:', 'scod-shipping').'</th>';
		   		echo '<td>'.$trace_tracking->cnote->pod_status.'</td>';
		   	echo '</tr>';
		   	echo '</table>';

		   	echo '<h6>'.__('Tracking History:', 'scod-shipping').'</h6>';
		   		echo '<table style="text-align: left;">';
		   		echo '<tr>';
			   		echo '<th>'.__('Date', 'scod-shipping').'</th>';
			   		echo '<th>'.__('Status', 'scod-shipping').'</th>';
			   	echo '</tr>';	
			   	foreach ($trace_tracking->history as $history) {
					echo '<tr>';
				   		echo '<td>'.$history->date.'</td>';
				   		echo '<td>'.$history->desc.'</td>';
				   	echo '</tr>';
			   	}
			   	echo '</table>';
=======
	        echo '<p><strong>'. __("Shipping Number", "scod-shipping").':</strong><mark class="order-status">' . get_post_meta( $order->get_id(), '_sejoli_shipping_number', true ) . '</mark></p>';
	    }
	}

	/**
	 * Display Custom Shipping Number Meta Box Value on the Order Edit Page Admin
	 * Hook via woocommerce_admin_order_data_after_billing_address
	 * @since    1.0.0
	 */
	// Display field value on the order edit page (not in custom fields metabox)
	public function shipping_number_field_display_admin_order_meta($order){
	    $shipping_number = get_post_meta( $order->get_id(), '_sejoli_shipping_number', true );
	    if ( ! empty( $shipping_number ) ) {
	        echo '<p><strong>'. __("Shipping Number", "scod-shipping").':</strong><mark class="order-status">' . get_post_meta( $order->get_id(), '_sejoli_shipping_number', true ) . '</mark></p>';
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	    }
	}

	/**
	 * Custom checkout fields
	 * Hook via woocommerce_checkout_fields
	 * @since    1.0.0
	 */
	public function scod_checkout_fields( $fields ) {
		if( WC()->customer->get_shipping_country() != 'ID' ) {
			return $fields;
		}
		
		// Add custom fields for city dropdown
	    $fields['billing']['billing_city2'] = $fields['shipping']['shipping_city2'] = array(
	        'label'		   => $fields['billing']['billing_city']['label'],
		    'type'         => 'select',
	        'required'     => true,
	        'options'      => array( '' => '' ),
	        'autocomplete' => 'address-level2',
			'placeholder'  => __( 'Select an option...', 'scod-shipping' ),
	        'class'		   => array( 'address-field', 'form-row-wide', 'hidden' ),
	    );

	    // Add custom fields for district dropdown
	    $fields['billing']['billing_district'] = $fields['shipping']['shipping_district'] = array(
<<<<<<< HEAD
	        'label'       => __( 'District', 'scod-shipping' ),
		    'type'		  => 'select',
		    'required'    => true,
	    	'options'	  => array( '' => '' ),
			'placeholder' => __( 'Select an option...', 'scod-shipping' ),
    		'class'       => array( 'form-row-wide', 'address-field', 'hidden' ),
=======
	        'label'     	=> __( 'District', 'scod-shipping' ),
		    'type'			=> 'select',
		    'required'  	=> true,
	    	'options'		=> array( '' => '' ),
			'placeholder'	=> __( 'Select an option...', 'scod-shipping' ),
    		'class'     	=> array( 'form-row-wide', 'address-field', 'hidden' ),
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	    );

		// Disabled default country select to prevent conflict with wc dynamic fields.
		$fields['billing']['billing_country']['custom_attributes'] = array( 'disabled' => 'disabled' );
		$fields['shipping']['shipping_country']['custom_attributes'] = array( 'disabled' => 'disabled' );

	    // Sort fields
<<<<<<< HEAD
	    $fields['billing']['billing_first_name']['priority']  = 1;
	    $fields['billing']['billing_last_name']['priority']   = 2;
	    $fields['billing']['billing_company']['priority'] 	  = 3;
	    $fields['billing']['billing_country']['priority'] 	  = 4;
	    $fields['billing']['billing_state']['priority'] 	  = 5;
	    $fields['billing']['billing_city']['priority'] 		  = 6;
	    $fields['billing']['billing_city2']['priority'] 	  = 7;
	    $fields['billing']['billing_district']['priority'] 	  = 8;
	    $fields['billing']['billing_address_2']['priority']   = 9;
	    $fields['billing']['billing_address_1']['priority']   = 10;
	    $fields['billing']['billing_postcode']['priority'] 	  = 11;
	    $fields['billing']['billing_email']['priority'] 	  = 12;
	    $fields['billing']['billing_phone']['priority'] 	  = 13;

	    $fields['shipping']['shipping_first_name']['priority'] = 1;
	    $fields['shipping']['shipping_last_name']['priority']  = 2;
	    $fields['shipping']['shipping_company']['priority']    = 3;
	    $fields['shipping']['shipping_country']['priority']    = 4;
	    $fields['shipping']['shipping_state']['priority'] 	   = 5;
	    $fields['shipping']['shipping_city']['priority'] 	   = 6;
	    $fields['shipping']['shipping_city2']['priority'] 	   = 7;
	    $fields['shipping']['shipping_district']['priority']   = 8;
	    $fields['shipping']['shipping_address_2']['priority']  = 9;
	    $fields['shipping']['shipping_address_1']['priority']  = 10;
	    $fields['shipping']['shipping_postcode']['priority']   = 11;
=======
	    $fields['billing']['billing_first_name']['priority'] 	= 1;
	    $fields['billing']['billing_last_name']['priority'] 	= 2;
	    $fields['billing']['billing_company']['priority'] 		= 3;
	    $fields['billing']['billing_country']['priority'] 		= 4;
	    $fields['billing']['billing_state']['priority'] 		= 5;
	    $fields['billing']['billing_city']['priority'] 			= 6;
	    $fields['billing']['billing_city2']['priority'] 		= 7;
	    $fields['billing']['billing_district']['priority'] 		= 8;
	    $fields['billing']['billing_address_2']['priority'] 	= 9;
	    $fields['billing']['billing_address_1']['priority'] 	= 10;
	    $fields['billing']['billing_postcode']['priority'] 		= 11;
	    $fields['billing']['billing_email']['priority'] 		= 12;
	    $fields['billing']['billing_phone']['priority'] 		= 13;

	    $fields['shipping']['shipping_first_name']['priority'] 	= 1;
	    $fields['shipping']['shipping_last_name']['priority'] 	= 2;
	    $fields['shipping']['shipping_company']['priority'] 	= 3;
	    $fields['shipping']['shipping_country']['priority'] 	= 4;
	    $fields['shipping']['shipping_state']['priority'] 		= 5;
	    $fields['shipping']['shipping_city']['priority'] 		= 6;
	    $fields['shipping']['shipping_city2']['priority'] 		= 7;
	    $fields['shipping']['shipping_district']['priority'] 	= 8;
	    $fields['shipping']['shipping_address_2']['priority'] 	= 9;
	    $fields['shipping']['shipping_address_1']['priority'] 	= 10;
	    $fields['shipping']['shipping_postcode']['priority'] 	= 11;
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee

		return $fields;
	}

	
	/**
	 * Override checkout fields locale
	 * Hook via woocommerce_default_address_fields
	 * @since    1.0.0
	 */
	public function override_locale_fields( $fields ) {
		if( WC()->customer->get_shipping_country() != 'ID' ) {
			return $fields;
		}

		// Custom fields for city
		$fields['city2'] = array(
<<<<<<< HEAD
	        'label'		   => $fields['city']['label'],
		    'type'         => 'select',
	        'required'     => true,
	        'options'      => array( '' => '' ),
	        'autocomplete' => 'address-level2',
			'placeholder'  => __( 'Select an option...', 'scod-shipping' ),
	        'class'		   => array( 'address-field', 'form-row-wide', 'hidden' ),
=======
	        'label'		  	=> $fields['city']['label'],
		    'type'         	=> 'select',
	        'required'     	=> true,
	        'options'      	=> array( '' => '' ),
	        'autocomplete' 	=> 'address-level2',
			'placeholder'	=> __( 'Select an option...', 'scod-shipping' ),
	        'class'		   	=> array( 'address-field', 'form-row-wide', 'hidden' ),
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
	    );

		// Custom field for District
	    $fields['district'] = array(
<<<<<<< HEAD
	        'label'        => __( 'District', 'scod-shipping' ),
		    'type'		   => 'select',
		    'required'     => true,
	    	'options'	   => array( '' => '' ),
			'placeholder'  => __( 'Select an option...', 'scod-shipping' ),
    		'class'        => array( 'form-row-wide', 'address-field', 'hidden' ),
	    );

		$fields['state']['priority'] 	 = 5;
	    $fields['city']['priority'] 	 = 6;
	    $fields['city2']['priority'] 	 = 6;
	    $fields['address_2']['priority'] = 7; //custom district
	    $fields['district']['priority']  = 7;
	    $fields['address_1']['priority'] = 8;
	    $fields['postcode']['priority']  = 9;
=======
	        'label'     	=> __( 'District', 'scod-shipping' ),
		    'type'			=> 'select',
		    'required'  	=> true,
	    	'options'		=> array( '' => '' ),
			'placeholder'	=> __( 'Select an option...', 'scod-shipping' ),
    		'class'     	=> array( 'form-row-wide', 'address-field', 'hidden' ),
	    );

		$fields['state']['priority'] 		= 5;
	    $fields['city']['priority'] 		= 6;
	    $fields['city2']['priority'] 		= 6;
	    $fields['address_2']['priority'] 	= 7; //custom district
	    $fields['district']['priority'] 	= 7;
	    $fields['address_1']['priority'] 	= 8;
	    $fields['postcode']['priority'] 	= 9;
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee

	    return $fields;
	}

	/**
	 * Add hidden country field to replace disabled default field.
	 * Hook via woocommerce_after_checkout_billing_form
	 *
	 * @since    1.0.0
	 */
	public function checkout_country_hidden_fields_replacement( $fields ) {
		$billing_country = WC()->customer->get_billing_country();
		$shipping_country = WC()->customer->get_shipping_country();
		?>
		
		<input type="hidden" name="billing_country" value="<?php echo $billing_country; ?>">
		<input type="hidden" name="shipping_country" value="<?php echo $shipping_country; ?>">
		
		<?php
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
            'state_id' => NULL,
            'nonce'    => NULL
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
            'city_id' => NULL,
            'nonce'   => NULL
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
			$shipping_methods	  = $order->get_shipping_methods();
			$shipping_method_id   = NULL;
			$shipping_instance_id = NULL;
			$courier_name 		  = NULL;

			foreach ($shipping_methods as $shipping_method) {
				$shipping_name 		  = $shipping_method['name'];
				$shipping_method_id   = $shipping_method->get_method_id();
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
			$store_id 		  = NULL;
			$store_secret_key = NULL;

			if( $shipping_instance_id ) {
				$shipping_class   = new Shipping_Method( $shipping_instance_id );
				$store_id 		  = $shipping_class->get_option( 'store_id' );
				$store_secret_key = $shipping_class->get_option( 'store_secret_key' );
			}

			// Default params
			$order_params = array(
<<<<<<< HEAD
				'store_id'		  => $store_id,
				'secret_key'	  => $store_secret_key,
				'buyer_name'	  => $order->get_billing_first_name() .' '. $order->get_billing_last_name(),
				'buyer_email'	  => $order->get_billing_email(),
				'buyer_phone'	  => $order->get_billing_phone(),
				'courier_name'	  => $courier_name,
				'invoice_number'  => $order->get_order_number(),
				'invoice_total'   => $order->get_total(),
				'shipping_fee'	  => $order->get_total_shipping(),
				'shipping_status' => 'pending',
				'notes'			  => $order->get_customer_note(),
				'order'			  => $order
=======
				'store_id'			=> $store_id,
				'secret_key'		=> $store_secret_key,
				'buyer_name'		=> $order->get_billing_first_name() .' '. $order->get_billing_last_name(),
				'buyer_email'		=> $order->get_billing_email(),
				'buyer_phone'		=> $order->get_billing_phone(),
				'courier_name'		=> $courier_name,
				'invoice_number'	=> $order->get_order_number(),
				'invoice_total' 	=> $order->get_total(),
				'shipping_fee'		=> $order->get_total_shipping(),
				'shipping_status'	=> 'pending',
				'notes'				=> $order->get_customer_note(),
				'order'				=> $order
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
			);

			// Send data to API
			$api_scod 	  = new API_SCOD();
			$create_order = $api_scod->post_create_order( $order_params );

			if( ! is_wp_error( $create_order ) ) {
				// Flag the action as done (to avoid repetitions on reload for example)
				$order->update_meta_data( '_sync_order_action_scod_done', true );
				if( $order->save() ) {
					error_log( 'Sync order success ..' );
				}
			} else {
				error_log( 'Sync order error .. ' );
			}
			
			error_log( 'Done processing order ID '. $order_id );
	    }
	}
<<<<<<< HEAD
}
=======

	/**
	 * WooCommerce action to add actions when pickup proces request.
	 *
	 * @since    1.0.0
	 */
	// Order status - `pickup-shipping`
	function add_actions_processing_pickup_order($order_id) {
		if ( ! $order_id ) return;

	    // Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );

		// Check payment method
		if( $order->get_payment_method() != 'cod' ) {
			return;
		}

		$status = "pickup";
		update_post_meta( $order_id, '_sejoli_shipping_number', 0);
		$shipNumber = get_post_meta( $order_id, '_sejoli_shipping_number', true );

		// Send data to API
		$api_scod = new API_SCOD();
		$update_order = $api_scod->post_update_order( $order_id, $status, $shipNumber );

		if( ! is_wp_error( $update_order ) ) {
			// Flag the action as done (to avoid repetitions on reload for example)
			// $order->update_meta_data( '_sync_order_action_scod_done', true );
			if( $order->save() ) {
				error_log( 'Sync order success ..' );
			}
		} else {
			error_log( 'Sync order error .. ' );
		}
	}

	/**
	 * WooCommerce action to add actions when airway bill proces request.
	 *
	 * @since    1.0.0
	 */
	// Order status - `in-shipping`
	function add_actions_processing_in_shipping_order($order_id) {
		if ( ! $order_id ) return;

	    // Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );
        $order_id  = $order->get_id();

		// Check payment method
		if( $order->get_payment_method() != 'cod' ) {
			return;
		}

		$status = "on-the-way";
		update_post_meta( $order_id, '_sejoli_shipping_number', sanitize_text_field( $_POST[ 'sejoli_shipping_number' ] ) );
		$shipNumber = get_post_meta( $order_id, '_sejoli_shipping_number', true );

		// Send data to API
		$api_scod = new API_SCOD();
		$update_order = $api_scod->post_update_order( $order_id, $status, $shipNumber );

		if( ! is_wp_error( $update_order ) ) {
			// Flag the action as done (to avoid repetitions on reload for example)
			// $order->update_meta_data( '_sync_order_action_scod_done', true );
			if( $order->save() ) {
				error_log( 'Sync order success ..' );
			}
		} else {
			error_log( 'Sync order error .. ' );
		}
	}

	/**
	 * WooCommerce action to generate airwaybill by request.
	 *
	 * @since    1.0.0
	 */
	public function generate_airwaybill() {
		$params = wp_parse_args( $_POST, array(
            'orderID'  		=> NULL,
            'shipperName' 	=> NULL,
            'shipperAddr1' 	=> NULL,
            'shipperAddr2' 	=> NULL,
            'shipperCity' 	=> NULL,
            'shipperZip' 	=> NULL,
            'shipperPhone' 	=> NULL,
            'receiverName' 	=> NULL,
            'receiverAddr1' => NULL,
            'receiverAddr2' => NULL,
            'receiverCity' 	=> NULL,
            'receiverZip' 	=> NULL,
            'receiverPhone' => NULL,
            'qty' 			=> NULL,
            'weight' 		=> NULL,
            'goodsDesc' 	=> NULL,
            'goodsValue' 	=> NULL,
            'goodsType' 	=> NULL,
            'origin' 		=> NULL,
            'destination' 	=> NULL,
            'service' 		=> NULL,
            'codAmount' 	=> NULL,
            'nonce' 		=> NULL
        ));

        $respond  = [
            'valid'   => false,
            'message' => NULL
        ];

        if( wp_verify_nonce( $params['nonce'], 'scods-generate-airwaybill') ) :

            unset( $params['nonce'] );

            $do_update = API_JNE::set_params()->get_airwaybill( $params['orderID'], $params['shipperName'], $params['shipperAddr1'], $params['shipperAddr2'], $params['shipperCity'], $params['shipperZip'], $params['shipperPhone'], $params['receiverName'], $params['receiverAddr1'], $params['receiverAddr2'], $params['receiverCity'], $params['receiverZip'], $params['receiverPhone'], $params['qty'], $params['weight'], $params['goodsDesc'], $params['goodsValue'], $params['goodsType'], $params['origin'], $params['destination'], $params['service'], $params['codAmount'] );

            if ( ! is_wp_error( $do_update ) ) {

                $respond['valid']  = true;

            } else {

                $respond['message'] = $do_update->get_error_message();
            }

        endif;

        echo wp_send_json( $do_update[0]->cnote_no );
	}

}
>>>>>>> ff9ca5dcf261d36bf81e01de765b63ed4cd631ee
