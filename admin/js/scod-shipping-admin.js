(function( $ ) {
	'use strict';

	$( document ).ready( function( $ ) {
		$('#woocommerce_scod-shipping_shipping_origin').select2({
			allowClear: true
		});

		$(document).on( "click", '.generate-airwaybill', function(event) {
            event.preventDefault();
            //Set params
            let orderID 	   = $(this).data('id');
            let shipperName    = $(this).data('shipper-name');
            let shipperAddr1   = $(this).data('shipper-addr1');
            let shipperAddr2   = $(this).data('shipper-addr2');
            let shipperCity    = $(this).data('shipper-city');
            let shipperRegion  = $(this).data('shipper-region');
            let shipperZip 	   = $(this).data('shipper-zip');
            let shipperPhone   = $(this).data('shipper-phone');
            let receiverName   = $(this).data('receiver-name');
            let receiverAddr1  = $(this).data('receiver-addr1');
            let receiverAddr2  = $(this).data('receiver-addr2');
            let receiverCity   = $(this).data('receiver-city');
            let receiverRegion = $(this).data('receiver-region');
            let receiverZip    = $(this).data('receiver-zip');
            let receiverPhone  = $(this).data('receiver-phone');
            let qty 		   = $(this).data('qty');
            let weight 		   = $(this).data('weight');
            let goodsDesc 	   = $(this).data('goodsdesc');
            let goodsValue 	   = $(this).data('goodsvalue');
            let goodsType 	   = $(this).data('goodstype');
            let insurance 	   = $(this).data('insurance');
            let origin 		   = $(this).data('origin');
            let destination    = $(this).data('destination');
            let service 	   = $(this).data('service');
            let codflag 	   = $(this).data('codflag');
            let codAmount 	   = $(this).data('codamount');
            let baseURL 	   = scod_admin_ajax.generate_airwaybill.ajaxurl;
	    	let nonce 		   = scod_admin_ajax.generate_airwaybill.nonce;

	    	//Get detail request
	    	$.ajax({
	    		dataType: "json",
                url : baseURL,
                type : 'POST',
                data : {
                    orderID: orderID,
                    shipperName: shipperName,
                    shipperAddr1: shipperAddr1,
                    shipperAddr2: shipperAddr2,
                    shipperCity: shipperCity,
                    shipperRegion: shipperRegion,
                    shipperZip: shipperZip,
                    shipperPhone: shipperPhone,
                    receiverName: receiverName,
                    receiverAddr1: receiverAddr1,
                    receiverAddr2: receiverAddr2,
                    receiverCity: receiverCity,
                    receiverRegion: receiverRegion,
                    receiverZip: receiverZip,
                    receiverPhone: receiverPhone,
                    qty: qty,
                    weight: weight,
                    goodsDesc: goodsDesc,
                    goodsValue: goodsValue,
                    goodsType: goodsType,
                    insurance: insurance,
                    origin: origin,
                    destination: destination,
                    service: service,
                    codflag: codflag,
                    codAmount: codAmount,
                    nonce:  nonce
                },
                success : function(response) {
                    console.log(response);
                    $('input#sejoli_shipping_number').val(response);
                    $('#shipping-number').text(response);
                    $('a.generate-airwaybill').hide();
                    window.location.reload();
                },
                error: function (request, status, error) {
                    console.log(request);
                    console.log(error);
                }
            });
        });
	});

})( jQuery );
