<?php
namespace SCOD_Shipping\Model\SiCepat;

use SCOD_Shipping\Model\Main as Eloquent;

class Destination extends Eloquent
{
    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $table = 'scod_shipping_sicepat_destination';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
	   'city_id', 'district_id', 'subdistrict', 'city', 'province', 'destination_code'
	];

    /**
     * Get static table name with no prefix
     *
     * @since   1.0.0
     * @return  string
     */
    public function getTableName() {
        
        return $this->table;
    
    }

}
