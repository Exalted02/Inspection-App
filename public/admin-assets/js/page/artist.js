/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	
	$(document).on('click','.save-artist', function(){
		let artist = $('#name').val().trim();
		//let createdDate = $('#created_date').val().trim();
		let isValid = true;
		$('.invalid-feedback').hide();
		$('.form-control').removeClass('is-invalid');
		if (artist === '')
		{
			$('#name').addClass('is-invalid');
			$('#name').next('.invalid-feedback').show();
			isValid = false;
		}
		if (isValid) {
			var form = $("#frmproductcode");
			var URL = $('#frmproductcode').attr('action');
			var formData = new FormData(form[0]);
			formData.append('_token', csrfToken);
			//alert(csrfToken);
			$.ajax({
				url: URL,
				type: "POST",
				data: formData,
				processData: false,
				contentType: false, 
				success: function(response) {
					if (!response.success) {
						$('#name').addClass('is-invalid');
						$('#name').next('.invalid-feedback').text(response.message).show();
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
	


$(document).on('click','.edit-artist', function(){
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
			$('#name').val(response.name);
			
			var galleriesHtml = ''; 
			var app_url =  response.app_url;
			var photo =  response.photo;
			$('#hid_image').val(photo);
			//alert(photo);
			if(photo !=null)
			{
			galleriesHtml += `
					<div class="col-md-3 position-relative">
						<!-- Cross Icon to delete image -->
						<div class="position-absolute top-0 end-0 m-2">
							<button type="button" 
									data-img="${photo.image}" 
									data-id="${response.id}" 
									data-imagename="${photo}"  
									class="btn removeImage bg-transparent" style="position: relative; right: -10px;">
								<i class="fa fa-times-circle custom_gallery_icon" style="font-size:20px;color:red;margin-top:38px;"></i>
							</button>
						</div>

						<!-- Image -->
						<img src="${app_url}/uploads/artist/${response.id}/${photo}" 
							 class="img-thumbnail" 
							 alt="Gallery Image">
					</div>
				`;
			
			$('.image-preview').html(galleriesHtml);
			}
			$('#add_artist').modal('show');
			//alert(JSON.stringify(response));
			
		},
	});
}); 

$(document).on("click",'.removeImage', function (event) {
	var id = $(this).data('id');
	var imagename = $(this).data('imagename');
	
	//alert(imagename);
	var URL = $("#del-artist-image").val();
	//alert(URL);
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id,imagename:imagename, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			//alert(response.status);			
			//$('.image-preview').html('');
			 const preview = document.getElementById('preview');
                preview.src = '#';
                preview.style.display = 'none';

                // Optionally clear other images (if needed)
                $('.image-preview .col-md-3').remove(); 
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

$(document).on('click','.add_artist', function(){
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

});
