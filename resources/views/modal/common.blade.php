<div class="modal fade" id="confirmChkSelect" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon bg-warning">
						<i class="la la-meh-blank"></i>
					</div>
					<h3>Please check checkbox.</h3>
					<div class="col-lg-12 text-center form-wizard-button">
						<a href="#" class="button btn-lights" data-bs-dismiss="modal">OK</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="confirmMultiStatus" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">			
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon bg-warning">
						<i class="la la-meh-blank"></i>
					</div>
					<h3></h3>
					<div class="col-lg-12 text-center form-wizard-button">
						<a href="#" class="button btn-lights" data-bs-dismiss="modal">Cancel</a>
						<button type="button" class="button btn-primary" id="change">Submit</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="confirmMultiDelete" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">			
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon bg-warning">
						<i class="la la-meh-blank"></i>
					</div>
					<h3></h3>
					<div class="col-lg-12 text-center form-wizard-button">
						<a href="#" class="button btn-lights" data-bs-dismiss="modal">Cancel</a>
						<button type="button" class="button btn-primary" id="change_delete">Submit</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Success status -->
<div class="modal custom-modal fade" id="success_status_msg" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon">
						<i class="la la-user-shield"></i>
					</div>
					<h3>{{ __('status_updated_successfully') }}!!!</h3>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Success delete -->
<div class="modal custom-modal fade" id="success_delete_msg" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon">
						<i class="la la-user-shield"></i>
					</div>
					<h3>{{ __('data_deleted_successfully') }}!!!</h3>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /Success Contact -->

<!-- No permission status -->
<div class="modal custom-modal fade" id="module_permission" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon bg-danger">
						<i class="la la-lock"></i>
					</div>
					<h3>{{ __('no_permission_module_message') }}!!!</h3>
				</div>
			</div>
		</div>
	</div>
</div>