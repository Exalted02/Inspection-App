/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	
	$(document).on('click','.save-product-code', function(){
		let codeName = $('#stage_name').val().trim();
		//let createdDate = $('#created_date').val().trim();
		let isValid = true;
		$('.invalid-feedback').hide();
		$('.form-control').removeClass('is-invalid');if (codeName === '')
		{
			$('#stage_name').addClass('is-invalid');
			$('#stage_name').next('.invalid-feedback').show();
			isValid = false;
		}
		if (isValid) {
			var form = $("#frmproductcode");
			var URL = $('#frmproductcode').attr('action');
			//alert(URL);
			$.ajax({
				url: URL,
				type: "POST",
				data: form.serialize() + '&_token=' + csrfToken,
				//dataType: 'json',
				success: function(response) {
					$('#success_msg').modal('show');
					setTimeout(() => {
						window.location.reload();
					}, "2000");
				},
			});
		}
	});
	
	$(document).on('click','.edit-product-code', function(){
		var id = $(this).data('id');
		var URL = $(this).data('url');
		//alert(URL);
		$.ajax({
			url: URL,
			type: "POST",
			data: {id:id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.state);
				$('#id').val(response.id);
				$('#message_subject').val(response.message_subject);
				$('#message').val(response.message);
				$('#edit_product_code').modal('show');
				//alert(JSON.stringify(response));
				
			},
		});
	}); 

	$(document).on('click','.update-product-code-form', function()
	{
		let isValid = true;
		if (isValid) {
			var form = $("#frmeditproductcode");
			var URL = $('#frmeditproductcode').attr('action');
			$.ajax({
				url: URL,
				type: "POST",
				data: form.serialize() + '&_token=' + csrfToken,
				//dataType: 'json',
				success: function(response) {
					$('#updt_success_msg').modal('show');
					setTimeout(() => {
						window.location.reload();
					}, "2000");
				},
				error: function(xhr) {
					// Handle validation errors
					if (xhr.status === 422) {
						const errors = xhr.responseJSON.errors;
						let errorMessage = '';

						$('.invalid-feedback').hide();
						$('.form-control').removeClass('is-invalid');
						// Loop through errors and format them
						$.each(errors, function(key, value) {
							errorMessage += value[0] + '\n'; // Assuming you want the first error message for each field
							if ($('#'+key).length) { // Which have id
								$('#'+key).addClass('is-invalid');
								$('#'+key).next('.invalid-feedback').show().text(value[0]);
							}else{ // Which have not any id
								var fieldName = key.split('.')[0]; // Get the base field name (e.g., product_sale_price)
								var index = key.split('.').pop();
								var inputField = $('input[name="' + fieldName + '[]"]').eq(index);
								inputField.addClass('is-invalid');
								inputField.next('.invalid-feedback').show().text(value[0]);
							}
						});
						// Display the errors
						//alert('Validation errors:\n' + errorMessage);
					} else {
						alert('An unexpected error occurred.');
					}
				}
			});
		}
	});
});
