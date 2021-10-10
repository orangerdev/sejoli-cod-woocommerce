<?php
namespace SCOD_Shipping\Database;

use SCOD_Shipping\Model\State as State;
use SCOD_Shipping\Model\City as City;
use SCOD_Shipping\Model\District as District;
use SCOD_Shipping\Model\JNE\Origin as JNE_Origin;
use SCOD_Shipping\Model\JNE\Destination as JNE_Destination;
use SCOD_Shipping\API\JNE as API_JNE;
use SCOD_Shipping\Model\SiCepat\Origin as SICEPAT_Origin;
use SCOD_Shipping\Model\SiCepat\Destination as SICEPAT_Destination;
use SCOD_Shipping\API\SICEPAT as API_SICEPAT;

Class Seed
{
    public function __construct() {
        $state = State::all();
        if( count( $state ) == 0 ) { $this->state(); }
           
        $city = City::all();
        if( count( $city ) == 0 ) { $this->city(); }

        $district = District::all();
        if( count( $district ) == 0 ) { $this->district(); }

        $origin = JNE_Origin::all();
        if( count( $origin ) == 0 ) { $this->origin(); }

        $destination = JNE_Destination::all();
        if( count( $destination ) == 0 ) { $this->destination(); }

        $sicepat_origin = SICEPAT_Origin::all();
        if( count( $sicepat_origin ) == 0 ) { $this->sicepat_origin(); }

        $sicepat_destination = SICEPAT_Destination::all();
        if( count( $sicepat_destination ) == 0 ) { $this->sicepat_destination(); }
    }

    public function parseJsonFile( $file ) {
        $data = file_get_contents( $file ); 
        return json_decode($data, true);
    }

    public function state() {
        error_log( __METHOD__ . ' seed state data' );

        $file = SCOD_SHIPPING_DIR . 'database/indonesia/data/state.json';
        $jsonData = $this->parseJsonFile( $file );

        foreach( $jsonData as $id => $row ) {
            $data = array(
                'ID'    => $row[ 'id' ],
                'name'  => $row[ 'label' ],
                'code'  => $row[ 'value' ]
            );
            
            State::insert( $data );
        }
    }

    public function city() {
        error_log( __METHOD__ . ' seed city data' );

        $file = SCOD_SHIPPING_DIR . 'database/indonesia/data/city.json';
        $jsonData = $this->parseJsonFile( $file );

        foreach( $jsonData as $id => $row ) {
            $data = array(
                'ID'        => $row[ 'id' ],
                'name'      => $row[ 'value' ],
                'state_id'  => $row[ 'state_id' ]
            );
            
            City::insert( $data );
        }
    }

    public function district() {
        error_log( __METHOD__ . ' seed district data' );

        $file = SCOD_SHIPPING_DIR . 'database/indonesia/data/district.json';
        $jsonData = $this->parseJsonFile( $file );

        foreach( $jsonData as $id => $row ) {
            $data = array(
                'ID'        => $row[ 'id' ],
                'name'      => $row[ 'value' ],
                'city_id'   => $row[ 'city_id' ]
            );
            
            District::insert( $data );
        }
    }

    public function origin() {
        error_log( __METHOD__ . ' seed origin data' );

        $file = SCOD_SHIPPING_DIR . 'database/indonesia/data/city.json';
        $jsonData = $this->parseJsonFile( $file );
        $origins = API_JNE::set_params()->get_origin();

        foreach($origins as $key => $val){
            $isReserved = false; 

            foreach($jsonData as $val2){
                if($val->City_Name === $val2['value']){
                    $isReserved = true;
                    break;
                }
            }

            if (true === $isReserved) {
                $set_city_id = $val2[ 'id' ]; 
            } else {
                $set_city_id = 0; 
            }

            $data = array(
                'city_id' => $set_city_id,
                'name' => $val->City_Name,
                'code' => $val->City_Code
            );

            JNE_Origin::insert( $data );
        }
    }

    public function destination() {
        error_log( __METHOD__ . ' seed destination data' );

        $file = SCOD_SHIPPING_DIR . 'database/indonesia/data/district.json';
        $jsonData = $this->parseJsonFile( $file );
        $fileDestinations = SCOD_SHIPPING_DIR . 'database/jne/data/jne_destination-updated.json';
        $destinations = $this->parseJsonFile( $fileDestinations );

        foreach($destinations as $key => $val){
            $isReserved = false; 

            foreach($jsonData as $val2){
                if($val[ 'district_name' ] === $val2['value']){
                    $isReserved = true;
                    break;
                }
            }

            if (true === $isReserved) {
                $set_city_id = $val2[ 'city_id' ]; 
                $set_district_id = $val2[ 'id' ]; 
            } else {
                $set_city_id = 0; 
                $set_district_id = 0;
            }

            $data = array(
                'ID'            => $val[ 'ID' ],
                'city_id'       => $val[ 'city_id' ],
                'district_id'   => $val[ 'district_id' ],
                'city_name'     => $val[ 'city_name' ],
                'district_name' => $val[ 'district_name' ],
                'code'          => $val[ 'code' ]
            );
            
            JNE_Destination::insert( $data );
        }
    }

    public function sicepat_origin() {
        error_log( __METHOD__ . ' seed origin data' );

        $file = SCOD_SHIPPING_DIR . 'database/indonesia/data/city.json';
        $jsonData = $this->parseJsonFile( $file );
        $origins = API_SICEPAT::set_params()->get_origin();

        foreach($origins as $key => $val){
            $isReserved = false; 

            foreach($jsonData as $val2){
                if($val->origin_name === $val2['value']){
                    $isReserved = true;
                    break;
                }
            }

            if (true === $isReserved) {
                $set_city_id = $val2[ 'id' ]; 
            } else {
                $set_city_id = 0; 
            }

            $data = array(
                'city_id'     => $set_city_id,
                'origin_code' => $val->origin_code,
                'origin_name' => $val->origin_name
            );

            SICEPAT_Origin::insert( $data );
        }
    }

    public function sicepat_destination() {
        error_log( __METHOD__ . ' seed destination data' );

        $file = SCOD_SHIPPING_DIR . 'database/indonesia/data/district.json';
        $jsonData = $this->parseJsonFile( $file );
        $destinations = API_SICEPAT::set_params()->get_destination();

        foreach($destinations as $key => $val){
            $isReserved = false;

            foreach($jsonData as $val2){
                if($val->subdistrict === $val2['value']){
                    $isReserved = true;
                    break;
                }
            }

            if (true === $isReserved) {
                $set_city_id = $val2[ 'city_id' ]; 
                $set_district_id = $val2[ 'id' ]; 
            } else {
                $set_city_id = 0; 
                $set_district_id = 0;
            }

            $data = array(
                'city_id'          => $set_city_id,
                'district_id'      => $set_district_id,
                'subdistrict'      => $val->subdistrict,
                'city'             => $val->city,
                'province'         => $val->province,
                'destination_code' => $val->destination_code
            );

            SICEPAT_Destination::insert( $data );
        }
    }
}
