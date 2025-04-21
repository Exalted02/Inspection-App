/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	// Country change function
	$(document).on('change', '.select_country', function() {
		//$("#country").change(function (event) {
			var country_id = $(this).val();
			var URL = $(this).data('url');
			$.ajax({
				url: URL,
				type: "POST",
				data: {country_id:country_id, _token: csrfToken},
				dataType: 'json',
				success: function(response) {
					//alert(JSON.stringify(response));
					$(".select_state").html(JSON.stringify(response));
					$(".select_city").html('<option value="">Please Select</option>').val('').trigger('change');
				},
			});
		//});
	});
	// Country change function
	// State change function
	$(document).on('change', '.select_state', function() {
		//$("#country").change(function (event) {
			var state_id = $(this).val();
			var URL = $(this).data('url');
			$.ajax({
				url: URL,
				type: "POST",
				data: {state_id:state_id, _token: csrfToken},
				dataType: 'json',
				success: function(response) {
					$(".select_city").html(JSON.stringify(response));
				},
			});
		//});
	});
	// Country change function
	
	
	// For filter section
	// Country change function
	$(document).on('change', '.select_search_country', function() {
		//$("#country").change(function (event) {
			var country_id = $(this).val();
			var URL = $(this).data('url');
			$.ajax({
				url: URL,
				type: "POST",
				data: {country_id:country_id, page:'search', _token: csrfToken},
				dataType: 'json',
				success: function(response) {
					$(".select_search_state").html(JSON.stringify(response));
					$(".select_search_city").html('<option value="">Please Select</option>').val('').trigger('change');
				},
			});
		//});
	});
	// Country change function
	// State change function
	$(document).on('change', '.select_search_state', function() {
		//$("#country").change(function (event) {
			var state_id = $(this).val();
			var URL = $(this).data('url');
			$.ajax({
				url: URL,
				type: "POST",
				data: {state_id:state_id, page:'search', _token: csrfToken},
				dataType: 'json',
				success: function(response) {
					$(".select_search_city").html(JSON.stringify(response));
				},
			});
		//});
	});
	// Country change function
	
	// For Business Setup page
	// Country change function
	$(document).on('change', '.business_country', function() {
		//$("#country").change(function (event) {
			var country_id = $(this).val();
			var URL = $(this).data('url');
			var id = $(this).data('id');
			$.ajax({
				url: URL,
				type: "POST",
				data: {country_id:country_id, _token: csrfToken},
				dataType: 'json',
				success: function(response) {
					$("#"+id+"_state").html(JSON.stringify(response));
					$("#"+id+"_city").html('<option value="">Please Select</option>').val('').trigger('change');
				},
			});
		//});
	});
	// Country change function
	// State change function
	$(document).on('change', '.business_state', function() {
		//$("#country").change(function (event) {
			var state_id = $(this).val();
			var URL = $(this).data('url');
			var id = $(this).data('id');
			$.ajax({
				url: URL,
				type: "POST",
				data: {state_id:state_id, _token: csrfToken},
				dataType: 'json',
				success: function(response) {
					$("#"+id+"_city").html(JSON.stringify(response));
				},
			});
		//});
	});
	// Country change function
});

