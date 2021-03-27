<?php
namespace SCOD_Shipping\Database\JNE;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class that responsible to database-functions for City data
 * @since   1.0.0
 */
Class Destination extends \SCOD_Shipping\Database
{
    /**
     * Table name
     * @since   1.0.0
     */
    static protected $table       = 'scod_shipping_jne_destination';

    /**
     * Create table if not exists
     * @return void
     */
    static public function create_table()
    {
        parent::$table = self::$table;

        if( ! Capsule::schema()->hasTable( self::table() ) ):

            Capsule::schema()->create( self::table(), function( $table ){

                $table->increments  ('ID');
                $table->integer     ('province_id');
                $table->string      ('city_id');
                $table->integer     ('subdistrict_id');
                $table->string      ('zip_code');

            });

        endif;
    }

}
