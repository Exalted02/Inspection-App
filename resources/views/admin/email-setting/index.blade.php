@extends('admin.layouts.app')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <form id="frmEmailSettings" action="{{ route('admin.email-settings-save') }}">
                    
                    {{--<div class="input-block mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="php_mail_smtp" id="phpmail" value="0">
                            <label class="form-check-label" for="phpmail">PHP Mail</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="php_mail_smtp" id="smtpmail" value="1">
                            <label class="form-check-label" for="smtpmail">SMTP</label>
                        </div>
                    </div>--}}
                    <h4 class="page-title">{{ __('email_settings') }}</h4>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('email_from_address') }}</label>
                                <input class="form-control" type="email" id="email_from_address" name="email_from_address" value="{{$email->email_from_address ?? ''}}">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('email_from_name') }}</label>
                                <input class="form-control" type="text" id="emails_from_name" name="emails_from_name" value="{{$email->emails_from_name ?? ''}}">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <h4 class="page-title m-t-30">{{ __('smtp_email_settings') }}</h4>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('smtp_host') }}</label>
                                <input class="form-control" type="text" id="smtp_host" name="smtp_host" value="{{$email->smtp_host ?? ''}}">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('smtp_user') }}</label>
                                <input class="form-control" type="text" id="smtp_user" name="smtp_user" value="{{$email->smtp_user ?? ''}}">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('smtp_password') }}</label>
                                <input class="form-control" type="password" id="smtp_password" name="smtp_password" value="{{$email->smtp_password ?? ''}}">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('smtp_port') }}</label>
                                <input class="form-control" type="text" id="smpt_port" name="smpt_port" value="{{$email->smpt_port ?? ''}}">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('smtp_security') }}</label>
                                <select class="select" id="smtp_security" name="smtp_security">
                                    <option value="0" {{ isset($email->smtp_security) && $email->smtp_security == 0 ? 'selected' : '' }}>{{ __('none') }}</option>
                                    <option value="1" {{ isset($email->smtp_security) && $email->smtp_security == 1 ? 'selected' : '' }}>{{ __('ssl') }}</option>
                                    <option value="2" {{ isset($email->smtp_security) && $email->smtp_security == 2 ? 'selected' : '' }}>{{ __('tls') }}</option>
                                </select>
                            </div>
                        </div>
                        {{--<div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">SMTP Authentication Domain</label>
                                <input class="form-control" type="text" name="smtp_authentication_domain">
                            </div>
                        </div>--}}
                    </div>
                    <h4 class="page-title m-t-30">{{ __('admin_email_settings') }}</h4>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('admin_email') }}</label>
                                <input class="form-control" type="email" id="admin_email" name="admin_email" value="{{$email->admin_email ?? ''}}">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary save-update" type="button" >{{ __('save_and_update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
	<!-- /Page Content -->
<!-- update Success message -->
<div class="modal custom-modal fade" id="updt_success_msg" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon">
						<i class="la la-pencil"></i>
					</div>
					<h3>{{ __('data_updated_successfully') }}!!!</h3>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection 
@section('scripts')
<script>
//var csrfToken = "{{ csrf_token() }}";
$( document ).ready(function() {
	$(document).on('click','.save-update', function(){
		let isValid = true;
		if (isValid) {
			var form = $("#frmEmailSettings");
			var URL = $('#frmEmailSettings').attr('action');
			$.ajax({
				url: URL,
				type: "POST",
				data: form.serialize() + '&_token=' + csrfToken,
				//dataType: 'json',
				success: function(response) {
					$('#updt_success_msg').modal('show');
					setTimeout(() => {
						window.location.reload();
					}, "2000");
				},
				error: function(xhr) {
					// Handle validation errors
					if (xhr.status === 422) {
						const errors = xhr.responseJSON.errors;
						let errorMessage = '';

						$('.invalid-feedback').hide();
						$('.form-control').removeClass('is-invalid');
						// Loop through errors and format them
						$.each(errors, function(key, value) {
							errorMessage += value[0] + '\n'; // Assuming you want the first error message for each field
							if ($('#'+key).length) { // Which have id
								$('#'+key).addClass('is-invalid');
								$('#'+key).next('.invalid-feedback').show().text(value[0]);
							}else{ // Which have not any id
								var fieldName = key.split('.')[0]; // Get the base field name (e.g., product_sale_price)
								var index = key.split('.').pop();
								var inputField = $('input[name="' + fieldName + '[]"]').eq(index);
								inputField.addClass('is-invalid');
								inputField.next('.invalid-feedback').show().text(value[0]);
							}
						});
						// Display the errors
						//alert('Validation errors:\n' + errorMessage);
					} else if(xhr.status == 403){
						$('#module_permission').modal('show');
						setTimeout(() => {
							window.location.reload();
						}, "2000");
					}else{
						alert('An unexpected error occurred.');
					}
				}
			});
		}
	});
});
</script>
@endsection
