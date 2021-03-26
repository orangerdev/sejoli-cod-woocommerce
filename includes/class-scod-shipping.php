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
 * @package    Scod_Shipping
 * @subpackage Scod_Shipping/includes
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
 * @package    Scod_Shipping
 * @subpackage Scod_Shipping/includes
 * @author     Sejoli Team <orangerdigiart@gmail.com>
 */
class Scod_Shipping {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Scod_Shipping_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * - Scod_Shipping_Loader. Orchestrates the hooks of the plugin.
	 * - Scod_Shipping_i18n. Defines internationalization functionality.
	 * - Scod_Shipping_Admin. Defines all hooks for the admin area.
	 * - Scod_Shipping_Public. Defines all hooks for the public side of the site.
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
		 * The class responsible for database connection
		 */
		require_once SCOD_SHIPPING_DIR . 'includes/class-scod-shipping-database.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SCOD_SHIPPING_DIR . 'admin/class-scod-shipping-admin.php';
		require_once SCOD_SHIPPING_DIR . 'admin/class-scod-shipping-wc.php';

		/**
		 * The class responsible for defining all API actions
		 */
		require_once SCOD_SHIPPING_DIR . 'admin/class-scod-shipping-jne.php';

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

		$this->loader = new Scod_Shipping_Loader();

		Scod_Shipping\Core\Database::connection();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Scod_Shipping_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Scod_Shipping_i18n();

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

		$admin = new Scod_Shipping\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		add_action( 'woocommerce_shipping_init', '\Scod_Shipping\Admin\scod_shipping_init' );

		// $wc = new Scod_Shipping\Admin\Shipping_Method();

		// $this->loader->add_action( 'woocommerce_shipping_init', 			$wc, 'scod_shipping_method_init', 		999 );
		// $this->loader->add_action( 'woocommerce_update_options_shipping_', 	$wc, 'process_admin_options', 		999 );
		// $this->loader->add_filter( 'woocommerce_shipping_methods', 			$wc, 'add_scod_shipping_method' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$public = new Scod_Shipping_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

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
	 * @return    Scod_Shipping_Loader    Orchestrates the hooks of the plugin.
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

}
