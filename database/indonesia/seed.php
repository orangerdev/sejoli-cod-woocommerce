<?php
namespace SCOD_Shipping\Database;

use SCOD_Shipping\Model\State as State;
use SCOD_Shipping\Model\City as City;
use SCOD_Shipping\Model\District as District;
use SCOD_Shipping\Model\JNE\Origin as JNE_Origin;
use SCOD_Shipping\API\JNE as API_JNE;

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
                'ID'        => $row[ 'id' ],
                'name'      => $row[ 'label' ],
                'code'      => $row[ 'value' ]
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

        $origins = API_JNE::set_params()->get_origin();
        
        foreach( $origins as $id => $row ) {

            $data = array(
                'code'     => $row->City_Code,
                'name'     => $row->City_Name
            );
            
            JNE_Origin::insert( $data );
        }
    }

}
