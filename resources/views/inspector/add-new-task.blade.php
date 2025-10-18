@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($locationWisecategory);die;
 
 
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
	
	$task_type = $taskData ? $taskData->task_type : '';
	$observation = $taskData ? $taskData->observation : '';
}
@endphp
	<!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<!-- =-=-=-=-=-=-= New Structure start =-=-=-=-=-=-= -->
	<div class="container checklist">
		<h2 class="checklist-title"></h2>
			
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container1">
					<div class="custom-tab" style="margin-bottom: 80px;">
						<div class="row form-group">
							<div class="col-md-12">
								@if(empty($task_id))
								<h2 class="owner-checklist-title">Add Task</h2>
								@else
								<h2 class="owner-checklist-title">Edit Task</h2>
								@endif
							</div>
							<div class="col-md-12">
								<label>{{ __('Task Type') }}</label>
								<div class="task-type-radio-group">
									<div class="task-type-item" @if(isset($task_type) && $task_type != 0) style="display:none;" @endif>
										<div class="task-type-radio">
											@if(empty($task_type))
											<input type="radio" name="task_type" value="0">
											@endif
										</div>
										<div class="task-type-name"><strong>Routine</strong></div>
									</div>
									<div class="task-type-item" @if(isset($task_type) && $task_type != 1) style="display:none;" @endif>
										<div class="task-type-radio">
											@if(empty($task_type))
											<input type="radio" name="task_type" value="1">
											@endif
										</div>
										<div class="task-type-name"><strong>Ad-Hoc</strong></div>
									</div>
								</div>
							</div>
							<div class="col-md-12">								
								<form id="frmtaskadhoc" action="{{ route('save-task-adhoc-data') }}" enctype="multipart/form-data" class="form-adhoc" style="display: none;">
									<input type="hidden" id="location_id" name="location_id" value="{{ $location_id ?? ''}}">
									<input type="hidden" id="hid_task_id" name="id" value="{{ $task_id ?? ''}}">
									<input type="hidden" id="hid_task_image" name="hid_task_image" value="{{ $task_image ?? ''}}">
									<input type="hidden" id="adhoc_task_type" name="adhoc_task_type">
									@csrf	
									<div class="row form-group task-main-form">
										<div class="col-md-12 mt-2">
											<div class="row form-group">
												<div class="col-md-12">
													<label>{{ __('Task Title') }}</label>
													<input class="form-control" placeholder="Add task title" type="text" name="adhoc_task_title" id="adhoc_task_title" value="{{ $task_title ?? ''}}">
													<span id="adhoc_tasktitle_id_error" style="display:none;  color: red;"></span>
												</div>
											</div>
											<div class="row form-group">
												<div class="col-md-12">
													<label>{{ __('Category') }}</label>
													<div><button class="button-add-category add-category" type="button"><i class="fa-solid fa-plus"></i> Add category</button></div>
													<span id="adhoc_add_cat_id_error" style="display:none;  color: red;"></span>
												</div>
												
												<div class="col-md-12 mt-2">
													<div class="category-tag tag-container">
													</div>
												</div>
											</div>
											<div class="row form-group">
												<div class="col-md-12">
													<label>{{ __('What’s your observation?') }}</label>
													<textarea class="form-control" placeholder="State your observations" name="observation" id="observation" >{{ old('observation', $observation ?? '') }}</textarea>
													<span id="adhoc_observation_id_error" style="display:none;  color: red;"></span>
												</div>
											</div>
											<div class="row align-items-center update-image">
												<div class="col-md-4 mb-3">
													<label for="adhoc_task_image" class="task-cover-image">Upload Cover</label>
													<div class="upload-wrapper">
														<input type="file" name="adhoc_task_image" id="adhoc_task_image" style="display: none;" accept="image/*">
														<label for="adhoc_task_image" class="task-upload-label">
														<span class="task-upload-text">Update image</span>
														<i class="fa fa-upload task-upload-icon"></i>
														</label>
														<span id="taskimage_id_error" style="display:none;  color: red; margin-left:17px;">please </span>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="form-group  col-md-12  col-sm-12 adhoctaskImg" style="display:block;">
													<div class="task-preview-wrapper position-relative d-inline-block">
														<img id="" class="img-responsive task-img-upload" src="{{ $task_image ? url('uploads/task/' . $task_image) : url('images/noimages/default-task-pic.png') }}" alt=""/>
														<button type="button" class="task-img-delete" id="adhoc-delete-image">×</button>
													</div>
												</div>
											</div>
										</div>
										<div class="">
										@if(empty($task_id))
											<button class="sticky-footer save-adhoc-task adhoc-task-load-add" type="button">Add Task</button>
										@else
											<button class="sticky-footer save-adhoc-task adhoc-task-load-edit" type="button">Edit Task</button>
										@endif
										</div>
									</div>
									<div class="row form-group task-category-form" style="display: none;">
										<div class="col-md-12 mt-2">
											<h2 class="owner-checklist-title">Select Category</h2>
											<span id="select_category_id_error" style="display:none;  color: red;"></span>
										</div>
										<div class="col-md-12 list-category">
											<ul class="accordion mt-2">
											@foreach($locationWisecategory as $categoties)
											
											@php 
											$categoryId = $categoties['id'];
											$category_chklst_subchklst = App\Models\Checklist::with('get_subchecklist')->where('category_id', $categoties['id'])->orderBy('order_no')->get();
											
											@endphp
												<li class="category-item">
													<h3 class="accordion-title"><a href="javascript:void(0);" id="edit-slide-down-{{ $categoties['id']}}">{{ $categoties['name'] }}
													<input type="hidden" name="loc_category[]" value="{{ $categoties['id'] }}">
													<input type="hidden" name="loc_category_name[]" value="{{ $categoties['name'] }}">
													</a></h3>
												   <div class="accordion-content">
													  <div class="subcategory-box">
														@foreach($category_chklst_subchklst as $checklists)
														@php 
															$isChecklistChecked = isset($structuredArray[$categoryId][$checklists->id]);
														@endphp
														<div class="subcategory-item">
																<div class="subcategory-checkbox">
																	<input type="checkbox" name="loc_category_checklist[]" value="{{ $checklists->id}}" {{ $isChecklistChecked ? 'checked' : '' }} >
																</div>
																<div class="subcategory-name"><strong>{{ $checklists->name }}</strong></div>														
														</div>
															@if(!empty($checklists->get_subchecklist))
																	@foreach($checklists->get_subchecklist as $subchecklist)
																
																 @php 
																	$isSubChecked = isset($structuredArray[$categoryId][$checklists->id]) && in_array($subchecklist->id, $structuredArray[$categoryId][$checklists->id]);
																@endphp
	<div class="subcategory-sub-item" data-parent="{{ $checklists->id }}">
		<div class="subcategory-checkbox">
		<input type="checkbox" name="loc_category_checklist_subchecklist[]" value="{{ $subchecklist->id }}" {{ $isSubChecked ? 'checked' : '' }}>
		</div>
		<div class="subcategory-name"><strong>{{ $subchecklist->name }}</strong></div>
	</div>							
