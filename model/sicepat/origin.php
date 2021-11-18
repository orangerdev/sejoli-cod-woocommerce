<?php
namespace SCOD_Shipping\Model\SiCepat;

use SCOD_Shipping\Model\Main as Eloquent;

class Origin extends Eloquent
{
    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $table = 'scod_shipping_sicepat_origin';

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
	   'city_id', 'origin_code', 'origin_name'
	];

}
