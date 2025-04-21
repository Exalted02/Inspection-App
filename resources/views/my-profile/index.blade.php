@extends('layouts.app')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h3 class="page-title">{{ __('my_profile') }}</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="admin-dashboard.html">Dashboard</a></li>
                                <li class="breadcrumb-item active">{{ __('my_profile') }}</li>
                            </ul>
                        </div>  
                    </div>
                </div>
                <!-- /Page Header -->

                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <!-- Content Starts -->
					
							<!-- <h4 class="card-title">Solid justified</h4> -->
							<ul class="nav nav-tabs nav-tabs-bottom">
								<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_resource"><span><i class="fa-solid fa-user-plus"></i> {{ __('resource') }}</span></a></li>
								<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_personal"><span><i class="fa-solid fa-user"></i> {{ __('personal') }}</span></a></li>
							</ul>

					<div class="tab-content">
							<!-- Additions Tab -->
							<div class="tab-pane show active" id="tab_resource">
									<form id="frmprospectstage" action="{{ route('user.save-prospect-stage') }}">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-2 col-lg-2 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('name') }}</label>
                                                                    <input class="form-control" type="text"placeholder = "Enter Name">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 col-lg-2 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('gender') }}</label>
                                                                    <select class="select">
                                                                        <option>Select</option>
                                                                        <option>{{ __('male') }}</option>
                                                                        <option>{{ __('female') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 col-lg-2 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('marital_status') }}</label>
                                                                    <select class="select">
                                                                        <option>Select</option>
                                                                        <option>{{ __('unMarried') }}</option>
                                                                        <option>{{ __('married') }}</option>
                                                                        <option>{{ __('widow') }}</option>
                                                                        <option>{{ __('divorced') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 col-lg-2 col-sm-12">
                                                                <div class="input-block">
                                                                <label class="col-form-label">{{ __('date_of_birth') }}</label>
                                                                    <div class="cal-icon">
                                                                        <input class="form-control floating datetimepicker" type="text"  name="dob" id="dob">
                                                                    </div>                           
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-md-2 col-lg-2 col-sm-12">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('religion') }}</label>
                                                                    <select class="select">
                                                                        <option>Select</option>
                                                                        <option>{{ __('hindu') }}</option>
                                                                        <option>{{ __('muslim') }}</option>
                                                                        <option>{{ __('Sikh') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 col-lg-2 col-sm-12">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('cast') }}</label>	
                                                                    <select class="select">
                                                                        <option>Select</option>
                                                                        <option>{{ __('general') }}</option>
                                                                        <option>{{ __('oBC') }}</option>
                                                                        <option>{{ __('st') }}</option>
                                                                        <option>{{ __('sc') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 col-lg-2 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('official_mobile_no') }}</label>  
                                                                    <input class="form-control" type="text"placeholder = "Enter official mobile no">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 col-lg-2 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('official_whatsApp_no') }}</label>  
                                                                    <input class="form-control" type="text"placeholder = "Enter Official whatsapp no">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 col-lg-2 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('official_email_id') }}</label>  
                                                                    <input class="form-control" type="text"placeholder = "Enter Official email id">                           
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>	
                                        </div>	
									</form>
								<!-- /Payroll Additions Table -->
							</div>
							<!-- /Additions Tab -->

							<!-- Deductions Tab -->
							<div class="tab-pane show" id="tab_personal">
									<form id="frmprospectstage">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('personal_primary_mobile_no') }}</label>
                                                                    <input class="form-control" type="text" placeholder = "Enter personal primary mobile no">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('personal_secondary_mobile_no') }}</label>
                                                                    <input class="form-control" type="text" placeholder = "Enter personal secondary mobile no">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('personal_primary_whatsApp_no') }}</label>
                                                                    <input class="form-control" type="text" placeholder = "Enter personal primary whatsApp no">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('personal_secondary_whatsApp_no') }}</label>
                                                                    <input class="form-control" type="text" placeholder = "Enter personal secondary whatsApp no">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('personal_primary_email_id') }}</label>
                                                                    <input class="form-control" type="text" placeholder = "Enter personal primary email id">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('personal_secondary_email_id') }}</label>
                                                                        <input class="form-control" type="text" placeholder = "Enter personal secondary email id">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('date_of_anniversary') }}</label>
                                                                    <div class="cal-icon">
                                                                        <input class="form-control floating datetimepicker" type="text">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                <label class="col-form-label">{{ __('date_of_birth') }}</label>
                                                                    <div class="cal-icon">
                                                                        <input class="form-control floating datetimepicker" type="text"  name="dob" id="dob">
                                                                    </div>                           
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12 mt-3">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('address_1') }}</label>
                                                                        <input class="form-control" type="text" placeholder = "Enter address 1">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12 mt-3">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('address_2') }}</label>
                                                                        <input class="form-control" type="text" placeholder = "Enter address 2">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12 mt-3">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('landmark') }}</label>
                                                                        <input class="form-control" type="text" placeholder = "Enter landmark">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12 mt-3">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('country') }}</label>
                                                                    <select class="select">
                                                                        <option>Select</option>
                                                                        <option>Collins</option>
                                                                        <option>Konopelski</option>
                                                                        <option>Adams</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('select_state') }}</label>
                                                                    <select class="select">
                                                                        <option>Select</option>
                                                                        <option>Collins</option>
                                                                        <option>Konopelski</option>
                                                                        <option>Adams</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('select_city') }}</label>
                                                                    <select class="select">
                                                                        <option>Select</option>
                                                                        <option>Collins</option>
                                                                        <option>Konopelski</option>
                                                                        <option>Adams</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                        <label class="col-form-label">{{ __('pincode') }}</label>
                                                                        <input class="form-control" type="text" placeholder = "Enter pincode">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12"></div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12 mt-3">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('addhar_no') }}</label>
                                                                    <input class="form-control" type="file">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12 mt-3">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('pan_no') }}</label>
                                                                    <input class="form-control" type="file">
                                                                </div>
                                                            </div>													
                                                            <div class="col-md-3 col-lg-3 col-sm-12 mt-3">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('voter_card') }}</label>
                                                                    <input class="form-control" type="file">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12 mt-3">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('driving_license') }}</label>
                                                                    <input class="form-control" type="file">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('madhyamik_sheet') }}</label>
                                                                    <input class="form-control" type="file">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('higher_secondary') }}</label>
                                                                    <input class="form-control" type="file">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('graduate') }}</label>
                                                                    <input class="form-control" type="file">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                                <div class="input-block">
                                                                    <label class="col-form-label">{{ __('post_graduate') }}</label>
                                                                    <input class="form-control" type="file">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-lg-3 col-sm-12">
                                                            </div>
                                                            <div class="col-md-12 col-lg-12 col-sm-12 mt-3 mb-2"><h5>{{ __('local_reference') }}</h5></div>
                                                            <div class="col-md-12 lead-myprofile-col del-myprofile-col border-top">
                                                                <div class="row">
                                                                    <div class="col-md-3 col-lg-3 col-sm-12 mt-2">
                                                                        <div class="input-block">
                                                                            <label class="col-form-label">{{ __('name') }}</label>
                                                                            <input class="form-control" type="text" placeholder = "Enter name">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-lg-3 col-sm-12 mt-2">
                                                                        <div class="input-block">
                                                                            <label class="col-form-label">{{ __('mobile') }}</label>
                                                                            <input class="form-control" type="text" placeholder = "Enter mobile">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-lg-3 col-sm-12 mt-2">
                                                                        <div class="input-block">
                                                                            <label class="col-form-label">{{ __('email') }}</label>
                                                                            <input class="form-control" type="text" placeholder = "Enter email">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2 col-lg-2 col-sm-12 mt-2">
                                                                        <div class="input-block">
                                                                            <label class="col-form-label">{{ __('relations') }}</label>
                                                                            <input class="form-control" type="text" placeholder = "Enter relations">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-1 col-lg-1 col-sm-12 mt-3">
                                                                        <div class="add-modal-row-icon input-block">
                                                                            <a href="#" class="add-modal-row add-myprofile-info"><i class="la la-plus-circle"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>	
                                        </div>	
									</form>
								<!-- /Payroll Additions Table -->
							</div>
							<!-- /Deductions Tab tab -->

						</div>
                </div>
                <div class="modal-footer ">
                    <div class="row">
                        <div class="col-lg-12 text-end form-wizard-button">
                            <button class="button btn-lights reset-btn" type="reset">{{ __('reset') }}</button>
                            <button class="btn btn-primary wizard-next-btn" type="button">{{ __('save') }}</button>
                        </div>					
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Content -->

</div>
	<!-- /Page Content -->
@include('modal.resources-modal')

@endsection 
@section('scripts')
<script src="{{ url('front-assets/js/page/my_profile.js') }}"></script>
<script>
//var csrfToken = "{{ csrf_token() }}";
$( document ).ready(function() {
	if ($.fn.DataTable.isDataTable('.datatable')) {
		$('.datatable').DataTable().destroy(); // Destroy existing instance
	}

	$('.datatable').DataTable({
		//searching: false,
		language: {
			"lengthMenu": "{{ __('Show_MENU_entries') }}",
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
