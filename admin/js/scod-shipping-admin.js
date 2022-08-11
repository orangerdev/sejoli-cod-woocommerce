(function( $ ) {
	'use strict';

	$( document ).ready( function( $ ) {
		$('#woocommerce_scod-shipping_shipping_origin').select2({
			allowClear: true
		});

		// $(document).on( "click", '.generate-airwaybill', function(event) {
  //           event.preventDefault();
  //           //Set params
  //           let orderID 	   = $(this).data('id');
  //           let shipperName    = $(this).data('shipper-name');
  //           let shipperAddr1   = $(this).data('shipper-addr1');
  //           let shipperAddr2   = $(this).data('shipper-addr2');
  //           let shipperCity    = $(this).data('shipper-city');
  //           let shipperRegion  = $(this).data('shipper-region');
  //           let shipperZip 	   = $(this).data('shipper-zip');
  //           let shipperPhone   = $(this).data('shipper-phone');
  //           let receiverName   = $(this).data('receiver-name');
  //           let receiverAddr1  = $(this).data('receiver-addr1');
  //           let receiverAddr2  = $(this).data('receiver-addr2');
  //           let receiverCity   = $(this).data('receiver-city');
  //           let receiverRegion = $(this).data('receiver-region');
  //           let receiverZip    = $(this).data('receiver-zip');
  //           let receiverPhone  = $(this).data('receiver-phone');
  //           let qty 		   = $(this).data('qty');
  //           let weight 		   = $(this).data('weight');
  //           let goodsDesc 	   = $(this).data('goodsdesc');
  //           let goodsValue 	   = $(this).data('goodsvalue');
  //           let goodsType 	   = $(this).data('goodstype');
  //           let insurance 	   = $(this).data('insurance');
  //           let origin 		   = $(this).data('origin');
  //           let destination    = $(this).data('destination');
  //           let service 	   = $(this).data('service');
  //           let codflag 	   = $(this).data('codflag');
  //           let codAmount 	   = $(this).data('codamount');
  //           let baseURL 	   = scod_admin_ajax.generate_airwaybill.ajaxurl;
	 //    	let nonce 		   = scod_admin_ajax.generate_airwaybill.nonce;

  //           if (confirm('Apakah Anda yakin ingin melakukan proses request pickup order id #'+orderID+'?')) {
  //               // Save it!
  //               console.log('Requesting Pickup Succesfull.');

  //               //Get detail request
  //               $.ajax({
  //                   dataType: "json",
  //                   url: baseURL,
  //                   type: 'POST',
  //                   data: {
  //                       orderID: orderID,
  //                       shipperName: shipperName,
  //                       shipperAddr1: shipperAddr1,
  //                       shipperAddr2: shipperAddr2,
  //                       shipperCity: shipperCity,
  //                       shipperRegion: shipperRegion,
  //                       shipperZip: shipperZip,
  //                       shipperPhone: shipperPhone,
  //                       receiverName: receiverName,
  //                       receiverAddr1: receiverAddr1,
  //                       receiverAddr2: receiverAddr2,
  //                       receiverCity: receiverCity,
  //                       receiverRegion: receiverRegion,
  //                       receiverZip: receiverZip,
  //                       receiverPhone: receiverPhone,
  //                       qty: qty,
  //                       weight: weight,
  //                       goodsDesc: goodsDesc,
  //                       goodsValue: goodsValue,
  //                       goodsType: goodsType,
  //                       insurance: insurance,
  //                       origin: origin,
  //                       destination: destination,
  //                       service: service,
  //                       codflag: codflag,
  //                       codAmount: codAmount,
  //                       nonce:  nonce
  //                   },
  //                   success : function(response) {
  //                       $('input#sejoli_shipping_number').val(response);
  //                       $('#shipping-number').text(response);
  //                       $('a.generate-airwaybill').hide();
  //                       window.location.reload();
  //                   },
  //                   error: function (request, status, error) {
  //                       console.log(error);
  //                   }
  //               });
  //           } else {
  //               // Do nothing!
  //               console.log('Requesting Pickup Failed.');
  //           }

  //       });
  
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
                        alert('No. Resi: ' + response);
                        $('input#sejoli_shipping_number').val(response);
                        $('#shipping-number').text(response);
                        $('a.generate-airwaybill').hide();
                        window.location.reload();
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

        // $(document).on( "click", '.generate-airwaybill-sicepat', function(event) {
        //     event.preventDefault();

        //     //Set params
        //     let orderID               = $(this).data('id');
        //     let pickup_merchant_name  = $(this).data('pickup_merchant_name');
        //     let pickup_address        = $(this).data('pickup_address');
        //     let pickup_city           = $(this).data('pickup_city');
        //     let pickup_merchant_phone = $(this).data('pickup_merchant_phone');
        //     let pickup_merchant_email = $(this).data('pickup_merchant_email');
        //     let origin_code           = $(this).data('origin_code');
        //     let delivery_type         = $(this).data('delivery_type');
        //     let parcel_category       = $(this).data('parcel_category');
        //     let parcel_content        = $(this).data('parcel_content');
        //     let parcel_qty            = $(this).data('parcel_qty');
        //     let parcel_value          = $(this).data('parcel_value');
        //     let cod_value             = $(this).data('cod_value');
        //     let total_weight          = $(this).data('total_weight');
        //     let shipper_name          = $(this).data('shipper_name');
        //     let shipper_address       = $(this).data('shipper_address');
        //     let shipper_province      = $(this).data('shipper_province');
        //     let shipper_city          = $(this).data('shipper_city');
        //     let shipper_district      = $(this).data('shipper_district');
        //     let shipper_zip           = $(this).data('shipper_zip');
        //     let shipper_phone         = $(this).data('shipper_phone');
        //     let recipient_name        = $(this).data('recipient_name');
        //     let recipient_address     = $(this).data('recipient_address');
        //     let recipient_province    = $(this).data('recipient_province');
        //     let recipient_city        = $(this).data('recipient_city');
        //     let recipient_district    = $(this).data('recipient_district');
        //     let recipient_zip         = $(this).data('recipient_zip');
        //     let recipient_phone       = $(this).data('recipient_phone');
        //     let destination_code      = $(this).data('destination_code');
        //     let baseURL               = scod_admin_ajax.generate_airwaybill_sicepat.ajaxurl;
        //     let nonce                 = scod_admin_ajax.generate_airwaybill_sicepat.nonce;

        //     if (confirm('Apakah Anda yakin ingin melakukan proses request pickup order id #'+orderID+'?')) {
        //         // Save it!
        //         console.log('Requesting Pickup Succesfull.');

        //         //Get detail request
        //         $.ajax({
        //             dataType: "json",
        //             url: baseURL,
        //             type: 'POST',
        //             data: {
        //                 orderID: orderID,
        //                 pickup_merchant_name: pickup_merchant_name,
        //                 pickup_address: pickup_address,
        //                 pickup_city: pickup_city,
        //                 pickup_merchant_phone: pickup_merchant_phone,
        //                 pickup_merchant_email: pickup_merchant_email,
        //                 origin_code: origin_code,
        //                 delivery_type: delivery_type,
        //                 parcel_category: parcel_category,
        //                 parcel_content: parcel_content,
        //                 parcel_qty: parcel_qty,
        //                 parcel_value: parcel_value,
        //                 cod_value: cod_value,
        //                 total_weight: total_weight,
        //                 shipper_name: shipper_name,
        //                 shipper_address: shipper_address,
        //                 shipper_province: shipper_province,
        //                 shipper_city: shipper_city,
        //                 shipper_district: shipper_district,
        //                 shipper_zip: shipper_zip,
        //                 shipper_phone: shipper_phone,
        //                 recipient_name: recipient_name,
        //                 recipient_address: recipient_address,
        //                 recipient_province: recipient_province,
        //                 recipient_city: recipient_city,
        //                 recipient_district: recipient_district,
        //                 recipient_zip: recipient_zip,
        //                 recipient_phone: recipient_phone,
        //                 destination_code: destination_code,
        //                 nonce:  nonce
        //             },
        //             success : function(response) {
        //                 $('input#sejoli_shipping_number').val(response);
        //                 $('#shipping-number').text(response);
        //                 $('a.generate-airwaybill-sicepat').hide();
        //                 window.location.reload();
        //             },
        //             error: function (request, status, error) {
        //                 console.log(error);
        //             }
        //         });
        //     } else {
        //         // Do nothing!
        //         console.log('Requesting Pickup Failed.');
        //     }

        // });
        
	});

})( jQuery );
