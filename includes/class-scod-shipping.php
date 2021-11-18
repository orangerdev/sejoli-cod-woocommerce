<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    SCOD_Shipping
 * @subpackage SCOD_Shipping/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    SCOD_Shipping
 * @subpackage SCOD_Shipping/includes
 * @author     Sejoli Team <orangerdigiart@gmail.com>
 */
class SCOD_Shipping {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      SCOD_Shipping_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SCOD_SHIPPING_VERSION' ) ) {
			$this->version = SCOD_SHIPPING_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'scod-shipping';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - SCOD_Shipping_Loader. Orchestrates the hooks of the plugin.
	 * - SCOD_Shipping_i18n. Defines internationalization functionality.
	 * - SCOD_Shipping_Admin. Defines all hooks for the admin area.
	 * - SCOD_Shipping_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SCOD_SHIPPING_DIR . 'includes/class-scod-shipping-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SCOD_SHIPPING_DIR . 'includes/class-scod-shipping-i18n.php';

		/**
		 * The class responsible for integrating with database
		 * @var [type]
		 */
		require_once SCOD_SHIPPING_DIR . '/includes/class-scod-shipping-database.php';

		/**
		 * The class responsible for creating database tables.
		 */
		require_once SCOD_SHIPPING_DIR . 'database/main.php';
		require_once SCOD_SHIPPING_DIR . 'database/indonesia/state.php';
		require_once SCOD_SHIPPING_DIR . 'database/indonesia/city.php';
		require_once SCOD_SHIPPING_DIR . 'database/indonesia/district.php';
		require_once SCOD_SHIPPING_DIR . 'database/jne/origin.php';
		require_once SCOD_SHIPPING_DIR . 'database/jne/destination.php';
		require_once SCOD_SHIPPING_DIR . 'database/jne/tariff.php';
		require_once SCOD_SHIPPING_DIR . 'database/sicepat/origin.php';
		require_once SCOD_SHIPPING_DIR . 'database/sicepat/destination.php';
		require_once SCOD_SHIPPING_DIR . 'database/sicepat/tariff.php';

		/**
		 * The class responsible for database seed.
		 */
		require_once SCOD_SHIPPING_DIR . 'database/indonesia/seed.php';

		/**
		 * The class responsible for database models.
		 */
		require_once SCOD_SHIPPING_DIR . 'model/main.php';
		require_once SCOD_SHIPPING_DIR . 'model/state.php';
		require_once SCOD_SHIPPING_DIR . 'model/city.php';
		require_once SCOD_SHIPPING_DIR . 'model/district.php';
		require_once SCOD_SHIPPING_DIR . 'model/jne/origin.php';
		require_once SCOD_SHIPPING_DIR . 'model/jne/destination.php';
		require_once SCOD_SHIPPING_DIR . 'model/jne/tariff.php';
		require_once SCOD_SHIPPING_DIR . 'model/sicepat/origin.php';
		require_once SCOD_SHIPPING_DIR . 'model/sicepat/destination.php';
		require_once SCOD_SHIPPING_DIR . 'model/sicepat/tariff.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SCOD_SHIPPING_DIR . 'admin/class-scod-shipping-admin.php';

		/**
		 * The class responsible for defining all related WooCommerce functions.
		 */
		require_once SCOD_SHIPPING_DIR . 'includes/class-scod-shipping-method.php';

		/**
		 * The class responsible for defining API related functions.
		 */
		require_once SCOD_SHIPPING_DIR . 'includes/class-scod-shipping-api.php';
		require_once SCOD_SHIPPING_DIR . 'api/class-scod-shipping-jne.php';
		require_once SCOD_SHIPPING_DIR . 'api/class-scod-shipping-sicepat.php';
		require_once SCOD_SHIPPING_DIR . 'api/class-scod-shipping-saas.php';

		// Custom WebHook & Callback
		require_once SCOD_SHIPPING_DIR . 'includes/class-scod-order-webhook.php';

		/**
		 * The class responsible for defining CLI command and function
		 * side of the site.
		 */
		require_once SCOD_SHIPPING_DIR . 'cli/jne.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SCOD_SHIPPING_DIR . 'public/class-scod-shipping-public.php';

		$this->loader = new SCOD_Shipping_Loader();

