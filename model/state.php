<?php
namespace SCOD_Shipping\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class State extends Eloquent
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

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
	   'label', 'value'
	];

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->table = self::get_table();
	}

	/**
	 * Get model database table
	 *
	 * @since    1.0.0
	 * @return 	string
	 */
	public static function get_table() {

		global $wpdb;

        $prefix = $wpdb->prefix;

        return $prefix . 'scod_shipping_state';
	}

	public function city() {

		return $this->hasMany( 'SCOD_Shipping\Model\City' );
	}
}