@endforeach																			
															@endif
														@endforeach
														</div>
												   </div>
												</li>
											@endforeach	
											</ul>
										</div>
										<div class="">
											<button class="sticky-footer select-category" type="button">Select Category</button>
										</div>
									</div>
								</form>
								
								<form id="frmcategory" action="{{ route('save-task-data') }}" enctype="multipart/form-data" class="form-routine" style="display: none;">
									<input type="hidden" id="location_id" name="location_id" value="{{ $location_id ?? ''}}">
									<input type="hidden" id="hid_task_id" name="id" value="{{ $task_id ?? ''}}">
									<input type="hidden" id="hid_task_image" name="hid_task_image" value="{{ $task_image ?? ''}}">
									<input type="hidden" id="routing_task_type" name="routing_task_type">
									@csrf	
										<div class="row form-group">
											<div class="col-md-12 mt-2">
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
										<div class="row d-flex1 align-items-center update-image">
											<div class="col-md-4 mb-3">
												<label for="task_image" class="task-cover-image">Upload Cover</label>
												<div class="upload-wrapper">
													<input type="file" name="task_image" id="task_image" style="display: none;" accept="image/*">
													<label for="task_image" class="task-upload-label">
													<span class="task-upload-text">Update image</span>
													<i class="fa fa-upload task-upload-icon"></i>
													</label>
													<span id="taskimage_id_error" style="display:none;  color: red; margin-left:17px;">please </span>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group  col-md-12  col-sm-12 taskImg" style="display:block;">
												<div class="task-preview-wrapper position-relative d-inline-block">
													<img id="" class="img-responsive task-img-upload" src="{{ $task_image ? url('uploads/task/' . $task_image) : url('images/noimages/default-task-pic.png') }}" alt=""/>
													<button type="button" class="task-img-delete" id="delete-image">×</button>
												</div>
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
									
									<div class="">
									@if(empty($task_id))
										<button class="sticky-footer save-task task-load-add" type="button">Add Task</button>
									@else
										<button class="sticky-footer save-task task-load-edit" type="button">Edit Task</button>
									@endif
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
	<input type="hidden" value="{{ $task_type ?? ''}}" id="hid_task_type">
	<input type="hidden" id="has_task_id"  value="{{ $task_id ?? ''}}">
	{{--<div class="container checklist">
		<h2 class="checklist-title"></h2>
			
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container1">
					<div class="custom-tab" style="margin-bottom: 80px;">
						
					</div>
				</div>
			</section>
		</div>
    </div>--}}
	<!-- =-=-=-=-=-=-= New Structure end =-=-=-=-=-=-= -->
	
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	{{--<div class="container checklist">
		<h2 class="checklist-title"></h2>
			
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container1">
					<div class="custom-tab" style="margin-bottom: 80px;">
						<div class="row">
							<div class="col-md-12">
								@if(empty($task_id))
								<h2 class="owner-checklist-title">Add Task</h2>
								@else
								<h2 class="owner-checklist-title">Edit Task</h2>
								@endif
							</div>
						</div>
						
					</div>
				</div>
			</section>
		</div>
    </div>--}}
	
