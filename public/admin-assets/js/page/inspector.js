/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	
	$(document).on('click','.save-inspector', function(){
		let name = $('#name').val().trim();
		let email = $('#email').val().trim();
		let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		let passwords = $('#password').val().trim();
		//let company_name = $('#company_name').val().trim();
		let user_type = $('#user_type').val().trim();
		var edit_id = $('#id').val(); 
		//alert(edit_id);
		let avatar = $('#avatar')[0].files.length;
		let backgroung_image = $('#backgroung_image')[0].files.length;
		
		let isValid = true;
		$('.invalid-feedback').hide();
		$('.form-control').removeClass('is-invalid');
		if (name === '')
		{
			$('#name').addClass('is-invalid');
			$('#name').next('.invalid-feedback').show();
			isValid = false;
		}
		if (email === '')
		{
			$('#email').addClass('is-invalid');
			$('#email').next('.invalid-feedback').show();
			isValid = false;
		}
		else if (!emailPattern.test(email)) {
			$('#email').addClass('is-invalid');
			$('#email').next('.invalid-feedback').text('Please enter valid email.').show();
			isValid = false;
		} 
		
		if(edit_id =='')
		{
			if (passwords === '')
			{
				$('#password').addClass('is-invalid');
				$('#password').next('.invalid-feedback').show();
				isValid = false;
			}
		}
		
		if (user_type === '')
		{
			$('#user_type').addClass('is-invalid');
			$('#user_type').siblings('.invalid-feedback').show();
			isValid = false;
		}
		
		
		let selectedLocations = [];
		$('input[name="location[]"]:checked').each(function() {
			selectedLocations.push($(this).val());
		});
		//alert(selectedLocations);
		if (selectedLocations.length === 0) {
			$('input[name="location[]"]').first().addClass('is-invalid');
			$('input[name="location[]"]').first().closest('.select-people-checkbox-s').siblings('.invalid-feedback').show();
			isValid = false;
		}
		
		/*if(edit_id == '')
		{
			if (avatar === 0) {
				$('#avatar_id_error').text('Please select avatar').fadeIn().delay(5000).fadeOut();
				isValid = false;
			}
			
			if (backgroung_image === 0) {
				$('#backgroung_image_id_error').text('Please select background image').fadeIn().delay(5000).fadeOut();
				isValid = false;
			}
		}*/
		
		if (isValid) {
			//var form = $("#frmlocation");
			var URL = $('#frminspector').attr('action');
			var id = $('#id').val();
			$('#user_type').prop('disabled', false); 
			
			let formData = new FormData($('#frminspector')[0]);
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
						if(response.label == 'name')
						{
							$('#name').addClass('is-invalid');
							$('#name').next('.invalid-feedback').text(response.message).show();
						}
						
						if(response.label == 'email')
						{
							$('#email').addClass('is-invalid');
							$('#email').next('.invalid-feedback').text(response.message).show();
						}
						
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
	
$(document).on('click','.edit-inspector', function(){
	$('.invalid-feedback').hide();
	$('.form-control').removeClass('is-invalid');
	var id = $(this).data('id');
	var URL = $(this).data('url');
	//alert(URL);
	$('input[name="location[]"]').each(function () {
		$(this).prop('checked', false);
	});
	
	$.ajax({
		url: URL,
		type: "POST",
		data: {id:id, _token: csrfToken},
		dataType: 'json',
		success: function(response) {
			//alert(response.state);
			$('#id').val(response.id);
			$('#user_type').val(response.user_type).trigger('change');
			$('#name').val(response.name);
			$('#email').val(response.email);
			$('#password').val('');
			$('#company_id').val(response.company_name);
			
			$('#hid_avatar').val(response.avatar);
			$('#hid_back_grd_image').val(response.background_image);
			
			var app_url = response.app_url;
			//alert(app_url);alert(response.avatar);			
			$('#preview').attr('src', app_url + '/' + response.avatar).show();
			$('#preview_backgrnd').attr('src', app_url + '/' + response.background_image).show();
			
			let selectedLocations = Array.isArray(response.location_data)? response.location_data : String(response.location_data).split(',');
			selectedLocations = selectedLocations.map(String);
			
			$('#user_type').prop('disabled', true);
			
			$('input[name="location[]"]').each(function () {
				let val = $(this).val().toString();
				if (selectedLocations.includes(val)) {
					$(this).prop('checked', true);
				}
			});
			
			$('#head-label').html(response.edit);
			$('#add_inspector').modal('show');
			
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



$(document).on('click','.delete-inspector-name', function(){
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
				$('#delete-msg').html('<font color="green">Record Deleted Successfully</font>');
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
	$('#search-inspector-frm').submit();
	
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


$('#avatar').on('change', function (event) {
    const [file] = event.target.files;
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $('#preview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(file);
    }
});

$('#backgroung_image').on('change', function (event) {
    const [file] = event.target.files;
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $('#preview_backgrnd').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(file);
    }
});



});
function check_location(id)
{
	var user_tpy = $('#user_type').val();
	let isValid = true;
	$('.invalid-feedback').hide();
	$('.form-control').removeClass('is-invalid');
	if(user_tpy == '')
	{
		$('input[name="location[]"]').each(function () {
			$(this).prop('checked', false);
		});
		
		$('#user_type').addClass('is-invalid');
		$('#user_type').siblings('.invalid-feedback').show();
		isValid = false;
	}
	
	/*if(user_tpy == 2 || user_tpy == 3 || user_tpy == 4)
	{
		$('input[name="location[]"]').each(function () {
			if($(this).val() != id)
			{
				$(this).prop('checked', false);
			}
		});
	}*/
	
	
	
	var company_id = $('#companyid').val();;
	var URL = $('#check_location_by_user_type').val();;
	//alert(URL);
	if(isValid)
	{
		$.ajax({
			url: URL,
			type: "POST",
			data: {user_type:user_tpy,company_id:company_id,location_id:id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				if(response.status == 'error'){
					//$('#add_inspector .modal-body').animate({ scrollTop: 0 }, 500);
					/*$('#add_inspector').on('shown.bs.modal', function () {
						setTimeout(() => {
							$('#add_inspector .modal-body')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
						}, 200);
					});*/
					//var position = $('.container-fluid').offset().top; 
					//$('html, body').animate({ scrollTop: position }, 'slow');
					$( ".container-fluid" ).scrollTop( 300 );

					//document.querySelector('#add_inspector .modal-body .form-group').scrollIntoView({ behavior: 'smooth', block: 'start' });

					/*setTimeout(() => {
						$('#add_inspector .modal-body, #add_inspector .modal-content').animate({ scrollTop: 0 }, 300);
					}, 100);*/
					
					$('#location_check_error').text(response.msg).fadeIn().delay(2000).fadeOut();
					$('input[name="location[]"]').each(function () {
						$(this).prop('checked', false);
					});
					
					
				}else{
					
				}
				
				/*setTimeout(() => {
					window.location.reload();
				}, "2000");*/
			},
		});
	}
}