<?php
namespace SCOD_Shipping\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use SCOD_Shipping\Model\State as State;
use SCOD_Shipping\Model\City as City;
use SCOD_Shipping\Model\Subdistrict as Subdistrict;

Class Seed extends \SCOD_Shipping\Database
{
    public function __construct() {

        $state = Capsule::table( State::get_table() )->get();
        if( count( $state ) == 0 ) { $this->state(); }
           
        $city = Capsule::table( City::get_table() )->get();
        if( count( $city ) == 0 ) { $this->city(); }

        $subdistrict = Capsule::table( Subdistrict::get_table() )->get();
        if( count( $subdistrict ) == 0 ) { $this->subdistrict(); }
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
                'label'     => $row[ 'label' ],
                'value'     => $row[ 'value' ]
            );
            
            Capsule::table( State::get_table() )->insertGetId( $data );
        }
    }

    public function city() {
        error_log( __METHOD__ . ' seed city data' );

        $file = SCOD_SHIPPING_DIR . 'database/indonesia/data/city.json';
        $jsonData = $this->parseJsonFile( $file );

        foreach( $jsonData as $id => $row ) {

            $data = array(
                'ID'        => $row[ 'id' ],
                'value'     => $row[ 'value' ],
                'state_id'  => $row[ 'state_id' ]
            );
            
            Capsule::table( City::get_table() )->insertGetId( $data );
        }
    }

    public function subdistrict() {
        error_log( __METHOD__ . ' seed subdistrict data' );

        $file = SCOD_SHIPPING_DIR . 'database/indonesia/data/subdistrict.json';
        $jsonData = $this->parseJsonFile( $file );

        foreach( $jsonData as $id => $row ) {

            $data = array(
                'ID'        => $row[ 'id' ],
                'value'     => $row[ 'value' ],
                'city_id'   => $row[ 'city_id' ]
            );
            
            Capsule::table( Subdistrict::get_table() )->insertGetId( $data );
        }
    }

}
