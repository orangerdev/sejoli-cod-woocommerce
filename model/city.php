<?php
namespace SCOD_Shipping\Model;

use SCOD_Shipping\Model\Main as Eloquent;

class City extends Eloquent
{
    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $table = 'scod_shipping_city';

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
	   'name', 'state_id'
	];

    /**
     * Define relationship with State model
     *
     * @since    1.0.0
     * @return  string
     */
	public function state() {
		return $this->belongsTo( 'SCOD_Shipping\Model\State', 'state_id' );
	}

    /**
     * Define relationship with District model
     *
     * @since    1.0.0
     * @return  string
     */
    public function districts() {
        return $this->hasMany( 'SCOD_Shipping\Model\District', 'city_id' );
    }

}
