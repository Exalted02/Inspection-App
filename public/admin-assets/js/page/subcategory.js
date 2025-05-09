/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	
	$(document).on('click','.save-subcategory', function(){
		//let category = $('#category').val().trim();
		let subcategory = $('#subcategory').val().trim();
		
		let isValid = true;
		$('.invalid-feedback').hide();
		$('.form-control').removeClass('is-invalid');
		/*if (category === '')
		{
			$('#category').addClass('is-invalid');
			$('#category').siblings('.invalid-feedback').show();
			isValid = false;
		}*/
		if (subcategory === '')
		{
			$('#subcategory').addClass('is-invalid');
			$('#subcategory').next('.invalid-feedback').show();
			isValid = false;
		}
		
		
		if (isValid) {
			//var form = $("#frmlocation");
			var URL = $('#frmsubcategory').attr('action');
			var id = $('#id').val();
			
			let formData = new FormData($('#frmsubcategory')[0]);
			formData.append('_token', csrfToken);
			//alert(URL);
			$.ajax({
				url: URL,
				type: "POST",
				data: formData,
				processData: false,
				contentType: false,
				//dataType: 'json',
				success: function(response) {
					if (!response.success) {
						$('#subcategory').addClass('is-invalid');
						$('#subcategory').next('.invalid-feedback').text(response.message).show();
					} else {
						if(id=='')
						{
							$('#success_msg').modal('show');
						}
						else{
							$('#updt_success_msg').modal('show');
						}
						setTimeout(() => {
							window.location.reload();
						}, "2000");
					}
				},
			});
		}
	});
	


$(document).on('click','.edit-subcategory', function(){
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
			//$('#category').val(response.category).trigger('change');
			$('#category').val(response.category);
			$('#subcategory').val(response.name);
			$('#head-label').html(response.edit);
			$('#add_subcategory').modal('show');
			//alert(JSON.stringify(response));
		},
	});
}); 

$(document).on('click','.update-product-code-form', function(){
	
	let stageName = $('#edit_code_name').val().trim();
	//let createdDate = $('#created_date').val().trim();
	let isValid = true;
	$('.invalid-feedback').hide();
	$('.form-control').removeClass('is-invalid');
	if (stageName === '') 
	{
		$('#edit_code_name').addClass('is-invalid');
		$('#edit_code_name').next('.invalid-feedback').show();
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
					$('#edit_code_name').addClass('is-invalid');
					$('#edit_code_name').next('.invalid-feedback').text(response.message).show();
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



$(document).on('click','.delete-subcategory', function(){
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
			$('.data-id-list').attr('data-id', id);
			$('#list_name').html(response);
			$('#delete_location_modal').modal('show');
		},
	});
	
});
$(document).on('click','.data-id-list', function(){
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
				$('#delete-msg').html('<font color="red">Record Deleted Successfully</font>');
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
	//alert(URL);
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
	$('#search-category-frm').submit();
	
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

/*$(document).on('click','.add_location', function(){
	//alert('ok');
	$('#frmlocation')[0].reset();
	$('#id').val('');
	alert(translations.addlocation);
	$('#head-label').html(translations.addlocation);
	$('.invalid-feedback').hide();
	$('.form-control').removeClass('is-invalid');
});*/


$('#category_image').on('change', function (event) {
    const [file] = event.target.files;
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $('#preview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(file);
    }
});

});
