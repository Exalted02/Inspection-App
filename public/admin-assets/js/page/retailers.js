/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	
	$(document).on('click','.save-customer', function(){
		let name = $('#name').val().trim();
		let email = $('#email').val().trim();
		let phone = $('#phone').val().trim();
		let id = $('#id').val();
		
		//let createdDate = $('#created_date').val().trim();
		let isValid = true;
		$('.invalid-feedback').hide();
		$('.form-control').removeClass('is-invalid');
		if (name === '')
		{
			$('#name').addClass('is-invalid');
			$('#name').next('.invalid-feedback').show();
			isValid = false;
		}
		
		if (email === '') {
			$('#email').addClass('is-invalid');
			$('#email').next('.invalid-feedback').show();
			isValid = false;
		} else if (!validateEmail(email)) {
			$('#email').addClass('is-invalid');
			$('#email').next('.invalid-feedback').text('Please enter valid email').show();
			isValid = false;
		}
		
		
		let phoneRegex = /^[0-9]{10}$/; 

		if (phone === '') {
			$('#phone').addClass('is-invalid');
			$('#phone').next('.invalid-feedback').text('Please enter a phone number.').show();
			isValid = false;
		} else if (!phoneRegex.test(phone)) {
			$('#phone').addClass('is-invalid');
			$('#phone').next('.invalid-feedback').text('Please enter a valid 10-digit phone number.').show();
			isValid = false;
		} else {
			$('#phone').removeClass('is-invalid');
			$('#phone').next('.invalid-feedback').hide();
		}
		
		if (isValid) {
			var form = $("#frmuser");
			var URL = $('#frmuser').attr('action');
			//alert(URL);
			$.ajax({
				url: URL,
				type: "POST",
				data: form.serialize() + '&_token=' + csrfToken,
				//dataType: 'json',
				success: function(response) {
					if (!response.success) {
						$('#email').addClass('is-invalid');
						$('#email').next('.invalid-feedback').text(response.message).show();
					} else {
						if(id == '')
						{
							$('#success_msg').modal('show');
							setTimeout(() => {
								window.location.reload();
							}, "2000");
						}
						else{
							$('#updt_success_msg').modal('show');
							setTimeout(() => {
								window.location.reload();
							}, "2000");
						}
					}
				},
			});
		}
	});
	
$(document).on('click','.save-customer-amount', function(){
		let paid_amount = $('#paid_amount').val().trim();
		
		
		//let createdDate = $('#created_date').val().trim();
		let isValid = true;
		$('.invalid-feedback').hide();
		$('.form-control').removeClass('is-invalid');
		let amountRegex = /^[0-9]+(\.[0-9]+)?$/;

		if (paid_amount === '') {
			$('#paid_amount').addClass('is-invalid');
			$('#paid_amount').next('.invalid-feedback').text('Paid amount is required.').show();
			isValid = false;
		} else if (!amountRegex.test(paid_amount)) {
			$('#paid_amount').addClass('is-invalid');
			$('#paid_amount').next('.invalid-feedback').text('Paid amount must be a valid number.').show();
			isValid = false;
		} else {
			$('#paid_amount').removeClass('is-invalid');
			$('#paid_amount').next('.invalid-feedback').hide();
		}
		
		if (isValid) {
			var form = $("#frmpaidamt");
			var URL = $('#frmpaidamt').attr('action');
			//alert(URL);
			$.ajax({
				url: URL,
				type: "POST",
				data: form.serialize() + '&_token=' + csrfToken,
				//dataType: 'json',
				success: function(response) {
					
						if(id == '')
						{
							$('#success_msg').modal('show');
							setTimeout(() => {
								window.location.reload();
							}, "2000");
						}
						else{
							$('#updt_success_msg').modal('show');
							setTimeout(() => {
								window.location.reload();
							}, "2000");
						}
					
				},
			});
		}
	});

