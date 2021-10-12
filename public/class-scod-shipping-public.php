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
			),
			'shipment_tracking' => array(
				'ajaxurl'	=> add_query_arg(array(
						'action' => 'sejoli_shipment_tracking_result'
					), admin_url('admin-ajax.php')
				),
				'nonce'	=> wp_create_nonce('sejoli_shipment_tracking_result')
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
		$order 				 	 = wc_get_order( $order_id );
		$order_id  				 = $order->get_id(); // Get the order ID
		$order_data 			 = $order->get_data(); // The Order data
		$order_shipping_state 	 = $order_data['shipping']['state'];
		$order_shipping_city 	 = $order_data['shipping']['city'];
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
	    );

	    return $address;
	}

	/**
	 * Custom Order to Formatted Billing Address
	 * Hook via woocommerce_order_formated_shipping_address
	 * @since    1.0.0
	 */
	public function woo_custom_order_formatted_billing_address( $address, $order_id ) {
		$order 				 	= wc_get_order( $order_id );
		$order_id  			 	= $order->get_id(); // Get the order ID
		$order_data 		 	= $order->get_data(); // The Order data
		$order_billing_state 	= $order_data['billing']['state'];
		$order_billing_city 	= $order_data['billing']['city'];
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
	    );

	    return $address;
	}

	/**
	 * Filter Woocommerce My Account Formatted Address (Billing and Shipping)
	 * Hook via woocommerce_my_account_my_address_formatted_address
	 * @since    1.0.0
	 */
	public function filter_woocommerce_my_account_my_address_formatted_address( $address, $customer_id, $name ) {
		$order_billing_state 	= get_user_meta($customer_id, $name . '_state', true);
		$order_billing_city 	= get_user_meta($customer_id, $name . '_city', true);
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
	    	'last_name'  => get_user_meta($customer_id, $name . '_last_name', true), 
	    	'company' 	 => get_user_meta($customer_id, $name . '_company', true), 
	    	'address_1'  => get_user_meta($customer_id, $name . '_address_1', true), 
	    	'address_2'  => isset($getDistrictName[0]->name) ? $getDistrictName[0]->name : get_user_meta($customer_id, $name . '_address_2', true), 
	    	'city' 		 => isset($getCityName[0]->name) ? $getCityName[0]->name : get_user_meta($customer_id, $name . '_city', true), 
	    	'state' 	 => isset($getStateName[0]->name) ? $getStateName[0]->name : get_user_meta($customer_id, $name . '_state', true), 
	    	'postcode' 	 => get_user_meta($customer_id, $name . '_postcode', true), 
	    	'country' 	 => get_user_meta($customer_id, $name . '_country', true) 
	    );

	    return $address; 
	}

	/**
	 * Ajax Send Custom Package When Checkout Actions
	 * Hook via wp_footer
	 * @since    1.0.0
	 */
	public function checkout_send_custom_package_via_ajax_js() {
	    if ( is_checkout() && ! is_wc_endpoint_url() ) :
	    ?>
		<script type="text/javascript">
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
	                'billing'  => WC()->customer->get_meta('billing_'.$field_key),
	                'shipping' => WC()->customer->get_meta('shipping_'.$field_key)
	            );
	        }

	        // Sanitizing data sent
	        $fieldset = esc_attr($_POST['fieldset']);
	        $city2 	  = sanitize_text_field($_POST[$field_key]);

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
	                'billing'  => WC()->customer->get_meta('billing_'.$field_key),
	                'shipping' => WC()->customer->get_meta('shipping_'.$field_key)
	            );
	        }

	        // Sanitizing data sent
	        $fieldset = esc_attr($_POST['fieldset']);
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
	    }
	}

	/**
	 * Display Shipment Tracking Form With Shortcode
	 * 
	 * @since    1.0.0
	 */
	public function sejoli_shipment_tracking_shortcode($atts) {
	  	$form = '
        <form id="shipment-tracking-form" action="">
          	<label for="shipment-number">'.__('Shipment Number', 'scod-shipping').'</label>
          	<input type="text" id="shipment-number" name="shipment-number" value="">
          	<br>
          	<input type="submit" name="submit-tracking" value="Search" >
        </form>';
 
        $form .= '<div id="shipment-history"></div>';

        return $form;
	}

	/**
	 * Display Shipment Tracking Result On Shortcode
	 * 
	 * @since    1.0.0
	 */
	public function sejoli_shipment_tracking_result() {
		$params = wp_parse_args( $_POST, array(
            'shipmentNumber' => NULL,
            'nonce' 		 => NULL
        ));

        $respond  = [
            'valid'   => false,
            'message' => NULL
        ];

        if( wp_verify_nonce( $params['nonce'], 'sejoli_shipment_tracking_result') ) :

            unset( $params['nonce'] );

            $trace_tracking = API_JNE::set_params()->get_tracking( $params['shipmentNumber'] );

            if ( ! is_wp_error( $trace_tracking ) ) {

                $respond['valid']  = true;

            } else {

                $respond['message'] = $trace_tracking->get_error_message();
            }

        endif;

        $html = '<h6>'.__('Number Resi:', 'scod-shipping').'</h6>';
	    	$html .= '<div class="shipping-number" style="font-size:26px;"><b>'.$params['shipmentNumber'].'</b></div>';

		   	$html .= '<h6>'.__('Shipping Details:', 'scod-shipping').'</h6>';
		   	$html .= '<table style="text-align: left;">';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Courier:', 'scod-shipping').'</th>';
		   		$html .= '<td>JNE - '.$trace_tracking->cnote->cnote_services_code.'</td>';
		   	$html .= '</tr>';
		   	foreach ($trace_tracking->detail as $detail) {
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('From:', 'scod-shipping').'</th>';
		   		$html .= '<td>'.$detail->cnote_shipper_name.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Shipper City:', 'scod-shipping').'</th>';
		   		$html .= '<td>'.$detail->cnote_shipper_city.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Shipper Address:', 'scod-shipping').'</th>';
		   		$html .= '<td>'.$detail->cnote_shipper_addr1.' - '.$detail->cnote_shipper_addr2.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('To:', 'scod-shipping').'</th>';
		   		$html .= '<td>'.$detail->cnote_receiver_name.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Receiver City:', 'scod-shipping').'</th>';
		   		$html .= '<td>'.$detail->cnote_receiver_city.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Receiver Address:', 'scod-shipping').'</th>';
		   		$html .= '<td>'.$detail->cnote_receiver_addr1.' - '.$detail->cnote_receiver_addr2.'</td>';
		   	$html .= '</tr>';
		   	}
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Receiver:', 'scod-shipping').'</th>';
		   		$html .= '<td>'.$trace_tracking->cnote->cnote_receiver_name.' - ('.$trace_tracking->cnote->keterangan.')</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Last Status:', 'scod-shipping').'</th>';
		   		$html .= '<td>'.$trace_tracking->cnote->pod_status.'</td>';
		   	$html .= '</tr>';
		   	$html .= '</table>';

        $html .= '<h6>'.__('Tracking History:', 'scod-shipping').'</h6>';
		   		$html .= '<table style="text-align: left;">';
		   		$html .= '<tr>';
			   		$html .= '<th>'.__('Date', 'scod-shipping').'</th>';
			   		$html .= '<th>'.__('Status', 'scod-shipping').'</th>';
			   	$html .= '</tr>';	
			   	foreach ($trace_tracking->history as $history) {
					$html .= '<tr>';
				   		$html .= '<td>'.$history->date.'</td>';
				   		$html .= '<td>'.$history->desc.'</td>';
				   	$html .= '</tr>';
			   	}
			   	$html .= '</table>';

        echo wp_send_json( $html );
    }

    /**
	 * Display Shipment Tracking Form Shortcode
	 * Hook via init
	 * 
	 * @since    1.0.0
	 */
	public function sejoli_init_tracking_shipment_shortcode() {
	    add_shortcode( 'sejoli_shipment_tracking', array( $this , 'sejoli_shipment_tracking_shortcode' ) );
	    add_action('wp_ajax_nopriv_sejoli_shipment_tracking_result', array($this, 'sejoli_shipment_tracking_result'));
        add_action('wp_ajax_sejoli_shipment_tracking_result', array($this, 'sejoli_shipment_tracking_result'));
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
	        'label'       => __( 'District', 'scod-shipping' ),
		    'type'		  => 'select',
		    'required'    => true,
	    	'options'	  => array( '' => '' ),
			'placeholder' => __( 'Select an option...', 'scod-shipping' ),
    		'class'       => array( 'form-row-wide', 'address-field', 'hidden' ),
	    );

		// Disabled default country select to prevent conflict with wc dynamic fields.
		$fields['billing']['billing_country']['custom_attributes'] = array( 'disabled' => 'disabled' );
		$fields['shipping']['shipping_country']['custom_attributes'] = array( 'disabled' => 'disabled' );

	    // Sort fields
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
	        'label'		   => $fields['city']['label'],
		    'type'         => 'select',
	        'required'     => true,
	        'options'      => array( '' => '' ),
	        'autocomplete' => 'address-level2',
			'placeholder'  => __( 'Select an option...', 'scod-shipping' ),
	        'class'		   => array( 'address-field', 'form-row-wide', 'hidden' ),
	    );

		// Custom field for District
	    $fields['district'] = array(
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
	 * Hook via woocommerce_available_payment_gateways
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
	 * Hook via woocommerce_package_rates
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
	 * Hook via woocommerce_thankyou
	 *
	 * @since    1.0.0
	 */
	public function send_order_data_to_api( $order_id ) {
	    if ( ! $order_id ) return;

	    // Allow code execution only once
	    if( ! get_post_meta( $order_id, '_sync_order_action_scod_done', true ) ) {

			// Get an instance of the WC_Order object
	        $order = wc_get_order( $order_id );
	    	$order_data   = $order->get_data(); // The Order Data

			// Check payment method
			if( $order->get_payment_method() != 'cod' ) {
				return;
			}

			// Get Store Information
			$store_address   = get_option( 'woocommerce_store_address' );
			$store_address_2 = get_option( 'woocommerce_store_address_2' );
			$store_city      = get_option( 'woocommerce_store_city' );
			$store_postcode  = get_option( 'woocommerce_store_postcode' );
			$store_phone 	 = get_option( 'woocommerce_store_phone' );

			// The store country/state
			$store_raw_country = get_option( 'woocommerce_default_country' );
			$split_country 	   = explode( ":", $store_raw_country );
			$store_country     = $split_country[0];
			$store_state   	   = $split_country[1];

			$getStoreStatesName = DB::table( 'scod_shipping_state' )
	                ->where( 'code', $store_state )
	                ->get();

	        $getStoreState 				= isset($getStoreStatesName[0]->name) ? $getStoreStatesName[0]->name : $store_state;
	        $getStoreCityState 			= $store_city .', '.$getStoreState;
			$order_payment_method_title = $order_data['payment_method_title'];
	        $order_total 				= $order_data['total'];
			$order_shipping_first_name  = $order_data['shipping']['first_name'];
			$order_shipping_last_name 	= $order_data['shipping']['last_name'];
			$order_shipping_fullname 	= $order_data['shipping']['first_name'].' '.$order_data['shipping']['last_name'];
			$order_shipping_company 	= $order_data['shipping']['company'];
			$order_shipping_address 	= $order_data['shipping']['address_1'];
			$order_shipping_district 	= $order_data['shipping']['address_2'];
			$order_shipping_city 		= $order_data['shipping']['city'];
			$order_shipping_state 		= $order_data['shipping']['state'];
			$order_shipping_postcode 	= $order_data['shipping']['postcode'];
			$order_shipping_country 	= $order_data['shipping']['country'];
			$order_billing_phone 		= $order_data['billing']['phone'];

			$getStatesName = DB::table( 'scod_shipping_state' )
	                ->where( 'ID', $order_shipping_state )
	                ->get();
	        $getCityName = DB::table( 'scod_shipping_city' )
	                ->where( 'ID', $order_shipping_city )
	                ->get();
	        $getDistrictName = DB::table( 'scod_shipping_district' )
	                ->where( 'ID', $order_shipping_district )
	                ->get();

	        $getState 	  = isset($getStatesName[0]->name) ? $getStatesName[0]->name : $order_data['shipping']['state'];
	        $getCity 	  = isset($getCityName[0]->name) ? $getCityName[0]->name : $order_data['shipping']['city'];
	        $getDistrict  = isset($getDistrictName[0]->name) ? $getDistrictName[0]->name : $order_data['shipping']['address_2'];
			$getCityState = $getCity. ', ' .$getState;

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

				if( \str_contains( strtolower( $shipping_name ), 'sicepat' ) ):
					$courier_name = 'SiCepat';
				endif;
			}

			if($shipping_name === "JNE - REG (1-2 hari)") {
				$shipping_service = "REG";
			} elseif($shipping_name === "JNE - OKE (2-3 hari)") {
				$shipping_service = "OKE";
			} elseif($shipping_name === "JNE - YES (1 hari)") {
				$shipping_service = "YES";
			} else {
				$shipping_service = "JTR";
			}

			if($shipping_name === "SICEPAT - BEST (1 hari)") {
				$shipping_service = "BEST";
			} elseif($shipping_name === "SICEPAT - GOKIL (2 - 3 hari)") {
				$shipping_service = "GOKIL";
			} elseif($shipping_name === "SICEPAT - KEPO (1 - 2 hari)") {
				$shipping_service = "KEPO";
			} elseif($shipping_name === "SICEPAT - REG (1 - 2 hari)") {
				$shipping_service = "REG";
			} elseif($shipping_name === "SICEPAT - SDS (1 hari)") {
				$shipping_service = "SDS";
			} elseif($shipping_name === "SICEPAT - SIUNT (1 - 2 hari)") {
				$shipping_service = "SIUNT";
			} else {
				$shipping_service = "Cargo";
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

				$shipping_origin  = $shipping_class->get_option( 'shipping_origin' );

				$packages = WC()->shipping->get_packages();

				$packages['destination']['country']   = $store_country;
				$packages['destination']['state'] 	  = $order_shipping_state;
				$packages['destination']['postcode']  = $order_shipping_postcode;
				$packages['destination']['city'] 	  = $order_shipping_city;
				$packages['destination']['address']   = $order_shipping_address;
				$packages['destination']['address_1'] = $order_shipping_address;
				$packages['destination']['address_2'] = $order_shipping_district;
				$packages['destination']['city2'] 	  = $order_shipping_city;
				$packages['destination']['district']  = $order_shipping_district;

				if($shipping_name === "JNE - REG (1-2 hari)" || $shipping_name === "JNE - OKE (2-3 hari)" || $shipping_name === "JNE - JTR>250 (3-4 hari)" || $shipping_name === "JNE - JTR<150 (3-4 hari)" || $shipping_name === "JNE - JTR250 (3-4 hari)" || $shipping_name === "JNE - JTR (3-4 hari)") {
		        	$getOrigin = $shipping_class->get_origin_info()->code;
					$destination = $shipping_class->get_destination_info( $packages['destination'] )->code;
				} elseif($shipping_name === "SICEPAT - REG (1 - 2 hari)" || $shipping_name === "SICEPAT - GOKIL (2 - 3 hari)" || $shipping_name === "SICEPAT - BEST (1 hari)" || $shipping_name === "SICEPAT - KEPO (1 - 2 hari)" || $shipping_name === "SICEPAT - SDS (1 hari)"  || $shipping_name === "SICEPAT - SIUNT (1 - 2 hari)") {
		        	$getOrigin = $shipping_class->get_sicepat_origin_info()->origin_code;
					$destination = $shipping_class->get_sicepat_destination_info( $packages['destination'] )->destination_code;
				}
				
			}

			// Iterating through each WC_Order_Item_Product objects
			// https://stackoverflow.com/questions/39401393/how-to-get-woocommerce-order-details
			$quantity = 0;
			$product_weight = 0;
			foreach ($order->get_items() as $item_key => $item ):
			    // Item ID is directly accessible from the $item_key in the foreach loop or
			    $item_id = $item->get_id();

			    ## Using WC_Order_Item_Product methods ##
			    $product      	= $item->get_product(); // Get the WC_Product object
			    $item_type    	= $item->get_type(); // Type of the order item ("line_item")
			    $item_name    	= $item->get_name(); // Name of the product
			    $quantity     	+= $item->get_quantity();  
			    $product_weight = $product->get_weight();
			    $total_weight 	= ( floatval($quantity) * floatval($product_weight) );
			endforeach;

			// Check Payment Method COD or NOT
			$order_payment_method = $order_data['payment_method'];
	        if($order_payment_method == "cod"){
	        	$codflag   = "YES";
	        	if($shipping_name === "JNE - REG (1-2 hari)" || $shipping_name === "JNE - OKE (2-3 hari)" || $shipping_name === "JNE - JTR>250 (3-4 hari)" || $shipping_name === "JNE - JTR<150 (3-4 hari)" || $shipping_name === "JNE - JTR250 (3-4 hari)" || $shipping_name === "JNE - JTR (3-4 hari)") {
		        	$percentage = 0.04;
					$codamount = $order_total * $percentage;
				} elseif($shipping_name === "SICEPAT - REG (1 - 2 hari)" || $shipping_name === "SICEPAT - GOKIL (2 - 3 hari)" || $shipping_name === "SICEPAT - BEST (1 hari)" || $shipping_name === "SICEPAT - KEPO (1 - 2 hari)" || $shipping_name === "SICEPAT - SDS (1 hari)"  || $shipping_name === "SICEPAT - SIUNT (1 - 2 hari)") {
		        	$percentage = 0.04;
					$codamount = $order_total * $percentage;
				} else {
					$codamount = 0;
				}
	        } else {
	        	$codflag   = "N";
	        	$codamount = 0;
	        }

	        // Insurance YES or NO
			$insurance = "N";

			// Default params
			$order_params = array(
				'store_id'		  => $store_id,
				'secret_key'	  => $store_secret_key,
				'buyer_name'	  => $order->get_billing_first_name() .' '. $order->get_billing_last_name(),
				'buyer_email'	  => $order->get_billing_email(),
				'buyer_phone'	  => $order->get_billing_phone(),
				'courier_name'	  => $courier_name,
				'invoice_number'  => $order->get_order_number(),
				'shipper_name'    => get_bloginfo('name'),
		        'shipper_addr1'   => $store_address,
		        'shipper_addr2'   => $store_address_2,
		        'shipper_city'    => $getStoreCityState,
		        'shipper_region'  => $getStoreState,
		        'shipper_zip'     => $store_postcode,
		        'shipper_phone'   => $store_phone,
		        'receiver_name'   => $order_shipping_fullname,
		        'receiver_addr1'  => $order_shipping_address,
		        'receiver_addr2'  => $getDistrict,
		        'receiver_city'   => $getCityState,
		        'receiver_region' => $getState,
		        'receiver_zip'    => $order_shipping_postcode,
		        'receiver_phone'  => $order_billing_phone,
		        'qty'             => $quantity,
		        'weight'          => $total_weight,
		        'goods_desc'      => $item_name,
		        'goods_value'     => $quantity,
		        'goods_type'      => '1',
		        'insurance'       => $insurance,
		        'origin'          => $getOrigin,
		        'destination'     => $destination,
		        'service'         => $shipping_service,
		        'codflag'         => $codflag,
		        'codamount'       => $codamount,
				'invoice_total'   => $order->get_total(),
				'shipping_fee'	  => $order->get_total_shipping(),
				'shipping_status' => 'pending',
				'notes'			  => $order->get_customer_note(),
				'order'			  => $order
			);

			// Send data to API
			$api_scod 	  = new API_SCOD();
			$create_order = $api_scod->post_create_order( $order_params );

			error_log(print_r($order_params, true));

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

	/**
	 * Adding Markup Price COD
	 * Hook via woocommerce_cart_calculate_fees
	 * Reference: https://awhitepixel.com/blog/woocommerce-checkout-add-custom-fees/
	 * @since    1.0.0
	 */
	public function adding_markup_price_cod() {
		if (!is_admin() && !defined('DOING_AJAX')) {
			return;
		}
	 
		$chosen_shipping_method = WC()->session->get('chosen_shipping_methods');
		$chosen_payment_method  = WC()->session->get('chosen_payment_method');
	 			
	 	if($chosen_payment_method === 'cod') {
			if (strpos( $chosen_shipping_method[0], 'scod-shipping_jne_reg19' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_jne_oke19' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_jne_jtrbt250' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_jne_jtrlt150' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_jne_jtr250' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_jne_jtr18' ) !== false) {
				
				foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {
				    // Check if a shipping for the current package exist
				    if ( WC()->session->__isset( 'shipping_for_package_'.$package_id ) ) {
				        // Loop through shipping rates for the current package
				        
				        foreach ( WC()->session->get( 'shipping_for_package_'.$package_id )['rates'] as $shipping_rate_id => $shipping_rate ) {
				            $shipping_method_id   = $shipping_rate->get_method_id(); // The shipping method slug
				            $shipping_instance_id = $shipping_rate->get_instance_id(); // The instance ID
				        }
				    }
				}

				if( $shipping_instance_id ) {
					$shipping_class      = new Shipping_Method( $shipping_instance_id );
					$label_biaya_markup  = $shipping_class->get_option( 'jne_label_markup_cod' );
					$option_biaya_markup = $shipping_class->get_option( 'jne_biaya_markup' );
					
					$percentage = 0.04;
					$percentage_fee = WC()->cart->get_cart_contents_total() * $percentage;
				 	
				 	if($option_biaya_markup === 'no') {
						WC()->cart->add_fee($label_biaya_markup, $percentage_fee);
				 	} else {
				 		return false;
				 	}
				}

			}

			if (strpos( $chosen_shipping_method[0], 'scod-shipping_sicepat_gokil' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_sicepat_reg' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_sicepat_kepo' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_sicepat_sds' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_sicepat_siunt' ) !== false ||
				strpos( $chosen_shipping_method[0], 'scod-shipping_sicepat_best' ) !== false)  {
				
				foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {
				    // Check if a shipping for the current package exist
				    if ( WC()->session->__isset( 'shipping_for_package_'.$package_id ) ) {
				        // Loop through shipping rates for the current package
				        
				        foreach ( WC()->session->get( 'shipping_for_package_'.$package_id )['rates'] as $shipping_rate_id => $shipping_rate ) {
				            $shipping_method_id   = $shipping_rate->get_method_id(); // The shipping method slug
				            $shipping_instance_id = $shipping_rate->get_instance_id(); // The instance ID
				        }
				    }
				}

				if( $shipping_instance_id ) {
					$shipping_class      = new Shipping_Method( $shipping_instance_id );
					$label_biaya_markup  = $shipping_class->get_option( 'sicepat_label_markup_cod' );
					$option_biaya_markup = $shipping_class->get_option( 'sicepat_biaya_markup' );
					
					$percentage = 0.04;
					$percentage_fee = WC()->cart->get_cart_contents_total() * $percentage;
				 	
				 	if($option_biaya_markup === 'no') {
						WC()->cart->add_fee($label_biaya_markup, $percentage_fee);
				 	} else {
				 		return false;
				 	}
				}

			}
		}
	}

	/**
	 * Adding Markup Price COD Payment Ajax
	 * Hook via woocommerce_review_order_before_payment
	 * Reference: https://awhitepixel.com/blog/woocommerce-checkout-add-custom-fees/
	 * @since    1.0.0
	 */
	public function adding_markup_price_cod_payment_ajax() {
?>
	    <script type="text/javascript">
	        (function($){
	            $('form.checkout').on('change', 'input[name^="payment_method"]', function() {
	                $('body').trigger('update_checkout');
	            });
	        })(jQuery);
	    </script>
<?php
	}


}