		SCOD_Shipping\DBIntegration::connection();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the SCOD_Shipping_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new SCOD_Shipping_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$admin = new SCOD_Shipping\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		add_action( 'woocommerce_shipping_init', 'SCOD_Shipping\scod_shipping_init' );
		$this->loader->add_filter( 'woocommerce_shipping_methods', $this, 'register_scod_method' );

		// Requesting Processing
		$this->loader->add_action( 'woocommerce_order_status_processing', $admin, 'add_actions_processing_order');

		// Requesting In-Shipping
		$this->loader->add_action( 'woocommerce_order_status_in-shipping', $admin, 'add_actions_processing_in_shipping_order');

		// Requesting Completed
		$this->loader->add_action( 'woocommerce_order_status_completed', $admin, 'add_actions_processing_completed_order');

		// Requesting Cancelled
		$this->loader->add_action( 'woocommerce_order_status_cancelled', $admin, 'add_actions_processing_cancelled_order');

		// Requesting Failed
		$this->loader->add_action( 'woocommerce_order_status_failed', $admin, 'add_actions_processing_failed_order');

		// Requesting On-Hold
		$this->loader->add_action( 'woocommerce_order_status_on-hold', $admin, 'add_actions_processing_on_hold_order');

		// Store Address Custom Fields
		$this->loader->add_filter('woocommerce_general_settings', $admin, 'general_settings_add_shop_phone_field');

		// Custom Order Item Meta
		$this->loader->add_action( 'woocommerce_after_order_itemmeta', $admin, 'show_weight_admin_order_item_meta', 10, 3 );

		// Custom Order Status Woocommerce
		$this->loader->add_action( 'init', $admin, 'register_new_order_statuses' );
		$this->loader->add_filter( 'wc_order_statuses', $admin, 'add_custom_order_statuses' );

		// Custom Order Meta Box
		// https://stackoverflow.com/questions/37772912/woocommerce-add-custom-metabox-to-admin-order-page
		$this->loader->add_action( 'add_meta_boxes', $admin, 'add_order_shipping_number_meta_boxes' );
		$this->loader->add_action( 'save_post', $admin, 'save_wc_order_shipping_number_fields', 10, 1 );
		$this->loader->add_action( 'woocommerce_admin_order_data_after_billing_address', $admin, 'shipping_number_field_display_admin_order_meta', 10, 1 );

		// Ajax Generate Airwaybill
		$this->loader->add_action( 'wp_ajax_scods-generate-airwaybill', $admin, 'generate_airwaybill', 1);
		$this->loader->add_action( 'wp_ajax_nopriv_scods-generate-airwaybill', $admin, 'generate_airwaybill',	1);
		$this->loader->add_action( 'wp_ajax_scods-generate-airwaybill-sicepat', $admin, 'generate_airwaybill_sicepat', 1);
		$this->loader->add_action( 'wp_ajax_nopriv_scods-generate-airwaybill-sicepat', $admin, 'generate_airwaybill_sicepat',	1);
		
		// Setting Cron Jobs Update Status Completed based on Shipping Status is Delivered
		$this->loader->add_filter( 'cron_schedules', $admin, 'sejoli_update_status_cron_schedules' );
		$this->loader->add_action( 'admin_init', $admin, 'schedule_update_order_to_complete_based_on_delivered_shipping' );
		$this->loader->add_action( 'update_status_order_to_completed', $admin, 'update_status_order_to_completed_based_on_delivered_shipping' );

