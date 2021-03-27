<?php
namespace SCOD_Shipping;

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
			$this->title                = $this->get_option( 'title' );
			$this->tax_status 			= $this->get_option( 'tax_status' ); 

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
        			'description' 	=> __( 'Please enter your registered store secret key.', 'scod-shipping' ),
        			'default' 		=> '',
        		),
        		'title' => array(
        			'title' 		=> __( 'Sejoli COD Shipping', 'scod-shipping' ),
        			'type' 			=> 'text',
        			'description' 	=> __( 'This controls the title which the user sees during checkout.', 'scod-shipping' ),
        			'default'		=> __( 'Sejoli COD Shipping', 'scod-shipping' ),
        			'desc_tip'		=> true
        		),
				'tax_status'                => array(
					'title'   		=> __( 'Tax Status', 'scod-shipping' ),
        			'description' 	=> __( 'This controls whether tax should be calculated for this shipping.', 'scod-shipping' ),
					'type'    		=> 'select',
					'default' 		=> 'none',
					'options' 		=> array(
						'taxable' 	=> __( 'Taxable', 'scod-shipping' ),
						'none'    	=> _x( 'None', 'Tax status', 'scod-shipping' ),
					),
				),
			);

			$this->instance_form_fields = $settings;
		}

	    /**
	     * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
	     *
	     * @param array $package default: array()
	     */
	    public function calculate_shipping( $package = array() ) {

	        // $package[destination] => Array
	        // (
	        //     [country] => ID
	        //     [state] => LA
	        //     [postcode] => 12450
	        //     [city] => Kabupaten Lampung Selatan
	        //     [address] => Jl Damai Musyawarah RT/RW 11/03 no 69 Pondok Labu, Cilandak, Jakarta Selatan 12450
	        //     [address_1] => Jl Damai Musyawarah RT/RW 11/03 no 69 Pondok Labu, Cilandak, Jakarta Selatan 12450
	        //     [address_2] => Candipuro
	        // )

	        $this->add_rate( array(
				'id'    => $this->id . $this->instance_id,
				'label' => $this->title,
				'cost'  => 100,
			) );
	    }

	}

}