@endsection 
@section('scripts')
<script src="{{ url('front-assets/css/bootstrap.min.css') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
	let openCategoryId = {{ $firstCategoryId ?? ''}};
	let firstCatId = {{ $locationWisecategory[0]['id'] }}
	
	if (openCategoryId) {
        
		if(openCategoryId != firstCatId)
		{
			setTimeout(() => {
				//alert(openCategoryId);
				$('#edit-slide-down-' + openCategoryId).trigger('click');
			}, 600);
		}
		else
		{
			const section = $('#edit-slide-down-' + openCategoryId)
				.closest('.category-item')
				.find('.accordion-content');

			section.slideDown();
		}
    }
	else
	{
		const section = $('#edit-slide-down-' + openCategoryId)
			.closest('.category-item')
			.find('.accordion-content');
		section.slideDown();
	}
	
	//alert($('#has_task_id').val());
	if($('#hid_task_id').val() !='')
	{
		let hid_task_type = $('#hid_task_type').val();
		//alert(hid_task_type);
		if(hid_task_type == 1)
		{
			setTimeout(function() {
				$('.button-add-category').trigger('click');
			}, 300);
			
			setTimeout(function() {
				$('.select-category').trigger('click');
			}, 300);
			
			setTimeout(function() {
				$('.form-routine').slideUp();
				$('.form-adhoc').slideDown();
			}, 300);
			
			$('#adhoc_task_type').val(1);
			
		}
		
		if(hid_task_type == 0)
		{
			$('.form-routine').slideUp();
			$('.form-adhoc').slideDown();
			$('.select-category').click();
			$('#routing_task_type').val(0);
		}
	}
	
	
    $('input[name="task_type"]').on('change', function() {
		if ($(this).val() == 0) {
			$('.form-adhoc').slideUp(); // smooth hide
			$('.form-routine').slideDown(); // smooth show
			
			$('.task-category-form').slideUp();
			$('.task-main-form').slideDown();
			$('#routing_task_type').val(0);
		} else {
			$('.form-routine').slideUp(); // smooth hide
			$('.form-adhoc').slideDown(); // smooth show
			$('#adhoc_task_type').val(1);
		}
	});
	
    $('.add-category').on('click', function() {
		$('.task-main-form').slideUp();
		$('.task-category-form').slideDown();
		localStorage.setItem('taskBackButton', 1);
		/*if (openCategoryId) {
			//$('.accordion-content').slideUp();
			setTimeout(function() {
				$('#edit-slide-down-' + openCategoryId).trigger('click');
			}, 400)
        }*/
	});
	
    $('.select-category').on('click', function() {
		
		let result = [];
		$('.category-item').each(function() {
			let categoryId = $(this).find('input[name="loc_category[]"]').val();
			let categoryName = $(this).find('input[name="loc_category_name[]"]').val();
			let categoryData = {
				category_id: categoryId,
				category_name: categoryName,
				checklists: []
			};

			
			$(this).find('input[name="loc_category_checklist[]"]:checked').each(function() {
				let checklistId = $(this).val();
				let checklistData = {
					id: checklistId,
					subchecklists: []
				};

				
				$(this)
					.closest('.subcategory-box')
					.find(`.subcategory-sub-item[data-parent="${checklistId}"] input[name="loc_category_checklist_subchecklist[]"]:checked`)
					.each(function() {
						checklistData.subchecklists.push($(this).val());
					});

				categoryData.checklists.push(checklistData);
			});

			if (categoryData.checklists.length > 0) {
				result.push(categoryData);
			}
		});
		
		//alert(JSON.stringify(result));
		
		if(JSON.stringify(result) == "[]")
		{
			$('#select_category_id_error').text('Please select category').fadeIn().delay(2000).fadeOut();
			return false;
		}
		
		$('.category-tag.tag-container').empty();
		//alert(JSON.stringify(result));
		result.forEach(cat => {
		  let tagHTML ='<div class="tag-content"><div class="tag">' + cat.category_name + '</div><span class="close reject_category" data-id="' +cat.category_id + '">&times;</span></div>';
		  $('.category-tag.tag-container').append(tagHTML);
		});
		
		$('.task-category-form').slideUp();
		$('.task-main-form').slideDown();
	});
	
	$(document).on('click', '.reject_category', function(){
		
		let cat_id = $(this).data('id');
		$(this).closest('.tag-content').remove();
		
		let result = [];
		$('.category-item').each(function() {
			let categoryId = $(this).find('input[name="loc_category[]"]').val();
			let categoryName = $(this).find('input[name="loc_category_name[]"]').val();
			let categoryData = {
				category_id: categoryId,
				category_name: categoryName,
				checklists: []
			};

			
			$(this).find('input[name="loc_category_checklist[]"]:checked').each(function() {
				let checklistId = $(this).val();
				let checklistData = {
					id: checklistId,
					subchecklists: []
				};

				
				$(this)
					.closest('.subcategory-box')
					.find(`.subcategory-sub-item[data-parent="${checklistId}"] input[name="loc_category_checklist_subchecklist[]"]:checked`)
					.each(function() {
						checklistData.subchecklists.push($(this).val());
					});

				categoryData.checklists.push(checklistData);
			});

			if (categoryData.checklists.length > 0) {
				result.push(categoryData);
			}
		});
		
		result = result.filter(cat => cat.category_id != cat_id);
		$(this).closest('.tag-content').remove();
		//alert(JSON.stringify(result));
		let location_id = $('#location_id').val();
		// initialize the selected the categories after delete category
		$.ajax({
			url: "{{ route('add-task-initialize-category') }}",
			type: "POST",
			data: {location_id:location_id,result:result,_token:csrfToken},
			success: function(response) {
				//alert(response.html);
				$('.list-category').html(response.html);
				initializeAccordion();
			},
		});
	});
	
	$(document).on('change', 'input[name="loc_category_checklist[]"]', function () {
		let checklistId = $(this).val();
		let isChecked = $(this).is(':checked');

		// Check/uncheck all subchecklists under this checklist
		$(`.subcategory-sub-item[data-parent="${checklistId}"] input[type="checkbox"]`).prop('checked', isChecked);
	});
	
	$(document).on('change', 'input[name="loc_category_checklist_subchecklist[]"]', function () {
		let parentId = $(this).closest('.subcategory-sub-item').data('parent');
		//alert(parentId);
		let allSubCheckboxes = $(`.subcategory-sub-item[data-parent="${parentId}"] input[type="checkbox"]`);
		
		//alert(allSubCheckboxes.filter(':checked').length);
		
		let allChecked = allSubCheckboxes.length === allSubCheckboxes.filter(':checked').length;
		
		if(allSubCheckboxes.filter(':checked').length > 0)
		{
			$(`input[name="loc_category_checklist[]"][value="${parentId}"]`).prop('checked', true);
		}
		
		if(allSubCheckboxes.length == allSubCheckboxes.filter(':checked').length)
		{
			$(`input[name="loc_category_checklist[]"][value="${parentId}"]`).prop('checked', allChecked);
		}

		//$(`input[name="loc_category_checklist[]"][value="${parentId}"]`).prop('checked', allChecked);
		
		if(allSubCheckboxes.filter(':checked').length == 0)
		{
			$(`input[name="loc_category_checklist[]"][value="${parentId}"]`).prop('checked', false);
		}
	});
});
</script>

