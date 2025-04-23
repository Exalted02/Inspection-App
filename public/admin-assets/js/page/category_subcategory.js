/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	// Category change function
	$(document).on('change', '.select_category', function() {
		var category_id = $(this).val();
		var URL = $(this).data('url');
		$.ajax({
			url: URL,
			type: "POST",
			data: {category_id:category_id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				$(".subcategory").html(JSON.stringify(response));
			},
		});
	});
});