		$scod_api = new SCOD_Shipping\API\SCOD( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( 'http_request_args', $scod_api, 'disable_ssl_verify', 10, 2 ); //local dev purpose only
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$public = new SCOD_Shipping\Front( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );
		
		if( !is_admin() ){
			$this->loader->add_filter( 'woocommerce_states', $public, 'checkout_state_dropdown' );

			// Custom Shipping Number Meta Box
			$this->loader->add_action( 'woocommerce_order_details_after_order_table', $public, 'display_sejoli_shipping_number_in_order_view', 10, 1 );

			// Disable Calculate Shipping Field
			add_filter( 'woocommerce_shipping_calculator_enable_country', '__return_true' );
			add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_false' );
			add_filter( 'woocommerce_shipping_calculator_enable_state', '__return_false' );
			add_filter( 'woocommerce_shipping_calculator_enable_postcode', '__return_false' );
		}

		$this->loader->add_filter( 'woocommerce_checkout_fields', 				$public, 'scod_checkout_fields' );
		$this->loader->add_filter( 'woocommerce_default_address_fields', 		$public, 'override_locale_fields' );
		$this->loader->add_filter( 'woocommerce_after_checkout_billing_form', 	$public, 'checkout_country_hidden_fields_replacement' );
		$this->loader->add_action( 'wp_enqueue_scripts', 						$public, 'enqueue_styles' );
		$this->loader->add_action( 'woocommerce_available_payment_gateways', 	$public, 'scods_checkout_available_payments', 1);
		$this->loader->add_filter( 'woocommerce_package_rates', 				$public, 'scods_checkout_available_shippings', 10, 2 );
		$this->loader->add_action( 'wp_ajax_scods-get-city-by-state', 			$public, 'get_city_by_state',		1);
		$this->loader->add_action( 'wp_ajax_nopriv_scods-get-city-by-state',	$public, 'get_city_by_state',		1);
		$this->loader->add_action( 'wp_ajax_scods-get-district-by-city', 		$public, 'get_district_by_city',	1);
		$this->loader->add_action( 'wp_ajax_nopriv_scods-get-district-by-city',	$public, 'get_district_by_city',	1);
		$this->loader->add_action( 'woocommerce_thankyou',						$public, 'send_order_data_to_api',	10, 1 );
		
		// Formatted Billing and Shipping Address
		$this->loader->add_filter( 'woocommerce_order_formatted_billing_address', 		  $public, 'woo_custom_order_formatted_billing_address', 10, 2 );
		$this->loader->add_filter( 'woocommerce_order_formatted_shipping_address', 	      $public, 'woo_custom_order_formatted_shipping_address', 10, 2 );
		$this->loader->add_filter( 'woocommerce_my_account_my_address_formatted_address', $public, 'filter_woocommerce_my_account_my_address_formatted_address', 10, 3 ); 

		// Add New Package Destination Woocommerce
		$this->loader->add_action( 'wp_footer', 						 $public, 'checkout_send_custom_package_via_ajax_js' );
		$this->loader->add_action( 'wp_ajax_city2', 					 $public, 'set_city2_to_wc_session' );
		$this->loader->add_action( 'wp_ajax_nopriv_city2', 				 $public, 'set_city2_to_wc_session' );
		$this->loader->add_filter( 'woocommerce_checkout_get_value', 	 $public, 'update_city2_checkout_fields_values', 10, 2 );
		$this->loader->add_filter( 'woocommerce_cart_shipping_packages', $public, 'add_city2_to_destination_shipping_package' );
		$this->loader->add_action( 'woocommerce_checkout_order_created', $public, 'remove_city2_custom_wc_session_variable' );
		$this->loader->add_action( 'wp_ajax_district', 					 $public, 'set_district_to_wc_session' );
		$this->loader->add_action( 'wp_ajax_nopriv_district', 			 $public, 'set_district_to_wc_session' );
		$this->loader->add_filter( 'woocommerce_checkout_get_value', 	 $public, 'update_district_checkout_fields_values', 10, 2 );
		$this->loader->add_filter( 'woocommerce_cart_shipping_packages', $public, 'add_district_to_destination_shipping_package' );
		$this->loader->add_action( 'woocommerce_checkout_order_created', $public, 'remove_district_custom_wc_session_variable' );

		// Shortcode
		$this->loader->add_action( 'init', $public, 'sejoli_init_tracking_shipment_shortcode' );
		$this->loader->add_action( 'wp_ajax_nopriv_sejoli_shipment_tracking_result', $public, 'sejoli_shipment_tracking_result' );
        $this->loader->add_action( 'wp_ajax_sejoli_shipment_tracking_result', $public, 'sejoli_shipment_tracking_result' );

		// Markup COD
		$this->loader->add_action( 'woocommerce_cart_calculate_fees', $public, 'adding_markup_price_cod' );
		$this->loader->add_action( 'woocommerce_review_order_before_payment', $public, 'adding_markup_price_cod_payment_ajax' );
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    SCOD_Shipping_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register shipping method to WooCommerce.
	 *
	 * @since 1.0.0
	 *
	 * @param array $methods Registered shipping methods.
	 */
	public function register_scod_method( $methods ) {
	    $methods[ 'scod-shipping' ] = new \SCOD_Shipping\Shipping_Method();
	    return $methods;
	}

}
