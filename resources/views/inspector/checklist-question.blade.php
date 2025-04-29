@extends('layouts.app')
@section('component-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
@endsection
@section('content')
@php
//echo "<pre>";print_r($checklistdata);die;
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist-question">
	@if(isset($checklistdata) && $checklistdata->get_subchecklist->isEmpty())
		<div class="single-checklist d-none1">
			<div class="question-header">{{ $checklistdata->get_subcategory->name ?? '' }}</div>
			<div class="question-text">
				<span id="single-question">{{ $checklistdata->name ?? '' }}</span>

			</div>
			<div class="reject-form mb-3" id="rejectForm-1">
				<textarea id="single_rejecttext" placeholder="State why you rejected this..."></textarea>
				<input type="hidden" id="mode" value="single">
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
			</div>
		</div>
	@else
		<div class="sub-checklist">
			<div class="question-header">{{ $checklistdata->get_subcategory->name ?? '' }}</div>
			<div class="question-text">
				<span id="multiple-question">{{ $checklistdata->name ?? '' }}:</span>
			</div>
			@if($checklistdata && $checklistdata->get_subchecklist && $checklistdata->get_subchecklist->isNotEmpty())
				@foreach($checklistdata->get_subchecklist as $subchecklists)
				<div class="sub-checklist-question">
					<div class="action-buttons">
						<span class="d-flex align-items-center">{{ $subchecklists->name ?? ''}}</span>
						<div class="btn-div">
							<button class="rejected" id="question-reject-{{ $subchecklists->id }}" onclick="handleReject({{ $subchecklists->id }})"><i class="fa-solid fa-xmark"></i></button>
							<button class="approved" id="question-approve-{{ $subchecklists->id }}" onclick="handleApprove({{ $subchecklists->id }})"><i class="fa-solid fa-check"></i></button>
						</div>
					</div>
					<div class="reject-form mb-3" id="rejectForm-{{ $subchecklists->id }}">
						<textarea placeholder="State why you rejected this..."></textarea>
						<input type="hidden" id="mode" value="multiple">
						
						<form action="{{ route('reject-files')}}" class="dropzone" id="dropzon-{{ $subchecklists->id }}">
							<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="{{ $checklistdata->id ?? '' }}">
							<input type="hidden" id="category_id" value="{{ $checklistdata->category_id ?? '' }}">
							<input type="hidden" name="subcategory_id" id="subcategory_id" value="{{ $checklistdata->subcategory_id ?? '' }}">
							<input type="hidden" name="task_id" id="task_id" value="{{ $task_id ?? '' }}">
						</form>
					</div>
				</div>
				@endforeach
			@endif
			{{--<div class="sub-checklist-question">
				<div class="action-buttons">
					<span class="d-flex align-items-center">Face shield</span>
					<div class="btn-div">
						<button class="rejected" id="question-reject-3" onclick="handleReject(3)"><i class="fa-solid fa-xmark"></i></button>
						<button class="approved" id="question-approve-3" onclick="handleApprove(3)"><i class="fa-solid fa-check"></i></button>
					</div>
				</div>
				<div class="reject-form mb-3" id="rejectForm-3">
					<textarea placeholder="State why you rejected this..."></textarea>
					<form action="/your-upload-route" class="dropzone" id="dropzone-3"></form>
				</div>
			</div>--}}
		</div>
	@endif
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->		
    </div>
	<input type="hidden" id="current_checklist_id" value="{{ $checklistdata->id ?? '' }}">
	<input type="hidden" id="category_id" value="{{ $checklistdata->category_id ?? '' }}">
	<input type="hidden" id="subcategory_id" value="{{ $checklistdata->subcategory_id ?? '' }}">
	<input type="hidden" id="task_id" value="{{ $task_id ?? '' }}">
	<div class="checklist-question-sticky-footer">
		<div class="progress-bar">
			<span style="width: 40%;"></span>
		</div>
		<div class="clearfix"></div>
		<div class="footer-content question-navigation d-flex justify-content-between">
			<button class="previous_question">Back</button>
			<button class="next_question ms-auto">Next</button>
		</div>
	</div>
@endsection 
@section('scripts')
<script>
function handleReject(id) {
	document.getElementById('rejectForm-'+id).style.display = 'flex';
	
	document.getElementById("question-approve-"+id).classList.remove("active");
	document.getElementById("question-reject-"+id).classList.add("active");
}
function handleApprove(id) {
	document.getElementById('rejectForm-'+id).style.display = 'none';
	
	document.getElementById("question-reject-"+id).classList.remove("active");
	document.getElementById("question-approve-"+id).classList.add("active");
}

Dropzone.autoDiscover = false; // very important

// This will automatically find and initialize all dropzones
document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
    new Dropzone(dropzoneElement, {
        url: "{{ route('reject-files')}}", // your upload URL
        maxFiles: 5,
        maxFilesize: 2, // MB
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
		headers: {
			'X-CSRF-TOKEN': csrfToken
		},
        dictDefaultMessage: 'Drag & drop or click to upload',
        success: function (file, response) {
            console.log('File uploaded', response);
        },
        error: function (file, errorMessage) {
            console.error('Upload error', errorMessage);
        }
    });
});

