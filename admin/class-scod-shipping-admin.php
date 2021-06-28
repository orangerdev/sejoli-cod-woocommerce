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
// use SCOD_Shipping\scod_shipping_init\Shipping_Method;

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
class Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in SCOD_Shipping_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SCOD_Shipping_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/scod-shipping-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in SCOD_Shipping_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SCOD_Shipping_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/scod-shipping-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'scod_admin_ajax', array(
			'generate_airwaybill' => array(
				'ajaxurl'	=> add_query_arg(array(
						'action' => 'scods-generate-airwaybill'
					), admin_url('admin-ajax.php')
				),
				'nonce'	=> wp_create_nonce('scods-generate-airwaybill')
			)
        ));

	    wp_enqueue_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
	}

	/**
	 * Register Custom Order Status
	 * Hook via init
	 * @since    1.0.0
	 */
	// Add to list of Register Custom WC Order statuses
	function register_new_order_statuses() {
	    register_post_status( 'wc-pickup-shipping', array(
	        'label'                     => __( 'Pickup', 'scod-shipping' ),
	        'public'                    => true,
	        'exclude_from_search'       => false,
	        'show_in_admin_all_list'    => true,
	        'show_in_admin_status_list' => true,
	        'label_count'               => ''
	    ) );
	    register_post_status( 'wc-in-shipping', array(
	        'label'                     => __( 'In-Shipping', 'scod-shipping' ),
	        'public'                    => true,
	        'exclude_from_search'       => false,
	        'show_in_admin_all_list'    => true,
	        'show_in_admin_status_list' => true,
	        'label_count'               => ''
	    ) );
	}

	/**
	 * Custom Order Status
	 * Hook via wc_order_statuses
	 * @since    1.0.0
	 */
	// Add to list of Custom WC Order statuses
	public function add_custom_order_statuses( $order_statuses ) {
	    $new_order_statuses = array();
	 
	    // add new order status after processing
	    foreach ( $order_statuses as $key => $status ) {
	        $new_order_statuses[ $key ] = $status;

	        if ( 'wc-processing' === $key ) {
	        	$new_order_statuses['wc-pickup-shipping'] = __( 'Pickup', 'scod-shipping' );
	            $new_order_statuses['wc-in-shipping'] 	  = __( 'In-Shipping', 'scod-shipping' );
	        }
	    }
	 
	    return $new_order_statuses;
	}

	/**
	 * Custom Field Phone Number for Store Address
	 * Hook via woocommerce_general_settings
	 * @since    1.0.0
	 */
	// https://stackoverflow.com/questions/47362940/add-a-custom-field-to-woocommerce-general-setting-in-store-address-section
	function general_settings_add_shop_phone_field($settings) {
	    $key = 0;

	    foreach( $settings as $values ){
	        $new_settings[$key] = $values;
	        $key++;

	        // Inserting array just after the post code in "Store Address" section
	        if($values['id'] == 'woocommerce_store_postcode'){
	            $new_settings[$key] = array(
	                'title'    => __('Phone Number', 'scod-shipping'),
	                'desc'     => __('Optional phone number of your store.', 'scod-shipping'),
	                'id'       => 'woocommerce_store_phone', // <= The field ID (important)
	                'default'  => '',
	                'type'     => 'text',
	                'desc_tip' => true, // or false
	            );
	            $key++;
	        }
	    }
	    return $new_settings;
	}

	function show_weight_admin_order_item_meta( $item_id, $item, $product ) {
		// IF PRODUCT OR IT'S VARIATION HAS WEIGHT
		if( $product ) { ?>
			<table cellspacing="0" class="display_meta">
				<tbody>
					<tr>
						<th><?php _e( 'Total Weight:', 'scod-shipping' ); ?></th>
						<td><p><?php echo ($item->get_quantity())*($product->get_weight())." ".get_option('woocommerce_weight_unit'); ?></p></td>
					</tr>
				</tbody>
			</table>
		<?php }
	} 

	/**
	 * WooCommerce action to add actions when pickup proces request.
	 *
	 * @since    1.0.0
	 */
	// Order status - `pickup-shipping`
	function add_actions_processing_pickup_order($order_id) {
		if ( ! $order_id ) return;

	    // Get an instance of the WC_Order object
        $order 	  = wc_get_order( $order_id );
        $order_id = $order->get_id();

		// Check payment method
		if( $order->get_payment_method() != 'cod' ) {
			return;
		}

		$status = "pickup";
		update_post_meta( $order_id, '_sejoli_shipping_number', 0);
		$shipNumber = get_post_meta( $order_id, '_sejoli_shipping_number', true );

		// Send data to API
		$api_scod 	  = new API_SCOD();
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
        $order 	  = wc_get_order( $order_id );
        $order_id = $order->get_id();

		// Check payment method
		if( $order->get_payment_method() != 'cod' ) {
			return;
		}

		$status = "on-the-way";
		$shipNumber = get_post_meta( $order_id, '_sejoli_shipping_number', true );

		// Send data to API
		$api_scod 	  = new API_SCOD();
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
	 * WooCommerce action to add actions when order status is completed.
	 *
	 * @since    1.0.0
	 */
	// Order status - `completed`
	function add_actions_processing_completed_order($order_id) {
		if ( ! $order_id ) return;

	    // Get an instance of the WC_Order object
        $order 	  = wc_get_order( $order_id );
        $order_id = $order->get_id();

		// Check payment method
		if( $order->get_payment_method() != 'cod' ) {
			return;
		}

		$status = "completed";
		$shipNumber = get_post_meta( $order_id, '_sejoli_shipping_number', true );

		// Send data to API
		$api_scod 	  = new API_SCOD();
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
	 * WooCommerce action to add actions when cancelled proces request.
	 *
	 * @since    1.0.0
	 */
	// Order status - `cancelled`
	function add_actions_processing_cancelled_order($order_id) {
		if ( ! $order_id ) return;

	    // Get an instance of the WC_Order object
        $order 	  = wc_get_order( $order_id );
        $order_id = $order->get_id();

		// Check payment method
		if( $order->get_payment_method() != 'cod' ) {
			return;
		}

		$status = "cancelled";
		$shipNumber = get_post_meta( $order_id, '_sejoli_shipping_number', true );

		// Send data to API
		$api_scod 	  = new API_SCOD();
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
	 * WooCommerce action to add actions when failed proces request.
	 *
	 * @since    1.0.0
	 */
	// Order status - `failed`
	function add_actions_processing_failed_order($order_id) {
		if ( ! $order_id ) return;

	    // Get an instance of the WC_Order object
        $order 	  = wc_get_order( $order_id );
        $order_id = $order->get_id();

		// Check payment method
		if( $order->get_payment_method() != 'cod' ) {
			return;
		}

		$status = "failed";
		$shipNumber = get_post_meta( $order_id, '_sejoli_shipping_number', true );

		// Send data to API
		$api_scod 	  = new API_SCOD();
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
	 * WooCommerce action to add actions when on-hold proces request.
	 *
	 * @since    1.0.0
	 */
	// Order status - `on-hold`
	function add_actions_processing_on_hold_order($order_id) {
		if ( ! $order_id ) return;

	    // Get an instance of the WC_Order object
        $order 	  = wc_get_order( $order_id );
        $order_id = $order->get_id();

		// Check payment method
		if( $order->get_payment_method() != 'cod' ) {
			return;
		}

		$status = "pending";
		$shipNumber = get_post_meta( $order_id, '_sejoli_shipping_number', true );

		// Send data to API
		$api_scod 	  = new API_SCOD();
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
	 * Add Shipping Number Meta Box in Order Detail Admin
	 * Hook via add_meta_boxes
	 * @since    1.0.0
	 */
	// Adding Meta container admin shop_order pages
    public function add_order_shipping_number_meta_boxes()
    {
        add_meta_box(
	       'sejoli_shipping_number',
	       __('Shipping Information', 'scod-shipping'),
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
    // https://wordpress.stackexchange.com/questions/319346/woocommerce-get-physical-store-address
    public function add_other_fields_for_shipping_number($post)
    {
	    $order 		  = wc_get_order( $post->ID );
	    $order_status = $order->get_status(); // The Order Status
	    $order_data   = $order->get_data(); // The Order Data

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

		// Get Origin Code and Destination Code
	    // https://wordpress.org/support/topic/how-to-get-shipping-method-instance-field-value-from-instance-id/
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

		if($shipping_name == "JNE - REG (1-2 days)") {
			$shipping_service = "REG";
		} elseif($shipping_name == "JNE - OKE (2-3 days)") {
			$shipping_service = "OKE";
		} elseif($shipping_name == "JNE - YES (1 day)") {
			$shipping_service = "YES";
		} else {
			$shipping_service = "JTR";
		}

		// Check selected shipping
		if( $shipping_method_id != 'scod-shipping' ) {
			return;
		}

		$shipping_class_names = WC()->shipping->get_shipping_method_class_names();
		$method_instance 	  = new $shipping_class_names['scod-shipping']( $shipping_instance_id );
		$shipping_origin 	  = $method_instance->get_option( 'shipping_origin' );
		$getOrigin 			  = $method_instance->get_origin_info();
		$packages 			  = WC()->shipping->get_packages();

		$packages['destination']['country']   = $store_country;
		$packages['destination']['state'] 	  = $order_shipping_state;
		$packages['destination']['postcode']  = $order_shipping_postcode;
		$packages['destination']['city'] 	  = $order_shipping_city;
		$packages['destination']['address']   = $order_shipping_address;
		$packages['destination']['address_1'] = $order_shipping_address;
		$packages['destination']['address_2'] = $order_shipping_district;
		$packages['destination']['city2'] 	  = $order_shipping_city;
		$packages['destination']['district']  = $order_shipping_district;
		
		$destination = $method_instance->get_destination_info( $packages['destination'] );

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
		    $total_weight 	= ( $quantity * $product_weight );
		endforeach;

		// Check Payment Method COD or NOT
		$order_payment_method = $order_data['payment_method'];
        if($order_payment_method == "cod"){
        	$codflag   = "YES";
        	$codamount = $order_total;
        } else {
        	$codflag   = "N";
        	$codamount = 0;
        }

        // Insurance YES or NO
		$insurance = "N";

		// SHipping Number Metabox Field
	    $value = get_post_meta( $post->ID, '_sejoli_shipping_number', true );
	    $text  = !empty( $value ) ? esc_attr( $value ) : '';

	    $trace_tracking = API_JNE::set_params()->get_tracking( $text );

	   	if($value){
	   		echo '<h4>'.__('Number Resi:', 'scod-shipping').'</h4>';
	    	echo '<div class="shipping-number" style="font-size:20px;">'.$text.'</div>';

		   	echo '<h4>'.__('Shipping Details:', 'scod-shipping').'</h4>';
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

		   	echo '<h4>'.__('Tracking History:', 'scod-shipping').'</h4>';
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
	   	} else {
	   		if ($order_status == 'pickup-shipping' || $order_status == 'processing') {
		   		echo '<input type="hidden" class="input-text" name="sejoli_shipping_number" id="sejoli_shipping_number" value="' . $text . '" style="width:100%" />';
	    		echo '<input type="hidden" name="sejoli_shipping_number_nonce" value="' . wp_create_nonce() . '">';

	    		echo '<h4>'.__('Number Resi:', 'scod-shipping').'</h4>';
		    	echo '<div id="shipping-number" style="font-size:20px;">'.$text.'</div>';

		   		echo '<a href="#"
		   		data-id="'.$post->ID.'"
		   		data-shipper-name="'.get_bloginfo('name').'"
		   		data-shipper-addr1="'.$store_address.'"
		   		data-shipper-addr2="'.$store_address_2.'"
		   		data-shipper-city="'.$getStoreCityState.'"
		   		data-shipper-region="'.$getStoreState.'"
		   		data-shipper-zip="'.$store_postcode.'"
		   		data-shipper-phone="'.$store_phone.'"
		   		data-receiver-name="'.$order_shipping_fullname.'"
		   		data-receiver-addr1="'.$order_shipping_address.'"
		   		data-receiver-addr2="'.$getDistrict.'"
		   		data-receiver-city="'.$getCityState.'"
		   		data-receiver-region="'.$getState.'"
		   		data-receiver-zip="'.$order_shipping_postcode.'"
		   		data-receiver-phone="'.$order_billing_phone.'"
		   		data-qty="'.$quantity.'"
		   		data-weight="'.$total_weight.'"
		   		data-goodsdesc="'.$item_name.'"
		   		data-goodsvalue="'.$quantity.'"
		   		data-goodstype="1"
		   		data-insurance="'.$insurance.'"
		   		data-origin="'.$getOrigin->code.'"
		   		data-destination="'.$destination->code.'"
		   		data-service="'.$shipping_service.'"
		   		data-codflag="'.$codflag.'"
		   		data-codamount="'.$order_total.'"
		   		class="button button-primary generate-airwaybill">'.__("Request Pickup", "scod-shipping").'</a>';
		   	}
	   	}
    }

    /**
	 * Save Custom Meta Box Shipping Number Field
	 * Hook via save_post
	 * @since    1.0.0
	 */
    // Save the data of the Meta field
    public function save_wc_order_shipping_number_fields( $post_id ) {
	    // Only for shop order
	    $setPostType = isset($_POST['post_type']) ? $_POST['post_type'] : '';
	    if ( 'shop_order' != $setPostType )
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
	    }
	}

	/**
	 * WooCommerce action to generate airwaybill by request.
	 *
	 * @since    1.0.0
	 */
	public function generate_airwaybill($order_id) {
		$params = wp_parse_args( $_POST, array(
            'orderID'  		 => NULL,
            'shipperName' 	 => NULL,
            'shipperAddr1' 	 => NULL,
            'shipperAddr2' 	 => NULL,
            'shipperCity' 	 => NULL,
            'shipperRegion'  => NULL,
            'shipperZip' 	 => NULL,
            'shipperPhone' 	 => NULL,
            'receiverName' 	 => NULL,
            'receiverAddr1'  => NULL,
            'receiverAddr2'  => NULL,
            'receiverCity' 	 => NULL,
            'receiverRegion' => NULL,
            'receiverZip' 	 => NULL,
            'receiverPhone'  => NULL,
            'qty' 			 => NULL,
            'weight' 		 => NULL,
            'goodsDesc' 	 => NULL,
            'goodsValue' 	 => NULL,
            'goodsType' 	 => NULL,
            'insurance'		 => NULL,
            'origin' 		 => NULL,
            'destination' 	 => NULL,
            'service' 		 => NULL,
            'codflag'		 => NULL,
            'codAmount' 	 => NULL,
            'nonce' 		 => NULL
        ));

        $respond  = [
            'valid'   => false,
            'message' => NULL
        ];

        if( wp_verify_nonce( $params['nonce'], 'scods-generate-airwaybill') ) :

            unset( $params['nonce'] );

            $do_update = API_JNE::set_params()->get_airwaybill( $params['orderID'], $params['shipperName'], $params['shipperAddr1'], $params['shipperAddr2'], $params['shipperCity'], $params['shipperRegion'], $params['shipperZip'], $params['shipperPhone'], $params['receiverName'], $params['receiverAddr1'], $params['receiverAddr2'], $params['receiverCity'], $params['receiverRegion'], $params['receiverZip'], $params['receiverPhone'], $params['qty'], $params['weight'], $params['goodsDesc'], $params['goodsValue'], $params['goodsType'], $params['insurance'], $params['origin'], $params['destination'], $params['service'], $params['codflag'], $params['codAmount'] );

            if ( ! is_wp_error( $do_update ) ) {

                $respond['valid']  = true;

            } else {

                $respond['message'] = $do_update->get_error_message();
            }

        endif;

        $order 	  	= wc_get_order( $params['orderID'] );
        $order_id 	= $order->get_id();
        $numberResi = $do_update[0]->cnote_no;

        // echo $respond;
		if ( $order_id > 0 ) {
			if($numberResi){
				update_post_meta( $order_id, '_sejoli_shipping_number', $numberResi );
			} else {
				update_post_meta( $order_id, '_sejoli_shipping_number', 0 );
			}
        }

		// Send update status data to API
        $status 	  = "on-the-way";
		$api_scod 	  = new API_SCOD();
		$update_order = $api_scod->post_update_order( $order_id, $status, $numberResi );

		if( ! is_wp_error( $update_order ) ) {
			// Flag the action as done (to avoid repetitions on reload for example)
			if( $order->save() ) {
				error_log( 'Sync order success ..' );
			}
		}

		wp_update_post(['ID' => $order_id, 'post_status' => 'wc-in-shipping']);

        echo wp_send_json( $numberResi );
	}

	/**
	 * Create Updating Status Order to Complete Based on Shipping Status is Delivered Cron Job
	 *
	 * @since    1.0.0
	 */
	function sejoli_update_status_cron_schedules($schedules)
	{
	    $schedules['once_every_5m'] = array(
	    	'interval' => 300, 
	    	'display' => 'Once every 5 minutes'
	    );
	    return $schedules;
	}

	/**
	 * Set Schedule Event for Updating Status Order to Complete Based on Shipping Status is Delivered Cron Job
	 *
	 * @since    1.0.0
	 */
	function schedule_update_order_to_complete_based_on_delivered_shipping($order_id) {
	  	// Schedule an action if it's not already scheduled
		if ( ! wp_next_scheduled( 'update_status_order_to_completed' ) ) {
		    wp_schedule_event( time(), 'once_every_5m', 'update_status_order_to_completed' );
		}
	}

	/**
	 * Create Updating Status Order to Complete Based on Shipping Status is Delivered Functiona
	 *
	 * @since    1.0.0
	 */
	function update_status_order_to_completed_based_on_delivered_shipping() {
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE 'shop_order' AND post_status LIKE 'wc-in-shipping'");
		
		// Loop through each order post object
		foreach( $results as $result ){
		    $order_id = $result->ID; // The Order ID
		    // Get an instance of the WC_Order Object
		    $order = wc_get_order( $result->ID );
		    $shipping_number = get_post_meta( $order_id, '_sejoli_shipping_number', true );

		    $trace_tracking = API_JNE::set_params()->get_tracking( $shipping_number );

		    // if($trace_tracking->cnote->pod_status == "DELIVERED" && $order_status == "in-shipping"){
		    if($trace_tracking->cnote->pod_status == "DELIVERED"){
		    	// Send update status data to API
		        $status 	  = "completed";
				$api_scod 	  = new API_SCOD();
				$update_order = $api_scod->post_update_order( $order_id, $status, $shipping_number );

				if( ! is_wp_error( $update_order ) ) {
					// Flag the action as done (to avoid repetitions on reload for example)
					if( $order->save() ) {
						error_log( 'Sync order success ..' );
					}
				}

	        	$order->update_status( 'completed', 'order_note' );
		    }
		}
	}
}
