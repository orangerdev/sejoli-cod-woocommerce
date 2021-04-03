<?php

use SCOD_Shipping\Model\State as State;
use SCOD_Shipping\Model\City as City;
use SCOD_Shipping\Model\District as District;

/**
 * Get indonesia location data based on parameter given
 *
 * @since 	1.0.0
 *
 * @param 	array $location array with state code, city name, district name
 *
 * @return 	(array|false) returns location data array with object, or false if not found
 */
function scod_get_indonesia_data( $location ) {
	
	$location_data = array(
		'state'		=> null,
		'city'		=> null,
		'district'	=> null
	);

	if( $location['state'] ) {
		
		$state =  State::where( 'code', $location['state'] )->first();
		// error_log( __METHOD__ . print_r( $state, true ) );

		if( $state ) {
			$location_data[ 'state' ] = $state;
		}
	}

	if( $location['city'] ) {
		
		$city =  City::where( 'name', $location['city'] )->first();
		// error_log( __METHOD__ . print_r( $city, true ) );

		if( $city ) {
			$location_data[ 'city' ] = $city;
		}
	}

	if( $location['district'] ) {
		
		$district = District::where( 'name', $location['district'] )->first();
		// error_log( __METHOD__ . print_r( $district, true ) );

		if( $district ) {
			$location_data[ 'district' ] = $district;
		}
	}

	return $location_data;
}
