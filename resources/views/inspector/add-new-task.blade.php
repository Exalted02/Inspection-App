@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($locationWisecategory );die;
 
 $rejected_region = '';
 
 
 
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
							<h2 class="owner-checklist-title">Add Task</h2>
						</div>
						
						  <form id="frmcategory" action="{{ route('save-task-data') }}" enctype="multipart/form-data">
							<input type="hidden" id="location_id" name="location_id" value="{{ $location_id ?? ''}}">
							@csrf	
								<div class="row form-group">
									<div class="col-md-12">
										<label>{{ __('Task Title') }}</label>
										<input class="form-control" placeholder="Add task title" type="text" name="task_title" id="task_title">
										<span id="tasktitle_id_error" style="display:none;  color: red;"></span>
									</div>
								</div>
								
								<div class="row form-group">
									<div class="col-md-12">
										<label>{{ __('Timeline') }}</label>
										<div class="split-placeholder-wrapper">
											<input class="form-control set-timeline-input" placeholder="" type="text" name="set_time" id="set_time" readonly>
											<span class="custom-left-placeholder" id="selected_time">Settime</span>
											<span class="custom-right-placeholder" id="selected_date">Setdate</span>
										</div>
										<span id="settimeline_id_error" style="display:none;  color: red;"></span>
										<input type="hidden" id="hidden_set_date" name="hidden_set_date">
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
										<img id="" class="img-responsive task-img-upload" src="{{ url('images/noimages/default-task-pic.jpg') }}" alt=""/>
										<button type="button" class="task-img-delete" id="delete-image">×</button>
									</div>
								</div>
								@if(!empty($locationWisecategory))
								<div class="row form-group">
									<div class="col-md-12">
										<label><strong>Select Category</strong></label>
										<div class="subcategory-box mt-2">
											@foreach($locationWisecategory as $category)
												<div class="subcategory-item">
													<div class="subcategory-checkbox">
														<input type="checkbox" name="location_category[]" value="{{ $category['id'] }}">
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
								<button type="button">Add Task</button>
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
    onChange: function(selectedDates, dateStr, instance) {
			if (selectedDates.length == 1) {
				const date = selectedDates[0];
				//alert(date);
				const dateOnly = flatpickr.formatDate(date, "d M Y");
				const timeOnly = flatpickr.formatDate(date, "H:i");
                //alert(date);alert(dateOnly);alert(timeOnly);
				document.getElementById('selected_time').innerText = 'Settime';
				document.getElementById('selected_date').innerText = dateOnly;
				$('#hidden_set_date').val(dateOnly);
				$('#hidden_set_time').val(timeOnly);
				
				// Responsive fix for mobile view
				if (window.innerWidth <= 576) {
					//instance.input.value = '';
					//instance.input.blur();
					//$('.custom-left-placeholder').hide();
					//alert(timeOnly);
					//document.getElementById('selected_time').innerText = 'Settime';
					//$('.split-placeholder-wrapper').hide();
					//$('#set_time').val('');
					//$('#selected_time').val('');
					
					// Stack vertically in mobile view
					/*$('.custom-left-placeholder').css({
						'font-size': '12px',
						'top': '35%',
						'transform': 'none',
						'left': '10px',
						'display': 'block'
					});
					
					$('.custom-right-placeholder').css({
						'font-size': '12px',
						'top': '35%',
						'transform': 'none',
						'right': '10px'
					});*/
					
					
					//setTimeout(() => {
							instance.input.value = '';
							instance.input.blur();
						//}, 0);
					
					
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
				
				// Delay clearing input to prevent recursion
				/*setTimeout(() => {
					instance.input.value = '';
					instance.input.blur();
				}, 0);*/
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
	
	$("#task_image").change(function() {
		$('#delete-image').show();
		$('.taskImg').show();
        readURL(this);
    });
   
   $(document).on('click','.save-task', function(){
		//let category_id = $('#category_id').val().trim();
		let task_title = $('#task_title').val().trim();
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
					$('#task_title').val('');
					localStorage.setItem('taskcreated', 1);
					
					var baseUrl = "{{ url('/location-details') }}";
					var location_id = $('#location_id').val();
					//alert(location_id);
					var redirectUrl = baseUrl + '/'+ location_id;
					window.location.href = redirectUrl;
					/*setTimeout(() => {
						window.location.reload();
					}, "2000");*/
				}
			},
		});
		
	});
	
	$('#delete-image').on('click', function() {
		//$('#img-upload').attr('src', '');
		var defaultImg = "{{ url('images/noimages/default-task-pic.jpg') }}";
		$('.task-img-upload').attr('src', defaultImg);
		$('#task_image').val('');
		$('#delete-image').hide();
		$('.taskImg').show();
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

