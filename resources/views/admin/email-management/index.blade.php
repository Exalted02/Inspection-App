@extends('admin.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ url('front-assets/plugins/summernote/summernote-bs4.min.css') }}">
@endsection 
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
	<!-- Page Content -->
	<div class="content container-fluid">
	
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col-md-4">
					<h3 class="page-title">{{ __('email_management') }}</h3>
					<ul class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('dashboard') }}</a></li>
						<li class="breadcrumb-item active">{{ __('email_management') }}</li>
					</ul>
				</div>
			</div>
		</div>
		<!-- /Page Header -->
		<hr>
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-striped custom-table datatable">
						<thead>
							<tr>
								<th>{{ __('email_subject') }}</th>
								<th class="text-end">{{ __('action') }}</th>
							</tr>
						</thead>
						<tbody>
							@foreach($data as $val)
							<tr>
								<td>{{ $val->message_subject ?? ''}}</td>
								<td class="text-end">
									<div class="dropdown dropdown-action">
										<a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item edit-product-code" href="{{ route('email-management-edit', $val->id) }}"><i class="fa-solid fa-pencil m-r-5"></i> {{ __('edit') }}</a>
										</div>
									</div>
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
	<!-- /Page Content -->
@include('modal.email-management-modal')
@include('modal.common')
@endsection 
@section('scripts')
<script src="{{ url('front-assets/js/page/email_management.js') }}"></script>
<script src="{{ url('front-assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>
//var csrfToken = "{{ csrf_token() }}";
$( document ).ready(function() {
	if ($.fn.DataTable.isDataTable('.datatable')) {
		$('.datatable').DataTable().destroy(); // Destroy existing instance
	}

	$('.datatable').DataTable({
		//searching: false,
		language: {
			"lengthMenu": "{{ __('Show _MENU_ entries') }}",
			"zeroRecords": "{{ __('No records found') }}",
			"info": "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
			"infoEmpty": "{{ __('No entries available') }}",
			"infoFiltered": "{{ __('(filtered from _MAX_ total entries)') }}",
			"search": "{{ __('search') }}",
			"paginate": {
				"first": "{{ __('First') }}",
				"last": "{{ __('Last') }}",
				"next": "{{ __('Next') }}",
				"previous": "{{ __('Previous') }}"
			},
		}
	});
});
</script>
@endsection
