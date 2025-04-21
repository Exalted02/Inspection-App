<!-- Footer -->

<script>
$(document).ready(function() {
	if ($.fn.DataTable.isDataTable('.datatable')) {
		$('.datatable').DataTable().destroy(); // Destroy existing instance
	}
	
	$('.datatable').DataTable({
		pageLength: 50, // Set default records per page to 50
		ordering: false, 
		language: {
			"lengthMenu": "{{ __('Show _MENU_ entries') }}",
			"zeroRecords": "{{ __('No records found') }}",
			"info": "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
			"infoEmpty": "{{ __('No entries available') }}",
			"infoFiltered": "{{ __('(filtered from _MAX_ total entries)') }}",
			"search": "{{ __('Search') }}",
			"paginate": {
				"first": "{{ __('First') }}",
				"last": "{{ __('Last') }}",
				"next": "{{ __('Next') }}",
				"previous": "{{ __('Previous') }}"
			},
		},
		columnDefs: [
			{ orderable: false, targets: 0 }, 
			{ orderable: false, targets: '_all' } 
		],
	});
});

</script>

<!-- Footer -->