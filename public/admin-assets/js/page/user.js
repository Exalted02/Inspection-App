/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
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
				
			},
		});
	}); 

	$(document).on('click','.delete-customer', function(){
		var id = $(this).data('id');
		var URL = $(this).data('url');
		//alert(id);alert(URL);
		$.ajax({
			url: URL,
			type: "POST",
			data: {id:id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				$('#delete_model').modal('show');
			},
		});
		
	});
});
