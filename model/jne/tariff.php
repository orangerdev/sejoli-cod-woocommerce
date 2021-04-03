<?php
namespace SCOD_Shipping\Model\JNE;

use SCOD_Shipping\Model\Main as Eloquent;

class Tariff extends Eloquent
{
    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $table = 'scod_shipping_jne_tariff';

    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $label = 'JNE';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
	   'jne_origin_id', 'jne_destination_id', 'tariff_data'
	];

    /**
     * Set tariff data.
     *
     * @param  string  $value
     * @return void
     */
    public function setTariffDataAttribute( $value ) {
        $this->attributes['tariff_data'] = serialize( $value );
    }

    /**
     * Get tariff data.
     *
     * @param  string  $value
     * @return string
     */
    public function getTariffDataAttribute( $value ) {
        return unserialize( $value );
    }

    /**
     * Define relationship with Origin model
     *
     * @since    1.0.0
     * @return  string
     */
    public function origin() {
        return $this->belongsTo( 'SCOD_Shipping\Model\JNE\Origin', 'jne_origin_id' );
    }

    /**
     * Define relationship with Destination model
     *
     * @since    1.0.0
     * @return  string
     */
    public function destination() {
        return $this->belongsTo( 'SCOD_Shipping\Model\JNE\Destination', 'jne_destination_id' );
    }

    /**
     * Get tariff label.
     *
     * @param   $rate array of service information
     * @return  string
     */
    public function getLabel( $rate ) {

        $label = array();

        if( $this->label ) {
            $label[] = $this->label;
        }

        if( $rate->service_display ) {
            $label[] = $rate->service_display; 
        }

        $label = implode( " - ", $label );

        if( $rate->etd_from && $rate->etd_thru ) {
            
            $label .= ' (';

            if( $rate->etd_from == 1 && $rate->etd_from == $rate->etd_thru ) {
                $label .= $rate->etd_from;

                if( $rate->times == 'D' ) {
                    $label .= ' day';
                }
            } else {
                $label .= $rate->etd_from . '-' . $rate->etd_thru;

                if( $rate->times == 'D' ) {
                    $label .= ' days';
                }
            }

            $label .= ')'; 
        }

        return $label;
    }

}
