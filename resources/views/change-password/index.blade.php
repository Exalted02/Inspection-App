@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <div class="row">
            <div class="col-md-6 offset-md-3">
				<form id="frmChangePassword" action="{{ route('change-password-save') }}">
					<h4 class="page-title">{{ __('change_password') }}</h4>
					<div class="row">
                        <div class="col-md-12">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('old_password') }}</label>
                                <input type="password" class="form-control" id="old_password" name="old_password">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('new_password') }}</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="input-block mb-3">
                                <label class="col-form-label">{{ __('confirm_new_password') }}</label>
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
								<div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary save-password" type="button" >{{ __('update_password') }}</button>
                    </div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- update Success message -->
<div class="modal custom-modal fade" id="updt_success_msg" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon">
						<i class="la la-pencil"></i>
					</div>
					<h3>{{ __('password_updated_successfully') }}!!!</h3>
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
	$(document).on('click','.save-password', function(){
		let isValid = true;
		if (isValid) {
			var form = $("#frmChangePassword");
			var URL = $('#frmChangePassword').attr('action');
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