<script>
$(document).ready(function() {
	flatpickr("#set_time", {
    enableTime: false,
    dateFormat: "d M Y H:i",
	minDate: "today",
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
		//instance.input.value = '';
		this.value = '';
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
	
	$("#adhoc_task_image").change(function() {
		$('#adhoc-delete-image').show();
		$('.adhoctaskImg').show();
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
					
					if(hid_task_id == '')
					{
						$('.task-load-add').html('Add Task').prop('disabled', false);
					}
					else{
						$('.task-load-edit').html('Edit Task').prop('disabled', false);
					}
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
	
	$(document).on('click','.save-adhoc-task', function(){
		
		let task_title = $('#adhoc_task_title').val().trim();
		let observation = $('#observation').val().trim();
		let hid_task_id = $('#hid_task_id').val();
		
		let result = [];
		$('.category-item').each(function() {
			let categoryId = $(this).find('input[name="loc_category[]"]').val();
			let categoryName = $(this).find('input[name="loc_category_name[]"]').val();
			let categoryData = {
				category_id: categoryId,
				category_name: categoryName,
				checklists: []
			};

			
			$(this).find('input[name="loc_category_checklist[]"]:checked').each(function() {
				let checklistId = $(this).val();
				let checklistData = {
					id: checklistId,
					subchecklists: []
				};

				
				$(this)
					.closest('.subcategory-box')
					.find(`.subcategory-sub-item[data-parent="${checklistId}"] input[name="loc_category_checklist_subchecklist[]"]:checked`)
					.each(function() {
						checklistData.subchecklists.push($(this).val());
					});

				categoryData.checklists.push(checklistData);
			});

			if (categoryData.checklists.length > 0) {
				result.push(categoryData);
			}
		});
		
		//alert(JSON.stringify(result));
		
		
		if (task_title === '') {
			$('#adhoc_tasktitle_id_error').text('Please enter task title').fadeIn().delay(2000).fadeOut();
			return false;
		}
		
		if(JSON.stringify(result) == "[]")
		{
			$('#adhoc_add_cat_id_error').text('Please add category').fadeIn().delay(2000).fadeOut();
			return false;
		}
		
		if (observation === '')
		{
			$('#adhoc_observation_id_error').text('Please enter observation').fadeIn().delay(2000).fadeOut();
			return false;
		}
		
		
		var URL = $('#frmtaskadhoc').attr('action');
		var id = $('#id').val();
		
		if(hid_task_id == '')
		{
			$('.adhoc-task-load-add').prop('disabled', true);
			$('.adhoc-task-load-add').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Submitting...').prop('disabled', true);
		}
		else{
			$('.adhoc-task-load-edit').prop('disabled', true);
			$('.adhoc-task-load-edit').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Submitting...').prop('disabled', true);
		}
		
		
		let formData = new FormData($('#frmtaskadhoc')[0]);
		formData.append('category_ids', JSON.stringify(result));
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
					
					if(hid_task_id == '')
					{
						$('.adhoc-task-load-add').html('Add Task').prop('disabled', false);
					}
					else{
						$('.adhoc-task-load-edit').html('Edit Task').prop('disabled', false);
					}
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
					$('.adhoc-task-load-add').prop('disabled', false);
				}
				else{
					$('.adhoc-task-load-edit').prop('disabled', false);
				}
			}
		});
	})
	
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
	
	$('#adhoc-delete-image').on('click', function() {
		//$('#img-upload').attr('src', '');
		var defaultImg = "{{ url('images/noimages/default-task-pic.png') }}";
		$('.task-img-upload').attr('src', defaultImg);
		$('#adhoc_task_image').val('');
		$('#adhoc-delete-image').hide();
		$('.adhoctaskImg').show();
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

function initializeAccordion() {
    $(document).off('click', '.accordion-title a'); // prevent duplicate bindings
    $(document).on('click', '.accordion-title a', function(e) {
        e.preventDefault();

        var $content = $(this).closest('li').find('.accordion-content');

        // Toggle accordion
        if ($content.is(':visible')) {
            $content.slideUp();
            $(this).removeClass('active');
        } else {
            $('.accordion-content').slideUp(); // Close others
            $('.accordion-title a').removeClass('active');
            $content.slideDown();
            $(this).addClass('active');
        }
    });
}
</script>
@endsection

