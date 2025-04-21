/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	// Date Range Picker FOR order list page search filter
	if ($('#order_search_daterange').length > 0) {
		function booking_range(start, end) {
			// Update the input field with the selected date range in MM/DD/YYYY format
			$('#order-search-daterange').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
		}

		$('#order_search_daterange').daterangepicker({
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
		$('#order_search_daterange').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
		});

		// Event when the date range picker is canceled
		$('#order_search_daterange').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});

		// Clear the input initially to keep it blank
		$('#order_search_daterange').val('MM/DD/YYYY - MM/DD/YYYY');
	}
	
	// Date Range Picker FOR order wishlist list page search filter
	if ($('#order_wishlist_search_daterange').length > 0) {
		function booking_range(start, end) {
			// Update the input field with the selected date range in MM/DD/YYYY format
			$('#order_wishlist_search_daterange').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
		}

		$('#order_wishlist_search_daterange').daterangepicker({
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
		$('#order_wishlist_search_daterange').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
		});

		// Event when the date range picker is canceled
		$('#order_wishlist_search_daterange').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});

		// Clear the input initially to keep it blank
		$('#order_wishlist_search_daterange').val('MM/DD/YYYY - MM/DD/YYYY');
	}
	
	
});
