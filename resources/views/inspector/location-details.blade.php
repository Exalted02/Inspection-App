@extends('layouts.app')
@section('content')
@push('styles')

{{--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">--}}

@endpush
@php 
//echo "<pre>";print_r($location_categories);die;
//echo "<pre>";print_r($task_list_data);die;
use Carbon\Carbon;
$month = '';
$day = '';
$week= '';
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container location-details">
		<div class="d-flex align-items-center location-header mb-3">
			<img src="{{url('uploads/location/' . $location_categories[0]->image )}}" alt="Location" />
			<div>
				<div class="title">{{ ucfirst($location_categories[0]->location_name) ?? ''}}</div>
				<small class="text-muted"><i class="fa fa-location-dot mr-5px"></i>{{ $location_categories[0]->address ?? ''}}, {{ $location_categories[0]->zipcode ?? ''}}</small>
			</div>
		</div>
		<button class="grey-button width-full add-new-category">+ Add Task</button>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container">
				@if(auth()->user()->user_type == 1)
				    <div class="col-md-12 col-sm-12 d-grid">
					{{--<div class="form-group text-center add-new-category">
							<div class="add-task-box">
								<span class="plus-sign">+</span>
								<div class="add-task-text">Add Tasks</div>
							</div>
						</div>
						<button class="grey-button">+ Add Task</button>--}}
					</div>
				@endif
				
					<div class="row custom-tab">
						<!-- Tabs -->
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#inprogress_tab" aria-controls="inprogress_tab" role="tab" data-toggle="tab">In progress</a></li>
							<li role="presentation"><a href="#completed_tab" aria-controls="completed_tab" role="tab" data-toggle="tab">Completed</a></li>
						</ul>
						<!-- Tab panes -->
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane active" id="inprogress_tab">
							@if($task_list_data->isNotEmpty())
								@foreach($task_list_data as $tasks)
								@php
								
								   /*$categoryData = App\Models\Category::where('id', $categories->category_id)->first();*/
								   
								   $month = Carbon::parse($tasks->created_at)->format('M');
								   $day =   Carbon::parse($tasks->created_at)->format('d');
								   $week= strtoupper(Carbon::parse($tasks->created_at)->format('D'));
								   
								   $img = $tasks->image !='' ? url('uploads/task/' . $tasks->image) : url('images/noimages/noimage_task.png');
								@endphp
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<div class="date">
											<div class="day"> {{ $month ?? '' }} </div>
											<div class="dow">{{ $day ?? '' }}</div>
											<div class="dod"> {{ $week ?? '' }}</div>
										</div>
									</div>
									<div class="flex-grow-1">
									@if(auth()->user()->user_type == 1 || auth()->user()->user_type == 3)
										<a href="{{ route('category', ['location_id'=>$tasks->location_id,'task_id'=>$tasks->id, 'active'=>1]) }}">
									@elseif(auth()->user()->user_type == 2)
										<a href="{{ route('location-owner', ['location_id'=>$tasks->location_id,'task_id'=>$tasks->id, 'active'=>1]) }}">
									@endif
										
										<img src="{{$img }}" alt="Task"/>
										
											<h6>{{ $tasks->task_title ?? '' }}</h6>
											<p class="text-muted mb-0">{{ get_task_status(auth()->user()->id, $tasks->id, $tasks->location_id) }}</p>
										</a>
									</div>
								</div>
								@endforeach
							@else
								<div class="text-center"><strong><h3>No record found</h3></strong></div>
							@endif
							</div>
							<div role="tabpanel" class="tab-pane" id="completed_tab">
								
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
    </div>
	
	<!-- =-=-=-=-=-=-= Task add  Modal =-=-=-=-=-=-= -->
      <div class="modal fade price-quote" id="add_category" tabindex="-1" role="dialog" aria-hidden="true">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                  <h3 class="modal-title" id="lineModalLabel">{{ __('Add task') }}</h3>
               </div>
               <div class="modal-body">
                  
                  <!-- content goes here -->
                  <form id="frmcategory" action="{{ route('save-task-data') }}" enctype="multipart/form-data">
					<input type="hidden" id="id" name="id">
					<input type="hidden" id="location_id" name="location_id" value="{{ $location_id ?? ''}}">
					@csrf
                    
                    <div class="form-group  col-md-12  col-sm-12">
                        <label>{{ __('Task Title') }}</label>
						<div class="split-placeholder-wrapper">
                        <input class="form-control" placeholder="Add task title" type="text" name="task_title" id="task_title">
						<span id="tasktitle_id_error" style="display:none;  color: red;"></span>
                    </div>
					<div class="form-group  col-md-12  col-sm-12">
                        <label>{{ __('Set Timeline') }}</label>
						<div class="split-placeholder-wrapper">
							<input class="form-control" placeholder="" type="text" name="set_time" id="set_time">
						</div>
						<span id="settimeline_id_error" style="display:none;  color: red;"></span>
                    </div>
					<div class="form-group col-md-12 col-sm-12">
						<label><strong>Select Category</strong></label>
						<div class="subcategory-box mt-2">
							@foreach($locationWisecategory as $category)
								<div class="subcategory-item">
									<div class="subcategory-checkbox">
										<input type="checkbox" name="location_category[]" value="{{ $category->id }}">
									</div>
									<div class="subcategory-name"><strong>{{ $category->name }}</strong></div>
									
								</div>
							@endforeach
							<span id="tasktcategory_id_error" style="display:none;  color: red;">Please select category</span>
						</div>
					</div>

					<div class="row margin-bottom-20">
						<div class="form-group">
							<div class="col-md-9">
								<div class="input-group" style="margin-left: 16px;">
									<span class="input-group-btn">
									<span class="btn btn-default btn-file">
									Browse… <input type="file" id="task_image" name="task_image">
									</span>
									</span>
									<input type="text" class="form-control" readonly name="task_image" id="task_image" accept="image/*">
								</div>
								<span id="taskimage_id_error" style="display:none;  color: red; margin-left:17px;">please </span>
							</div>
						</div>
					</div>
					<div class="form-group  col-md-12  col-sm-12">
						<div class="col-md-3">
							<img id="img-upload" class="img-responsive" src="images/users/2.jpg" alt="" style="width: 100%; border: 0px solid #ccc; border-radius: 5px;"/>
							<button type="button" id="delete-image" style="position: absolute; top: -10px; right: 4px; background: red; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; text-align: center; line-height: 20px; cursor: pointer; display:none;">×</button>
						</div>
					</div>
					<div class="clearfix"></div>
                    <div class="col-md-12  col-sm-12 margin-bottom-20 margin-top-20">
                        <button type="button" class="btn btn-theme btn-block save-task button-color">Submit</button>
                    </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
	
@endsection 
@section('scripts')
{{--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>--}}
<script>
$(document ).ready(function() {
	localStorage.removeItem('selectedTab');
	
	var taskcreated = localStorage.getItem('taskcreated');
	if(taskcreated == 1)
	{
		$('.task-created-button').fadeIn().delay(2000).fadeOut();
		localStorage.removeItem('taskcreated');
	}
	
	$(document).on('click', '.add-new-category', function(){
		$('#task_title').val('');
		$('input[name="location_category[]"]:checked').each(function() {
			$('input[name="location_category[]"]').prop('checked', false);
		});
		
		$('#delete-image').hide();
		$('#img-upload').attr('src', '');
		$('#task_image').val('');
		$('#add_category').modal('show');
	});
	
	$("#task_image").change(function() {
		$('#delete-image').show();
        readURL(this);
    });
	
	$(document).on('click','.save-task', function(){
		//let category_id = $('#category_id').val().trim();
		let task_title = $('#task_title').val().trim();
		let task_image = $('#task_image')[0].files.length;
		/*if (category_id === '') {
			$('#category_id_error').text('Please enter category').fadeIn().delay(2000).fadeOut();
			return false;
		}*/
		
		if (task_title === '') {
			$('#tasktitle_id_error').text('Please enter task title').fadeIn().delay(2000).fadeOut();
			return false;
		}
		
		
		
		let selectedLocations = [];
		$('input[name="location_category[]"]:checked').each(function() {
			selectedLocations.push($(this).val());
		});
		//alert(selectedLocations);
		if (selectedLocations.length === 0) {
			$('#tasktcategory_id_error').text('Please select category').fadeIn().delay(2000).fadeOut();
			/*$('input[name="location[]"]').first().addClass('is-invalid');
			$('input[name="location[]"]').first().closest('.select-people-checkbox-s').siblings('.invalid-feedback').show();*/
			isValid = false;
		}
		
		if (task_image === 0) {
			$('#taskimage_id_error').text('Please select image').fadeIn().delay(2000).fadeOut();
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
					
					setTimeout(() => {
						window.location.reload();
					}, "2000");
				}
			},
		});
		
	});
	
	/*$('#task_image').on('change', function (event) {
		const [file] = event.target.files;
		if (file) {
			const reader = new FileReader();
			reader.onload = function (e) {
				$('#preview').attr('src', e.target.result).show();
			}
			reader.readAsDataURL(file);
		}
	});*/
	
	$('#delete-image').on('click', function() {
		$('#img-upload').attr('src', '');
		$('#task_image').val('');
		$('#delete-image').hide();
	});
});
function readURL(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function(e) {
			$('#img-upload').attr('src', e.target.result);
		};
		reader.readAsDataURL(input.files[0]);
	}
}
</script>
@endsection


