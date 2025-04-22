/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	// Date Range Picker FOR manage company name
	if ($('#src_company_name_date_range').length > 0) {
		function booking_range(start, end) {
			// Update the input field with the selected date range in MM/DD/YYYY format
			$('#src_company_name_date_range').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
		}

		$('#src_company_name_date_range').daterangepicker({
			autoUpdateInput: false,  // Prevents the input from being auto-filled on initialization
			ranges: {
				'Today': [moment(), moment()],
				'Tomorrow': [moment().add(1, 'days'), moment().add(1, 'days')],
				'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
				'Next Month': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')]
			}
		}, booking_range);

		// Event when a date range is selected
		$('#src_company_name_date_range').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
		});

		// Event when the date range picker is canceled
		$('#src_company_name_date_range').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});

		// Clear the input initially to keep it blank
		if($('#src_company_name_date_range').val() != ''){
			// $('#date_range_expiry_date').val('MM/DD/YYYY - MM/DD/YYYY');
		}else{
			$('#src_company_name_date_range').val('MM/DD/YYYY - MM/DD/YYYY');
		}
	}
	
	// manage search location 
	
	if ($('#src_location_date_range').length > 0) {
		function booking_range(start, end) {
			// Update the input field with the selected date range in MM/DD/YYYY format
			$('#src_location_date_range').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
		}

		$('#src_location_date_range').daterangepicker({
			autoUpdateInput: false,  // Prevents the input from being auto-filled on initialization
			ranges: {
				'Today': [moment(), moment()],
				'Tomorrow': [moment().add(1, 'days'), moment().add(1, 'days')],
				'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
				'Next Month': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')]
			}
		}, booking_range);

		// Event when a date range is selected
		$('#src_location_date_range').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
		});

		// Event when the date range picker is canceled
		$('#src_location_date_range').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});

		// Clear the input initially to keep it blank
		if($('#src_location_date_range').val() != ''){
			// $('#date_range_expiry_date').val('MM/DD/YYYY - MM/DD/YYYY');
		}else{
			$('#src_location_date_range').val('MM/DD/YYYY - MM/DD/YYYY');
		}
	}
	
	// manage search category 
	
	if ($('#src_category_name_date_range').length > 0) {
		function booking_range(start, end) {
			// Update the input field with the selected date range in MM/DD/YYYY format
			$('#src_category_name_date_range').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
		}

		$('#src_category_name_date_range').daterangepicker({
			autoUpdateInput: false,  // Prevents the input from being auto-filled on initialization
			ranges: {
				'Today': [moment(), moment()],
				'Tomorrow': [moment().add(1, 'days'), moment().add(1, 'days')],
				'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
				'Next Month': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')]
			}
		}, booking_range);

		// Event when a date range is selected
		$('#src_category_name_date_range').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
		});

		// Event when the date range picker is canceled
		$('#src_category_name_date_range').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});

		// Clear the input initially to keep it blank
		if($('#src_category_name_date_range').val() != ''){
			// $('#date_range_expiry_date').val('MM/DD/YYYY - MM/DD/YYYY');
		}else{
			$('#src_category_name_date_range').val('MM/DD/YYYY - MM/DD/YYYY');
		}
	}
	
});
