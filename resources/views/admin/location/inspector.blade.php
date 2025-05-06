@extends('admin.layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($inspector);die;
@endphp
<!-- Page Wrapper -->
<div class="page-wrapper">
	<!-- Page Content -->
	<div class="content container-fluid">
	
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col-md-4">
					<h3 class="page-title">{{ __('users') }}</h3>
					<ul class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('dashboard') }}</a></li>
						<li class="breadcrumb-item active">{{ __('users') }}</li>
					</ul>
				</div>
				<div class="col-md-8 float-end ms-auto">
					<div class="d-flex title-head">
						<div class="view-icons">
							<a href="{{ route('admin.inspector') }}" class="grid-view btn btn-link"><i class="las la-redo-alt"></i></a>
							<a href="#" class="list-view btn btn-link" id="collapse-header"><i class="las la-expand-arrows-alt"></i></a>
							<a href="javascript:void(0);" class="list-view btn btn-link" id="filter_search"><i class="las la-filter"></i></a>
						</div>
						{{--<div class="form-sort">
							<a href="javascript:void(0);" class="list-view btn btn-link" data-bs-toggle="modal" data-bs-target="#export"><i class="fa-solid fa-file-export"></i>{{ __('export') }}</a>
						</div>
						<div class="form-sort">
							<a href="javascript:void(0);" class="list-view btn btn-link" data-bs-toggle="modal" data-bs-target="#import"><i class="fa-solid fa-file-import"></i>{{ __('import') }}</a>
						</div>--}}
						<a href="#" class="btn add-btn add_inspector" data-bs-toggle="modal" data-bs-target="#add_inspector"><i class="la la-plus-circle"></i> {{ __('add') }}</a>
					</div>
				</div>
			</div>
		</div>
		<!-- /Page Header -->
		
		<!-- Search Filter -->
		<div class="filter-filelds" id="filter_inputs">
		<form name="search-frm" method="post" action="{{ route('admin.manage-company-users', ['id'=>$company_id])}}" id="search-inspector-frm">
		@csrf
		<input type="hidden" value="{{ $company_id ?? ''}}" name="src_company_id">
			<div class="row filter-row">
				<div class="col-xl-3">  
					<div class="input-block">
						<select class="select" name="src_user_type" id="src_user_type">
							<option value="">{{ __('user_type') }}</option>
							<option value="1" {{ old('src_user_type', request('src_user_type')) == "1" ? 'selected' : '' }}>Inspector</option>
							<option value="2" {{ old('src_user_type', request('src_user_type')) == "2" ? 'selected' : '' }}>LO</option>
							<option value="3" {{ old('src_user_type', request('src_user_type')) == "3" ? 'selected' : '' }}>LOC</option>
							<option value="4" {{ old('src_user_type', request('src_user_type')) == "4" ? 'selected' : '' }}>Management</option>
						</select>
					</div>
				</div>
				<div class="col-xl-3">  
					 <div class="input-block">
						 <input type="text" class="form-control" name="search_name" placeholder="{{ __('inspector_name')}}" value="{{ old('search_name', request('search_name'))}}">
					 </div>
				</div>
				<div class="col-xl-3">  
					 <div class="input-block">
						 <input type="text" class="form-control" name="search_email" placeholder="{{ __('email')}}" value="{{ old('search_email', request('search_email'))}}">
					 </div>
				</div>
				{{--<div class="col-xl-3">  
					 <div class="input-block">
						 <select class="select" name="search_company_name">
							<option value="">{{ __('company_name') }}</option>
							@foreach($companies as $company)
								<option value="{{ $company->id ?? '' }}" {{ old('search_company_name', request('search_company_name')) == (string) $company->id ? 'selected' : '' }}>{{ $company->company_name ?? '' }}</option>
							@endforeach
						 </select>
					 </div>
				</div>--}}
				<div class="col-xl-3">  
					 <div class="input-block">
						<input type="text" class="form-control date-range" name="date_range_phone" placeholder="{{ __('from_to_date')}}" id="src_inspector_name_date_range" value="{{ old('date_range_phone', request('date_range_phone')) }}">
					 </div>
				 </div>
				 <div class="col-xl-2">  
					 <div class="input-block">
						 <select class="select" name="search_status">
							<option value="">{{ __('please_select') }}</option>
							<option value="1" {{ old('search_status', request('search_status')) == "1" ? 'selected' : '' }}>{{ __('active') }}</option>
							<option value="0" {{ old('search_status', request('search_status')) == "0" ? 'selected' : '' }}>{{ __('inactive') }}</option>
						</select>
					 </div>
				</div>
				<div class="col-xl-2 p-r-0">  
				<a href="javascript:void(0);" class="btn btn-success w-100 search-data"><i class="fa-solid fa-magnifying-glass"></i> {{ __('search') }} </a> 
				</div>
				<div class="col-xl-2 p-r-0">
					<button type="reset" class="btn custom-reset w-100 reset-button" data-id="1">
						<i class="fa-solid fa-rotate-left"></i> {{ __('reset') }}
					</button>
				</div>
			</div>
			</form>
		</div>
		 <hr>
		 <!-- /Search Filter -->
		 <div class="row">
		 @if($inspector->count() > 0)
			<div class="col-lg-6 mb-2">
				<div class="btn-group">
					<button type="button" class="btn action-btn add-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" onclick="change_multi_status('1','User','{{url('admin/change-multi-status')}}')">Active</a></li>
						<li><a class="dropdown-item" onclick="change_multi_status('0','User','{{url('admin/change-multi-status')}}')">Inactive</a></li>
						<li><a class="dropdown-item" onclick="delete_multi_data('User','{{url('admin/delete-multi-data')}}')">Delete</a></li>
					</ul>
				</div>
			</div>
		@endif
			<div class="col-lg-6">
				<div class="filter-section">
					<ul>
						{{--<li>
							<div class="view-icons">
								<a href="javascript:void(0);" class="list-view btn btn-link active"><i class="las la-list"></i></a>
								<a href="javascript:void(0);" class="grid-view btn btn-link"><i class="las la-th"></i></a>
							</div>
						</li>--}}
						@if($inspector->count() > 0)
						<li>
							<div class="form-sort">
								<i class="las la-sort-alpha-up-alt"></i>
								<form  method="post" action="" id="search-sortby">
						@csrf
								<select class="select search-sort-by form-control" name="search_sort_by">
									<option value="">{{ __('select_sort_by') }}</option>
									<option value="ASC" {{ request('search_sort_by') == 'ASC' ? 'selected' : '' }}>{{ __('select_sort_by_a_z') }}</option>
									<option value="DESC" {{ request('search_sort_by') == 'DESC' ? 'selected' : '' }}>{{ __('select_sort_by_z_a') }}</option>
									<option value="created_at" {{ request('search_sort_by') == 'created_at' ? 'selected' : '' }}>{{ __('recently_added') }}</option>
								</select>
								</form>
							</div>
						</li>
						@endif
					</ul>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-striped custom-table datatable">
						<thead>
							<tr>
							{{--<th class="no-sort"></th>--}}
							@if($inspector->count() > 0)
								<th>
									<label class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" id="checkAll">
									</label>
								</th>
							@endif
								{{--<th>{{ __('sl_no') }}</th>--}}
								<th>{{ __('user_type') }}</th>
								<th>{{ __('Name') }}</th>
								<th>{{ __('email') }}</th>
								<th>{{ __('company_name') }}</th>
								<th>{{ __('avatar') }}</th>
								<th>{{ __('backgroung_image') }}</th>
								<th>{{ __('created_date') }}</th>
								<th>{{ __('status') }}</th>
								<th class="text-end">Action</th>
							</tr>
						</thead>
						<tbody>
						@foreach($inspector as $val)
							<tr>
								@if($inspector->count() > 0)
								<td>
									<label class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" name="chk_id" data-emp-id="{{$val->id}}">
									</label>
								</td>
								@endif
								<td>{{ $val->user_type==1 ? 'Inspector' : ($val->user_type==2 ? 'LO' : ($val->user_type==3 ? 'LOS': 'Management' )) }}</td>
								<td>{{ $val->name ?? ''}}</td>
								<td>{{ $val->email ?? ''}}</td>
								<td>{{ $val->get_company->company_name ?? ''}}</td>
								<td><img src="{{  !empty($val->profile_image) ? url('uploads/profile/' . $val->id. '/inspector/'.$val->profile_image) : url('images/noimage.png') }}" width="50" height="50"></td>
								<td><img src="{{ !empty($val->background_image) ? url('uploads/profile/' . $val->id. '/inspector/'. $val->background_image) : url('images/noimage.png') }}" width="50" height="50"></td>
								<td>{{ date('d-m-Y', strtotime($val->created_at)) ?? ''}}</td>
								<td>
								@if($val->status ==1)
									<div class="dropdown action-label">
										<a class="btn btn-white btn-sm badge-outline-success dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="fa-regular fa-circle-dot text-success"></i> {{ __('active') }}
										</a>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item update-status" href="javascript:void(0);" data-id="{{ $val->id }}" data-url="{{ route('admin.inspector_update_status') }}"><i class="fa-regular fa-circle-dot text-success"></i> {{ __('active') }}</a>
											<a class="dropdown-item update-status" href="javascript:void(0);" data-id="{{ $val->id }}" data-url="{{ route('admin.inspector_update_status') }}"><i class="fa-regular fa-circle-dot text-danger"></i> {{ __('inactive') }}</a>
										</div>
									</div>
								 @else
									<div class="dropdown action-label">
										<a class="btn btn-white btn-sm badge-outline-danger dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="fa-regular fa-circle-dot text-danger"></i> {{ __('inactive') }}
										</a>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item update-status" href="javascript:void(0);" data-id="{{ $val->id }}" data-url="{{ route('admin.inspector_update_status') }}"><i class="fa-regular fa-circle-dot text-success"></i> {{ __('active') }}</a>
											<a class="dropdown-item update-status" href="javascript:void(0);" data-id="{{ $val->id }}" data-url="{{ route('admin.inspector_update_status') }}"><i class="fa-regular fa-circle-dot text-danger"></i> {{ __('inactive') }}</a>
										</div>
									</div> 
								 
								 @endif
								</td>
									
								
								<td class="text-end">
									<div class="dropdown dropdown-action">
										<a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
										<div class="dropdown-menu dropdown-menu-right">
											<a class="dropdown-item edit-inspector" href="javascript:void(0);" data-id="{{ $val->id ??''}}" data-url="{{ route('admin.edit-inspector') }}"><i class="fa-solid fa-pencil m-r-5"></i> {{ __('edit') }}</a>
											<a class="dropdown-item delete-inspector-name text-danger" href="javascript:void(0);" data-id="{{ $val->id ?? '' }}" data-url="{{ route('admin.getDeleteinspector') }}"><i class="fa-regular fa-trash-can m-r-5"></i> {{ __('delete') }}</a>
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
<input type="text" id="companyid" value="{{ $company_id ?? '' }}">
	<!-- /Page Content -->

