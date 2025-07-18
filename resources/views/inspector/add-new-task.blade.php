@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($locationWisecategory );die;
 
 $rejected_region = '';

$task_title = '';
$selected_date = '';
$task_image = '';
$categotyArr = [];
if(!empty($task_id))
{
	$taskData = App\Models\Task_lists::where('id', $task_id)->first();
	$task_title = $taskData ? $taskData->task_title : '';
	$timeline = $taskData ? $taskData->created_at : '';
	$selected_date = date('d M Y', strtotime($timeline));
	$task_image = $taskData ? $taskData->image : '';
	
	$getAllCatgory = App\Models\Task_location_categories::where('task_list_id', $task_id)->get();
	
	if($getAllCatgory->isNotEmpty())
	{
		foreach($getAllCatgory as $catVal)
		{
			$categotyArr[] = $catVal->category_id;
		}
		
	}
}
 
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist">
		<h2 class="checklist-title"></h2>
			
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container1">
					<div class="custom-tab" style="margin-bottom: 80px;">
						<div class="row">
							@if(empty($task_id))
							<h2 class="owner-checklist-title">Add Task</h2>
							@else
							<h2 class="owner-checklist-title">Edit Task</h2>
							@endif
						</div>
						
						  <form id="frmcategory" action="{{ route('save-task-data') }}" enctype="multipart/form-data">
							<input type="hidden" id="location_id" name="location_id" value="{{ $location_id ?? ''}}">
							<input type="hidden" id="hid_task_id" name="id" value="{{ $task_id ?? ''}}">
							<input type="hidden" id="hid_task_image" name="hid_task_image" value="{{ $task_image ?? ''}}">
							@csrf	
								<div class="row form-group">
									<div class="col-md-12">
										<label>{{ __('Task Title') }}</label>
										<input class="form-control" placeholder="Add task title" type="text" name="task_title" id="task_title" value="{{ $task_title ?? ''}}">
										<span id="tasktitle_id_error" style="display:none;  color: red;"></span>
									</div>
								</div>
								
								<div class="row form-group">
									<div class="col-md-12">
										<label>{{ __('Timeline') }}</label>
										<div class="split-placeholder-wrapper">
											<input class="form-control set-timeline-input" placeholder="" type="text" name="set_time" id="set_time" readonly>
											<span class="custom-left-placeholder" id="selected_time">Set Time</span>
											<span class="custom-right-placeholder" id="selected_date">{{ $selected_date ? $selected_date : 'Set Date'}}</span>
										</div>
										<span id="settimeline_id_error" style="display:none;  color: red;"></span>
										<input type="hidden" id="hidden_set_date" name="hidden_set_date" value="{{ $selected_date ?? ''}}">
										<input type="hidden" id="hidden_set_time" name="hidden_set_time">
									</div>
								</div>
								<div class="task-cover-image">Upload Cover</div>
								<div class="row d-flex align-items-center update-image">
									<div class="col-md-4 mb-3">
										<label for="task_image"></label>
										<div class="upload-wrapper">
											<input type="file" name="task_image" id="task_image" style="display: none;">
											<label for="task_image" class="task-upload-label">
											<i class="fa fa-upload task-upload-icon"></i>
											<span class="task-upload-text">Update image</span>
											</label>
											<span id="taskimage_id_error" style="display:none;  color: red; margin-left:17px;">please </span>
										</div>
									</div>
								</div>
								<div class="form-group  col-md-12  col-sm-12 taskImg" style="display:block;margin-left: -10px;">
									<div class="task-preview-wrapper position-relative d-inline-block">
										<img id="" class="img-responsive task-img-upload" src="{{ $task_image ? url('uploads/task/' . $task_image) : url('images/noimages/default-task-pic.png') }}" alt=""/>
										<button type="button" class="task-img-delete" id="delete-image">×</button>
									</div>
								</div>
								@if(!empty($locationWisecategory))
								<div class="row form-group">
									<div class="col-md-12">
										<label><strong>Select Category</strong></label>
										<div class="subcategory-box mt-2">
											@foreach($locationWisecategory as $category)
											@php 
												$chk = '';
												if(in_array($category['id'], $categotyArr))
												{
													$chk=1;
												}
											@endphp
												<div class="subcategory-item">
													<div class="subcategory-checkbox">
														<input type="checkbox" name="location_category[]" value="{{ $category['id'] }}" {{ $chk==1 ? 'checked' : '' }}>
													</div>
													<div class="subcategory-name"><strong>{{ $category['name'] }}</strong></div>
													
												</div>
											@endforeach
											<span id="tasktcategory_id_error" style="display:none;  color: red;">Please select category</span>
										</div>
									</div>
								</div>
								@else
									<span class="category-message">Category not present for this location, please add from admin</span>
								@endif
							
							<div class="sticky-footer save-task">
							@if(empty($task_id))
								<button class="task-load-add" type="button">Add Task</button>
							@else
								<button class="task-load-edit" type="button">Edit Task</button>
							@endif
							</div>
						</form>
					</div>
				</div>
			</section>
		</div>
    </div>
