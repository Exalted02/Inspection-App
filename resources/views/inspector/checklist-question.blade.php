@extends('layouts.app')
@section('component-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
@endsection
@section('content')
@php
//echo $previous_checklist_id; die;
//echo "<pre>";print_r($checklistdata);die;
//echo "<pre>";print_r($checklistdata->get_subchecklist);die;


$total_checklist = [];
$countCheklist = 0;
$percentage = '';
//if(!empty($checklistdata->category_id) && !empty($checklistdata->subcategory_id))
if(!empty($checklistdata->category_id))
{
	$total_checklist = App\Models\Checklist::where('category_id', $checklistdata->category_id)->orderBy('order_no')->get();
	$countCheklist  = $total_checklist->count();
	$percentage = ceil(100/$countCheklist);
}
//echo "<pre>";print_r($total_checklist); die;
$existingFiles = [];

$existingSubChecklistFiles = [];

if ($checklistdata && $checklistdata->get_subchecklist && $checklistdata->get_subchecklist->isNotEmpty()) {
	foreach ($checklistdata->get_subchecklist as $subchecklists) {
		$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_checklist_id',$subchecklists->checklist_id)->where('subchecklist_id', $subchecklists->id)->first();

		if ($subchecklistData) {
			$task_list_subchecklist_id = $subchecklistData->id;
			$files = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $task_list_subchecklist_id)->get();

			foreach ($files as $file) {
				$existingSubChecklistFiles[] = [
					'subchecklist_id' => $subchecklists->id,
					'name' => $file->file,
					'url' => asset('uploads/reject-files/subchecklist/' . $file->file),
				];
			}
		}
	}
}
//echo "<pre>";print_r($existingSubChecklistFiles);die;
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist-question" style="display : {{ $isFinalEdit== 'no' ? 'block' : 'none' }}">
	
	<button class="task-filter-button save-next">Save & exit</button>
	
	@if(isset($checklistdata) && $checklistdata->get_subchecklist->isEmpty())
		@php 
			$checkImageFile = App\Models\Task_list_checklists::where('task_list_id',$task_id)
						->where('checklist_id',$checklistdata->id)->first();
			$task_list_checklist_id = $checkImageFile ? $checkImageFile->id : null;
			$rejected_region = $checkImageFile ? $checkImageFile->rejected_region : '';
			$approve = $checkImageFile ? $checkImageFile->approve : '';
			//$existingFiles = [];
			if (isset($task_list_checklist_id)) {
				$imageData = App\Models\Task_list_checklist_rejected_files::where('task_list_checklist_id', $task_list_checklist_id)->get();
				foreach ($imageData as $file) {
					$filename = $file->file;
					$existingFiles[] = [
						'name' => $filename,
						'size' => file_exists(public_path('uploads/reject-files/' . $filename)) ? filesize(public_path('uploads/reject-files/' . $filename)) : 123456, // default if unknown
						'url' => asset('uploads/reject-files/' . $filename),
					];
				}
			}
				
		@endphp
		<div class="single-checklist d-none1">
		{{--<div class="question-header">{{ $checklistdata->get_subcategory->name ?? '' }}</div>--}}
			<div class="question-header">{{ $checklistdata->get_category->name ?? '' }}</div>
			<div class="question-text">
				<span id="single-question">{{ $checklistdata->name ?? '' }}</span>
			</div>
			<span id="errormsg" class="question-error-message">
				Please enter text or file.
			</span>
			<div class="reject-form mb-3" id="rejectForm-1">
				<textarea id="single_rejecttext" placeholder="State why you rejected this...">{{ $rejected_region ??  '' }}</textarea>
				<input type="hidden" id="mode" value="single">
				<input type="hidden" id="hasEditFile" value="">
				<input type="hidden" id="approveStatus" value="{{ $approve}}">
				<form action="{{ route('reject-files')}}" class="dropzone" id="dropzone-1">
					<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="{{ $checklistdata->id ?? '' }}">
					<input type="hidden" name="subcategory_id" id="single-subcategory_id" value="{{ $checklistdata->subcategory_id ?? '' }}">
					<input type="hidden" name="checklistid" id="single-checklistid" value="{{ $checklistdata->checklist_id ?? '' }}">
					<input type="hidden" name="task_id" id="single-task_id" value="{{ $task_id ?? '' }}">
				</form>
			</div>
			<div class="action-buttons-without-text">
				<button class="rejected" id="question-reject-1" onclick="handleReject(1)"><i class="fa-solid fa-xmark"></i></button>
				<button class="approved" id="question-approve-1" onclick="handleApprove(1)"><i class="fa-solid fa-check"></i></button>
				<button class="notavailable" id="question-na-1" onclick="handleNotavailable(1)"><span class="button-na">NA</span></button>
			</div>
		</div>
	@else
		<div class="sub-checklist">
		{{--<div class="question-header">{{ $checklistdata->get_subcategory->name ?? '' }}</div>--}}
			<div class="question-header">{{ $checklistdata->get_category->name ?? '' }}</div>
			<div class="question-text">
				<span id="multiple-question">{{ $checklistdata->name ?? '' }}:</span>
				<a href="#" class="get_checklist" id="getchecklist" data-cat="{{ $checklistdata->category_id ?? '' }}" data-subcat="{{ $checklistdata->subcategory_id ?? '' }}" data-task="{{ $task_id }}" data-checklist="{{ $checklistdata->id ?? '' }}"></a>
			</div>
			@if($checklistdata && $checklistdata->get_subchecklist && $checklistdata->get_subchecklist->isNotEmpty())
				@foreach($checklistdata->get_subchecklist as $subchecklists)
				@php 
					$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_checklist_id', $subchecklists->checklist_id)->where('subchecklist_id', $subchecklists->id)->first();
					$subChkId			=  $subchecklistData ? $subchecklistData->id : '';
					$rejected_region	=  $subchecklistData ? $subchecklistData->rejected_region : '';
					$approve			=  $subchecklistData ? $subchecklistData->approve : '';
				@endphp
				<div class="sub-checklist-question">
					<div class="action-buttons">
						<span class="d-flex align-items-center">{{ $subchecklists->name ?? ''}}</span>
						<div class="btn-div">
							<button class="rejected" id="question-reject-{{ $subchecklists->id }}" onclick="handleReject({{ $subchecklists->id }})"><i class="fa-solid fa-xmark"></i></button>
							<button class="approved" id="question-approve-{{ $subchecklists->id }}" onclick="handleApprove({{ $subchecklists->id }})"><i class="fa-solid fa-check"></i></button>
							<button class="notavailable" id="question-na-{{ $subchecklists->id }}" onclick="handleNotavailable({{ $subchecklists->id }})"><span class="button-na">NA</span></button>
						</div>
					</div>
					<span id="errorMulmsg{{ $subchecklists->id }}"  class="question-error-message">
					Please enter text or file.
					</span>
					<div class="reject-form mb-3" id="rejectForm-{{ $subchecklists->id }}">
						<textarea placeholder="State why you rejected this..."> {{ $rejected_region ?? ''}} </textarea>
						<input type="hidden" id="mode" value="multiple">
						<input type="hidden" id="approveMultipleStatus{{$subchecklists->id}}" value="">
						
						
						<form action="{{ route('reject-subchecklist-files')}}" class="dropzone" id="dropzon-{{ $subchecklists->id }}">
							@csrf
							<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="{{ $checklistdata->id ?? '' }}">
							<input type="hidden" id="category_id" value="{{ $checklistdata->category_id ?? '' }}">
							<input type="hidden" name="subchecklist_id"  value="{{ $subchecklists->id ?? '' }}">
							<input type="hidden" name="task_id" id="task_id" value="{{ $task_id ?? '' }}">
						</form>
					</div>
				</div>
				@endforeach
			@endif
		</div>
	@endif
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->		
    </div>
	<input type="hidden" id="location_id" value="{{ $location_id ?? ''}}">
	<input type="hidden" id="current_checklist_id" value="{{ $checklistdata->id ?? '' }}">
	<input type="hidden" name="order_no" id="order_no" value="{{$checklistdata->order_no ?? '' }}">
	<input type="hidden" id="category_id" value="{{ $checklistdata->category_id ?? '' }}">
	<input type="hidden" id="subcategory_id" value="{{ $checklistdata->subcategory_id ?? '' }}">
	<input type="hidden" id="task_id" value="{{ $task_id ?? '' }}">
	<input type="hidden" id="isFinalEdit" value="{{ $isFinalEdit }}">
	<input type="hidden" id="direct_edit">
	<input type="hidden" id="skip_order_no" value="{{ $skip_order_no ?? ''}}">
	<input type="hidden" id="pending-count">
	
	<div class="checklist-question-sticky-footer" style="display : {{ $isFinalEdit== 'no' ? 'block' : 'none' }}">
	
		<div class="d-flex justify-content-between mb-3" style="gap: 4px;" id="progress-bar-section">
		@if(!empty($total_checklist))
			@foreach($total_checklist as $val)
				@php 
					$progressStatus = '';
					$hasTaskChecklist = App\Models\Task_list_checklists::where('task_list_id', $task_id)
									->where('checklist_id', $val->id)->exists();
					if($hasTaskChecklist)
					{
						$progressStatus = 'completed';
					}
					else 
					{
						$hasTaskSubChecklist = App\Models\Task_list_subchecklists::where('task_list_id', $task_id)->where('task_list_checklist_id', $val->id)->exists();
						if($hasTaskSubChecklist)
						{
							$progressStatus = 'completed';
						}
					}
				@endphp
				<div class="step-block {{ $progressStatus ?? '' }}" style="width:{{ $percentage  }}%;" id="progress-status-{{ $val->id }}"></div>
			@endforeach
	    @endif
		</div>
	
		<div class="clearfix"></div>
		<div class="footer-content question-navigation d-flex justify-content-between">
			<button class="previous_question" style="display:none">Back</button>
			<span class="flex-spacer"></span>
			<button class="next_question ms-auto">Next</button>
		</div>
	</div>
	{{--<button type="button" class="get_checklist" data-cat="1" data-subcat="2" data-task="1" data-checklist="6"></button>--}}
