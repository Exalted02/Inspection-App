@extends('layouts.app')
@section('content')
@php 
use Carbon\Carbon;
 //echo "<pre>";print_r($categoryData);die;
 //echo $task_id; die;
 $userData = App\Models\Task_lists::with('get_user')->where('id', $task_id)->first();
 
 $rejected_region = '';
 $image_arr = [];
 
 $checklist = App\Models\Checklist::where('id', $checklist_id)->first();
 if($type == 'checklist')
 {
	 $taskChecklist = App\Models\Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
	 
	 $images = App\Models\Task_list_checklist_rejected_files::where('task_list_checklist_id', $taskChecklist->id)->get();
	 
	 foreach($images as $image)
	 {
		 $image_arr[] = [
				'url'=> url('uploads/reject-files/' .$image->file ),
			];
	 }
	 
	 $rejected_region = $taskChecklist->rejected_region;
	 $created_at = $taskChecklist->created_at;
	 
 }
 
 $taskSubChecklist = null;
 if($type == 'subchecklist')
 {
	 
	 $taskSubChecklist = App\Models\Task_list_subchecklists::where('task_list_id', $task_id)->where('task_list_checklist_id', $checklist_id)->where('subchecklist_id', $subchecklist_id)->where('approve', 0)->first();
	 
	 
	$subImages = collect();
	$subChecklistName = '';
	
	
	if ($taskSubChecklist) {
		$subImages = App\Models\Task_list_subchecklist_rejected_files::where('task_list_checklist_id',$taskSubChecklist->task_list_checklist_id)->where('task_list_subchecklist_id', $taskSubChecklist->id)->get();
		
		$subChecklistName = App\Models\Subchecklist::where('id', $taskSubChecklist->subchecklist_id)->first()->name;
		
		$rejected_region = $taskSubChecklist->rejected_region;
		$created_at = $taskSubChecklist->created_at;
		
		foreach($subImages as $image)
		{
		    $image_arr[] = [
				'url' => url('uploads/reject-files/subchecklist/' .$image->file),
			];
		}
	}
 }
 
 $loopCnt = 0;
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist">
		<h2 class="checklist-title">{{ $tab == 'corrective-action' ? 'Corrective action' : 'Corrective check' }} for rejected item</h2>
			
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container1">
					<div class="custom-tab" style="margin-bottom: 80px;">
						<div class="row">
							<div class="col-md-12">
								<h2 class="owner-checklist-title">{{ $checklist->name ?? ''}}</h2>
							</div>
						</div>
						@if(!empty($subChecklistName))
						<div class="row">
							<div class="col-md-12">
								<div class="owner-subchecklist-title">{{ $subChecklistName ?? ''}}</div>
							</div>
						</div>
						@endif
					
						
						<div class="row">
							<div class="col-md-12"><label>Reason</label></div>
						</div>
						<div class="row">
						<div class="col-md-12"><p class="text-muted mb-0">{{ $rejected_region ?? '' }}</p></div>
						</div>
						
						
						<div class="row">
							<div class="col-md-12 mt-1">
								@if(!empty($image_arr))
									@foreach($image_arr as $url)
									@php 
										$urls = $url['url'] ?? '';
										$extension = pathinfo($urls, PATHINFO_EXTENSION);
										$extension = strtolower($extension);
										$loopCnt++;
									@endphp
									<div class="cheklist-reply-images">
										@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
										<img src="{{ $url['url'] ?? '' }}">
										@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
										<video controls src="{{ $url['url'] ?? '' }}" width="90" height="90"></video>
										@endif
									</div>
									@endforeach
								@endif
							</div>
							<div class="col-md-6 text-ia-lo-los d-flex justify-content-between flex-wrap">
							<img src="{{ url('uploads/profile/'. $userData->get_user->id . '/inspector/'. $userData->get_user->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
							<span>By (IA) {{ $userData->get_user->name ?? ''}}</span><span>{{ change_date_format($created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}</span></div>
								{{--<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap">
							<img src="{{ url('uploads/profile/'. $corrective_action?->get_inspector?->id . '/inspector/'. $corrective_action?->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
							<span>By (IA) {{ $userData->get_user->name ?? ''}}</span><span>·</span><span>{{ change_date_format($created_at, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>--}}
						</div>
						<hr class="horizontal-line">

						<div class="row mt-2">
							<div class="col-md-12">
								<label>How to solve the issue ?</label>
								<textarea name="lo_corrective_action_plan" id="lo_corrective_action_plan" placeholder="Input corrective action plan" class="form-control"></textarea>
								<span id="action_plan" style="display: none; color: red;">This field is require.</span>
							</div>
						</div>
						<div class="row align-items-center">
							<div class="col-md-4">
								<label for="lo_file"></label>
								<div class="upload-wrapper">
								  <input type="file" name="lo_file[]" id="lo_file" multiple style="display: none;">
								  <label for="lo_file" class="custom-upload-label">
									<span class="upload-text">Upload image</span>
									<i class="fa fa-upload upload-icon"></i>
								  </label>
								</div>
							</div>
							<div class="col-md-8 d-flex flex-wrap gap-2" id="preview-container">
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<label class="d-block col-form-label"></label>
								<div class="status-toggle">
									<input type="checkbox" name="lo_direct_approve" id="lo_direct_approve" class="check">
									<label for="lo_direct_approve" class="checktoggle">Corrective Done</label>
								</div>
							</div>
						</div>
						
						<div class="row set_time_div" style="margin-top:17px;">
							<div class="col-md-12">
								<label>{{ __('Set Timeline') }}</label>
								<div class="split-placeholder-wrapper">
								<input class="form-control set-timeline-input" placeholder="" type="text" name="set_time" id="set_time">
								<span class="custom-left-placeholder" id="selected_time">Set Time</span>
								<span class="custom-right-placeholder" id="selected_date">Set Date</span>
							</div>
							<span id="settimeline_id_error" style="display:none;  color: red;"></span>
							<input type="hidden" id="hidden_set_date" name="hidden_set_date">
							<input type="hidden" id="hidden_set_time" name="hidden_set_time">
							</div>
						</div>
						
					
					<input type="hidden" id="task_id" value="{{ $task_id ?? ''}}">
					<input type="hidden" id="checklist_id" value="{{ $checklist_id  ?? ''}}">
					<input type="hidden" id="subchecklist_id" value="{{ $subchecklist_id ?? ''}}">
					<input type="hidden" id="type" value="{{ $type ?? ''}}">
					<input type="hidden" id="tab" value="{{ $tab ?? ''}}">
					<input type="hidden" id="location_id" value="{{ $location_id ?? ''}}">
						
					</div>
					<div class="">
						<button class="sticky-footer submitChecklist">Submit checklist</button>
					</div>
				</div>
			</section>
		</div>
    </div>
	
	
	<!-- =-=-=-=-=-=-= Rejected reason =-=-=-=-=-=-= -->
	<div class="modal fade" id="forward-task" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<h2 class="owner-checklist-title">Forward to...</h3>
							<div class="search-widget">
							   <input placeholder="Forward to" type="text" oninput="input_search(this.value)" id="search-text">
							   <button type="submit"><i class="fa fa-search"></i></button>
							</div>
						</div>
						<span id="errorMessage1" style="display: block; text-align: center;"></span>
						<div class="col-md-12">
							<div class="user-list">
							@if($all_lo->isNotEmpty())
								@foreach($all_lo as $lo)
								<div class="user-item">
								  <div class="user-info">
									<img src="{{ url('uploads/profile/'. $lo->id . '/locationowner/'. $lo->profile_image) }}" alt="{{ $lo->name ?? ''}}">
									<span>{{ $lo->name ?? ''}}</span>
								  </div>
								  <input type="radio" id="lo_id" name="user" value="{{ $lo->id}}">
								</div>
								@endforeach
							@endif
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="modal-checklist-question-sticky-footer">
						<div class="footer-content question-navigation d-flex justify-content-between">
							<button class="reject-class-button">Cancel</button>
							<button class="ms-auto change-location">Forward</button>
						</div>
					</div>				
				</div>
            </div>
		</div>
	</div>
	
@endsection 
@section('scripts')
<script src="{{ url('front-assets/css/bootstrap.min.css') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  // Detect if device is mobile
  function isMobileDevice() {
    return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
  }
 
  window.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('lo_file');
 
    if (isMobileDevice()) {
      // Add capture attribute (forces camera)
      fileInput.setAttribute('capture', 'camera');
    } else {
      // Remove capture attribute (normal browse)
      fileInput.removeAttribute('capture');
    }
  });
</script>
<script>
$(document).on('click','.forward-task', function(){
	$('#forward-task').modal('show');
});
</script>
<script>
$(document).ready(function() {
	flatpickr("#set_time", {
    enableTime: false,
    dateFormat: "d M Y H:i",
    onChange: function(selectedDates, dateStr, instance) {
		
			if (selectedDates.length == 1) {
				const date = selectedDates[0];
				const dateOnly = flatpickr.formatDate(date, "d M Y");
				const timeOnly = flatpickr.formatDate(date, "H:i");
				//alert(timeOnly);
				
				document.getElementById('selected_time').innerText = 'Set Time';
				document.getElementById('selected_date').innerText = dateOnly;
				$('#hidden_set_date').val(dateOnly);
				$('#hidden_set_time').val(timeOnly);
				// Delay clearing input to prevent recursion
				// Responsive fix for mobile view
				if (window.innerWidth <= 576) {
					//$('#selected_time').hide();
					$('.set-timeline-input').val('');
					setTimeout(() => {
							instance.input.value = '';
							instance.input.blur();
						}, 0);
					
					
				} else {
					// Reset for desktop
					$('#selected_time').css({
						'display': '',
						'position': '',
						'text-align': '',
						'margin-bottom': ''
					});
					$('#selected_date').css({
						'display': '',
						'position': '',
						'text-align': ''
					});
					
					setTimeout(() => {
						instance.input.value = '';
						instance.input.blur();
					}, 0);
				}
			} else {
				document.getElementById('selected_date').innerText = "Setdate";
			}
		}
	});
   
   let previewContainer = $('#preview-container');
   let selectedFiles = [];

   $(document).on('click','.submitChecklist', function(){
	   //e.preventDefault();
	   var task_id = $('#task_id').val();
	   //alert(task_id);
	   var checklist_id = $('#checklist_id').val();
	   var subchecklist_id = $('#subchecklist_id').val();
	   var type = $('#type').val();
	   var tab = $('#tab').val();
	   let lo_corrective_action_plan = $('#lo_corrective_action_plan').val().trim();
	   //let lo_completed_by = $('#lo_completed_by').val();
	   let hidden_set_date = $('#hidden_set_date').val();
	   let hidden_set_time = $('#hidden_set_time').val();
	   let lo_direct_approve = $('#lo_direct_approve').is(':checked');
	   //alert(hidden_set_time);
	   
	   if(lo_corrective_action_plan=='')
	   {
		   $('#action_plan').fadeIn().delay(2000).fadeOut();
		   return false;
	   }
	   
	   if(lo_direct_approve == false)
	   {
	    if(hidden_set_date === '') {
			$('#settimeline_id_error').text('Please enter date').fadeIn().delay(2000).fadeOut();
			return false;
		}
	   }
		
		let files = $('#lo_file')[0].files;
		
		let form = document.getElementById('myForm');
		let formData = new FormData();
		
		selectedFiles.forEach(file => {
			formData.append('lo_file[]', file);
		});
		
		formData.append('type', type);
		formData.append('task_id', task_id);
		formData.append('checklist_id', checklist_id);
		formData.append('subchecklist_id', subchecklist_id);
		formData.append('tab', tab);
		formData.append('lo_corrective_action_plan', lo_corrective_action_plan);
		formData.append('lo_direct_approve', lo_direct_approve);
		formData.append('hidden_set_date', hidden_set_date);
		formData.append('hidden_set_time', hidden_set_time);
		formData.append('_token', csrfToken);
		
		//data: {type:type,task_id:task_id,checklist_id:checklist_id,subchecklist_id:subchecklist_id,tab:tab,lo_corrective_action_plan:lo_corrective_action_plan,lo_direct_approve:lo_direct_approve,hidden_set_date:hidden_set_date,hidden_set_time:hidden_set_time, _token: csrfToken},
		
		if ($(this).prop('disabled')) {
			return;
		}
		
		$('.submitChecklist').prop('disabled', true);
		$('.submitChecklist').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Submitting...').prop('disabled', true);

		var URL = "{{ route('submit-lo-corrective-action') }}";
		   $.ajax({
				url: URL,
				type: "POST",
				data: formData,
				dataType: 'json',
				contentType: false,
				processData: false, 
				success: function(response) {
					if(lo_direct_approve)
					{
						localStorage.setItem('loActionSubmited', 1);
					}
					else{
						localStorage.setItem('loPlanSubmited', 1);
					}
					
					let location_id = response.location_id;
					let task_id = response.task_id;
					var active = 1;
					//var baseUrl = "{{ url('/location-owner') }}";
					var baseUrl = "{{ url('/lo-task-status') }}";
					//var redirectUrl = baseUrl + '/'+ location_id + '/' + task_id + '/' + active;
					var redirectUrl = baseUrl + '/'+ location_id + '/' + active;
					window.location.href = redirectUrl;
					
				},
				complete: function() {
					$('.submitChecklist').html('Submit checklist').prop('disabled', false);
					//$('.submit-loding').prop('disabled', false);
				}
			});
	});
	
	$(document).on('click', '#lo_direct_approve', function(){
		var lo_direct_approve  = $('#lo_direct_approve').is(':checked');
		if(lo_direct_approve == true)
		{
			$('.set_time_div').hide();
		}
		else{
			$('.set_time_div').show();
		}
	});
	



/*$('#lo_file').on('change', function (e) {
	
	let files = Array.from(e.target.files); // new
	selectedFiles = files; // new
	//selectedFiles = [...selectedFiles, ...files];
    previewContainer.empty(); // Clear previous previews
	
	Array.from(files).forEach((file, index) => {
	  if (file) {
		let reader = new FileReader();
		reader.onload = function (e) {
		  let previewHtml = '';

		  if (file.type.startsWith('image/')) {
			previewHtml = '<div class="preview-image-wrapper" data-index="' + index + '"><img src="' + e.target.result + '" class="preview-image" /><button type="button" class="remove-image" data-index="' + index + '">&times;</button></div>';
		  }else if (file.type.startsWith('video/')) {
			previewHtml = '<div class="preview-image-wrapper" data-index="' + index + '"><video src="' + e.target.result + '" class="preview-image" controls style="max-width: 120px; max-height: 120px;"></video><button type="button" class="remove-image" data-index="' + index + '">&times;</button></div>';
		  }
		  previewContainer.append(previewHtml);
		};

		reader.readAsDataURL(file);
	  }
	});
})

previewContainer.on('click', '.remove-image', function () {
	const indexToRemove = $(this).data('index');
	//alert(indexToRemove);alert(selectedFiles);
    $(this).parent().remove();
	selectedFiles[indexToRemove] = null;
	selectedFiles = selectedFiles.filter(file => file !== null);
  });
	
});*/

//let selectedFiles = []; // store all selected files globally
//--------- 13-08-2025----------------------------

	$('#lo_file').on('change', function (e) {
		let files = Array.from(e.target.files);

		selectedFiles = [...selectedFiles, ...files];

		files.forEach((file, index) => {
			let reader = new FileReader();
			reader.onload = function (e) {
				let previewHtml = '';

				if (file.type.startsWith('image/')) {
					previewHtml = '<div class="preview-image-wrapper" data-index="' 
						+ (selectedFiles.length - files.length + index) 
						+ '"><img src="' + e.target.result 
						+ '" class="preview-image" /><button type="button" class="remove-image" data-index="' 
						+ (selectedFiles.length - files.length + index) 
						+ '">&times;</button></div>';
				} else if (file.type.startsWith('video/')) {
					previewHtml = '<div class="preview-image-wrapper" data-index="' 
						+ (selectedFiles.length - files.length + index) 
						+ '"><video src="' + e.target.result 
						+ '" class="preview-image" controls style="max-width: 120px; max-height: 120px;"></video><button type="button" class="remove-image" data-index="' 
						+ (selectedFiles.length - files.length + index) 
						+ '">&times;</button></div>';
				}
				previewContainer.append(previewHtml);
			};
			reader.readAsDataURL(file);
		});

		$(this).val('');
	});


	// Remove file from preview & array
	previewContainer.on('click', '.remove-image', function () {
		const indexToRemove = $(this).data('index');
		$(this).parent().remove();
		selectedFiles[indexToRemove] = null;
		selectedFiles = selectedFiles.filter(file => file !== null);
	});
	
	
	$(document).on('click', '.change-location', function(){
		//var user_id = $(this).val();
		var selectedLoId = $('input[name="user"]:checked').val();
		if(selectedLoId) {
			var user_id = selectedLoId;
		}
		else{
			$('#errorMessage1').html('Select any one location owner').fadeIn().delay(3000).fadeOut();
			return false;
		}
		//alert(user_id);
		var task_id = $('#task_id').val();
		//alert(task_id);
		var checklist_id = $('#checklist_id').val();
		var subchecklist_id = $('#subchecklist_id').val();
		var type = $('#type').val();
		var tab = $('#tab').val();
		var location_id = $('#location_id').val();
		var URL = "{{ route('lo-transfer-location') }}";
		
		$.ajax({
			url: URL,
			type: "POST",
			data: {task_id:task_id,checklist_id:checklist_id,subchecklist_id:subchecklist_id,type:type,tab:tab,location_id:location_id,user_id:user_id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.msg);
				if(response.msg == 'success')
				{
					$('#forward-task').modal('hide');
					localStorage.setItem('transferLocation', 1);
					$('.corrective-message-forward').html('<i class="fa fa-check"></i>Check Forwarded').fadeIn().delay(3000).fadeOut();
					
					setTimeout(function() {
						window.location.href = "{{ route('inspector-dashboard') }}";
					}, 4000);
					
				}
				
			},
			complete: function() {
				$('.load-more-appr').html('Load more');
			}
		});
	});
	
	$(document).on('click','.reject-class-button', function(){
		$('#forward-task').modal('hide');
	});
});
function input_search(val)
{
	 var URL = "{{ route('lo-search-users-data') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {val:val, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.html);
				$(".user-list").html(response.html);
			},
			complete: function() {
				
			}
		});
}
</script>
@endsection

