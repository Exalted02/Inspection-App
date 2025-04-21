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
				<div class="card">
					<div class="card-header">
						<h5 class="card-title">{{ __('edit') }}</h5>
					</div>
					<div class="card-body">
						<form id="frmeditproductcode" action="{{ route('email-management-edit-save') }}">
						<input type="hidden" id="id" name="id" value="{{$data->id}}">
							<div class="row">
								<div class="col-sm-12">
									<div class="input-block mb-3">
										<label class="col-form-label">{{ __('email_subject') }}<span class="text-danger">*</span></label>
										<input class="form-control" type="text" name="message_subject" id="message_subject" value="{{$data->message_subject}}">
										<div class="invalid-feedback"></div>
									</div>
								</div>
								<div class="col-sm-12">
									<div class="input-block mb-3">
										<label class="col-form-label">{{ __('email_message') }}<span class="text-danger">*</span></label>
										<textarea class="form-control summernote" name="message" id="message" >{{$data->message}}</textarea>
										<div class="invalid-feedback"></div>
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
	</div>
</div>
	<!-- /Page Content -->
@include('modal.email-management-modal')
@include('modal.common')
@endsection 
@section('scripts')
<script src="{{ url('front-assets/js/page/email_management.js') }}"></script>
<script src="{{ url('front-assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
@endsection
