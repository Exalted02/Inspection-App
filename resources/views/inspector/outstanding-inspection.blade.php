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
$location_img = $location_categories[0] && $location_categories[0]->image != null ? url('uploads/location/' . $location_categories[0]->image) : url('images/noimages/noimage_region.png');

$getCategotyArr = [];
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container location-details">
		<div class="row">
			<div class="col-md-8 col-sm-8 col-xs-10 profile-name">
			   Outstanding Inspection
			</div>
			<div class="col-md-4 col-sm-4 col-xs-2 location-count" id="show_count"></div>
		</div>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="custom-tab">
					<div class="tab-content">
						@php
							$tot_task = 0;
						@endphp
						@if($groupedTasks->isNotEmpty())
							@foreach($groupedTasks as $locationId => $locationTasks)

								@php
									$location = $location_categories->firstWhere('id', $locationId);
									$tot_task = $tot_task + $locationTasks->count();
								@endphp

								<div class="row mt-2">
									<div class="col-md-8 col-sm-8 col-xs-10 location-name">{{ $location->location_name ?? $location->title ?? 'Location' }}</div>
									<div class="col-md-4 col-sm-4 col-xs-2 location-count">{{ $locationTasks->count() }}</div>
								</div>

									<div class="row">
									<div class="col-md-8">
								@foreach($locationTasks as $tasks)

									@php
										$month = Carbon::parse($tasks->created_at)->format('M');
										$day   = Carbon::parse($tasks->created_at)->format('d');
										$week  = strtoupper(Carbon::parse($tasks->created_at)->format('D'));

										$img = $tasks->image != ''
											? url('uploads/task/' . $tasks->image)
											: url('uploads/task/default-task-pic.png');

										$taskStatus = $tasks->status == 0
											? ''
											: ($tasks->status == 1 ? 'Incomplete' : '');

										$extension = strtolower(pathinfo($img, PATHINFO_EXTENSION));
									@endphp

										<div class="d-flex mb-3 task mt-2">

											<div class="date-box">
												<div class="date">
													<div class="day">{{ $month }}</div>
													<div class="dow">{{ $day }}</div>
													<div class="dod">{{ $week }}</div>
												</div>

												<div class="task-action">
													<div class="action-box">
														<a class="edit-button"
														   href="{{ route('task-list-edit', ['lid'=> $tasks->location_id,'id'=> $tasks->id]) }}">
															<i class="fa-solid fa-pencil"></i>
														</a>
													</div>

													<div class="action-box">
														<a class="delete-task"
														   data-id="{{ $tasks->id }}"
														   href="javascript:void(0);">
															<i class="fa-regular fa-trash-can m-r-5"></i>
														</a>
													</div>
												</div>
											</div>

											<div class="flex-grow-1">

												@if(auth()->user()->user_type == 1 || auth()->user()->user_type == 3)
													<a href="{{ route('category', ['location_id'=>$tasks->location_id,'task_id'=>$tasks->id,'active'=>1]) }}">
												@elseif(auth()->user()->user_type == 2)
													<a href="{{ route('location-owner', ['location_id'=>$tasks->location_id,'task_id'=>$tasks->id,'active'=>1]) }}">
												@endif

													@if(in_array($extension, ['jpg','jpeg','png','gif','webp']))
														<img src="{{ $img }}">
													@elseif(in_array($extension, ['mp4','webm','ogg']))
														<video controls src="{{ $img }}"></video>
													@endif

													<h6>{{ $tasks->task_title }}</h6>

													@if(!empty($taskStatus))
														<p class="text-muted mb-0" style="color:red">
															<i class="fa fa-clock"></i>
															{{ $taskStatus }}
														</p>
													@endif

												</a>

											</div>

										</div>

								@endforeach
									</div>
									</div>
								<hr>
							@endforeach
						@else
							<div class="col-md-12 col-sm-12 d-grid">
								<div class="form-group text-center">
									<div class="add-task-box">
										<div class="no-tasks-list-title">
											No On-going and <br> Upcoming Task
										</div>
									</div>
								</div>
							</div>
						@endif
					</div>
				</div>
			</section>
		</div>
    </div>
	
@endsection 
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
	$('#show_count').text('{{$tot_task}}');
	
	$(document).on('click', '.delete-task', function() {
		var id = $(this).data('id');
		Swal.fire({
			  title: '<div class="swal-title-class">Are you sure want to delete task?</div>',
			  //html: '<div class="swal-message-class">You can continue your saved attempt from the task list labeled as "incomplete".</div>',
			  icon: "warning",
			  showCancelButton: true,
			  cancelButtonText: "Cancel",
			  confirmButtonText: "Yes",
			  confirmButtonColor: "#0b2b57", 
			  cancelButtonColor: "#e0e0e0",
			  customClass: {
				cancelButton: 'swal-cancel-black',
				confirmButton : 'swal-save-exist-black'
			  }
			}).then((result) => {
			  if (result.isConfirmed) {
				deletetask(id); // your function
			  }
			});
		
	})
});	
function deletetask(id)
{
	$.ajax({
		url: "{{ route('delete-task')}}",
		type: "POST",
		data: {id:id, '_token':csrfToken},
		dataType: 'json',
		success: function(response) {
			var baseUrl = "{{ url('/outstanding-inspection') }}";
			var redirectUrl = baseUrl;
			window.location.href = redirectUrl;
			},
	});
}
</script>
@endsection


