<div class="modal custom-modal fade" id="delete_location_modal" role="dialog">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body">
				<div class="success-message text-center">
					<div class="success-popup-icon bg-danger" id="delete-msg">
						<i class="la la-trash-restore"></i>
					</div>
					<h3>{{ __('are_you_sure') }}, {{ __('you_want_delete') }}</h3>
					<p>{{ __('location_owner_supervisor_name') }} "<span id="list_name"></span>" {{ __('from_your_account') }}</p>
					<div class="col-lg-12 text-center form-wizard-button">
						<a href="#" class="button btn-lights" data-bs-dismiss="modal">{{ __('not_now') }}</a>
						<a href="javascript:void(0);" class="btn btn-primary data-id-list" data-url="{{ route('admin.deletelocationownerList') }}">{{ __('okay') }}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- Add product code -->
<div id="add_location_owner_supervisor" class="modal custom-modal fade" role="dialog">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title"><span id="head-label">{{ __('add_location') }}</span></h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form id="frmlocationownersupervisor" action="{{ route('admin.save-location-owner-supervisor') }}" enctype="multipart/form-data">
								<input type="hidden" id="id" name="id">
									<div class="row">
										<div class="col-sm-6">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('location_owner_supervisor_name') }}<span class="text-danger">*</span></label>
												<input class="form-control" type="text" name="name" id="name">
												<div class="invalid-feedback">{{ __('please_enter') }} {{ __('name')}}.</div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('email') }}<span class="text-danger">*</span></label>
												<input class="form-control" type="text" name="email" id="email">
												<div class="invalid-feedback">{{ __('please_enter') }} {{ __('email')}}.</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('password') }}<span class="text-danger">*</span></label>
												<input class="form-control" type="password" name="password" id="password">
												<div class="invalid-feedback">{{ __('please_enter') }} {{ __('password')}}.</div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('company_name') }}<span class="text-danger">*</span></label>
												<select class="select" name="company_name" id="company_name">
													<option value="">{{ __('company_name') }}</option>
													@foreach($companies as $company)
														<option value="{{ $company->id ?? '' }}">{{ $company->company_name ?? '' }}</option>
													@endforeach
												
												</select>
												<div class="invalid-feedback">{{ __('please_enter') }} {{ __('company_name')}}.</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-sm-6">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('avatar') }}</label>
												<input class="form-control" type="file" name="avatar" id="avatar" accept="image/*">
												<div class="invalid-feedback">{{ __('please_enter') }} {{ __('image')}}.</div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="input-block mb-3">
											<label class="col-form-label"></label>
											<img id="preview" src="#" alt="" style="max-width: 70px; margin-top: 25px; display: none;" />
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="input-block mb-3">
												<label class="col-form-label">{{ __('backgroung_image') }}</label>
												<input class="form-control" type="file" name="backgroung_image" id="backgroung_image" accept="image/*">
												<div class="invalid-feedback">{{ __('please_enter') }} {{ __('backgroung_image')}}.</div>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="input-block mb-3">
											<label class="col-form-label"></label>
											<img id="preview_backgrnd" src="#" alt="" style="max-width: 70px; margin-top: 25px; display: none;" />
											</div>
										</div>
									</div>
									<div class="row">
										<div class="tab-content" id="pills-tabContent">
											<div class="tab-pane fade" id="pills-public" role="tabpanel" aria-labelledby="pills-public-tab">

											</div>
											<div class="tab-pane fade" id="pills-private" role="tabpanel" aria-labelledby="pills-private-tab">
											</div>
											<div class="tab-pane fade show active" id="pills-select-people" role="tabpanel" aria-labelledby="pills-select-people-tab">
												<div class="people-select-tab" style="max-height: 200px; overflow-y: auto;">
													<div class="invalid-feedback">{{ __('please_select') }} {{ __('location')}}.</div>
													<h3>Select Location</h3>
													@foreach($locations as $location)
														<div class="select-people-checkbox-s">
															<label class="custom_check">
																<input type="checkbox" name="location[]" value="{{ $location->id }}">													
																<span class="checkmark"></span>
																<span class="people-profile">
																	<a href="#">{{ $location->location_name ?? '' }}</a>
																</span>
															</label>
														</div>
													@endforeach
												</div>
											</div>
										</div>
									</div>
									<input type="hidden" id="hid_avatar" name="hid_avatar">
									<input type="hidden" id="hid_back_grd_image" name="hid_back_grd_image">
									<div class="submit-section">
										<button class="btn btn-primary submit-btn save-location-owner-supervisor" type="button">Submit</button>
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