@endsection 
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
	var footerHeight = $('.checklist-question-sticky-footer').outerHeight();
	$('.checklist-question').css('padding-bottom', footerHeight + 'px');
	
	$('#direct_edit').val('');
	//-- 21-06-2025--
	var isFinalEdit = $('#isFinalEdit').val();
	var skip_order_no = $('#skip_order_no').val();
    //alert(skip_order_no);
	//alert(isFinalEdit);
	if(isFinalEdit == 'yes')
	{
		var task_id = $('#task_id').val();
		var category_id = $('#category_id').val();
		var URL = "{{ route('get-final-edit-page') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {
				task_id: task_id,
				category_id: category_id,
				_token: csrfToken
			},
			traditional: true,
			dataType: 'json',
			success: function(response) {
				//alert(response.task_id);alert(response.category_id)
				//$(".question-navigation").hide();
					//$('.question-navigation').css('display', 'none');
					$('.checklist-question-sticky-footer').addClass('d-none');
					$('.sticky-footer-completed').removeClass('d-none');
					
					if (!document.querySelector('link[href*="bootstrap.min.css"]')) {
						var bootstrapCSS = document.createElement('link');
						bootstrapCSS.rel = 'stylesheet';
						bootstrapCSS.href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css';
						document.head.appendChild(bootstrapCSS);
					}
					
					
					//const redirectUrl = checkoutUrlTemplate.replace('TASK_ID', task_id).replace('CAT_ID', category_id).replace('SUBCAT_ID', subcategory_id);
					//window.location.href = redirectUrl; hidden
					//return;
					let htmlCompleted = '<div class="container checklist">';
						htmlCompleted += '<h2 class="checklist-title">Review your checklist</h2>'; 
						htmlCompleted += '<h4>Review and check before moving on to next section. Checmical bonding & stor</h4>';
						htmlCompleted += '<div class="location-section">';
						htmlCompleted += '<div class="location-label">' + response.subcategoryname + '</div>';
						htmlCompleted += '</div>';
					htmlCompleted += '<div class="main-content-area clearfix">';
						htmlCompleted += '<section class="custom-padding1">';
							htmlCompleted += '<div class="container1">';
							htmlCompleted += '<div class="custom-tab">';
							htmlCompleted += '<div class="tab-content">';
							htmlCompleted += '<div role="tabpanel" class="tab-pane active" id="uncomplete_tab">';
							response.checklistdata.forEach((chklist, index) => {
								
								var aprvStatusHtml = '';
								var approveStatus = chklist.approve;
								//alert(approveStatus);
								if(approveStatus=='0')
								{
									aprvStatusHtml = '<button type="button" class="btn btn-outline-danger" style="pointer-events: none; background-color: transparent; border-color: #dc3545; color: #dc3545;">Rejected</button>';
								}
								else if(approveStatus=='1')
								{
									aprvStatusHtml = '<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754;">Accepted</button>';
								}
								else if(approveStatus=='2')
								{
									aprvStatusHtml = '<button type="button" class="btn btn-outline-info" style="pointer-events: none; background-color: transparent; border-color: #0dcaf0; color: #0dcaf0;">Not available</button>';
								}
								else
								{
									aprvStatusHtml = '<button type="button" class="btn btn-outline-info" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107;">Pending</button>';
								}
								
								htmlCompleted += '<div class="checklist-item">';
									htmlCompleted += '<div class="text">';
									htmlCompleted += '<div class="title">' + chklist.name + '</div>';
									htmlCompleted += '<div class="subtitle mt-3">' + aprvStatusHtml + '</div>';
									htmlCompleted += '</div>';
									htmlCompleted += '<a href="javascript:void(0)" style="text-decoration: none;"><div class="arrow get_checklist" data-checklist="' + chklist.id + '" data-task="' + task_id + '" data-cat="' + category_id + '" data-subcat="' + subcategory_id + '" data-dedit="directEdit"><small>Edit</small></div></a>';
								htmlCompleted += '</div>';
							})
								htmlCompleted += '<div class="sticky-footer-completed submit_task">';
									htmlCompleted += '<button class="submit_task_s">Submit checklist</button>';
								htmlCompleted += '</div>';
								
							htmlCompleted += '</div>';
								htmlCompleted += '<div role="tabpanel" class="tab-pane" id="reject_tab">';
								htmlCompleted += 'Not have any data';
								htmlCompleted += '</div>';
							
							htmlCompleted += '</div>';
							htmlCompleted += '</div>';
							htmlCompleted += '</div>';
							htmlCompleted += '<input type="hidden" id="completed_task_id" value="' + task_id+ '">';
							htmlCompleted += '<input type="hidden" id="completed_category_id" value="' + category_id+ '">';
							htmlCompleted += '<input type="hidden" id="completed_subcategory_id" value="' + subcategory_id+ '">';
						htmlCompleted += '</section>';
					
					
					htmlCompleted += '</div>';  // main-content-area end
					htmlCompleted += '</div>'; // main div end
					//alert(htmlCompleted);
					$('.checklist-question').show();
					$('.checklist-question').html(htmlCompleted);
					return;
			}
		
		});
	}
	//---------end----------
	if(skip_order_no != 0 && isFinalEdit == 'no')
	{
		//get_save_exist_checklist(task_id,category_id);
		$('.checklist-question-sticky-footer').show(); // 21-05-2025
	   var directEdit = ''; // 21-06-2025
	   var cat_id = $('#category_id').val();
	   var subcat_id = '';
	   var task_id = $('#task_id').val();
	   //var checklist_id = $(this).data('checklist');
	   var URL = "{{ route('get-save-exist-checklist-page') }}";
	   $.ajax({
			url: URL,
			type: "POST",
			data: {order_no:skip_order_no, task_id:task_id, cat_id:cat_id, subcat_id:subcat_id,directEdit:directEdit, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.barHtml);
				$('.previous_question').show();
				$('#direct_edit').val(response.directEdit);
				// ------progress bar work ----------
				
					$('#progress-bar-section').append(response.barHtml);
				// ----------------------------------
				//--implement 08-05-2025
				if (!document.querySelector('link[href*="bootstrap.min.css"]')) {
					var bootstrapCSS = document.createElement('link');
					bootstrapCSS.rel = 'stylesheet';
					bootstrapCSS.href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css';
					document.head.appendChild(bootstrapCSS);
				}
				//------
				
				$('#current_checklist_id').val(response.currentid);
				$('#order_no').val(response.order_no);
				const rejectFilesRoute = "{{ route('reject-files') }}";
				const rejectSubcheckFilesRoute = "{{ route('reject-subchecklist-files') }}";
				if (response.subchecklist.length > 0) {
				$('.checklist-question-sticky-footer').removeClass('d-none');
				$('.sticky-footer').addClass('d-none');
				// Has subchecklists
				let autoClicks = [];
				let html = '<button class="task-filter-button save-next">Save & exit</button>';
				html += '<div class="sub-checklist">';
				html += '<div class="question-header">' + response.categoryName + '</div>';
				html += '<div class="question-text">';
				html += '<span id="multiple-question">' + response.name + '</span>';
				html += '</div>';

				response.subchecklist.forEach((item, index) => {
					    //alert(item.id);
						let match = response.fetchsubChklistArr.find(e => e.subchecklist_id == item.id);
						let rejectedText = match ? match.rejected_region : '';
						let approveStatus = match ? match.approve : '';
						//alert(approveStatus);alert(rejectedText);
						
						let rejectId = 'rejectForm-' + item.id;
						html += '<div class="sub-checklist-question">';
						html += '<div class="action-buttons">';
						html += '<span class="d-flex align-items-center">' + item.name + '</span>';
						html += '<div class="btn-div">';
						html += '<button class="rejected" id="question-reject-' + item.id + '" onclick="handleReject(' + item.id + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + item.id + '" onclick="handleApprove(' + item.id + ')"><i class="fa-solid fa-check"></i></button>';
						html += '<button class="notavailable" id="question-na-' + item.id + '" onclick="handleNotavailable(' + item.id + ')"><span class="button-na">NA</span></button>';
						html += '</div>'; 
						html += '</div>';
						html += '<span id="errorMulmsg'+ item.id +'" class="question-error-message">Please enter text or file.</span>';
						html += '<div class="reject-form mb-3" id="' + rejectId + '">';
						html += '<textarea placeholder="State why you rejected this...">' + rejectedText +'</textarea>';
						html += '<input type="hidden" id="mode" value="multiple">';
						html += '<input type="hidden" id="approveMultipleStatus' + item.id + '">';
						html += '<input type="hidden" id="hasEditMultipleFile' + item.id +'" value="">'
						html += '<form action="' + rejectSubcheckFilesRoute + '" class="dropzone" id="dropzone-' + item.id + '">';
						html += '<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="' + response.currentid +'">';
						html += '<input type="hidden" name="subchecklist_id" value="' + item.id + '">';
						html += '<input type="hidden" name="task_id" id="single-task_id" value="' + task_id +'">';
						html += '</form>';
						html += '</div>'; 
						html += '</div>';
						
						if(approveStatus== '0')
						{
							autoClicks.push({ type: 'reject', id: item.id });
						}
						
						if(approveStatus== '1')
						{
							autoClicks.push({ type: 'approve', id: item.id });
						}
						
						if(approveStatus== '2')
						{
							autoClicks.push({ type: 'notavailable', id: item.id });
						}
					});

						html += '</div>'; 
						$('.checklist-question').html(html);
						
						setTimeout(() => {
							autoClicks.forEach(click => {
								if (click.type === 'reject') {
									const btn = document.getElementById('question-reject-' + click.id);
									if (btn) btn.click();
								}
								if (click.type === 'approve') {
									const btn = document.getElementById('question-approve-' + click.id);
									if (btn) btn.click();
								}
								if (click.type === 'notavailable') {
									const btn = document.getElementById('question-na-' + click.id);
									if (btn) btn.click();
								}
							});
						}, 0);
						
						// dropzone work
						Dropzone.autoDiscover = false;
						
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-subchecklist-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictRemoveFile: 'Delete file',
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;
									let subchecklistId = dropzoneElement.querySelector('[name="subchecklist_id"]').value;
									// Add existing files (preloaded from server)
									response.existingSubChecklistFiles
									.filter(file => file.subchecklist_id == subchecklistId)
									.forEach(function (file) {
										let mockFile = { name: file.name, size: file.size, accepted: true };

										dz.emit("addedfile", mockFile);
										//dz.emit("thumbnail", mockFile, file.url);
										let fileUrl = file.url;
										let extension = fileUrl.split('.').pop().toLowerCase();
										//alert(extension);
										if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension))
										{
											dz.emit("thumbnail", mockFile, file.url);
										}
										else if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
										  // Inject video preview manually
										  console.log(file.url);
										  setTimeout(() => {
											const videoElement = document.createElement('video');
											videoElement.setAttribute('src', file.url);
											videoElement.setAttribute('controls', 'true');
											videoElement.style.maxWidth = '100%';
											videoElement.style.maxHeight = '100px';
											mockFile.previewElement.querySelector(".dz-image").innerHTML = '';
											mockFile.previewElement.querySelector(".dz-image").appendChild(videoElement);
										  }, 0);
										}
										
										dz.emit("complete", mockFile);

										mockFile.previewElement.classList.add('dz-success', 'dz-complete');
										mockFile.uploadedFilename = file.name;
										$('#hasEditMultipleFile' + subchecklistId).val(1);
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											$.ajax({
												url: "{{ route('subchecklist-file-delete') }}", // handle deletion logic on server
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
													//alert(response.count);
													if(response.count == '0')
													{
														$('#hasEditMultipleFile' + response.subchecklist_id).val('');
													}
													else{
														$('#hasEditMultipleFile' + response.subchecklist_id).val(1);
													}
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						//--- upload new files ------- 
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							new Dropzone(dropzoneElement, {
								url: "{{ route('reject-subchecklist-files') }}",
								maxFiles: 5,
								maxFilesize: 2, // MB
								acceptedFiles: 'image/*',
								addRemoveLinks: true,
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								dictRemoveFile: 'Delete file',
								init: function () {
									this.on("success", function (file, response) {
										console.log('Uploaded:', response);
										//alert(response.filename);
										// Attach filename to file object so we can use it on removal
										file.uploadedFilename = response.filename;
										//alert(response.filename);
										// Replace default preview with file name
										file.previewElement.querySelector("[data-dz-name]").textContent = response.filename;
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											// Send AJAX request to delete the file from storage and DB
											$.ajax({
												url: "{{ route('reject-subckecklist-file-delete') }}", // You need to define this route
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						
				} else {
						$('.checklist-question-sticky-footer').removeClass('d-none');
						$('.sticky-footer').addClass('d-none');
						//alert(response.next_approve);
						let html = '<button class="task-filter-button save-next">Save & exit</button>';
						html += '<div class="single-checklist">';
						html += '<div class="question-header">' + response.categoryName + '</div>';
						html += '<div class="question-text">';
						html += '<span id="single-question">' + response.name + '</span>';
						html += '<input type="hidden" id="current_id" value="' + response.currentid + '">';
						html += '<input type="hidden" id="category_id" value="' + response.category_id + '">';
						html += '<input type="hidden" id="subcategory_id" value="' + response.subcategory_id + '">';
						html += '</div>'; 
						html += '<span id="errormsg" class="question-error-message">Please enter text or file.</span>';
						html += '<div class="reject-form mb-3" id="rejectForm-' + response.currentid + '">';
						html += '<textarea id="single_rejecttext" placeholder="State why you rejected this...">' + response.next_rejected_region + '</textarea>';
						html += '<input type="hidden" id="mode" value="single">';
						html += '<input type="hidden" id="hasEditFile" value="">';
						html += '<input type="hidden" id="approveStatus">';
						html += '<form action="' + rejectFilesRoute + '" class="dropzone" id="dropzone-1">';
						html += '<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="' + response.currentid +'">';
						html += '<input type="hidden" name="subcategory_id" id="single-subcategory_id" value="' + response.subcategory_id + '">';
						html += '<input type="hidden" name="task_id" id="single-task_id" value="' + task_id +'">';
						html += '</form>';
						html += '</div>'; 
						html += '<div class="action-buttons-without-text">';
						html += '<button class="rejected" id="question-reject-' + response.currentid + '" onclick="handleReject(' + response.currentid + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + response.currentid + '" onclick="handleApprove(' + response.currentid + ')"><i class="fa-solid fa-check"></i></button>';
						html += '<button class="notavailable" id="question-na-' + response.currentid + '" onclick="handleNotavailable(' + response.currentid + ')"><span class="button-na">NA</span></button>';
						html += '</div>'; 
						html += '</div>'; 

						$('.checklist-question').html(html); 
						
						if(response.next_approve== '0')
						{
							const rejectButton = document.getElementById('question-reject-' + response.currentid);
							rejectButton.click();
						}
						
						if(response.next_approve== '1')
						{
							const approveButton = document.getElementById('question-approve-' + response.currentid);
							approveButton.click();
						}
						
						if(response.next_approve== '2')
						{
							const naButton = document.getElementById('question-na-' + response.currentid);
							naButton.click();
						}
						
						// dropzone work
						Dropzone.autoDiscover = false;
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictRemoveFile: 'Delete file',
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;

									// Add existing files (preloaded from server)
									response.existingNextFiles.forEach(function (file) {
										let mockFile = { name: file.name, size: file.size, accepted: true };

										dz.emit("addedfile", mockFile);
										//dz.emit("thumbnail", mockFile, file.url);
										
										let fileUrl = file.url;
										let extension = fileUrl.split('.').pop().toLowerCase();
										//alert(extension);
										if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension))
										{
											dz.emit("thumbnail", mockFile, file.url);
										}
										else if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
										  // Inject video preview manually
										  console.log(file.url);
										  setTimeout(() => {
											const videoElement = document.createElement('video');
											videoElement.setAttribute('src', file.url);
											videoElement.setAttribute('controls', 'true');
											videoElement.style.maxWidth = '100%';
											videoElement.style.maxHeight = '100px';
											mockFile.previewElement.querySelector(".dz-image").innerHTML = '';
											mockFile.previewElement.querySelector(".dz-image").appendChild(videoElement);
										  }, 0);
										}
										
										dz.emit("complete", mockFile);

										mockFile.previewElement.classList.add('dz-success', 'dz-complete');

										// Store filename for deletion
										mockFile.uploadedFilename = file.name;
										$('#hasEditFile').val(1);
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											$.ajax({
												url: "{{ route('checklist-file-delete') }}", // handle deletion logic on server
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
													//alert(response.count);
													if(response.count == '0')
													{
														$('#hasEditFile').val('');
													}
													else{
														$('#hasEditFile').val(1);
													}
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						//--- upload new files ------- 
						Dropzone.autoDiscover = false; // very important
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 2, // MB
								acceptedFiles: 'image/*',
								addRemoveLinks: true,
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								dictRemoveFile: 'Delete file',
								init: function () {
									this.on("success", function (file, response) {
										console.log('Uploadedsss:', response);

										// Attach filename to file object so we can use it on removal
										file.uploadedFilename = response.filename;
										//alert(response.filename);
										// Replace default preview with file name
										file.previewElement.querySelector("[data-dz-name]").textContent = response.filename;
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											// Send AJAX request to delete the file from storage and DB
											$.ajax({
												url: "{{ route('reject-file-delete') }}", // You need to define this route
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
					}
			},
		});
	}
	
	if(skip_order_no == 0 && isFinalEdit == 'no')
	{
		setTimeout(function() {
			$('#getchecklist').trigger('click');
		}, 100); 
	}
	//$('.previous_question').css('visibility', 'hidden');
});
//Dropzone.autoDiscover = false;
document.addEventListener('DOMContentLoaded', function () {
	initializeDropzones();
	Dropzone.autoDiscover = false;
	const filesForDropzone = {!! json_encode($existingSubChecklistFiles) !!};
	//const filesForDropzone = JSON.stringify(filesForDropzones, null, 2);
	
	const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	//alert(window.existingSubChecklistFiles);
	@if($checklistdata && $checklistdata->get_subchecklist && $checklistdata->get_subchecklist->isNotEmpty())
		@foreach($checklistdata->get_subchecklist as $subchecklists)
			@php
				$approve == '';				
				$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_checklist_id', $subchecklists->checklist_id)->where('subchecklist_id', $subchecklists->id)->first();
				$approve = $subchecklistData ? $subchecklistData->approve : '';
				$task_list_subchecklist_id = $subchecklistData ? $subchecklistData->id : '';
				
				$fileData = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $task_list_subchecklist_id)->get();
				
			@endphp
			//console.log("Subchecklist ID: {{ $subchecklists->id }}, Approve: {{ $approve }}");
			@if($approve == '0')
				document.getElementById('question-reject-{{ $subchecklists->id }}')?.click();
			@elseif($approve == '1')
				document.getElementById('question-approve-{{ $subchecklists->id }}')?.click();
			@elseif($approve == '2')
				document.getElementById('question-na-{{ $subchecklists->id }}')?.click();
			@endif
		@endforeach
	@endif
	
	//Dropzone.autoDiscover = false;
	console.log('Dropzones found:', document.querySelectorAll('.dropzone').length);

	document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
		Dropzone.autoDiscover = false;
		//console.log(dropzoneElement);
		
			let subchecklistInput = dropzoneElement.querySelector('[name="subchecklist_id"]');
			if (!subchecklistInput) {
				console.warn('No subchecklist_id input in:', dropzoneElement);
				return; // skip this dropzone
			}
			let subchecklistId = subchecklistInput ? subchecklistInput.value : null;
			//alert('Outside init: ' + subchecklistId);
		let myDropzone = new Dropzone(dropzoneElement, {
			url: dropzoneElement.getAttribute('action'),
			maxFiles: 5,
			maxFilesize: 2, // MB
			acceptedFiles: 'image/*',
			addRemoveLinks: true,
			dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
			dictRemoveFile: 'Delete file',
			headers: {
				'X-CSRF-TOKEN': csrfToken
			},
			init: function () {
				let dz = this;
				let subchecklistId = dropzoneElement.querySelector('[name="subchecklist_id"]').value;
				// Add existing files (preloaded from server)
				response.filesForDropzone
				.filter(file => file.subchecklist_id == subchecklistId)
				.forEach(function (file) {
					let mockFile = { name: file.name, size: file.size, accepted: true };
                    //alert("ok1"+ file.url);
					dz.emit("addedfile", mockFile);
					dz.emit("thumbnail", mockFile, file.url);
					dz.emit("complete", mockFile);

					mockFile.previewElement.classList.add('dz-success', 'dz-complete');
					mockFile.uploadedFilename = file.name;
					$('#hasEditMultipleFile' + subchecklistId).val(1);
				});

				this.on("removedfile", function (file) {
					if (file.uploadedFilename) {
						$.ajax({
							url: "{{ route('subchecklist-file-delete') }}", // handle deletion logic on server
							type: "POST",
							data: {
								_token: csrfToken,
								filename: file.uploadedFilename
							},
							success: function (response) {
								console.log('Deleted:', response);
								//alert(response.count);
								if(response.count == '0')
								{
									$('#hasEditMultipleFile' + response.subchecklist_id).val('');
								}
								else{
									$('#hasEditMultipleFile' + response.subchecklist_id).val(1);
								}
							},
							error: function (xhr) {
								console.error('Delete failed:', xhr.responseText);
							}
						});
					}
				});
			}
		});
	});
	
});
</script>


<script>
const filesForDropzone = {!! json_encode($existingSubChecklistFiles) !!};

let existingFiles = @json($existingFiles);
//---------- show single image when page load ----------
Dropzone.autoDiscover = false; // very important

//"{{ route('reject-files') }}"
document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
    let myDropzone = new Dropzone(dropzoneElement, {
        url: dropzoneElement.getAttribute('action'), 
        maxFiles: 5,
        maxFilesize: 10, // MB
		acceptedFiles: 'image/*,video/*',
        //acceptedFiles: 'image/*',
        addRemoveLinks: true,
		dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
        dictRemoveFile: 'Delete file',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        init: function () {
            let dz = this;
			
			//--------upload new file-----------
			this.on("success", function(file, response) {
				if(response.new == 1)
				{
					const extension = response.filename.split('.').pop().toLowerCase();
					const fileUrl = response.url;

					// Add uploaded filename for later removal
					file.uploadedFilename = response.filename;

					// For videos, manually insert preview
					if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
						setTimeout(() => {
							const videoElement = document.createElement('video');
							videoElement.setAttribute('src', fileUrl);
							videoElement.setAttribute('controls', 'true');
							videoElement.style.maxWidth = '100%';
							videoElement.style.maxHeight = '100px';

							if (file.previewElement) {
								const dzImage = file.previewElement.querySelector('.dz-image');
								if (dzImage) {
									dzImage.innerHTML = '';
									dzImage.appendChild(videoElement);
								}
							}
						}, 0);
					}
				}
			});
			//--------------------
            // Add existing files (preloaded from server)
            existingFiles.forEach(function (file) {
                let mockFile = { name: file.name, size: file.size, accepted: true };
                //alert("ok2"+ file.url);
                dz.emit("addedfile", mockFile);
                dz.emit("thumbnail", mockFile, file.url);
                dz.emit("complete", mockFile);

                mockFile.previewElement.classList.add('dz-success', 'dz-complete');

                // Store filename for deletion
                mockFile.uploadedFilename = file.name;
				$('#hasEditFile').val(1);
            });

            this.on("removedfile", function (file) {
                if (file.uploadedFilename) {
                    $.ajax({
                        url: "{{ route('checklist-file-delete') }}", // handle deletion logic on server
                        type: "POST",
                        data: {
                            _token: csrfToken,
                            filename: file.uploadedFilename
                        },
                        success: function (response) {
                            console.log('Deleted:', response);
                        },
                        error: function (xhr) {
                            console.error('Delete failed:', xhr.responseText);
                        }
                    });
                }
            });
        }
    });
});
//alert(existingFiles);

function handleReject(id) {
	document.getElementById('rejectForm-'+id).style.display = 'flex';
	
	document.getElementById("question-approve-"+id).classList.remove("active");
	document.getElementById("question-na-"+id).classList.remove("active");
	document.getElementById("question-reject-"+id).classList.add("active");
	$('#approveStatus').val(0);
	$('#approveMultipleStatus' + id).val(0);
}
function handleApprove(id) {
	document.getElementById('rejectForm-'+id).style.display = 'none';
	
	document.getElementById("question-reject-"+id).classList.remove("active");
	document.getElementById("question-na-"+id).classList.remove("active");
	document.getElementById("question-approve-"+id).classList.add("active");
	$('#approveStatus').val(1);
	$('#approveMultipleStatus' + id).val(1);
}

function handleNotavailable(id) {
	document.getElementById('rejectForm-'+id).style.display = 'none';
	
	document.getElementById("question-reject-"+id).classList.remove("active");
	document.getElementById("question-approve-"+id).classList.remove("active");
	document.getElementById("question-na-"+id).classList.add("active");
	$('#approveStatus').val(2);
	$('#approveMultipleStatus' + id).val(2);
}

Dropzone.autoDiscover = false; // very important

//alert(filesForDropzone);
// when single file upload 
//"{{ route('reject-files') }}"
document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
    new Dropzone(dropzoneElement, {
        url: dropzoneElement.getAttribute('action'),
        maxFiles: 5,
        maxFilesize: 2, // MB
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
        dictRemoveFile: 'Delete file',
        init: function () {
            this.on("success", function (file, response) {
                console.log('Uploaded:', response);
                //alert("ok3"+ response.filename);
                // Attach filename to file object so we can use it on removal
                file.uploadedFilename = response.filename;

                // Replace default preview with file name
                file.previewElement.querySelector("[data-dz-name]").textContent = response.filename;
            });

            this.on("removedfile", function (file) {
                if (file.uploadedFilename) {
                    // Send AJAX request to delete the file from storage and DB
                    $.ajax({
                        url: "{{ route('reject-file-delete') }}", // You need to define this route
                        type: "POST",
                        data: {
                            _token: csrfToken,
                            filename: file.uploadedFilename
                        },
                        success: function (response) {
                            console.log('Deleted:', response);
                        },
                        error: function (xhr) {
                            console.error('Delete failed:', xhr.responseText);
                        }
                    });
                }
            });
        }
    });
});

</script>

<script>

const checkoutUrlTemplate = "{{ url('completed-task/TASK_ID/CAT_ID/SUBCAT_ID') }}";

$(document ).ready(function() {
	
	var isFinalEdit = $('#isFinalEdit').val();
	//alert(skip_order_no);alert(isFinalEdit);
	/*if(skip_order_no == 0 && isFinalEdit == 'no')
	{
		alert('hello');
		setTimeout(function() {
			$('#getchecklist').trigger('click');
		}, 100); 
	}*/
	
	var approveStatus = $('#approveStatus').val();
	if(approveStatus == '0')
	{
		const rejectButton = document.getElementById('question-reject-1');
		rejectButton.click();
	}
	
	if(approveStatus == '1')
	{
		const approveButton = document.getElementById('question-approve-1');
		approveButton.click();
	}
	
	if(approveStatus == '2')
	{
		const approveButton = document.getElementById('question-na-1');
		approveButton.click();
	}
	
	
	// ========= NEXT BUTTON ==============
	
    $(document).on('click','.next_question', function(){
		
		var mode = $('#mode').val();
		//alert(mode);
		var approveStatus = $('#approveStatus').val();
		//alert(approveStatus);
		//alert("approveStatus" + approveStatus);
		if(mode=='single')
		{
			if(approveStatus == '0')
			{
				var textIsEmpty = $('#single_rejecttext').val().trim() === '';
				var hasEditFile = $('#hasEditFile').val();
				//alert(hasEditFile);
				var hasFiles = false;
				try {
					var dzInstance = Dropzone.forElement('#dropzone-1');
					hasFiles = dzInstance.files.length > 0;
				} catch (e) {
					console.warn("Dropzone not found or not initialized yet.");
				}
				
				if (textIsEmpty &&  !hasFiles && !hasEditFile) {
					$('#errormsg').fadeIn().delay(2000).fadeOut();
					return false;
				}
			}
			
			/*if(approveStatus == '')
			{
				Swal.fire({
					  icon: "warning",
					  title: "Please select any one",
					  html: '<div style="display: flex; justify-content: center; gap: 20px; margin-top: 10px;"><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-xmark" style="font-size: 20px; color: #00000;"></i></span><span style="align-self: center;font-size: 20px"><strong>OR</strong></span><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-check" style="font-size: 20px; color: #00000;"></i></span><span style="align-self: center;font-size: 20px"><strong>OR</strong></span><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;font-size:15px;color:black;">NA</span></div>',
					  confirmButtonColor: "#0b2b57",
					  customClass: {
						confirmButton : 'swal-save-exist-black'
					  }
					  
					});
					
				return false;
			}*/
		}
		else
		{
			let mulSelected = 0; // 21-06-2025
			let mulCounter = 0 ;  // 21-06-2025
			let hasError = false;
			$('.reject-form').each(function () {
				const subchecklistId = $(this).attr('id').replace('rejectForm-', '');
				const text = $(this).find('textarea').val().trim();
				const approveMulStatus = $('#approveMultipleStatus' + subchecklistId).val();
				//alert(approveMulStatus);
				//var hasEditMultipleFile = parseInt($('#hasEditMultipleFile' + subchecklistId).val(), 10);
				var hasEditMultipleFile = $('#hasEditMultipleFile' + subchecklistId).val();
				//alert(hasEditMultipleFile); //if 1 get then has files if 0 no files
				//alert(approveMulStatus);
				if(approveMulStatus == '0')
				{
	
					const dropzoneInstance = Dropzone.forElement('#dropzone-' + subchecklistId);
					const files = dropzoneInstance ? dropzoneInstance.getAcceptedFiles() : [];
					
					
					//if (text === ''  && !hasEditMultipleFile)
					if (text === '' && files.length === 0 && !hasEditMultipleFile)
					{
						$('#errorMulmsg' + subchecklistId).fadeIn().delay(2000).fadeOut();
						hasError = true;
						return false;
					}
				}
				
				// validation without select cross or tick cannot go next
				if(approveMulStatus != '')
				{
					mulSelected++ ;
				}
				mulCounter++;
			});
			
			if (hasError) {
				return false;
			}
			
			/*if(mulSelected != mulCounter)
			{
				Swal.fire({
					  icon: "warning",
					  title: "Please select any one",
					  html: '<div style="display: flex; justify-content: center; gap: 20px; margin-top: 10px;"><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-xmark" style="font-size: 20px; color: #00000;"></i></span><span style="align-self: center;font-size: 20px"><strong>OR</strong></span><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-check" style="font-size: 20px; color: 00000;"></i></span><span style="align-self: center;font-size: 20px"><strong>OR</strong></span><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;font-size:15px;color:black;">NA</span></div>',
					  confirmButtonColor: "#0b2b57",
					  customClass: {
						confirmButton : 'swal-save-exist-black'
					  }
					});
					
				return false;
			}*/
			
		}
		
		var current_id = $('#current_checklist_id').val();
		//alert(current_id);
		var category_id = $('#category_id').val();
		var subcategory_id = $('#subcategory_id').val();
		var task_id = $('#task_id').val();
		//alert(task_id);
		var order_no = $('#order_no').val();
		var directEdit = $('#direct_edit').val();
		//var mode = $('#mode').val();
		//alert(mode);
		var rejectTextsSingle = '';
		let rejectTextsMultiple = {};
		if(mode=='single')
		{
			 var rejectTextsSingle = $('#single_rejecttext').val();
		}
		else{
			
			$('.reject-form').each(function () {
				const subchecklistId = $(this).attr('id').replace('rejectForm-', '');
				const text = $(this).find('textarea').val().trim();
				const approveStatus = $('#approveMultipleStatus' + subchecklistId).val();
				// Save only if there's any text entered (optional)
				
				if (text !== '' || approveStatus !== '') {
					rejectTextsMultiple[subchecklistId] = {
						text: text,
						approve_status: approveStatus
					};
				}
				
				/*if (text !== '') {
					rejectTextsMultiple[subchecklistId] = text;
				}*/
			});
			//alert(rejectTextsMultiple);
		}
		
		var URL = "{{ route('checklist-next-question') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {
				approveStatus: approveStatus,
				mode: mode,
				task_id: task_id,
				order_no: order_no,
				current_question_id: current_id,
				category_id: category_id,
				subcategory_id: subcategory_id,
				directEdit: directEdit,
				rejectTextsSingle: rejectTextsSingle,
				rejectTextsMultiple: JSON.stringify(rejectTextsMultiple), // <--- Fix here
				_token: csrfToken
			},
			traditional: true,
			dataType: 'json',
			success: function(response) {
				//alert(response.subcategoryname);
				//$('.previous_question').css('visibility', 'visible');
				$('.previous_question').show();
				$('.checklist-question').html('');
				// -- progress bar work -----------------
				if(response.progressStatus!='')
				{
					$('#progress-status-' + current_id).addClass('completed');
				}
				//---------------------------------------
				//alert(response.currentid);
				if(response.currentid=='')
				{
					$('#pending-count').val(0);
					//$(".question-navigation").hide();
					//$('.question-navigation').css('display', 'none');
					$('.checklist-question-sticky-footer').addClass('d-none');
					$('.sticky-footer-completed').removeClass('d-none');
					
					if (!document.querySelector('link[href*="bootstrap.min.css"]')) {
						var bootstrapCSS = document.createElement('link');
						bootstrapCSS.rel = 'stylesheet';
						bootstrapCSS.href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css';
						document.head.appendChild(bootstrapCSS);
					}
					
					
					//const redirectUrl = checkoutUrlTemplate.replace('TASK_ID', task_id).replace('CAT_ID', category_id).replace('SUBCAT_ID', subcategory_id);
					//window.location.href = redirectUrl; hidden
					//return;
					let pendingCount = 0;
					let htmlCompleted = '<div class="container checklist">';
						htmlCompleted += '<h2 class="checklist-title">Review your checklist</h2>'; 
						htmlCompleted += '<h4>Review and check before moving on to next section. Checmical bonding & stor</h4>';
						htmlCompleted += '<div class="location-section">';
						htmlCompleted += '<div class="location-label">' + response.subcategoryname + '</div>';
						htmlCompleted += '</div>';
					htmlCompleted += '<div class="main-content-area clearfix">';
						htmlCompleted += '<section class="custom-padding1">';
							htmlCompleted += '<div class="container1">';
							htmlCompleted += '<div class="custom-tab">';
							htmlCompleted += '<div class="tab-content">';
							htmlCompleted += '<div role="tabpanel" class="tab-pane active" id="uncomplete_tab">';
							response.checklistdata.forEach((chklist, index) => {
								
								var aprvStatusHtml = '';
								var approveStatus = chklist.approve;
								//alert(approveStatus);
								if(approveStatus=='0')
								{
									aprvStatusHtml = '<button type="button" class="btn btn-outline-danger" style="pointer-events: none; background-color: transparent; border-color: #dc3545; color: #dc3545;">Rejected</button>';
								}
								else if(approveStatus=='1')
								{
									aprvStatusHtml = '<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754;">Accepted</button>';
								}
								else if(approveStatus=='2')
								{
									aprvStatusHtml = '<button type="button" class="btn btn-outline-info" style="pointer-events: none; background-color: transparent; border-color: #0dcaf0; color: #0dcaf0;">Not available</button>';
								}
								else
								{
									aprvStatusHtml = '<button type="button" class="btn btn-outline-info" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107;">Pending</button>';
									
									pendingCount++;
									$('#pending-count').val(pendingCount);
								}
								
								htmlCompleted += '<div class="checklist-item">';
									htmlCompleted += '<div class="text">';
									htmlCompleted += '<div class="title">' + chklist.name + '</div>';
									htmlCompleted += '<div class="subtitle mt-3">' + aprvStatusHtml + '</div>';
									htmlCompleted += '</div>';
									htmlCompleted += '<a href="javascript:void(0)" style="text-decoration: none;"><div class="arrow get_checklist" data-checklist="' + chklist.id + '" data-task="' + task_id + '" data-cat="' + category_id + '" data-subcat="' + subcategory_id + '" data-dedit="directEdit"><small>Edit</small></div></a>';
								htmlCompleted += '</div>';
							})
								htmlCompleted += '<div class="sticky-footer-completed submit_task">';
									htmlCompleted += '<button class="submit_task_s">Submit checklist</button>';
								htmlCompleted += '</div>';
								
							htmlCompleted += '</div>';
								htmlCompleted += '<div role="tabpanel" class="tab-pane" id="reject_tab">';
								htmlCompleted += 'Not have any data';
								htmlCompleted += '</div>';
							
							htmlCompleted += '</div>';
							htmlCompleted += '</div>';
							htmlCompleted += '</div>';
							htmlCompleted += '<input type="hidden" id="completed_task_id" value="' + task_id+ '">';
							htmlCompleted += '<input type="hidden" id="completed_category_id" value="' + category_id+ '">';
							htmlCompleted += '<input type="hidden" id="completed_subcategory_id" value="' + subcategory_id+ '">';
						htmlCompleted += '</section>';
					
					
					htmlCompleted += '</div>';  // main-content-area end
					htmlCompleted += '</div>'; // main div end
					//alert(htmlCompleted);
					$('.checklist-question').html(htmlCompleted);
					return;
				}
				//alert(response.categoryName);
				$('#current_checklist_id').val(response.currentid);
				$('#order_no').val(response.order_no);
				 //$('#single-question').html(response.name);
				const rejectFilesRoute = "{{ route('reject-files') }}";
				const rejectSubcheckFilesRoute = "{{ route('reject-subchecklist-files') }}";
				if (response.subchecklist.length > 0) {
				
				$('.checklist-question-sticky-footer').removeClass('d-none');
				$('.sticky-footer-completed').addClass('d-none');
				// Has subchecklists
				let autoClicks = [];
				let html = '<button class="task-filter-button save-next">Save & exit</button>';
				html += '<div class="sub-checklist">';
				html += '<div class="question-header">' + response.categoryName + '</div>';
				html += '<div class="question-text">';
				html += '<span id="multiple-question">' + response.name + '</span>';
				html += '</div>';

				response.subchecklist.forEach((item, index) => {
					    //alert(item.id);
						
						let match = response.fetchsubChklistArr.find(e => e.subchecklist_id == item.id);
						let rejectedText = match ? match.rejected_region : '';
						let approveStatus = match ? match.approve : '';
						//alert(approveStatus);alert(rejectedText);
						
						let rejectId = 'rejectForm-' + item.id;
						html += '<div class="sub-checklist-question">';
						html += '<div class="action-buttons">';
						html += '<span class="d-flex align-items-center">' + item.name + '</span>';
						html += '<div class="btn-div">';
						html += '<button class="rejected" id="question-reject-' + item.id + '" onclick="handleReject(' + item.id + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + item.id + '" onclick="handleApprove(' + item.id + ')"><i class="fa-solid fa-check"></i></button>';
						html += '<button class="notavailable" id="question-na-' + item.id + '" onclick="handleNotavailable(' + item.id + ')"><span class="button-na">NA</span></button>';
						html += '</div>'; 
						html += '</div>';
						html += '<span id="errorMulmsg'+ item.id +'" class="question-error-message">Please enter text or file.</span>';
						html += '<div class="reject-form mb-3" id="' + rejectId + '">';
						html += '<textarea placeholder="State why you rejected this...">' + rejectedText +'</textarea>';
						html += '<input type="hidden" id="mode" value="multiple">';
						html += '<input type="hidden" id="approveMultipleStatus' + item.id + '">';
						html += '<input type="hidden" id="hasEditMultipleFile' + item.id +'" value="">'
						html += '<form action="' + rejectSubcheckFilesRoute + '" class="dropzone" id="dropzone-' + item.id + '">';
						html += '<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="' + response.currentid +'">';
						html += '<input type="hidden" name="subchecklist_id" value="' + item.id + '">';
						html += '<input type="hidden" name="task_id" id="single-task_id" value="' + task_id +'">';
						html += '</form>';
						html += '</div>'; 
						html += '</div>';
						
						if(approveStatus== '0')
						{
							autoClicks.push({ type: 'reject', id: item.id });
						}
						
						if(approveStatus== '1')
						{
							autoClicks.push({ type: 'approve', id: item.id });
						}
						
						if(approveStatus== '2')
						{
							autoClicks.push({ type: 'notavailable', id: item.id });
						}
						
					});

						html += '</div>'; 

						$('.checklist-question').html(html);
						
						setTimeout(() => {
							autoClicks.forEach(click => {
								if (click.type === 'reject') {
									const btn = document.getElementById('question-reject-' + click.id);
									if (btn) btn.click();
								}
								if (click.type === 'approve') {
									const btn = document.getElementById('question-approve-' + click.id);
									if (btn) btn.click();
								}
								if (click.type === 'notavailable') {
									const btn = document.getElementById('question-na-' + click.id);
									if (btn) btn.click();
								}
							});
						}, 0);
						
						// dropzone work
						Dropzone.autoDiscover = false;
						//alert(response.existingSubChecklistFiles);
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-subchecklist-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								dictRemoveFile: 'Delete file',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;
									let subchecklistId = dropzoneElement.querySelector('[name="subchecklist_id"]').value;
									
									
									//--------upload new file-----------
									this.on("success", function(file, response) {
										if(response.new == 1)
										{
											const extension = response.filename.split('.').pop().toLowerCase();
											const fileUrl = response.url;

											// Add uploaded filename for later removal
											file.uploadedFilename = response.filename;

											// For videos, manually insert preview
											if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
												setTimeout(() => {
													const videoElement = document.createElement('video');
													videoElement.setAttribute('src', fileUrl);
													videoElement.setAttribute('controls', 'true');
													videoElement.style.maxWidth = '100%';
													videoElement.style.maxHeight = '100px';

													if (file.previewElement) {
														const dzImage = file.previewElement.querySelector('.dz-image');
														if (dzImage) {
															dzImage.innerHTML = '';
															dzImage.appendChild(videoElement);
														}
													}
												}, 0);
											}
										}
									});
									//--------------------
									// Add existing files (preloaded from server)
									response.existingSubChecklistFiles
									.filter(file => file.subchecklist_id == subchecklistId)
									.forEach(function (file) {
										let mockFile = { name: file.name, size: file.size, accepted: true };

										dz.emit("addedfile", mockFile);
										//dz.emit("thumbnail", mockFile, file.url);
										
										let fileUrl = file.url;
										let extension = fileUrl.split('.').pop().toLowerCase();
										//alert(extension);
										if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension))
										{
											dz.emit("thumbnail", mockFile, file.url);
										}
										else if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
										  // Inject video preview manually
										  console.log(file.url);
										  setTimeout(() => {
											const videoElement = document.createElement('video');
											videoElement.setAttribute('src', file.url);
											videoElement.setAttribute('controls', 'true');
											videoElement.style.maxWidth = '100%';
											videoElement.style.maxHeight = '100px';
											mockFile.previewElement.querySelector(".dz-image").innerHTML = '';
											mockFile.previewElement.querySelector(".dz-image").appendChild(videoElement);
										  }, 0);
										}
										
										dz.emit("complete", mockFile);

										mockFile.previewElement.classList.add('dz-success', 'dz-complete');
										mockFile.uploadedFilename = file.name;
										$('#hasEditMultipleFile' + subchecklistId).val(1);
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											$.ajax({
												url: "{{ route('subchecklist-file-delete') }}", // handle deletion logic on server
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
													//alert(response.count);
													if(response.count == '0')
													{
														$('#hasEditMultipleFile' + response.subchecklist_id).val('');
													}
													else{
														$('#hasEditMultipleFile' + response.subchecklist_id).val(1);
													}
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						//--- upload new files ------- 
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							new Dropzone(dropzoneElement, {
								url: "{{ route('reject-subchecklist-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								dictRemoveFile: 'Delete file',
								init: function () {
									this.on("success", function (file, response) {
										console.log('Uploaded:', response);

										// Attach filename to file object so we can use it on removal
										file.uploadedFilename = response.filename;
										//alert(response.filename);
										// Replace default preview with file name
										file.previewElement.querySelector("[data-dz-name]").textContent = response.filename;
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											// Send AJAX request to delete the file from storage and DB
											$.ajax({
												url: "{{ route('reject-subckecklist-file-delete') }}", // You need to define this route
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						
				} else {
						$('.checklist-question-sticky-footer').removeClass('d-none');
						$('.sticky-footer-completed').addClass('d-none');
						//alert(response.next_approve);
						html = '<button class="task-filter-button save-next">Save & exit</button>';
						html += '<div class="single-checklist">';
						html += '<div class="question-header">' + response.categoryName + '</div>';
						html += '<div class="question-text">';
						html += '<span id="single-question">' + response.name + '</span>';
						html += '<input type="hidden" id="current_id" value="' + response.currentid + '">';
						html += '<input type="hidden" id="category_id" value="' + category_id + '">';
						html += '<input type="hidden" id="subcategory_id" value="' + subcategory_id + '">';
						html += '</div>'; 
						html += '<span id="errormsg" class="question-error-message">Please enter text or file.</span>';
						html += '<div class="reject-form mb-3" id="rejectForm-' + response.currentid + '">';
						html += '<textarea id="single_rejecttext" placeholder="State why you rejected this...">' + response.next_rejected_region + '</textarea>';
						html += '<input type="hidden" id="mode" value="single">';
						html += '<input type="hidden" id="hasEditFile" value="">';
						html += '<input type="hidden" id="approveStatus">';
						html += '<form action="' + rejectFilesRoute + '" class="dropzone" id="dropzone-1">';
						html += '<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="' + response.currentid +'">';
						html += '<input type="hidden" name="subcategory_id" id="single-subcategory_id" value="' + subcategory_id + '">';
						html += '<input type="hidden" name="task_id" id="single-task_id" value="' + task_id +'">';
						html += '</form>';
						html += '</div>'; 
						html += '<div class="action-buttons-without-text">';
						html += '<button class="rejected" id="question-reject-' + response.currentid + '" onclick="handleReject(' + response.currentid + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + response.currentid + '" onclick="handleApprove(' + response.currentid + ')"><i class="fa-solid fa-check"></i></button>';
						html += '<button class="notavailable" id="question-na-' + response.currentid + '" onclick="handleNotavailable(' + response.currentid + ')"><span class="button-na">NA</span></button>';
						html += '</div>'; 
						html += '</div>'; 

						$('.checklist-question').html(html); 
						
						if(response.next_approve== '0')
						{
							const rejectButton = document.getElementById('question-reject-' + response.currentid);
							rejectButton.click();
						}
						
						if(response.next_approve== '1')
						{
							const approveButton = document.getElementById('question-approve-' + response.currentid);
							approveButton.click();
						}
						
						if(response.next_approve== '2')
						{
							const naButton = document.getElementById('question-na-' + response.currentid);
							naButton.click();
						}
						
						// dropzone work
						Dropzone.autoDiscover = false;
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictRemoveFile: 'Delete file',
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;
									
									//--------upload new file-----------
									this.on("success", function(file, response) {
										if(response.new == 1)
										{
											const extension = response.filename.split('.').pop().toLowerCase();
											const fileUrl = response.url;

											// Add uploaded filename for later removal
											file.uploadedFilename = response.filename;

											// For videos, manually insert preview
											if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
												setTimeout(() => {
													const videoElement = document.createElement('video');
													videoElement.setAttribute('src', fileUrl);
													videoElement.setAttribute('controls', 'true');
													videoElement.style.maxWidth = '100%';
													videoElement.style.maxHeight = '100px';

													if (file.previewElement) {
														const dzImage = file.previewElement.querySelector('.dz-image');
														if (dzImage) {
															dzImage.innerHTML = '';
															dzImage.appendChild(videoElement);
														}
													}
												}, 0);
											}
										}
									});
									//--------------------
									// Add existing files (preloaded from server)
									response.existingNextFiles.forEach(function (file) {
										let mockFile = { name: file.name, size: file.size, accepted: true };

										dz.emit("addedfile", mockFile);
										//dz.emit("thumbnail", mockFile, file.url);
										let fileUrl = file.url;
										let extension = fileUrl.split('.').pop().toLowerCase();
										//alert(extension);
										if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension))
										{
											dz.emit("thumbnail", mockFile, file.url);
										}
										else if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
										  // Inject video preview manually
										  console.log(file.url);
										  setTimeout(() => {
											const videoElement = document.createElement('video');
											videoElement.setAttribute('src', file.url);
											videoElement.setAttribute('controls', 'true');
											videoElement.style.maxWidth = '100%';
											videoElement.style.maxHeight = '100px';
											mockFile.previewElement.querySelector(".dz-image").innerHTML = '';
											mockFile.previewElement.querySelector(".dz-image").appendChild(videoElement);
										  }, 0);
										}
										
										dz.emit("complete", mockFile);

										mockFile.previewElement.classList.add('dz-success', 'dz-complete');

										// Store filename for deletion
										mockFile.uploadedFilename = file.name;
										$('#hasEditFile').val(1);
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											$.ajax({
												url: "{{ route('checklist-file-delete') }}", // handle deletion logic on server
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
													//alert(response.count);
													if(response.count == '0')
													{
														$('#hasEditFile').val('');
													}
													else{
														$('#hasEditFile').val(1);
													}
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						//--- upload new files ------- 
						Dropzone.autoDiscover = false; // very important
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								dictRemoveFile: 'Delete file',
								init: function () {
									this.on("success", function (file, response) {
										console.log('Uploadedsss:', response);

										// Attach filename to file object so we can use it on removal
										file.uploadedFilename = response.filename;
										//alert(response.filename);
										// Replace default preview with file name
										file.previewElement.querySelector("[data-dz-name]").textContent = response.filename;
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											// Send AJAX request to delete the file from storage and DB
											$.ajax({
												url: "{{ route('reject-file-delete') }}", // You need to define this route
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
					}
			},
		});
	});
	
	
	// =============== BACK BUTTON ============
	
	$(document).on('click','.previous_question', function(){
		var current_id = $('#current_checklist_id').val();
		var order_no = $('#order_no').val();
		//alert(current_id);
		var category_id = $('#category_id').val();
		var subcategory_id = $('#subcategory_id').val();
		var task_id = $('#task_id').val();
		var URL = "{{ route('checklist-previous-question') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {
				task_id:task_id,
				current_question_id:current_id,
				order_no:order_no,
				category_id:category_id,
				subcategory_id:subcategory_id,
				_token: csrfToken
			},
			dataType: 'json',
			success: function(response) {
				//alert(response.currentid);
				 $('#current_checklist_id').val(response.currentid);
				 $('#order_no').val(response.order_no);
				 if(response.order_no == '1')
				 {
					 $('.previous_question').hide();
				 }
				 //$('#single-question').html(response.name);
				 const rejectFilesRoute = "{{ route('reject-files') }}";
				 const rejectSubcheckFilesRoute = "{{ route('reject-subchecklist-files') }}";
				if (response.subchecklist.length > 0) {
				// Has subchecklists
				let autoClicks = [];
				let html = '<button class="task-filter-button save-next">Save & exit</button>';
				html += '<div class="sub-checklist">';
				html += '<div class="question-header">' + response.categoryName + '</div>';
				html += '<div class="question-text">';
				html += '<span id="multiple-question">' + response.name + '</span>';
				html += '</div>';

				response.subchecklist.forEach((item, index) => {
						
						let match = response.fetchsubChklistArr.find(e => e.subchecklist_id == item.id);
						let rejectedText = match ? match.rejected_region : '';
						let approveStatus = match ? match.approve : '';
					
						let rejectId = 'rejectForm-' + item.id;
						html += '<div class="sub-checklist-question">';
						html += '<div class="action-buttons">';
						html += '<span class="d-flex align-items-center">' + item.name + '</span>';
						html += '<div class="btn-div">';
						html += '<button class="rejected" id="question-reject-' + item.id + '" onclick="handleReject(' + item.id + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + item.id + '" onclick="handleApprove(' + item.id + ')"><i class="fa-solid fa-check"></i></button>';
						html += '<button class="notavailable" id="question-na-' + item.id + '" onclick="handleNotavailable(' + item.id + ')"><span class="button-na">NA</span></button>';
						html += '</div>'; 
						html += '</div>'; 
						html += '<span id="errorMulmsg'+ item.id +'" class="question-error-message">Please enter text or file.</span>';
						html += '<div class="reject-form mb-3" id="' + rejectId + '">';
						html += '<textarea placeholder="State why you rejected this...">' + rejectedText + '</textarea>';
						html += '<input type="hidden" id="mode" value="multiple">';
						html += '<input type="hidden" id="approveMultipleStatus' + item.id + '">';
						html += '<input type="hidden" id="hasEditMultipleFile' + item.id +'" value="">'
						html += '<form action="' + rejectSubcheckFilesRoute + '" class="dropzone" id="dropzone-' + item.id + '">';
						html += '<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="' + response.currentid +'">';
						html += '<input type="hidden" name="subchecklist_id" value="' + item.id + '">';
						html += '<input type="hidden" name="task_id" id="single-task_id" value="' + task_id +'">';
						html += '</form>';
						html += '</div>'; 
						html += '</div>'; 
						
						if(approveStatus== '0')
						{
							autoClicks.push({ type: 'reject', id: item.id });
						}
						
						if(approveStatus== '1')
						{
							autoClicks.push({ type: 'approve', id: item.id });
						}
						
						if(approveStatus== '2')
						{
							autoClicks.push({ type: 'notavailable', id: item.id });
						}
					});

						html += '</div>'; 

						$('.checklist-question').html(html); 
						
						setTimeout(() => {
							autoClicks.forEach(click => {
								if (click.type === 'reject') {
									const btn = document.getElementById('question-reject-' + click.id);
									if (btn) btn.click();
								}
								if (click.type === 'approve') {
									const btn = document.getElementById('question-approve-' + click.id);
									if (btn) btn.click();
								}
								if (click.type === 'notavailable') {
									const btn = document.getElementById('question-na-' + click.id);
									if (btn) btn.click();
								}
							});
						}, 0);
						
						// dropzone work
						Dropzone.autoDiscover = false;
						//alert(response.existingSubChecklistFiles);
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-subchecklist-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictRemoveFile: 'Delete file',
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;
									let subchecklistId = dropzoneElement.querySelector('[name="subchecklist_id"]').value;
									// Add existing files (preloaded from server)
									//alert(response.existingSubChecklistFiles);
									response.existingSubChecklistFiles
									.filter(file => file.subchecklist_id == subchecklistId)
									.forEach(function (file) {
										let mockFile = { name: file.name, size: file.size, accepted: true };

										dz.emit("addedfile", mockFile);
										//dz.emit("thumbnail", mockFile, file.url);
										//alert(file.url);
										let fileUrl = file.url;
										let extension = fileUrl.split('.').pop().toLowerCase();
										//alert(extension);
										if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension))
										{
											dz.emit("thumbnail", mockFile, file.url);
										}
										else if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
										  // Inject video preview manually
										  console.log(file.url);
										  setTimeout(() => {
											const videoElement = document.createElement('video');
											videoElement.setAttribute('src', file.url);
											videoElement.setAttribute('controls', 'true');
											videoElement.style.maxWidth = '100%';
											videoElement.style.maxHeight = '100px';
											mockFile.previewElement.querySelector(".dz-image").innerHTML = '';
											mockFile.previewElement.querySelector(".dz-image").appendChild(videoElement);
										  }, 0);
										}
										
										
										
										dz.emit("complete", mockFile);

										mockFile.previewElement.classList.add('dz-success', 'dz-complete');
										mockFile.uploadedFilename = file.name;
										$('#hasEditMultipleFile' + subchecklistId).val(1);
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											$.ajax({
												url: "{{ route('subchecklist-file-delete') }}", // handle deletion logic on server
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
													//alert(response.count);
													if(response.count == '0')
													{
														$('#hasEditMultipleFile' + response.subchecklist_id).val('');
													}
													else{
														$('#hasEditMultipleFile' + response.subchecklist_id).val(1);
													}
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						//--- upload new files ------- 
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							new Dropzone(dropzoneElement, {
								url: "{{ route('reject-subchecklist-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								dictRemoveFile: 'Delete file',
								init: function () {
									this.on("success", function (file, response) {
										console.log('Uploaded:', response);

										// Attach filename to file object so we can use it on removal
										file.uploadedFilename = response.filename;
										//alert(response.filename);
										// Replace default preview with file name
										file.previewElement.querySelector("[data-dz-name]").textContent = response.filename;
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											// Send AJAX request to delete the file from storage and DB
											$.ajax({
												url: "{{ route('reject-subckecklist-file-delete') }}", // You need to define this route
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
				} else {
						
						let html = '<button class="task-filter-button save-next">Save & exit</button>';
						html += '<div class="single-checklist">';
						html += '<div class="question-header">' + response.categoryName + '</div>';
						html += '<div class="question-text">';
						html += '<span id="single-question">' + response.name + '</span>';
						html += '<input type="hidden" id="current_id" value="' + response.currentid + '">';
						html += '<input type="hidden" id="category_id" value="' + category_id + '">';
						html += '<input type="hidden" id="subcategory_id" value="' + subcategory_id + '">';
						html += '</div>'; 
						html += '<span id="errormsg" class="question-error-message">Please enter text or file.</span>';
						html += '<div class="reject-form mb-3" id="rejectForm-' + response.currentid + '">';
						html += '<textarea id="single_rejecttext" placeholder="State why you rejected this...">' + response.next_rejected_region + '</textarea>';
						html += '<input type="hidden" id="mode" value="single">';
						html += '<input type="hidden" id="hasEditFile" value="">';
						html += '<input type="hidden" id="approveStatus">';
						//html += '<form action="/your-upload-route" class="dropzone" id="dropzone-' + response.currentid + '"></form>';
						html += '<form action="' + rejectFilesRoute + '" class="dropzone" id="dropzone-1">';
						html += '<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="' + response.currentid +'">';
						html += '<input type="hidden" name="subcategory_id" id="single-subcategory_id" value="' + subcategory_id + '">';
						html += '<input type="hidden" name="task_id" id="single-task_id" value="' + task_id +'">';
						html += '</form>';
						html += '</div>'; 
						html += '<div class="action-buttons-without-text">';
						html += '<button class="rejected" id="question-reject-' + response.currentid + '" onclick="handleReject(' + response.currentid + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + response.currentid + '" onclick="handleApprove(' + response.currentid + ')"><i class="fa-solid fa-check"></i></button>';
						html += '<button class="notavailable" id="question-na-' + response.currentid + '" onclick="handleNotavailable(' + response.currentid + ')"><span class="button-na">NA</span></button>';
						html += '</div>'; 
						html += '</div>'; 

						$('.checklist-question').html(html);
						if(response.next_approve== '0')
						{
							const rejectButton = document.getElementById('question-reject-' + response.currentid);
							rejectButton.click();
						}
						
						if(response.next_approve== '1')
						{
							const approveButton = document.getElementById('question-approve-' + response.currentid);
							approveButton.click();
						}
						
						if(response.next_approve== '2')
						{
							const naButton = document.getElementById('question-na-' + response.currentid);
							naButton.click();
						}
						
						// dropzone work
						Dropzone.autoDiscover = false;
						
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictRemoveFile: 'Delete file',
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;
									
									//--------upload new file-----------
									this.on("success", function(file, response) {
										if(response.new == 1)
										{
											const extension = response.filename.split('.').pop().toLowerCase();
											const fileUrl = response.url;

											// Add uploaded filename for later removal
											file.uploadedFilename = response.filename;

											// For videos, manually insert preview
											if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
												setTimeout(() => {
													const videoElement = document.createElement('video');
													videoElement.setAttribute('src', fileUrl);
													videoElement.setAttribute('controls', 'true');
													videoElement.style.maxWidth = '100%';
													videoElement.style.maxHeight = '100px';

													if (file.previewElement) {
														const dzImage = file.previewElement.querySelector('.dz-image');
														if (dzImage) {
															dzImage.innerHTML = '';
															dzImage.appendChild(videoElement);
														}
													}
												}, 0);
											}
										}
									});
									//--------------------
									// Add existing files (preloaded from server)
									response.existingPreviousFiles.forEach(function (file) {
										let mockFile = { name: file.name, size: file.size, accepted: true };

										dz.emit("addedfile", mockFile);
										//dz.emit("thumbnail", mockFile, file.url);
										
										let fileUrl = file.url;
										let extension = fileUrl.split('.').pop().toLowerCase();
										//alert(extension);
										if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension))
										{
											dz.emit("thumbnail", mockFile, file.url);
										}
										else if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
										  // Inject video preview manually
										  console.log(file.url);
										  setTimeout(() => {
											const videoElement = document.createElement('video');
											videoElement.setAttribute('src', file.url);
											videoElement.setAttribute('controls', 'true');
											videoElement.style.maxWidth = '100%';
											videoElement.style.maxHeight = '100px';
											mockFile.previewElement.querySelector(".dz-image").innerHTML = '';
											mockFile.previewElement.querySelector(".dz-image").appendChild(videoElement);
										  }, 0);
										}
										
										
										dz.emit("complete", mockFile);

										mockFile.previewElement.classList.add('dz-success', 'dz-complete');

										// Store filename for deletion
										mockFile.uploadedFilename = file.name;
										$('#hasEditFile').val(1);
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											$.ajax({
												url: "{{ route('checklist-file-delete') }}", // handle deletion logic on server
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
													//alert(response.count);
													if(response.count == '0')
													{
														$('#hasEditFile').val('');
													}
													else{
														$('#hasEditFile').val(1);
													}
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						//--- upload files -------
						
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 2, // MB
								acceptedFiles: 'image/*',
								addRemoveLinks: true,
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								dictRemoveFile: 'Delete file',
								init: function () {
									this.on("success", function (file, response) {
										console.log('Uploaded:', response);

										// Attach filename to file object so we can use it on removal
										file.uploadedFilename = response.filename;

										// Replace default preview with file name
										file.previewElement.querySelector("[data-dz-name]").textContent = response.filename;
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											// Send AJAX request to delete the file from storage and DB
											$.ajax({
												url: "{{ route('reject-file-delete') }}", // You need to define this route
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
					}
			},
		});
	});
	
	$(document).on('click','.get_checklist', function(){
	//alert('ok');previous_question
	   $('.checklist-question-sticky-footer').show(); // 21-05-2025
	   var directEdit = $(this).data('dedit'); // 21-06-2025
	   var cat_id = $(this).data('cat');
	   var subcat_id = $(this).data('subcat');
	   var task_id = $(this).data('task');
	   var checklist_id = $(this).data('checklist');
	   var URL = "{{ route('get-checklist-page') }}";
	   $.ajax({
			url: URL,
			type: "POST",
			data: {checklist_id:checklist_id, task_id:task_id, cat_id:cat_id, subcat_id:subcat_id,directEdit:directEdit, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.barHtml);
				$('#direct_edit').val(response.directEdit);
				// ------progress bar work ----------
				
					$('#progress-bar-section').append(response.barHtml);
				// ----------------------------------
				//--implement 08-05-2025
				if (!document.querySelector('link[href*="bootstrap.min.css"]')) {
					var bootstrapCSS = document.createElement('link');
					bootstrapCSS.rel = 'stylesheet';
					bootstrapCSS.href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css';
					document.head.appendChild(bootstrapCSS);
				}
				//------
				
				$('#current_checklist_id').val(response.currentid);
				$('#order_no').val(response.order_no);
				const rejectFilesRoute = "{{ route('reject-files') }}";
				const rejectSubcheckFilesRoute = "{{ route('reject-subchecklist-files') }}";
				if (response.subchecklist.length > 0) {
				$('.checklist-question-sticky-footer').removeClass('d-none');
				$('.sticky-footer').addClass('d-none');
				// Has subchecklists
				let autoClicks = [];
				let html = '<button class="task-filter-button save-next">Save & exit</button>';
				html += '<div class="sub-checklist">';
				html += '<div class="question-header">' + response.categoryName + '</div>';
				html += '<div class="question-text">';
				html += '<span id="multiple-question">' + response.name + '</span>';
				html += '</div>';

				response.subchecklist.forEach((item, index) => {
					    //alert(item.id);
						let match = response.fetchsubChklistArr.find(e => e.subchecklist_id == item.id);
						let rejectedText = match ? match.rejected_region : '';
						let approveStatus = match ? match.approve : '';
						//alert(approveStatus);alert(rejectedText);
						
						let rejectId = 'rejectForm-' + item.id;
						html += '<div class="sub-checklist-question">';
						html += '<div class="action-buttons">';
						html += '<span class="d-flex align-items-center">' + item.name + '</span>';
						html += '<div class="btn-div">';
						html += '<button class="rejected" id="question-reject-' + item.id + '" onclick="handleReject(' + item.id + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + item.id + '" onclick="handleApprove(' + item.id + ')"><i class="fa-solid fa-check"></i></button>';
						html += '<button class="notavailable" id="question-na-' + item.id + '" onclick="handleNotavailable(' + item.id + ')"><span class="button-na">NA</span></button>';
						html += '</div>'; 
						html += '</div>';
						html += '<span id="errorMulmsg'+ item.id +'" class="question-error-message">Please enter text or file.</span>';
						html += '<div class="reject-form mb-3" id="' + rejectId + '">';
						html += '<textarea placeholder="State why you rejected this...">' + rejectedText +'</textarea>';
						html += '<input type="hidden" id="mode" value="multiple">';
						html += '<input type="hidden" id="approveMultipleStatus' + item.id + '">';
						html += '<input type="hidden" id="hasEditMultipleFile' + item.id +'" value="">'
						html += '<form action="' + rejectSubcheckFilesRoute + '" class="dropzone" id="dropzone-' + item.id + '">';
						html += '<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="' + response.currentid +'">';
						html += '<input type="hidden" name="subchecklist_id" value="' + item.id + '">';
						html += '<input type="hidden" name="task_id" id="single-task_id" value="' + task_id +'">';
						html += '</form>';
						html += '</div>'; 
						html += '</div>';
						
						if(approveStatus== '0')
						{
							autoClicks.push({ type: 'reject', id: item.id });
						}
						
						if(approveStatus== '1')
						{
							autoClicks.push({ type: 'approve', id: item.id });
						}
						
						if(approveStatus== '2')
						{
							autoClicks.push({ type: 'notavailable', id: item.id });
						}
					});

						html += '</div>'; 
						$('.checklist-question').html(html);
						
						setTimeout(() => {
							autoClicks.forEach(click => {
								if (click.type === 'reject') {
									const btn = document.getElementById('question-reject-' + click.id);
									if (btn) btn.click();
								}
								if (click.type === 'approve') {
									const btn = document.getElementById('question-approve-' + click.id);
									if (btn) btn.click();
								}
								if (click.type === 'notavailable') {
									const btn = document.getElementById('question-na-' + click.id);
									if (btn) btn.click();
								}
							});
						}, 0);
						
						// dropzone work
						Dropzone.autoDiscover = false;
						
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-subchecklist-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictRemoveFile: 'Delete file',
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;
									let subchecklistId = dropzoneElement.querySelector('[name="subchecklist_id"]').value;
									
									//--------upload new file-----------
									this.on("success", function(file, response) {
										if(response.new == 1)
										{
											const extension = response.filename.split('.').pop().toLowerCase();
											const fileUrl = response.url;

											// Add uploaded filename for later removal
											file.uploadedFilename = response.filename;

											// For videos, manually insert preview
											if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
												setTimeout(() => {
													const videoElement = document.createElement('video');
													videoElement.setAttribute('src', fileUrl);
													videoElement.setAttribute('controls', 'true');
													videoElement.style.maxWidth = '100%';
													videoElement.style.maxHeight = '100px';

													if (file.previewElement) {
														const dzImage = file.previewElement.querySelector('.dz-image');
														if (dzImage) {
															dzImage.innerHTML = '';
															dzImage.appendChild(videoElement);
														}
													}
												}, 0);
											}
										}
									});
									//--------------------
									// Add existing files (preloaded from server)
									response.existingSubChecklistFiles
									.filter(file => file.subchecklist_id == subchecklistId)
									.forEach(function (file) {
										//alert(file.name)
										let mockFile = { name: file.name, size: file.size, accepted: true };

										dz.emit("addedfile", mockFile);
										
										//dz.emit("thumbnail", mockFile, file.url);
										// Show thumbnail only if image
										let fileUrl = file.url;
										let extension = fileUrl.split('.').pop().toLowerCase();
										//alert(extension);
										if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension))
										{
											dz.emit("thumbnail", mockFile, file.url);
										}
										else if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
										  // Inject video preview manually
										  console.log(file.url);
										  setTimeout(() => {
											const videoElement = document.createElement('video');
											videoElement.setAttribute('src', file.url);
											videoElement.setAttribute('controls', 'true');
											videoElement.style.maxWidth = '100%';
											videoElement.style.maxHeight = '100px';
											mockFile.previewElement.querySelector(".dz-image").innerHTML = '';
											mockFile.previewElement.querySelector(".dz-image").appendChild(videoElement);
										  }, 0);
										}
										
										dz.emit("complete", mockFile);

										mockFile.previewElement.classList.add('dz-success', 'dz-complete');
										mockFile.uploadedFilename = file.name;
										$('#hasEditMultipleFile' + subchecklistId).val(1);
									});
									
									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											$.ajax({
												url: "{{ route('subchecklist-file-delete') }}", // handle deletion logic on server
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
													//alert(response.count);
													if(response.count == '0')
													{
														$('#hasEditMultipleFile' + response.subchecklist_id).val('');
													}
													else{
														$('#hasEditMultipleFile' + response.subchecklist_id).val(1);
													}
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						//--- upload new files ------- 
						// 04-07-2025 block today
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							new Dropzone(dropzoneElement, {
								url: "{{ route('reject-subchecklist-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								dictRemoveFile: 'Delete file',
								init: function () {
									//alert(response.new);
									this.on("success", function (file, response) {
										console.log('Uploaded:', response);
										//alert(response.filename);
										// Attach filename to file object so we can use it on removal
										file.uploadedFilename = response.filename;
										//alert(response.filename);
										// Replace default preview with file name
										file.previewElement.querySelector("[data-dz-name]").textContent = response.filename;
										
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											// Send AJAX request to delete the file from storage and DB
											$.ajax({
												url: "{{ route('reject-subckecklist-file-delete') }}", // You need to define this route
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						
				} else {
						$('.checklist-question-sticky-footer').removeClass('d-none');
						$('.sticky-footer').addClass('d-none');
						//alert(response.next_approve);
						let html = '<button class="task-filter-button save-next">Save & exit</button>';
						html += '<div class="single-checklist">';
						html += '<div class="question-header">' + response.categoryName + '</div>';
						html += '<div class="question-text">';
						html += '<span id="single-question">' + response.name + '</span>';
						html += '<input type="hidden" id="current_id" value="' + response.currentid + '">';
						html += '<input type="hidden" id="category_id" value="' + response.category_id + '">';
						html += '<input type="hidden" id="subcategory_id" value="' + response.subcategory_id + '">';
						html += '</div>'; 
						html += '<span id="errormsg" class="question-error-message">Please enter text or file.</span>';
						html += '<div class="reject-form mb-3" id="rejectForm-' + response.currentid + '">';
						html += '<textarea id="single_rejecttext" placeholder="State why you rejected this...">' + response.next_rejected_region + '</textarea>';
						html += '<input type="hidden" id="mode" value="single">';
						html += '<input type="hidden" id="hasEditFile" value="">';
						html += '<input type="hidden" id="approveStatus">';
						html += '<form action="' + rejectFilesRoute + '" class="dropzone" id="dropzone-1">';
						html += '<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="' + response.currentid +'">';
						html += '<input type="hidden" name="subcategory_id" id="single-subcategory_id" value="' + response.subcategory_id + '">';
						html += '<input type="hidden" name="task_id" id="single-task_id" value="' + task_id +'">';
						html += '</form>';
						html += '</div>'; 
						html += '<div class="action-buttons-without-text">';
						html += '<button class="rejected" id="question-reject-' + response.currentid + '" onclick="handleReject(' + response.currentid + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + response.currentid + '" onclick="handleApprove(' + response.currentid + ')"><i class="fa-solid fa-check"></i></button>';
						html += '<button class="notavailable" id="question-na-' + response.currentid + '" onclick="handleNotavailable(' + response.currentid + ')"><span class="button-na">NA</span></button>';
						html += '</div>'; 
						html += '</div>'; 

						$('.checklist-question').html(html); 
						
						if(response.next_approve== '0')
						{
							const rejectButton = document.getElementById('question-reject-' + response.currentid);
							rejectButton.click();
						}
						
						if(response.next_approve== '1')
						{
							const approveButton = document.getElementById('question-approve-' + response.currentid);
							approveButton.click();
						}
						
						if(response.next_approve== '2')
						{
							const naButton = document.getElementById('question-na-' + response.currentid);
							naButton.click();
						}
						
						// dropzone work
						Dropzone.autoDiscover = false;
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 10, // MB
								acceptedFiles: 'image/*,video/*',
								//acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictRemoveFile: 'Delete file',
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;
									
									//--------upload new file-----------
									this.on("success", function(file, response) {
										if(response.new == 1)
										{
											const extension = response.filename.split('.').pop().toLowerCase();
											const fileUrl = response.url;

											// Add uploaded filename for later removal
											file.uploadedFilename = response.filename;

											// For videos, manually insert preview
											if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
												setTimeout(() => {
													const videoElement = document.createElement('video');
													videoElement.setAttribute('src', fileUrl);
													videoElement.setAttribute('controls', 'true');
													videoElement.style.maxWidth = '100%';
													videoElement.style.maxHeight = '100px';

													if (file.previewElement) {
														const dzImage = file.previewElement.querySelector('.dz-image');
														if (dzImage) {
															dzImage.innerHTML = '';
															dzImage.appendChild(videoElement);
														}
													}
												}, 0);
											}
										}
									});
									//--------------------
									// Add existing files (preloaded from server)
									response.existingNextFiles.forEach(function (file) {
										let mockFile = { name: file.name, size: file.size, accepted: true };

										dz.emit("addedfile", mockFile);
										//dz.emit("thumbnail", mockFile, file.url);
										
										let fileUrl = file.url;
										let extension = fileUrl.split('.').pop().toLowerCase();
										//alert(extension);
										if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension))
										{
											dz.emit("thumbnail", mockFile, file.url);
										}
										else if (['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(extension)) {
										  // Inject video preview manually
										  console.log(file.url);
										  setTimeout(() => {
											const videoElement = document.createElement('video');
											videoElement.setAttribute('src', file.url);
											videoElement.setAttribute('controls', 'true');
											videoElement.style.maxWidth = '100%';
											videoElement.style.maxHeight = '100px';
											mockFile.previewElement.querySelector(".dz-image").innerHTML = '';
											mockFile.previewElement.querySelector(".dz-image").appendChild(videoElement);
										  }, 0);
										}
										
										dz.emit("complete", mockFile);

										mockFile.previewElement.classList.add('dz-success', 'dz-complete');

										// Store filename for deletion
										mockFile.uploadedFilename = file.name;
										$('#hasEditFile').val(1);
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											$.ajax({
												url: "{{ route('checklist-file-delete') }}", // handle deletion logic on server
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
													//alert(response.count);
													if(response.count == '0')
													{
														$('#hasEditFile').val('');
													}
													else{
														$('#hasEditFile').val(1);
													}
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
						
						//--- upload new files ------- 
						Dropzone.autoDiscover = false; // very important
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 2, // MB
								acceptedFiles: 'image/*',
								addRemoveLinks: true,
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								dictDefaultMessage: '<span><i class="fa-solid fa-arrow-up-from-bracket"></i><br>Upload File</span>',
								dictRemoveFile: 'Delete file',
								init: function () {
									this.on("success", function (file, response) {
										console.log('Uploadedsss:', response);

										// Attach filename to file object so we can use it on removal
										file.uploadedFilename = response.filename;
										//alert(response.filename);
										// Replace default preview with file name
										file.previewElement.querySelector("[data-dz-name]").textContent = response.filename;
									});

									this.on("removedfile", function (file) {
										if (file.uploadedFilename) {
											// Send AJAX request to delete the file from storage and DB
											$.ajax({
												url: "{{ route('reject-file-delete') }}", // You need to define this route
												type: "POST",
												data: {
													_token: csrfToken,
													filename: file.uploadedFilename
												},
												success: function (response) {
													console.log('Deleted:', response);
												},
												error: function (xhr) {
													console.error('Delete failed:', xhr.responseText);
												}
											});
										}
									});
								}
							});
						});
					}
			},
		});
	   
   });
   
    $(document).on('click','.submit_task', function(){
		var task_id  = $('#completed_task_id').val();
		var category_id = $('#completed_category_id').val();
		var subcategory_id = $('#completed_subcategory_id').val();
		var URL = "{{ route('submit-completed-task') }}";
		var pendingcount = $('#pending-count').val();
		//alert(pendingcount)
		if(pendingcount > 0)
		{
			Swal.fire({
					  icon: "warning",
					  title: "Task is pending. Please complete your task",
					  //html: '<div style="display: flex; justify-content: center; gap: 20px; margin-top: 10px;"></div>',
					  confirmButtonColor: "#0b2b57",
					  customClass: {
						confirmButton : 'swal-save-exist-black'
					  }
					  
					});
					
				return false;
		}
		
		$.ajax({
			url: URL,
			type: "POST",
			data: {task_id:task_id, category_id:category_id, subcategory_id:subcategory_id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				$('#pending-count').val(0);
				const thankyouUrlTemplate = "{{ url('thank-you/TASK_ID') }}";
				const redirectUrl = thankyouUrlTemplate.replace('TASK_ID', task_id);
				window.location.href = redirectUrl;
			},
		});
   });
   
   $(document).on('click', '.save-next', function(){
	   Swal.fire({
			  title: '<div class="swal-title-class">Are you sure you want to exit ?</div>',
			  html: '<div class="swal-message-class">You can continue your saved attempt from the task list labeled as "incomplete".</div>',
			  //icon: "warning",
			  showCancelButton: true,
			  cancelButtonText: "Cancel",
			  confirmButtonText: "Save and exit",
			  confirmButtonColor: "#0b2b57", 
			  cancelButtonColor: "#e0e0e0",
			  customClass: {
				cancelButton: 'swal-cancel-black',
				confirmButton : 'swal-save-exist-black'
			  }
			}).then((result) => {
			  if (result.isConfirmed) {
				saveAndExit(); // your function
			  }
			});


   })
});

function initializeDropzones() {
	document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
		if (dropzoneElement.dropzone) return; // Avoid initializing twice

		let subchecklistInput = dropzoneElement.querySelector('[name="subchecklist_id"]');
		let subchecklistId = subchecklistInput ? subchecklistInput.value : null;
		console.log('Initializing dropzone for ID:', subchecklistId);

		let myDropzone = new Dropzone(dropzoneElement, {
			// same config...
		});
	});
}
function saveAndExit()
{
	
	var mode = $('#mode').val();
		//alert(mode);
		var approveStatus = $('#approveStatus').val();
		//alert(approveStatus);
		//alert("approveStatus" + approveStatus);
		if(mode=='single')
		{
			if(approveStatus == '0')
			{
				var textIsEmpty = $('#single_rejecttext').val().trim() === '';
				var hasEditFile = $('#hasEditFile').val();
				//alert(hasEditFile);
				var hasFiles = false;
				try {
					var dzInstance = Dropzone.forElement('#dropzone-1');
					hasFiles = dzInstance.files.length > 0;
				} catch (e) {
					console.warn("Dropzone not found or not initialized yet.");
				}
				
				if (textIsEmpty &&  !hasFiles && !hasEditFile) {
					$('#errormsg').fadeIn().delay(2000).fadeOut();
					return false;
				}
			}
			
			/*if(approveStatus == '')
			{
				Swal.fire({
					  icon: "warning",
					  title: "Please select any one",
					  html: '<div style="display: flex; justify-content: center; gap: 20px; margin-top: 10px;"><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-xmark" style="font-size: 20px; color: #00000;"></i></span><span style="align-self: center;font-size: 20px"><strong>OR</strong></span><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-check" style="font-size: 20px; color: #00000;"></i></span><span style="align-self: center;font-size: 20px"><strong>OR</strong></span><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;font-size:15px;color:black;">NA</span></div>',
					  confirmButtonColor: "#0b2b57",
					  customClass: {
						confirmButton : 'swal-save-exist-black'
					  }
					  
					});
					
				return false;
			}*/
		}
		else
		{
			let mulSelected = 0; // 21-06-2025
			let mulCounter = 0 ;  // 21-06-2025
			let hasError = false;
			$('.reject-form').each(function () {
				const subchecklistId = $(this).attr('id').replace('rejectForm-', '');
				const text = $(this).find('textarea').val().trim();
				const approveMulStatus = $('#approveMultipleStatus' + subchecklistId).val();
				//alert(approveMulStatus);
				//var hasEditMultipleFile = parseInt($('#hasEditMultipleFile' + subchecklistId).val(), 10);
				var hasEditMultipleFile = $('#hasEditMultipleFile' + subchecklistId).val();
				//alert(hasEditMultipleFile); //if 1 get then has files if 0 no files
				//alert(approveMulStatus);
				if(approveMulStatus == '0')
				{
	
					const dropzoneInstance = Dropzone.forElement('#dropzone-' + subchecklistId);
					const files = dropzoneInstance ? dropzoneInstance.getAcceptedFiles() : [];
					
					
					//if (text === ''  && !hasEditMultipleFile)
					if (text === '' && files.length === 0 && !hasEditMultipleFile)
					{
						$('#errorMulmsg' + subchecklistId).fadeIn().delay(2000).fadeOut();
						hasError = true;
						return false;
					}
				}
				
				// validation without select cross or tick cannot go next
				if(approveMulStatus != '')
				{
					mulSelected++ ;
				}
				mulCounter++;
			});
			
			if (hasError) {
				return false;
			}
			
			/*if(mulSelected != mulCounter)
			{
				Swal.fire({
					  icon: "warning",
					  title: "Please select any one",
					  html: '<div style="display: flex; justify-content: center; gap: 20px; margin-top: 10px;"><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-xmark" style="font-size: 20px; color: #00000;"></i></span><span style="align-self: center;font-size: 20px"><strong>OR</strong></span><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-check" style="font-size: 20px; color: 00000;"></i></span><span style="align-self: center;font-size: 20px"><strong>OR</strong></span><span style="width: 40px; height: 40px; border-radius: 50%; background: #ffffff;border:1px solid #000000; display: flex; align-items: center; justify-content: center;font-size:15px;color:black;">NA</span></div>',
					  confirmButtonColor: "#0b2b57",
					  customClass: {
						confirmButton : 'swal-save-exist-black'
					  }
					  
					});
					
				return false;
			}*/
			
		}
		
		var current_id = $('#current_checklist_id').val();
		//alert(current_id);
		var category_id = $('#category_id').val();
		var subcategory_id = $('#subcategory_id').val();
		var task_id = $('#task_id').val();
		//alert(task_id);
		var order_no = $('#order_no').val();
		var directEdit = $('#direct_edit').val()
		//var mode = $('#mode').val();
		//alert(mode);
		var rejectTextsSingle = '';
		let rejectTextsMultiple = {};
		if(mode=='single')
		{
			 var rejectTextsSingle = $('#single_rejecttext').val();
		}
		else{
			
			$('.reject-form').each(function () {
				const subchecklistId = $(this).attr('id').replace('rejectForm-', '');
				const text = $(this).find('textarea').val().trim();
				const approveStatus = $('#approveMultipleStatus' + subchecklistId).val();
				// Save only if there's any text entered (optional)
				
				if (text !== '' || approveStatus !== '') {
					rejectTextsMultiple[subchecklistId] = {
						text: text,
						approve_status: approveStatus
					};
				}
				
				/*if (text !== '') {
					rejectTextsMultiple[subchecklistId] = text;
				}*/
			});
			//alert(rejectTextsMultiple);
		}
		
		var URL = "{{ route('save-exist-question') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {
				approveStatus: approveStatus,
				mode: mode,
				task_id: task_id,
				order_no: order_no,
				current_question_id: current_id,
				category_id: category_id,
				subcategory_id: subcategory_id,
				directEdit: directEdit,
				rejectTextsSingle: rejectTextsSingle,
				rejectTextsMultiple: JSON.stringify(rejectTextsMultiple), // <--- Fix here
				_token: csrfToken
			},
			traditional: true,
			dataType: 'json',
			success: function(response) {
				const location_id = $('#location_id').val();
				const task_id = $('#task_id').val();
				const refreshUrl = "{{ url('category/LOCATION_ID/TASK_ID/ACTIVE') }}";
				const redirectUrl = refreshUrl.replace('LOCATION_ID', location_id).replace('TASK_ID', task_id).replace('ACTIVE', 1);
				window.location.href = redirectUrl;
			}
			
		});
	
	/*const location_id = $('#location_id').val();
	const task_id = $('#task_id').val();
	const refreshUrl = "{{ url('category/LOCATION_ID/TASK_ID/ACTIVE') }}";
	const redirectUrl = refreshUrl.replace('LOCATION_ID', location_id).replace('TASK_ID', task_id).replace('ACTIVE', 1);
	window.location.href = redirectUrl;*/
}

// call this after dynamic HTML is loaded
//initializeDropzones();

</script>
@endsection

