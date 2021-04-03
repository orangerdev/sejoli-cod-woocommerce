<?php
namespace SCOD_Shipping\Model\JNE;

use SCOD_Shipping\Model\Main as Eloquent;

class Origin extends Eloquent
{
    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $table = 'scod_shipping_jne_origin';

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
	   'code', 'name'
	];

}