$(document).on('click','.edit-customer', function(){
	var id = $(this).data('id');
	var URL = $(this).data('url');
	//alert(URL);
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			//alert(response.name);
			$('#id').val(response.id);
			$('#name').val(response.name);
			$('#email').val(response.email);
			$('#phone').val(response.phone);
			$('#address').val(response.address);
			$('#edit-title').html(response.title);
			$('#add_edit_model').modal('show');
			//alert(JSON.stringify(response));
			
		},
	});
}); 

$(document).on('click','.edit-customer-amount', function(){
	var id = $(this).data('id');
	var URL = $(this).data('url');
	//alert(URL);
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			//alert(response.title);
			$('#amount_edit_id').val(response.id);
			$('#paid_amount').val(response.paid_amount);
			$('#stock_out_date').val(response.date);
			$('#edit-amount-title').html(response.title);
			$('#add_amount_model').modal('show');
			//alert(JSON.stringify(response));
			
		},
	});
});

$(document).on('click','.update-product-code-form', function(){
	
	let stageName = $('#edit_name').val().trim();
	//let createdDate = $('#created_date').val().trim();
	let isValid = true;
	$('.invalid-feedback').hide();
	$('.form-control').removeClass('is-invalid');if (stageName === '') 
	{
		$('#edit_name').addClass('is-invalid');
		$('#edit_name').next('.invalid-feedback').show();
		isValid = false;
	}
	if (isValid) {
		var form = $("#frmeditproductcode");
		var URL = $('#frmeditproductcode').attr('action');
		$.ajax({
			url: URL,
			type: "POST",
			data: form.serialize() + '&_token=' + csrfToken,
			//dataType: 'json',
			success: function(response) {
				if (!response.success) {
					$('#edit_name').addClass('is-invalid');
					$('#edit_name').next('.invalid-feedback').text(response.message).show();
				}
				else{
					$('#updt_success_msg').modal('show');
					setTimeout(() => {
						window.location.reload();
					}, "2000");
				}
			},
		});
	}
});



$(document).on('click','.delete-retailer', function(){
	var id = $(this).data('id');
	var URL = $(this).data('url');
	//alert(id);alert(URL);
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			//alert(response);
			//var url = "{{ route('deleteContactList') }}";
			$('.data-id-pcode-list').attr('data-id', id);
			$('#list_name').html(response);
			$('#delete_model').modal('show');
		},
	});
	
});
$(document).on('click','.data-id-pcode-list', function(){
	var id = $(this).data('id');
	var URL = $(this).data('url');
	//alert(URL);
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			if(response.result == 'success'){
				$('#delete-prospect-msg').html('<font color="green">Record Deleted Successfully</font>');
			}else{
				$('#data_already_use').modal('show');
			}
			setTimeout(() => {
				window.location.reload();
			}, "2000");
		},
	});
	
});
$(document).on('click','.update-status', function(){
	var id= $(this).data('id');
	var URL = $(this).data('url');
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			//alert(response);
			setTimeout(() => {
				window.location.reload();
			}, "1000");
		},
	});
});

$(document).on('click','.search-data', function(){
	$('#search-product-code-frm').submit();
	
});
$('.search-sort-by').on('change' ,function (event) {
	//var sort_by = $(this).val();
	$('#search-sortby').submit();
})


/*$(document).on('click','.contact-details', function(){
	var URL = $(this).data('url');
	window.location = URL;
});*/
$(document).on('click', '.dropdown-toggle, .dropdown-menu, .dropdown-item', function(event) {
    event.stopPropagation(); 
});

$('#exportForm').on('submit', function(e) {
	setTimeout(function() {
		$('#export').modal('hide');
	}, 2000);
});

$('#importForm').on('submit', function(e) {
	setTimeout(function() {
		$('#import').modal('hide');
	}, 2000);
});

$(document).on('click','.downloaddemo', function(){
	setTimeout(function() {
		$('#import').modal('hide');
	}, 1000);
});

$(document).on('click','.retailer-tax-download', function(){
	var id= $(this).data('id');
	//var URL = $(this).data('url');
	$('#retailer_id').val(id);
	$('#frmtax').submit();
	/*$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			//alert(response);
			setTimeout(() => {
				window.location.reload();
			}, "1000");
		},
	});*/
})

});

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