@include('modal.common')
@include('admin.modal.inspector-modal')
@endsection 
@section('scripts')
@include('_includes.footer')
<script src="{{ url('admin-assets/js/page/inspector.js') }}"></script>
<script src="{{ url('admin-assets/js/page/common.js') }}"></script>
<script src="{{ url('admin-assets/js/search-calender.js') }}"></script>
<script src="{{ url('admin-assets/js/page/country_state_city.js') }}"></script>
<script>
$(document).ready(function() {
	
	$(document).on('click',".reset-button", function(){
		var company_id = $('#companyid').val();
		var url = "{{ route('admin.manage-company-users', ['id' => 'COMPANY_ID']) }}";
		url = url.replace('COMPANY_ID', company_id);
		window.location.href = url;
	});
	
	const translations = {
        addlocation: @json(__('add_inspector')),
    };
	
	$(document).on('click','.add_inspector', function(){
		$('#frminspector')[0].reset();
		$('#id').val('');
		$('#company_name').val('').trigger('change');
		$('#preview').attr('src', '').show();
		$('#preview_backgrnd').attr('src', '').show();
		$('#head-label').html(translations.addlocation);
		$('.invalid-feedback').hide();
		$('.form-control').removeClass('is-invalid');
	});
	
	const has_search = @json($has_search);
	if(has_search==1)
	{
		$('#filter_search').click();
	}
	
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
