/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	
	$(document).on('click','.save-product-group', function(){
		let category_id = $('#category_id').val().trim();
		let sub_category_name = $('#sub_category_name').val().trim();
		//let createdDate = $('#created_date').val().trim();
		let isValid = true;
		$('.invalid-feedback').hide();
		$('.form-control').removeClass('is-invalid');
		if(category_id === '')
		{
			$('#category_id').addClass('is-invalid');
			$('.category_id_err').show();
			isValid = false;
		}
		
		// Validate product_group
		if (sub_category_name === '') {
			$('#sub_category_name').addClass('is-invalid');
			$('#sub_category_name').next('.invalid-feedback').show();
			isValid = false;
		}
		
		if (isValid) {
			var form = $("#frmproductgroup");
			var URL = $('#frmproductgroup').attr('action');
			//alert(URL);
			$.ajax({
				url: URL,
				type: "POST",
				data: form.serialize() + '&_token=' + csrfToken,
				//dataType: 'json',
				success: function(response) {
					if (!response.success) {
						$('#sub_category_name').addClass('is-invalid');
						$('#sub_category_name').next('.invalid-feedback').text(response.message).show();
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
	


$(document).on('click','.edit-product-group', function(){
	var id = $(this).data('id');
	var URL = $(this).data('url');
	//alert(URL);
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			//alert(response.subcategory_image_count);
			var remaining_upload = parseInt(12) - parseInt(response.subcategory_image_count)
			if (Dropzone.instances.length > 0) {
				// Assuming the first Dropzone instance is the one you want to update
				var dropzoneInstance = Dropzone.instances[0];
				dropzoneInstance.options.maxFiles = remaining_upload;
			} else {
				initializeDropzone(remaining_upload);
			}
			
			$('#text_show').html(remaining_upload);
			
			$('#id').val(response.id);
			$('#category_id').val(String(response.category_id)).trigger('change');
			$('#sub_category_name').val(response.sub_category_name);
			
			var galleriesHtml = ''; 
			var app_url =  response.app_url;
			var subcatnames = response.medias;
			//alert(response.medias);

			$.each(subcatnames, function (index, gallery) {
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
									data-subcategory="${gallery.media_source_id}" 
									data-imagename="${gallery.image}"  
									class="btn removeImage bg-transparent">
								<i class="fa fa-times-circle custom_gallery_icon" style="font-size:20px;color:red"></i>
							</button>
						</div>

						<!-- Image -->
						<img src="${app_url}/uploads/subcategory/${gallery.media_source_id}/gallery/thumbs/${gallery.image}" 
							 class="img-thumbnail" 
							 alt="Gallery Image">
					</div>
				`;

				// Close the row after every 4 items or at the last item
				if ((index % 4 === 3) || (index === subcatnames.length - 1)) {
					galleriesHtml += '</div>';
				}
			});

			// Append the HTML to the container
			$('#galleries_data').html(galleriesHtml);
			
			
			
			$('#add_subcategory').modal('show');
			//alert(JSON.stringify(response));
			
		},
	});
}); 

$(document).on("click",'.removeImage', function (event) {
	var id = $(this).data('id');
	var imagename = $(this).data('imagename');
	var subcategory_id = $(this).data('subcategory');
	var media_source_id = 2;
	//alert(imagename);
	var URL = $("#del-media-image").val();
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, subcategory_id:subcategory_id, image_name:imagename,media_source_id:media_source_id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {  
			//alert(JSON.stringify(response));var galleriesHtml = ''; 
			var app_url =  response.app_url;
			var galleriesHtml = ''; 
			var medianames = response.medias;
            //alert(vehicle_gallaries);
			$('#text_show').html(response.subcategory_remain);
			
			if (Dropzone.instances.length > 0) {
					// Assuming the first Dropzone instance is the one you want to update
					var dropzoneInstance = Dropzone.instances[0];
					dropzoneInstance.options.maxFiles = response.subcategory_remain;
				} else {
					initializeDropzone(response.subcategory_remain);
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
										data-id="${gallery.id}" data-subcategory="${gallery.media_source_id}" data-imagename="${gallery.image}"  
										class="btn removeImage bg-transparent">
									<i class="fa fa-times-circle custom_gallery_icon" style="font-size:20px;color:red"></i>
								</button>
							</div>

							<!-- Image -->
							<img src="${app_url}/uploads/subcategory/${gallery.media_source_id}/gallery/thumbs/${gallery.image}" 
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

$(document).on('click','.update-product-group-form', function(){
	
	let edit_category_id = $('#edit_category_id').val().trim();
	let edit_sub_category_name = $('#edit_sub_category_name').val().trim();
	//let createdDate = $('#created_date').val().trim();
	let isValid = true;
	$('.invalid-feedback').hide();
	$('.form-control').removeClass('is-invalid');
	if (edit_category_id === '') 
	{
		$('#edit_category_id').addClass('is-invalid');
		$('.edit_category_id_err').show();
		isValid = false;
	}
	// Validate product_group
	if (edit_sub_category_name === '') {
		$('#edit_sub_category_name').addClass('is-invalid');
		$('#edit_sub_category_name').next('.invalid-feedback').show();
		isValid = false;
	}
	
	if (isValid) {
		var form = $("#frmeditproductgroup");
		var URL = $('#frmeditproductgroup').attr('action');
		$.ajax({
			url: URL,
			type: "POST",
			data: form.serialize() + '&_token=' + csrfToken,
			//dataType: 'json',
			success: function(response) {
				if (!response.success) {
					$('#edit_sub_category_name').addClass('is-invalid');
					$('#edit_sub_category_name').next('.invalid-feedback').text(response.message).show();
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



$(document).on('click','.delete-product-group', function(){
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
			$('#list_code_name').html(response);
			$('#delete_product_group').modal('show');
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

$(document).on('click','.add_subcategory', function(){
	$('#category_id').val('').trigger('change');
	$('#sub_category_name').val('');
	$('#galleries_data').html('');
})

$(document).on('click','.search-data', function(){
	$('#search-product-group-frm').submit();
	
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


});
