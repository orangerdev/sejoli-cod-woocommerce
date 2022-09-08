(function( $ ) {
	'use strict';

	$( document ).ready( function( $ ) {
		$('#woocommerce_scod-shipping_shipping_origin').select2({
			allowClear: true
		});
  
        $(document).on( "click", '.generate-airwaybill', function(event) {
            event.preventDefault();
            //Set params
            let orderID             = $(this).data('id');
            let orderDate           = $(this).data('order-date');
            let expedition          = $(this).data('expedition');
            let shipperName         = $(this).data('shipper-name');
            let shipperPhone        = $(this).data('shipper-phone');
            let shipperAddress      = $(this).data('shipper-address');
            let shipperCity         = $(this).data('shipper-city');
            let shipperProvince     = $(this).data('shipper-province');
            let shipperDistrict     = $(this).data('shipper-district');
            let shipperZip          = $(this).data('shipper-zip');
            let receiverName        = $(this).data('receiver-name');
            let receiverPhone       = $(this).data('receiver-phone');
            let receiverAddress     = $(this).data('receiver-address');
            let receiverEmail       = $(this).data('receiver-email');
            let receiverCity        = $(this).data('receiver-city');
            let receiverZip         = $(this).data('receiver-zip');
            let receiverProvince    = $(this).data('receiver-province');
            let receiverDistrict    = $(this).data('receiver-district');
            let receiverSubdistrict = $(this).data('receiver-subdistrict');
            let origin              = $(this).data('origin');
            let destination         = $(this).data('destination');
            let branch              = $(this).data('branch');
            let service             = $(this).data('service');
            let weight              = $(this).data('weight');
            let qty                 = $(this).data('qty');
            let description         = $(this).data('description');
            let category            = $(this).data('category');
            let packageAmount       = $(this).data('package-amount');
            let insurance           = $(this).data('insurance');
            let note                = $(this).data('note');
            let codflag             = $(this).data('codflag');
            let codAmount           = $(this).data('codamount');
            let shippingPrice       = $(this).data('shipping-price');
            let baseURL             = scod_admin_ajax.generate_airwaybill.ajaxurl;
            let nonce               = scod_admin_ajax.generate_airwaybill.nonce;

            if (confirm('Apakah Anda yakin ingin melakukan proses request pickup order id #'+orderID+'?')) {
                // Save it!
                console.log('Requesting Pickup Succesfull.');

                //Get detail request
                $.ajax({
                    dataType: "json",
                    url: baseURL,
                    type: 'POST',
                    data: {
                        orderID: orderID,
                        orderDate: orderDate,
                        expedition: expedition,
                        shipperName: shipperName,
                        shipperPhone: shipperPhone,
                        shipperAddress: shipperAddress,
                        shipperCity: shipperCity,
                        shipperProvince: shipperProvince,
                        shipperDistrict: shipperDistrict,
                        shipperZip: shipperZip,
                        receiverName: receiverName,
                        receiverPhone: receiverPhone,
                        receiverAddress: receiverAddress,
                        receiverEmail: receiverEmail,
                        receiverCity: receiverCity,
                        receiverZip: receiverZip,
                        receiverProvince: receiverProvince,
                        receiverDistrict: receiverDistrict,
                        receiverSubdistrict: receiverSubdistrict,
                        origin: origin,
                        destination: destination,
                        branch: branch,
                        service: service,
                        weight: weight,
                        qty: qty,
                        packageAmount: packageAmount,
                        description: description,
                        category: category,
                        insurance: insurance,
                        note: note,
                        codflag: codflag,
                        codAmount: codAmount,
                        shippingPrice: shippingPrice,
                        nonce: nonce
                    },
                    success : function(response) {
                        if(response > 0) {
                            alert('No. Resi: ' + response);
                            $('input#sejoli_shipping_number').val(response);
                            $('#shipping-number').text(response);
                            $('a.generate-airwaybill').hide();
                            window.location.reload();
                        } else {
                            alert('Gagal Mendapatkan No Resi!');
                            window.location.reload();
                        }
                    },
                    error: function (request, status, error) {
                        console.log(error);
                    }
                });
            } else {
                // Do nothing!
                console.log('Requesting Pickup Failed.');
            }

        });

	});

})( jQuery );
