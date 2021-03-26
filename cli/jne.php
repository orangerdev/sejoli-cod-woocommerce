<?php

use Scod_Shipping\API\JNE as API_JNE;

// Add custom commands
if ( defined( 'WP_CLI' ) && WP_CLI ) {

    WP_CLI::add_command( 'scod-jne-get-tariff',         'get_tariff' );
    WP_CLI::add_command( 'scod-jne-get-origin',         'get_origin' );
    WP_CLI::add_command( 'scod-jne-get-destination',    'get_destination' );
}

/**
 * Get tariff
 *
 * @param array $args
 * @param array $assoc_args
 *
 * Usage: wp scod-jne-get-tariff --origin=CGK10000 --destination=BDO10000 --weight=1
 */
function get_tariff( $args = array(), $assoc_args = array() ) {
    
    // Get arguments.
    $arguments = wp_parse_args(
        $assoc_args,
        array(
            'origin'           => '',
            'destination'      => '',
            'weight'           => 1
        )
    );

    // Check if arguments are alright.
    if ( ! empty( $arguments['origin'] ) && ! empty( $arguments['destination'] ) ) {
       
        $api_jne = new API_JNE();
        $get_tariff = $api_jne->get_tariff( $arguments['origin'], $arguments['destination'], $arguments['weight'] );

        if( $get_tariff ) {

            // Show success message.
            WP_CLI::success( print_r( $get_tariff, true ) );

        } else {

            // Arguments not okay, show an error.
            WP_CLI::error( 'Fail to get result.' );

        }

    } else {

        // Arguments not okay, show an error.
        WP_CLI::error( 'Invalid arguments.' );

    }
}

/**
 * Get origin
 *
 * @param array $args
 * @param array $assoc_args
 *
 * Usage: wp scod-jne-get-origin
 */
function get_origin( $args = array(), $assoc_args = array() ) {
       
    $api_jne = new API_JNE();
    $get_origin = $api_jne->get_origin();

    if( $get_origin ) {

        // Show success message.
        WP_CLI::success( print_r( $get_origin, true ) );

    } else {

        // Arguments not okay, show an error.
        WP_CLI::error( 'Fail to get result.' );

    }
}

/**
 * Get destination
 *
 * @param array $args
 * @param array $assoc_args
 *
 * Usage: wp scod-jne-get-destination
 */
function get_destination( $args = array(), $assoc_args = array() ) {
       
    $api_jne = new API_JNE();
    $get_destination = $api_jne->get_destination();

    if( $get_destination ) {

        // Show success message.
        WP_CLI::success( print_r( $get_destination, true ) );

    } else {

        // Arguments not okay, show an error.
        WP_CLI::error( 'Fail to get result.' );

    }
}