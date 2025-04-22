/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {	
	// Search input trigger
	$(document).on('click', '#filter_undo', function () {
		var id = $(this).data('id');
		$('#'+id+' input').val('');
		$('#'+id+' select').val('').trigger('change');
		
		table.draw(); // Trigger DataTable redraw
	});	
});
