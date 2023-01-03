<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sejoli.co.id
 * @since             1.0.0
 * @package           SCOD_Shipping
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli - WooCommerce Shipping
 * Plugin URI:        https://sejoli.co.id
 * Description:       Plugin Sejoli Shipping untuk WooCommerce shipping.
 * Version:           1.0.0
 * Author:            Sejoli Team
 * Author URI:        https://sejoli.co.id
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       scod-shipping
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SCOD_SHIPPING_VERSION', '1.0.0' );
define( 'SCOD_SHIPPING_DIR',	 plugin_dir_path( __FILE__ ) );
define( 'SCOD_SHIPPING_URL',	 plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-scod-shipping-activator.php
 */
function activate_scod_shipping() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-scod-shipping-activator.php';
	SCOD_Shipping_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-scod-shipping-deactivator.php
 */
function deactivate_scod_shipping() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-scod-shipping-deactivator.php';
	SCOD_Shipping_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_scod_shipping' );
register_deactivation_hook( __FILE__, 'deactivate_scod_shipping' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-scod-shipping.php';

/**
 * Require vendor autoload.php
 * @since 1.0.0
 */
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_scod_shipping() {

	$plugin = new SCOD_Shipping();
	$plugin->run();

}
run_scod_shipping();
