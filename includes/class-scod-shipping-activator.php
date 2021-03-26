<?php

/**
 * Fired during plugin activation
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Scod_Shipping
 * @subpackage Scod_Shipping/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Scod_Shipping
 * @subpackage Scod_Shipping/includes
 * @author     Sejoli Team <orangerdigiart@gmail.com>
 */
class Scod_Shipping_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		Scod_Shipping\Database\State::create_table();
		Scod_Shipping\Database\City::create_table();
		Scod_Shipping\Database\Subdistrict::create_table();
		
		Scod_Shipping\Database\JNE\Origin::create_table();
		Scod_Shipping\Database\JNE\Destination::create_table();
		Scod_Shipping\Database\JNE\Tariff::create_table();
		$seed = new Scod_Shipping\Database\JNE\Seed();
	}

}
