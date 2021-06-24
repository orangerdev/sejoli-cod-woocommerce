(function( $ ) {
	'use strict';

	$( document ).ready( function( $ ) {

		var targetedCountry = scods_object.checkout.target_country,
	        initialBCountry = scods_object.checkout.billing_country,
	        initialSCountry = scods_object.checkout.shipping_country,
			billingState 	= $( 'select#billing_state' ),
			shippingState 	= $( 'select#shipping_state' );

		function showHideFields( country, fieldset ) {
	        var select2Classes = 'country_select select2-hidden-accessible';

	        if( country === targetedCountry ) {
	        	$('#'+fieldset+'_city2_field').removeClass('hidden');
	            $('#'+fieldset+'_city_field').addClass('hidden');
	            $('#'+fieldset+'_address_2_field').addClass('hidden');
	            $('select#'+fieldset+'_city2').addClass(select2Classes);
	            $('.woocommerce-address-fields select#'+fieldset+'_city').addClass(select2Classes);
	            $('.woocommerce-address-fields select#'+fieldset+'_address_2').addClass(select2Classes);
	            $('select#'+fieldset+'_district').addClass(select2Classes);
	        } else if( country !== targetedCountry && $('#'+fieldset+'_city_field').hasClass('hidden') ) {
	        	$('#'+fieldset+'_city2_field').addClass('hidden');
	            $('#'+fieldset+'_city_field').removeClass('hidden');
	            $('#'+fieldset+'_address_2_field').removeClass('hidden');
	            $('select#'+fieldset+'_city2').removeClass(select2Classes);
	            $('.woocommerce-address-fields select#'+fieldset+'_city').removeClass(select2Classes);
	            $('.woocommerce-address-fields select#'+fieldset+'_address_2').removeClass(select2Classes);
	            $('select#'+fieldset+'_district').removeClass(select2Classes);
	        }
	    }

		function resetLocOptions( field, fieldset ) {
			if( field == 'city' || field == 'all' ) {
				$('select#'+fieldset+'_city2').empty();
			}
			if( field == 'district' || field == 'all' ) {
				$('select#'+fieldset+'_district').empty();
			}
	    }

		function addDefaultOption( field ) {
			$( field ).append('<option selected></option>');
	    }

		function addLocOptions( field, data ) {
			$.each(data, function(index, value) {
				let newOption = new Option(value, index, true, true);
				field.append(newOption).trigger('change');
			});
		}

		function getCityResults( state_id, fieldset ) {
			var field = $('select#'+fieldset+'_city2');

			$.ajax({
				type : 'POST',
				url : scods_object.ajax_url,
				dataType: 'json',
				data : {
					nonce : scods_object.locations.city.nonce,
					action : scods_object.locations.city.action,
					state_id : state_id
				},
				success: function(data) {
					addLocOptions( field, data );
					addDefaultOption( field );
				},
	            error: function (response) {
	                console.log(response);
	            }
			});
		}

		function getAddressCityResults( state_id, fieldset ) {
			var field = $('.woocommerce-address-fields select#'+fieldset+'_city');

			$.ajax({
				type : 'POST',
				url : scods_object.ajax_url,
				dataType: 'json',
				data : {
					nonce : scods_object.locations.city.nonce,
					action : scods_object.locations.city.action,
					state_id : state_id
				},
				success: function(data) {
					addLocOptions( field, data );
					addDefaultOption( field );
				},
	            error: function (response) {
	                console.log(response);
	            }
			});
		}

		function getDistrictResults( city_id, fieldset ) {
			var field = $('select#'+fieldset+'_district');

			$.ajax({
				type : 'POST',
				url : scods_object.ajax_url,
				dataType: 'json',
				data : {
					nonce : scods_object.locations.district.nonce,
					action : scods_object.locations.district.action,
					city_id : city_id
				},
				success: function(data) {
					addLocOptions( field, data );
					addDefaultOption( field );
				},
	            error: function (response) {
	                console.log(response);
	            }
			});
		}

		function getAddressDistrictResults( city_id, fieldset ) {
			var field = $('.woocommerce-address-fields select#'+fieldset+'_address_2');

			$.ajax({
				type : 'POST',
				url : scods_object.ajax_url,
				dataType: 'json',
				data : {
					nonce : scods_object.locations.district.nonce,
					action : scods_object.locations.district.action,
					city_id : city_id
				},
				success: function(data) {
					addLocOptions( field, data );
					addDefaultOption( field );
				},
	            error: function (response) {
	                console.log(response);
	            }
			});
		}

		function showHideYesShipping() {
			var selected_payment = $('input[name="payment_method"]:checked' ).val();
			var targeted_shipping = $('#shipping_method_0_scod-shipping_jne_yes19');

			if( selected_payment == 'cod') {
				targeted_shipping.parent().hide();
			} else {
				targeted_shipping.parent().show();
			}
		}

	    // On Start (after Checkout page is loaded)
	    showHideFields(initialBCountry, 'billing');
	    showHideFields(initialSCountry, 'shipping');
		showHideYesShipping();
		if( billingState.val() ) { getCityResults( billingState.val(), 'billing' ) }
		if( shippingState.val() ) { getCityResults( shippingState.val(), 'shipping' ) }
		if( billingState.val() ) { getAddressCityResults( billingState.val(), 'billing' ) }
		if( shippingState.val() ) { getAddressCityResults( shippingState.val(), 'shipping' ) }

	    // Live: On Country change event
	    $('body').on( 'change', 'select#billing_country', function(){
	        showHideFields( $(this).val(), 'billing' );
	    });
	    $('body').on( 'change', 'select#shipping_country', function(){
	        showHideFields( $(this).val(), 'shipping' );
	    });

	    // Live: On City change event for Indonesia country
	    // $('body').on( 'change', 'select#billing_city2', function(){
	    // 	$( 'input#billing_city' ).val( $(this).val() );
	    // });
	    // $('body').on( 'change', 'select#shipping_city2', function(){
	    // 	$( 'input#shipping_city' ).val( $(this).val() );
	    // });
	    
	    // $('select#billing_city2').on('change', function() {
	    $('select#billing_city2').on('select2:select', function (e) {
	    	var selectedText = $(this).find("option:selected").val();
		  	$( 'input#billing_city' ).val( selectedText );
		});

	    $('select#shipping_city2').on('select2:select', function (e) {
	    	var selectedText = $(this).find("option:selected").val();
		  	$( 'input#shipping_city' ).val( selectedText );
		});

	    // $('select#billing_district').on('change', function() {
	    $('select#billing_district').on('select2:select', function (e) {
	    	var selectedText = $(this).find("option:selected").val();
		  	$( 'input#billing_address_2' ).val( selectedText );
		});

	   	$('select#shipping_district').on('select2:select', function (e) {
	    	var selectedText = $(this).find("option:selected").val();
		  	$( 'input#shipping_address_2' ).val( selectedText );
		}); 

		// Live: On State select2 change
		$('select#billing_state').on('select2:select', function (e) {
		    var data = e.params.data;
			resetLocOptions( 'all', 'billing' );
			getCityResults( data.id, 'billing' );
			getAddressCityResults( data.id, 'billing' );
			showHideYesShipping();
		});
		$('select#shipping_state').on('select2:select', function (e) {
		    var data = e.params.data;
			resetLocOptions( 'all', 'shipping' );
			getCityResults( data.id, 'shipping' );
			getAddressCityResults( data.id, 'shipping' );
			showHideYesShipping();
		});

		// Live: On City2 select2 change
		$('select#billing_city2').on('select2:select', function (e) {
		    var data = e.params.data;
			resetLocOptions( 'district', 'billing' );
			getDistrictResults( data.id, 'billing' );
			showHideYesShipping();
		});
		$('select#shipping_city2').on('select2:select', function (e) {
		    var data = e.params.data;
			resetLocOptions( 'district', 'shipping' );
			getDistrictResults( data.id, 'shipping' );
			showHideYesShipping();
		});

		// Live: On change payment method
		$('form.checkout').on( 'change', 'input[name^="payment_method"]', function() {
			showHideYesShipping();
	    });

	});

})( jQuery );
