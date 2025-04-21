@extends('admin.layouts.app')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <form action="{{ route('email-settings-save') }}" method="POST">
                    @csrf
                    <div class="input-block mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="php_mail_smtp" id="phpmail" value="0">
                            <label class="form-check-label" for="phpmail">PHP Mail</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="php_mail_smtp" id="smtpmail" value="1">
                            <label class="form-check-label" for="smtpmail">SMTP</label>
                        </div>
                    </div>
                    <h4 class="page-title">PHP Email Settings</h4>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">Email From Address</label>
                                <input class="form-control" type="email" name="email_from_address">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">Emails From Name</label>
                                <input class="form-control" type="text" name="emails_from_name">
                            </div>
                        </div>
                    </div>
                    <h4 class="page-title m-t-30">SMTP Email Settings</h4>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">SMTP HOST</label>
                                <input class="form-control" type="text" name="smtp_host">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">SMTP USER</label>
                                <input class="form-control" type="text" name="smtp_user">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">SMTP PASSWORD</label>
                                <input class="form-control" type="password" name="smtp_password">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">SMTP PORT</label>
                                <input class="form-control" type="text" name="smpt_port">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">SMTP Security</label>
                                <select class="select" name="smtp_security">
                                    <option value="0">None</option>
                                    <option value="1">SSL</option>
                                    <option value="2">TLS</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">SMTP Authentication Domain</label>
                                <input class="form-control" type="text" name="smtp_authentication_domain">
                            </div>
                        </div>
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary" type="submit" >Save &amp; update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
	<!-- /Page Content -->
@include('modal.resources-modal')

@endsection 
@section('scripts')
<script src="{{ url('front-assets/js/page/business.js') }}"></script>
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