@endsection 
@section('scripts')
<script src="{{ url('front-assets/css/bootstrap.min.css') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
	flatpickr("#set_time", {
    enableTime: false,
    dateFormat: "d M Y H:i",
	//allowInput: true,
    onChange: function(selectedDates, dateStr, instance) {
			if (selectedDates.length == 1) {
				const date = selectedDates[0];
				//alert(date);
				const dateOnly = flatpickr.formatDate(date, "d M Y");
				const timeOnly = flatpickr.formatDate(date, "H:i");
                //alert(date);alert(dateOnly);alert(timeOnly);
				document.getElementById('selected_time').innerText = 'Set Time';
				document.getElementById('selected_date').innerText = dateOnly;
				$('#hidden_set_date').val(dateOnly);
				$('#hidden_set_time').val(timeOnly);
				
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
		},
		onValueUpdate: function(selectedDates, dateStr, instance) {
			// Always clear the input after selection
			//if(window.innerWidth <= 576) {
					instance.input.value = '';
				//}
		}
	});
	
	$("#set_time").on("change", function(e) {
		 //alert('ok');
		 instance.input.value = '';
	  //e.preventDefault();
	});
	
	if($('#hid_task_image').val() != '')
	{
		$('#delete-image').show();
	}
	else{
		$('#delete-image').hide();
	}
	
	$("#task_image").change(function() {
		$('#delete-image').show();
		$('.taskImg').show();
        readURL(this);
    });
   
   $(document).on('click','.save-task', function(){
		//let category_id = $('#category_id').val().trim();
		let task_title = $('#task_title').val().trim();
		let hid_task_id = $('#hid_task_id').val();
		//alert(hid_task_id);
		let set_time = $('#set_time').val().trim();
		let task_image = $('#task_image')[0].files.length;
		let hidden_set_date = $('#hidden_set_date').val();
		let hidden_set_time = $('#hidden_set_time').val();
		
		if (task_title === '') {
			$('#tasktitle_id_error').text('Please enter task title').fadeIn().delay(2000).fadeOut();
			return false;
		}
		
		//alert(hidden_set_date);
		
		if (hidden_set_date === '') {
			$('#settimeline_id_error').text('Please enter date').fadeIn().delay(2000).fadeOut();
			return false;
		}
		
		
		
		let selectedLocations = [];
		$('input[name="location_category[]"]:checked').each(function() {
			selectedLocations.push($(this).val());
		});
		//alert(selectedLocations);
		
		//alert(task_image);
		/*if (task_image === 0) {
			$('#taskimage_id_error').text('Please select image').fadeIn().delay(2000).fadeOut();
			return false;
		}*/
		
		if (selectedLocations.length === 0) {
			$('#tasktcategory_id_error').text('Please select category').fadeIn().delay(2000).fadeOut();
			
			@if(empty($locationWisecategory))
			{
				Swal.fire({
					  icon: "warning",
					  title: "Category not present for this location, please add from admin",
					  
					  confirmButtonColor: "#0b2b57",
					  customClass: {
						confirmButton : 'swal-save-exist-black'
					  }
					  
					});
			}
			@endif
			return false;
		}
		
		//var form = $("#frmlocation");
		var URL = $('#frmcategory').attr('action');
		var id = $('#id').val();
		
		if(hid_task_id == '')
		{
			$('.task-load-add').prop('disabled', true);
			$('.task-load-add').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Submitting...').prop('disabled', true);
		}
		else{
			$('.task-load-edit').prop('disabled', true);
			$('.task-load-edit').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Submitting...').prop('disabled', true);
		}
		
		
		let formData = new FormData($('#frmcategory')[0]);
		formData.append('_token', csrfToken);
		//alert(URL);
		$.ajax({
			url: URL,
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			//dataType: 'json',
			success: function(response) {
				if (!response.success) {
					
					$('#tasktitle_id_error').text('Task title already exists.').fadeIn().delay(2000).fadeOut(); 
					//$('#task_title').addClass('is-invalid');
					//$('#task_title').next('.invalid-feedback').text(response.message).show();
				} else {
				
					$('#category_id').val('').trigger('change');
					//$('#task_title').val('');
					let hid_task_id = $('#hid_task_id').val();
					if(hid_task_id == '')
					{
						localStorage.setItem('taskcreated', 1);
					}
					else{
						localStorage.setItem('taskupdated', 1);
					}
					
					var baseUrl = "{{ url('/location-details') }}";
					var location_id = $('#location_id').val();
					var redirectUrl = baseUrl + '/'+ location_id;
					window.location.href = redirectUrl;
					/*setTimeout(() => {
						window.location.reload();
					}, "2000");*/
				}
			},
			complete: function() {
				
				let hid_task_id = $('#hid_task_id').val();
				if(hid_task_id == '')
				{
					$('.task-load-add').prop('disabled', false);
				}
				else{
					$('.task-load-edit').prop('disabled', false);
				}
			}
		});
		
	});
	
	$('#delete-image').on('click', function() {
		//$('#img-upload').attr('src', '');
		var defaultImg = "{{ url('images/noimages/default-task-pic.png') }}";
		$('.task-img-upload').attr('src', defaultImg);
		$('#task_image').val('');
		$('#delete-image').hide();
		$('.taskImg').show();
		var hid_task_image = $('#hid_task_image').val();
		var task_id = $('#hid_task_id').val();
		if(hid_task_image != '')
		{
			$.ajax({
				url: "{{ route('delete-task-image') }}",
				type: "POST",
				data: {task_id:task_id,task_image:hid_task_image,_token:csrfToken},
				//processData: false,
				//contentType: false,
				//dataType: 'json',
				success: function(response) {
					
				},
			});
		}
	});
	
	
});
function readURL(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function(e) {
			$('.task-img-upload').attr('src', e.target.result);
		};
		reader.readAsDataURL(input.files[0]);
	}
}
</script>
@endsection