</script>
<script>
$(document ).ready(function() {
    $(document).on('click','.next_question', function(){
		var current_id = $('#current_checklist_id').val();
		//alert(current_id);
		var category_id = $('#category_id').val();
		var subcategory_id = $('#subcategory_id').val();
		var task_id = $('#task_id').val();
		var mode = $('#mode').val();
		//alert(mode);
		var rejectTextsSingle = '';
		let rejectTextsMultiple = {};
		if(mode=='single')
		{
			 var rejectTextsSingle = $('#single_rejecttext').val();
			  alert(rejectTextsSingle);
		}
		else{
			
			$('.reject-form').each(function () {
				const subchecklistId = $(this).attr('id').replace('rejectForm-', '');
				const text = $(this).find('textarea').val().trim();
				// Save only if there's any text entered (optional)
				if (text !== '') {
					rejectTextsMultiple[subchecklistId] = text;
				}
			});
			//alert(rejectTextsMultiple);
		}
		var URL = "{{ route('checklist-next-question') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {
				mode: mode,
				task_id: task_id,
				current_question_id: current_id,
				category_id: category_id,
				subcategory_id: subcategory_id,
				rejectTextsSingle: rejectTextsSingle,
				rejectTextsMultiple: JSON.stringify(rejectTextsMultiple), // <--- Fix here
				_token: csrfToken
			},
			traditional: true,
			dataType: 'json',
			success: function(response) {
				//alert(response.currentid);
				$('#current_checklist_id').val(response.currentid);
				 //$('#single-question').html(response.name);
				const rejectFilesRoute = "{{ route('reject-files') }}";
				if (response.subchecklist.length > 0) {
				// Has subchecklists
				let html = '<div class="sub-checklist">';
				html += '<div class="question-header">' + response.subcategoryname + '</div>';
				html += '<div class="question-text">';
				html += '<span id="multiple-question">' + response.name + '</span>';
				html += '</div>';

				response.subchecklist.forEach((item, index) => {
					    //alert(item.id);
						let rejectId = 'rejectForm-' + item.id;
						html += '<div class="sub-checklist-question">';
						html += '<div class="action-buttons">';
						html += '<span class="d-flex align-items-center">' + item.name + '</span>';
						html += '<div class="btn-div">';
						html += '<button class="rejected" id="question-reject-' + item.id + '" onclick="handleReject(' + item.id + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + item.id + '" onclick="handleApprove(' + item.id + ')"><i class="fa-solid fa-check"></i></button>';
						html += '</div>'; 
						html += '</div>'; 
						html += '<div class="reject-form mb-3" id="' + rejectId + '">';
						html += '<textarea placeholder="State why you rejected this..."></textarea>';
						html += '<input type="hidden" id="mode" value="multiple">';
						html += '<form action="' + rejectFilesRoute + '" class="dropzone" id="dropzone-' + item.id + '"></form>';
						html += '</div>'; 
						html += '</div>'; 
					});

						html += '</div>'; 

						$('.checklist-question').html(html); 
				} else {
						//alert('nooo');
						let html = '<div class="single-checklist">';
						html += '<div class="question-header">' + response.subcategoryname + '</div>';
						html += '<div class="question-text">';
						html += '<span id="single-question">' + response.name + '</span>';
						html += '<input type="hidden" id="current_id" value="' + response.currentid + '">';
						html += '<input type="hidden" id="category_id" value="' + category_id + '">';
						html += '<input type="hidden" id="subcategory_id" value="' + subcategory_id + '">';
						html += '</div>'; 
						html += '<div class="reject-form mb-3" id="rejectForm-' + response.currentid + '">';
						html += '<textarea id="single_rejecttext" placeholder="State why you rejected this..."></textarea>';
						html += '<input type="hidden" id="mode" value="single">';
						html += '<form action="' + rejectFilesRoute + '" class="dropzone" id="dropzone-1">';
						html += '<input type="hidden" name="current_checklist_id" id="single_checklist_id" value="' + response.currentid +'">';
						html += '<input type="hidden" name="subcategory_id" id="single-subcategory_id" value="' + subcategory_id + '">';
						html += '<input type="hidden" name="task_id" id="single-task_id" value="' + task_id +'">';
						html += '</form>';
						html += '</div>'; 
						html += '<div class="action-buttons-without-text">';
						html += '<button class="rejected" id="question-reject-' + response.currentid + '" onclick="handleReject(' + response.currentid + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + response.currentid + '" onclick="handleApprove(' + response.currentid + ')"><i class="fa-solid fa-check"></i></button>';
						html += '</div>'; 
						html += '</div>'; 

						$('.checklist-question').html(html); 
					}
					
					
					Dropzone.autoDiscover = false;
					document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
						// Avoid double-initializing if Dropzone was already applied
						if (!dropzoneElement.classList.contains("dz-clickable")) {
							new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 2,
								acceptedFiles: 'image/*',
								addRemoveLinks: true,
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								dictDefaultMessage: 'Drag & drop or click to upload',
								success: function (file, response) {
									console.log('File uploaded', response);
								},
								error: function (file, errorMessage) {
									console.error('Upload error', errorMessage);
								}
							});
						}
					});
			},
		});
	});
	
	$(document).on('click','.previous_question', function(){
		var current_id = $('#current_checklist_id').val();
		//alert(current_id);
		var category_id = $('#category_id').val();
		var subcategory_id = $('#subcategory_id').val();
		var URL = "{{ route('checklist-previous-question') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {current_question_id:current_id,category_id:category_id,subcategory_id:subcategory_id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.currentid);
				 $('#current_checklist_id').val(response.currentid);
				 //$('#single-question').html(response.name);
				 
				if (response.subchecklist.length > 0) {
				// Has subchecklists
				let html = '<div class="sub-checklist">';
				html += '<div class="question-header">' + response.subcategoryname + '</div>';
				html += '<div class="question-text">';
				html += '<span id="multiple-question">' + response.name + '</span>';
				html += '</div>';

				response.subchecklist.forEach((item, index) => {
						let rejectId = 'rejectForm-' + item.id;
						html += '<div class="sub-checklist-question">';
						html += '<div class="action-buttons">';
						html += '<span class="d-flex align-items-center">' + item.name + '</span>';
						html += '<div class="btn-div">';
						html += '<button class="rejected" id="question-reject-' + item.id + '" onclick="handleReject(' + item.id + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + item.id + '" onclick="handleApprove(' + item.id + ')"><i class="fa-solid fa-check"></i></button>';
						html += '</div>'; 
						html += '</div>'; 
						html += '<div class="reject-form mb-3" id="' + rejectId + '">';
						html += '<textarea placeholder="State why you rejected this..."></textarea>';
						html += '<form action="/your-upload-route" class="dropzone" id="dropzone-' + item.id + '"></form>';
						html += '</div>'; 
						html += '</div>'; 
					});

						html += '</div>'; 

						$('.checklist-question').html(html); 
				} else {
						
						let html = '<div class="single-checklist">';
						html += '<div class="question-header">' + response.subcategoryname + '</div>';
						html += '<div class="question-text">';
						html += '<span id="single-question">' + response.name + '</span>';
						html += '<input type="hidden" id="current_id" value="' + response.currentid + '">';
						html += '<input type="hidden" id="category_id" value="' + category_id + '">';
						html += '<input type="hidden" id="subcategory_id" value="' + subcategory_id + '">';
						html += '</div>'; 
						html += '<div class="reject-form mb-3" id="rejectForm-' + response.currentid + '">';
						html += '<textarea placeholder="State why you rejected this..."></textarea>';
						html += '<form action="/your-upload-route" class="dropzone" id="dropzone-' + response.currentid + '"></form>';
						html += '</div>'; 
						html += '<div class="action-buttons-without-text">';
						html += '<button class="rejected" id="question-reject-' + response.currentid + '" onclick="handleReject(' + response.currentid + ')"><i class="fa-solid fa-xmark"></i></button>';
						html += '<button class="approved" id="question-approve-' + response.currentid + '" onclick="handleApprove(' + response.currentid + ')"><i class="fa-solid fa-check"></i></button>';
						html += '</div>'; 
						html += '</div>'; 

						$('.checklist-question').html(html); 
					}
				Dropzone.autoDiscover = false;
					document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
						// Avoid double-initializing if Dropzone was already applied
						if (!dropzoneElement.classList.contains("dz-clickable")) {
							new Dropzone(dropzoneElement, {
								url: "/your-upload-route",
								maxFiles: 5,
								maxFilesize: 2,
								acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictDefaultMessage: 'Drag & drop or click to upload',
								success: function (file, response) {
									console.log('File uploaded', response);
								},
								error: function (file, errorMessage) {
									console.error('Upload error', errorMessage);
								}
							});
						}
					});
			},
		});
	});
});
</script>
@endsection

