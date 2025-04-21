/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	
	$(document).on('click','.save-product', function(){
		var isValid = true;
		
		$('#frmproductcode [required]').each(function () {
			if ($(this).val().trim() === '') {
				isValid = false;
				$(this).addClass('is-invalid'); // Add Bootstrap invalid class
			} else {
				$(this).removeClass('is-invalid'); // Remove invalid class if valid
			}
		});
		
		var priceValue = $('#price').val().trim();
		var priceRegex = /^\d+(\.\d+)?$/;  // This regex matches numbers with an optional decimal portion
		if (!priceRegex.test(priceValue)) {
			isValid = false;
			$('#price').addClass('is-invalid');
			// Optionally, you can also show a custom message, e.g.:
			$('#price').siblings('.invalid-feedback').text('Please enter a valid price (numbers and an optional decimal point only).');
		} else {
			$('#price').removeClass('is-invalid');
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
					if (!response.success) {
						$('#product_code').addClass('is-invalid');
						$('#product_code').next('.invalid-feedback').text(response.message).show();
					} else {
						$('#success_msg').modal('show');
						setTimeout(() => {
							window.location.reload();
						}, "2000");
					}
				},
			});
		}
	});
	


$(document).on('click','.edit-product', function(){
	var id = $(this).data('id');
	var URL = $(this).data('url');
	//alert(URL);
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			var remaining_upload = parseInt(12) - parseInt(response.category_image_count)
			if (Dropzone.instances.length > 0) {
				// Assuming the first Dropzone instance is the one you want to update
				var dropzoneInstance = Dropzone.instances[0];
				dropzoneInstance.options.maxFiles = remaining_upload;
			} else {
				initializeDropzone(remaining_upload);
			}
			
			$('#text_show').html(remaining_upload);
			
			//alert(response.moulding_description);
			$('#id').val(response.id);
			$('#name').val(response.name);
			$('#product_code').val(response.product_code);
			$('#artist_id').val(response.artist_id).trigger('change');
			$('#category').val(response.category).trigger('change');
			$('#size').val(response.size).trigger('change');
			$('#color').val(response.color).trigger('change');
			$('#price').val(response.price);
			//$('#subcategory').val(response.subcategory).trigger('change');
			$('#description').val(response.description);
			//$('#moulding_description').val(response.moulding_description);
			$('#moulding_description').summernote('code', response.moulding_description);
			
			function waitForDropdownToLoad(selector, value, callback) {
				// Wait for the dropdown options to load
				let interval = setInterval(function () {
					if ($(selector).find('option').length > 1) { // Ensure dropdown has options
						clearInterval(interval);
						$(selector).val(value).trigger('change'); // Set the value and trigger change event
						if (typeof callback === 'function') {
							callback(); // Call the next function if provided
						}
					}
				}, 100); // Check every 100ms
			}

			waitForDropdownToLoad('#category', response.category, function() {
				waitForDropdownToLoad('#subcategory', response.subcategory, function() {
					//console.log('Category and subcategory loaded successfully.');
				});
			});

			
			
			var galleriesHtml = ''; 
			var app_url =  response.app_url;
			var medianames = response.medias;
			

			$.each(medianames, function (index, gallery) {
				// Open a new row every 4 items
				if (index % 4 === 0) {
					galleriesHtml += '<div class="row mb-3">';
				}

				// Add the image with a delete button
				galleriesHtml += `
					<div class="col-md-3 position-relative">
						<!-- Cross Icon to delete image -->
						<div class="position-absolute top-0 end-0 m-2">
							<button type="button" 
									data-img="${gallery.image}" 
									data-id="${gallery.id}" 
									data-product="${gallery.media_source_id}" 
									data-imagename="${gallery.image}"  
									class="btn removeImage bg-transparent">
								<i class="fa fa-times-circle custom_gallery_icon" style="font-size:20px;color:red"></i>
							</button>
						</div>

						<!-- Image -->
						<img src="${app_url}/uploads/product/${gallery.media_source_id}/gallery/thumbs/${gallery.image}" 
							 class="img-thumbnail" 
							 alt="Gallery Image">
					</div>
				`;

				// Close the row after every 4 items or at the last item
				if ((index % 4 === 3) || (index === medianames.length - 1)) {
					galleriesHtml += '</div>';
				}
			});

			// Append the HTML to the container
			$('#galleries_data').html(galleriesHtml);


			
			
			$('#add_product').modal('show');
			//alert(JSON.stringify(response));
			
		},
	});
}); 

$(document).on("click",'.removeImage', function (event) {
	var id = $(this).data('id');
	var imagename = $(this).data('imagename');
	var product_id = $(this).data('product');
	var media_source_id = 3;
	//alert(imagename);
	var URL = $("#del-media-image").val();
	//alert(URL);
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, product_id:product_id, image_name:imagename,media_source_id:media_source_id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {  
			//alert(JSON.stringify(response));var galleriesHtml = '';  product_remain
			var app_url =  response.app_url;
			var galleriesHtml = ''; 
			var medianames = response.medias;
            //alert(vehicle_gallaries);
			$('#text_show').html(response.product_remain);
			
			if (Dropzone.instances.length > 0) {
					// Assuming the first Dropzone instance is the one you want to update
					var dropzoneInstance = Dropzone.instances[0];
					dropzoneInstance.options.maxFiles = response.product_remain;
				} else {
					initializeDropzone(response.product_remain);
				}
			
			
			$.each(medianames, function (index, gallery) {
				
				if (index % 4 === 0) {
					galleriesHtml += '<div class="row mb-3">'; 
				}
				
				galleriesHtml += `
						<div class="col-md-3 position-relative">
							<!-- Cross Icon to delete image -->
							<div class="position-absolute top-0 end-0 m-2">
								<button type="button" 
										data-img="${gallery.image}" 
										data-id="${gallery.id}" data-product="${gallery.media_source_id}" data-imagename="${gallery.image}"  
										class="btn removeImage bg-transparent">
									<i class="fa fa-times-circle custom_gallery_icon" style="font-size:20px;color:red"></i>
								</button>
							</div>

							<!-- Image -->
							<img src="${app_url}/uploads/product/${gallery.media_source_id}/gallery/thumbs/${gallery.image}" 
								 class="img-thumbnail" 
								 alt="Gallery Image">
						</div>
					`;
				if ((index % 4 === 3) || (index === medianames.length - 1)) {
					galleriesHtml += '</div>'; // Close the row
				}
			});
			//alert(galleriesHtml);
			$('#galleries_data').html(galleriesHtml);
			
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



$(document).on('click','.delete-product-code', function(){
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
			$('#delete_product_code').modal('show');
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

$(document).on('click','.add_product', function(){
	$('#name').val('');
	$('#galleries_data').html('');
})

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

$(document).on('change','#category', function(){
	var id = $(this).val();
	var URL = $(this).data('url');
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			//alert(response);
			$('#subcategory').html(response)
			
		},
	});
});

	/*$(document).on('change','#category', function(){
	
	function waitForDropdownToLoad(selector, value, callback) {
					const interval = setInterval(() => {
							if ($(selector).find(`option[value="${value}"]`).length > 0) {
								$(selector).val(value).trigger('change');
								clearInterval(interval);
								if (callback) callback();
							}
						}, 100); 
					}
					waitForDropdownToLoad('#category', response.customers.country_id, function() {
						waitForDropdownToLoad('#state_id', response.customers.state_id, function() {
							waitForDropdownToLoad('#city_id', response.customers.city_id);
						});
					});
	})*/

});
