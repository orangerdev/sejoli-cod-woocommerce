<?php

/**
 * Fired during plugin activation
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    SCOD_Shipping
 * @subpackage SCOD_Shipping/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    SCOD_Shipping
 * @subpackage SCOD_Shipping/includes
 * @author     Sejoli Team <orangerdigiart@gmail.com>
 */
class SCOD_Shipping_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		SCOD_Shipping\Database\State::create_table();
		SCOD_Shipping\Database\City::create_table();
		SCOD_Shipping\Database\District::create_table();
		
		SCOD_Shipping\Database\JNE\Origin::create_table();
		SCOD_Shipping\Database\JNE\Destination::create_table();
		SCOD_Shipping\Database\JNE\Tariff::create_table();

		SCOD_Shipping\Database\SiCepat\Origin::create_table();
		SCOD_Shipping\Database\SiCepat\Destination::create_table();
		SCOD_Shipping\Database\SiCepat\Tariff::create_table();

		// $seed = new SCOD_Shipping\Database\Seed();

	}

}
