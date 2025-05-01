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
		@php 
			$checkImageFile = App\Models\Task_list_checklists::where('task_list_id',$task_id)
						->where('task_list_subcategory_id',$checklistdata->subcategory_id)
						->where('checklist_id',$checklistdata->id)->first();
			$task_list_checklist_id = $checkImageFile ? $checkImageFile->id : null;
			$rejected_region = $checkImageFile ? $checkImageFile->rejected_region : '';
			$approve = $checkImageFile ? $checkImageFile->approve : '';
			$existingFiles = [];
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
			<div class="question-header">{{ $checklistdata->get_subcategory->name ?? '' }}</div>
			<div class="question-text">
				<span id="single-question">{{ $checklistdata->name ?? '' }}</span>

			</div>
			<span id="errormsg" style="display: none; color: red;">
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
						<input type="hidden" id="approveMultipleStatus{{$subchecklists->id}}" value="">
						
						
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
	{{--<div class="progress-bar">
			<span style="width: 40%;"></span>
	</div>--}}
		<div class="progress-block-bar mb-4">
			<div class="step-block completed"></div>
			<div class="step-block completed"></div>
			<div class="step-block completed"></div>
			<div class="step-block completed"></div>
			<div class="step-block"></div>    
			<div class="step-block"></div> 
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
let existingFiles = @json($existingFiles);
//---------- show image when page load ----------
Dropzone.autoDiscover = false; // very important

document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
    let myDropzone = new Dropzone(dropzoneElement, {
        url: "{{ route('reject-files') }}", // still needed for new uploads
        maxFiles: 5,
        maxFilesize: 2, // MB
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
        dictRemoveFile: 'Delete file',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        init: function () {
            let dz = this;

            // Add existing files (preloaded from server)
            existingFiles.forEach(function (file) {
                let mockFile = { name: file.name, size: file.size, accepted: true };

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
	document.getElementById("question-reject-"+id).classList.add("active");
	$('#approveStatus').val(0);
	$('#approveMultipleStatus' + id).val(0);
}
function handleApprove(id) {
	document.getElementById('rejectForm-'+id).style.display = 'none';
	
	document.getElementById("question-reject-"+id).classList.remove("active");
	document.getElementById("question-approve-"+id).classList.add("active");
	$('#approveStatus').val(1);
	$('#approveMultipleStatus' + id).val(1);
}

//Dropzone.autoDiscover = false; // very important

/*document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
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
});*/


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
        dictDefaultMessage: 'Drag & drop or click to upload',
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


</script>
<script>
$(document ).ready(function() {
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
	
	
	// ========= NEXT BUTTON ==============
	
    $(document).on('click','.next_question', function(){
		
		var mode = $('#mode').val();
		//alert(mode);
		var approveStatus = $('#approveStatus').val();
		
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
		}
		
		var current_id = $('#current_checklist_id').val();
		//alert(current_id);
		var category_id = $('#category_id').val();
		var subcategory_id = $('#subcategory_id').val();
		var task_id = $('#task_id').val();
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
				let autoClicks = [];
				let html = '<div class="sub-checklist">';
				html += '<div class="question-header">' + response.subcategoryname + '</div>';
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
						html += '</div>'; 
						html += '</div>'; 
						html += '<div class="reject-form mb-3" id="' + rejectId + '">';
						html += '<textarea placeholder="State why you rejected this...">' + rejectedText +'</textarea>';
						html += '<input type="hidden" id="mode" value="multiple">';
						html += '<input type="hidden" id="approveMultipleStatus' + item.id + '">';
						html += '<form action="' + rejectFilesRoute + '" class="dropzone" id="dropzone-' + item.id + '"></form>';
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
							});
						}, 0);
				} else {
						//alert(response.next_approve);
						let html = '<div class="single-checklist">';
						html += '<div class="question-header">' + response.subcategoryname + '</div>';
						html += '<div class="question-text">';
						html += '<span id="single-question">' + response.name + '</span>';
						html += '<input type="hidden" id="current_id" value="' + response.currentid + '">';
						html += '<input type="hidden" id="category_id" value="' + category_id + '">';
						html += '<input type="hidden" id="subcategory_id" value="' + subcategory_id + '">';
						html += '</div>'; 
						html += '<span id="errormsg" style="display: none; color: red;">Please enter text or file.</span>';
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
						
						// dropzone work
						Dropzone.autoDiscover = false;
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 2, // MB
								acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictRemoveFile: 'Delete file',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;

									// Add existing files (preloaded from server)
									response.existingNextFiles.forEach(function (file) {
										let mockFile = { name: file.name, size: file.size, accepted: true };

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
								dictDefaultMessage: 'Drag & drop or click to upload',
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
					
					
					/*Dropzone.autoDiscover = false;
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
					});*/
			},
		});
	});
	
	
	// =============== BACK BUTTON ============
	
	$(document).on('click','.previous_question', function(){
		var current_id = $('#current_checklist_id').val();
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
				category_id:category_id,
				subcategory_id:subcategory_id,
				_token: csrfToken
			},
			dataType: 'json',
			success: function(response) {
				//alert(response.currentid);
				 $('#current_checklist_id').val(response.currentid);
				 //$('#single-question').html(response.name);
				 const rejectFilesRoute = "{{ route('reject-files') }}";
				if (response.subchecklist.length > 0) {
				// Has subchecklists
				let autoClicks = [];
				let html = '<div class="sub-checklist">';
				html += '<div class="question-header">' + response.subcategoryname + '</div>';
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
						html += '</div>'; 
						html += '</div>'; 
						html += '<div class="reject-form mb-3" id="' + rejectId + '">';
						html += '<textarea placeholder="State why you rejected this...">' + rejectedText + '</textarea>';
						html += '<input type="hidden" id="mode" value="multiple">';
						html += '<input type="hidden" id="approveMultipleStatus' + item.id + '">';
						html += '<form action="/your-upload-route" class="dropzone" id="dropzone-' + item.id + '"></form>';
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
							});
						}, 0);
						
				} else {
						
						let html = '<div class="single-checklist">';
						html += '<div class="question-header">' + response.subcategoryname + '</div>';
						html += '<div class="question-text">';
						html += '<span id="single-question">' + response.name + '</span>';
						html += '<input type="hidden" id="current_id" value="' + response.currentid + '">';
						html += '<input type="hidden" id="category_id" value="' + category_id + '">';
						html += '<input type="hidden" id="subcategory_id" value="' + subcategory_id + '">';
						html += '</div>'; 
						html += '<span id="errormsg" style="display: none; color: red;">Please enter text or file.</span>';
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
						
						// dropzone work
						Dropzone.autoDiscover = false;
						
						//---------- show image when page load ----------
						document.querySelectorAll('.dropzone').forEach(function(dropzoneElement) {
							let myDropzone = new Dropzone(dropzoneElement, {
								url: "{{ route('reject-files') }}",
								maxFiles: 5,
								maxFilesize: 2, // MB
								acceptedFiles: 'image/*',
								addRemoveLinks: true,
								dictRemoveFile: 'Delete file',
								headers: {
									'X-CSRF-TOKEN': csrfToken
								},
								init: function () {
									let dz = this;

									// Add existing files (preloaded from server)
									response.existingPreviousFiles.forEach(function (file) {
										let mockFile = { name: file.name, size: file.size, accepted: true };

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
								dictDefaultMessage: 'Drag & drop or click to upload',
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
					
					/*Dropzone.autoDiscover = false;
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
					});*/
			},
		});
	});
});
</script>
@endsection

