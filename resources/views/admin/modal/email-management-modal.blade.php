<!--- edit product code -->
<div id="edit_product_code" class="modal custom-modal fade" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ __('edit_new_stage') }}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="frmeditproductcode" action="{{ route('admin.email-management-edit-save') }}">
				<input type="hidden" id="id" name="id">
					<div class="row">
						<div class="col-sm-12">
							<div class="input-block mb-3">
								<label class="col-form-label">{{ __('email_subject') }}<span class="text-danger">*</span></label>
								<input class="form-control" type="text" name="message_subject" id="message_subject">
								<div class="invalid-feedback">{{ __('please_enter') }} {{ __('email_subject')}}.</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="input-block mb-3">
								<label class="col-form-label">{{ __('email_message') }}<span class="text-danger">*</span></label>
								<textarea class="form-control summernote" name="message" id="message" ></textarea>
								<div class="invalid-feedback">{{ __('please_enter') }} {{ __('email_message')}}.</div>
							</div>
						</div>
					</div>
					<div class="submit-section">
						<button class="btn btn-primary submit-btn update-product-code-form" type="button">Submit</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /Edit Contact -->
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
