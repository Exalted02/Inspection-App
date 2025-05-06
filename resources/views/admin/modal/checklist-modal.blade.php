<div class="modal custom-modal fade" id="delete_location_modal" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon bg-danger" id="delete-msg">
						<i class="la la-trash-restore"></i>
					</div>
					<h3>{{ __('are_you_sure') }}, {{ __('you_want_delete') }}</h3>
					<p>{{ __('checklist_name') }} "<span id="list_name"></span>" {{ __('from_your_account') }}</p>
					<div class="col-lg-12 text-center form-wizard-button">
						<a href="#" class="button btn-lights" data-bs-dismiss="modal">{{ __('not_now') }}</a>
						<a href="javascript:void(0);" class="btn btn-primary data-id-list" data-url="{{ route('admin.deletechecklist') }}">{{ __('okay') }}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- Add product code -->
<div id="add_checklist" class="modal custom-modal fade" role="dialog">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title"><span id="head-label">{{ __('add_location') }}</span></h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form id="frmchecklist" action="{{ route('admin.save-checklist') }}" enctype="multipart/form-data">
								<input type="hidden" value="{{ $category_id }}" name="category">
								<input type="hidden" value="{{ $subcategory_id }}" name="subcategory">
								<input type="hidden" id="id" name="id">
								{{--<div class="row">
										<div class="col-sm-12">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('category_name') }}<span class="text-danger">*</span></label>
												<select class="select select_category" name="category" id="category" data-url="{{ route('admin.get-subcategory')}}">
													<option value="">{{ __('category') }}</option>
													@foreach($categories as $category)
													<option value="{{  $category->id ?? ''}}">{{  $category->name ?? ''}}</option>
													@endforeach
												</select>
												<div class="invalid-feedback">{{ __('please_select') }} {{ __('category')}}.</div>
											</div>
										</div>
									</div>--}}
									{{--<div class="row">
										<div class="col-sm-12">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('sub_category_name') }}<span class="text-danger">*</span></label>
												<select class="select subcategory" name="subcategory" id="subcategory">
													<option value="">{{ __('sub_category') }}</option>
												</select>
												<div class="invalid-feedback">{{ __('please_select') }} {{ __('subcategory')}}.</div>
											</div>
										</div>
									</div>--}}
									<div class="row">
										<div class="col-sm-12">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('checklist_name') }}<span class="text-danger">*</span></label>
												<input type="text" name="name" id="name" class="form-control">
												<div class="invalid-feedback">{{ __('please_enter') }} {{ __('checklist_name')}}.</div>
											</div>
										</div>
									</div>
									<div class="submit-section">
										<button class="btn btn-primary submit-btn save-checklist" type="button">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
<!-- /Add product code -->

<!--- edit product code -->
{{--<div id="edit_product_code" class="modal custom-modal fade" role="dialog">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">{{ __('edit_new_stage') }}</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form id="frmeditproductcode" action="{{ route('admin.save-company-name') }}">
								<input type="hidden" id="id" name="id">
									<div class="row">
										<div class="col-sm-12">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('company_name') }}<span class="text-danger">*</span></label>
												<input class="form-control" type="text" name="company_name" id="company_name">
												<div class="invalid-feedback">{{ __('please_enter') }} {{ __('product_code')}}.</div>
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
				</div>--}}
<!-- /Edit Contact -->

<!-- Success Contact -->
<div class="modal custom-modal fade" id="success_msg" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon">
						<i class="la la-plus"></i>
					</div>
					<h3>{{ __('data_created_successfully') }}!!!</h3>
						{{--<p>{{ __('view_details_contact') }}</p>--}}
						{{--<div class="col-lg-12 text-center form-wizard-button">
						<a href="#" class="button btn-lights" data-bs-dismiss="modal">{{ __('close') }}</a>
						<a href="contact-details.html" class="btn btn-primary">{{ __('view_details') }}</a>
					</div>--}}
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Success Contact -->
<div class="modal custom-modal fade" id="data_already_use" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon bg-danger">
						<i class="la la-times-circle"></i>
					</div>
					<h3>{{ __('data_already_use') }}!!!</h3>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /Success Contact -->
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
						{{--<p>{{ __('view_details_deal') }}</p>--}}
						{{--<div class="col-lg-12 text-center form-wizard-button">
						<a href="#" class="button btn-lights" data-bs-dismiss="modal">{{ __('close') }}</a>
						<a href="contact-details.html" class="btn btn-primary">{{ __('view_details') }}</a>
					</div>--}}
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Export -->
{{--<div class="modal custom-modal fade modal-padding" id="export" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header header-border justify-content-between p-0">
				<h5 class="modal-title">{{ __('export') }}</h5>
				<button type="button" class="btn-close position-static" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body p-0">
				<form action="{{ route('export.product-code') }}" method="post" id="exportForm">
				@csrf
					<div class="row">
						<div class="col-md-6">
							<div class="input-block mb-3">
								<label class="col-form-label">{{ __('from_date') }} <span class="text-danger">*</span></label>
								<div class="cal-icon">	<input class="form-control floating datetimepicker" type="text" name="export_from_date" required>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="input-block mb-3">
								<label class="col-form-label">{{ __('to_date') }} <span class="text-danger">*</span></label>
								<div class="cal-icon">
									<input class="form-control floating datetimepicker" type="text" name="export_end_date" required>
								</div>
							</div>
						</div>
						<div class="col-lg-12 text-end form-wizard-button">
							<button class="button btn-lights reset-btn" type="reset" data-bs-dismiss="modal">{{ __('reset') }}</button>
							<button class="btn btn-primary" type="submit">{{ __('export') }} {{ __('now') }}</button>
						</div>
					</div>
				</form>
				
			</div>
		</div>
	</div>
</div>--}}
<!-- /Export -->

<!-- Import -->
{{--<div class="modal custom-modal fade modal-padding" id="import" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header header-border justify-content-between p-0">
				<h5 class="modal-title">{{ __('import') }}</h5>
				<button type="button" class="btn-close position-static" data-bs-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body p-0">
				<form action="{{ route('import.product-code') }}" method="post" enctype="multipart/form-data" id="importForm">
				@csrf
					<div class="row">
						<div class="col-md-12">
							<div class="input-block mb-3">
								<label class="col-form-label">{{ __('upload') }} <span class="text-danger">*</span> <span class="text-success">(Upload only xlsx file)</span></label>
								<div>	<input class="form-control" type="file" name="import_excel" accept=".xlsx, .xls" required>
								</div>
							</div>
						</div>
						
						<div class="col-lg-12 d-flex justify-content-between align-items-center">
							<div class="form-check p-0">
								<a href="{{ route('user.download-demo-product-code')}}"><button type="button" class="btn btn-primary downloaddemo"><i class="fa-solid fa-download me-1"></i> {{ __('download_sample_file') }}</button></a>
							</div>
							<div class="form-wizard-button">
								<button class="button btn-lights reset-btn" type="reset" data-bs-dismiss="modal">{{ __('reset') }}</button>
								<button class="btn btn-primary" type="submit">{{ __('import') }} {{ __('now') }}</button>
							</div>
						</div>
					</div>
				</form>
				
			</div>
		</div>
	</div>
</div>--}}
<!-- /Import -->