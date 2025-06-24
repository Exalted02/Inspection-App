@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($categoryData);die;
 
 use Carbon\Carbon;
 $rejected_region = '';
 $image_arr = [];
 
 $lo_corrective_action_plan = '';
 $lo_corrective_completed_by = '';
 
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
	 
	 /*$corrective_action_data = App\Models\Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->where('inspector_id', auth()->user()->id)->first();*/
	 
	 $corrective_action_data = App\Models\Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
	 
	 $lo_corrective_action_plan = $corrective_action_data ? $corrective_action_data->lo_corrective_action_plan : '';
	 
	 $lo_corrective_completed_by = $corrective_action_data ? $corrective_action_data->created_at : '';
	 
	 $corrective_action_primary_id = $corrective_action_data ? $corrective_action_data->id : '';
	 
	 $corrective_action_file_data = App\Models\Task_list_corrective_action_file::where('task_list_corrective_actions_id', $corrective_action_primary_id)->get();
	 
	 $corrective_action_files = [];
	 if($corrective_action_file_data->isNotEmpty())
	 {
		 foreach($corrective_action_file_data as $corrective_files)
		 {
			$corrective_action_files[] = [
				'url' => url('uploads/corrective_action/' .$corrective_files->file),
			];
		 }
	 }
	 
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
		
		foreach($subImages as $image)
		{
		 $image_arr[] = [
					'url'=> url('uploads/reject-files/subchecklist/' .$image->file ),
			 ];
		}
	}
	
	/*$corrective_action_data = App\Models\Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->where('subchecklist_id', $subchecklist_id)->where('inspector_id', auth()->user()->id)->first();*/
	
	$corrective_action_data = App\Models\Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->where('subchecklist_id', $subchecklist_id)->first();
	 
	$lo_corrective_action_plan = $corrective_action_data ? $corrective_action_data->lo_corrective_action_plan : '';
	 
	$lo_corrective_completed_by = $corrective_action_data ? $corrective_action_data->created_at : '';
	
	$corrective_action_primary_id = $corrective_action_data ? $corrective_action_data->id : '';
	
	$lo_corrective_action_plan_second_check = $corrective_action_data ? $corrective_action_data->lo_corrective_action_plan_second_check : '';
	
	$corrective_action_file_data = App\Models\Task_list_corrective_action_file::where('task_list_corrective_actions_id', $corrective_action_primary_id)->get();
	 
	 $corrective_action_files = [];
	 if($corrective_action_file_data->isNotEmpty())
	 {
		 foreach($corrective_action_file_data as $corrective_files)
		 {
			$corrective_action_files[] = [
				'url' => url('uploads/corrective_action/' .$corrective_files->file),
			];
		 }
	 }
 }
 
 //echo "<pre>";print_r($corrective_action_files);die;
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist">
		<h2 class="checklist-title">{{ $tab == 'corrective-action' ? 'Agree of corrective action' : 'Corrective check' }} plan</h2>
			
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container1">
					<div class="custom-tab" style="margin-bottom: 80px;">
						<div class="row">
							<h2 class="owner-checklist-title">{{ $checklist->name ?? ''}}</h2>
						</div>
						@if(!empty($subChecklistName))
						<div class="row">
							<div class="owner-subchecklist-title">{{ $subChecklistName ?? ''}}</div>
						</div>
						@endif
					
						
						<div class="row">
							<div class="col-md-12"><label>Reason</label></div>
						</div>
						<div class="row">
						<div class="col-md-12">{{ $rejected_region ?? '' }}</div>
						</div>
						
						
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
								@if(!empty($image_arr))
									@foreach($image_arr as $url)
									<div class="cheklist-reply-images">
										<img src="{{ $url['url'] ?? '' }}">
									</div>
									@endforeach
								@endif
							</div>
						</div>
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
								<label>What you need to do</label>
								<div>
									{{ $lo_corrective_action_plan ?? '' }}
								</div>
							</div>
						</div>
						
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
							<label>Completed By</label>
								<div class="mt-1">
									{{ Carbon::parse($lo_corrective_completed_by)->format('d M Y')}}
								</div>
							</div>
						</div>
						
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12"><label>Final checks</label></div>
						</div>
						<div class="row">
							<div class="col-md-12">{{ $lo_corrective_action_plan_second_check ?? '' }}</div>
						</div>
						
						<div class="row">
							<div class="col-md-12">
								@if(!empty($corrective_action_files))
									<div class="d-flex flex-wrap gap-3">
										@foreach($corrective_action_files as $fileurl)
											<div class="cheklist-reply-images">
												<img src="{{ $fileurl['url'] ?? '' }}" style="max-width: 150px; height: auto; border: 1px solid #ccc; padding: 5px;">
											</div>
										@endforeach
									</div>
								@endif
							</div>
						</div>
						
						{{--<div class="row">
							<div class="col-md-12">
								<label class="d-block mb-2 fw-bold">Final checks</label>

								@if(!empty($corrective_action_files))
									<div class="d-flex flex-wrap gap-3">
										@foreach($corrective_action_files as $fileurl)
											<div class="cheklist-reply-images">
												<img src="{{ $fileurl['url'] ?? '' }}" style="max-width: 150px; height: auto; border: 1px solid #ccc; padding: 5px;">
											</div>
										@endforeach
									</div>
								@endif
							</div>
						</div>--}}

					
					<input type="hidden" id="location_id" value="{{ $location_id ?? ''}}">
					<input type="hidden" id="task_id" value="{{ $task_id ?? ''}}">
					<input type="hidden" id="checklist_id" value="{{ $checklist_id  ?? ''}}">
					<input type="hidden" id="subchecklist_id" value="{{ $subchecklist_id ?? ''}}">
					<input type="hidden" id="type" value="{{ $type ?? ''}}">
					<input type="hidden" id="tab" value="{{ $tab ?? ''}}">
						
					</div>
					
					
					
					{{--<div class="sticky-footer">
						<button class="submitChecklist">Submit checklist</button>
					</div>--}}
				</div>
			</section>
		</div>
    </div>
	@if(auth()->user()->user_type == 1 && $inspector_action == 0)
	<div class="checklist-question-sticky-footer">
		<div class="clearfix"></div>
		<div class="footer-content question-navigation d-flex justify-content-between">
			<button class="reject-class-button inspector-rejected">Reject</button>
			<button class="ms-auto inspector-approve">Approve</button>
		</div>
	</div>
	@endif
	
	@if(auth()->user()->user_type == 3 && $los_action == 0)
		<div class="checklist-question-sticky-footer">
		<div class="clearfix"></div>
		<div class="footer-content question-navigation d-flex justify-content-between">
			<button class="reject-class-button inspector-rejected">Reject</button>
			<button class="ms-auto inspector-approve">Approve</button>
		</div>
	</div>
	@endif
	
@endsection 
@section('scripts')
<script src="{{ url('front-assets/css/bootstrap.min.css') }}"></script>
	{{--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>--}}
		{{--<script src="{{url('front-assets/js/moment.min.js') }}"></script>
<script src="{{url('front-assets/js/bootstrap-datetimepicker.min.js') }}"></script>--}}
<script>
$(document).ready(function() {
	/*$('.datetimepicker').datetimepicker({
		format: 'YYYY-MM-DD HH:mm' // Adjust format as needed
	});*/
   
   $(document).on('click','.inspector-agree', function(){
	   var task_id = $('#task_id').val();
	   var checklist_id = $('#checklist_id').val();
	   var subchecklist_id = $('#subchecklist_id').val();
	   var location_id = $('#location_id').val();
	   var inspector_action = 1;
	   //alert(lo_direct_approve);
	   var URL = "{{ route('submit-inspector-status') }}";
	   $.ajax({
			url: URL,
			type: "POST",
			data: {task_id:task_id,checklist_id:checklist_id,subchecklist_id:subchecklist_id, inspector_action:inspector_action,_token: csrfToken},
			dataType: 'json',
			success: function(response) {
				if(response.message=='success')
				{
					localStorage.setItem('insFinalApproved', 1);
					
					var active = 1;
					@if(auth()->user()->user_type == 1)
					{
						var baseUrl = "{{ url('/location-details') }}";
						var redirectUrl = baseUrl + '/'+ location_id ;
					}
					@endif
					
					@if(auth()->user()->user_type == 3)
					{
						var baseUrl = "{{ url('/los-task-status') }}";
						var redirectUrl = baseUrl + '/'+ active ;
					}
					@endif
					
					window.location.href = redirectUrl;
				}
				/*let location_id = response.location_id;
				let category_id = response.category_id;
				
				var baseUrl = "{{ url('/location-owner') }}";
				var redirectUrl = baseUrl + '/'+ location_id + '/' + category_id;
				window.location.href = redirectUrl;*/
				
			},
		});
	});
	
	$(document).on('click','.inspector-approve', function(){
	   var task_id = $('#task_id').val();
	   var checklist_id = $('#checklist_id').val();
	   var subchecklist_id = $('#subchecklist_id').val();
	   var location_id = $('#location_id').val();
	    @if(auth()->user()->user_type == 1)
			var inspector_action = 1;
		    var los_action = '';
	    @elseif(auth()->user()->user_type == 3)
			var los_action = 1;
			var inspector_action = '';
		@endif	
		
	   //alert(lo_direct_approve);
	   var URL = "{{ route('submit-inspector-approved') }}";
	   $.ajax({
			url: URL,
			type: "POST",
			data: {task_id:task_id,checklist_id:checklist_id,subchecklist_id:subchecklist_id,inspector_action:inspector_action,los_action:los_action, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				if(response.message=='success')
				{
					localStorage.setItem('insFinalApproved', 1);
					
					var active = 1;
					@if(auth()->user()->user_type == 1)
					{
						var baseUrl = "{{ url('/location-details') }}";
						var redirectUrl = baseUrl + '/'+ location_id ;
					}
					@endif
					
					@if(auth()->user()->user_type == 3)
					{
						var baseUrl = "{{ url('/los-task-status') }}";
						var redirectUrl = baseUrl + '/'+ active ;
					}
					@endif
					
					window.location.href = redirectUrl;
				}
			},
		});
	});
	
	$(document).on('click','.inspector-rejected', function(){
	   var task_id = $('#task_id').val();
	   var checklist_id = $('#checklist_id').val();
	   var subchecklist_id = $('#subchecklist_id').val();
	   var location_id = $('#location_id').val();
	   //var inspector_action = 2;
	   
	    @if(auth()->user()->user_type == 1)
			var inspector_action = 2;
		    var los_action = 2;
	    @elseif(auth()->user()->user_type == 3)
			var los_action = 2;
			var inspector_action = 2;
		@endif	
	   
	   //alert(lo_direct_approve);
	   var URL = "{{ route('submit-inspector-approved') }}";
	   $.ajax({
			url: URL,
			type: "POST",
			data: {task_id:task_id,checklist_id:checklist_id,subchecklist_id:subchecklist_id,inspector_action:inspector_action,los_action:los_action, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				if(response.message=='success')
				{
					localStorage.setItem('insFinalRejected', 1);
					var active = 1;
					@if(auth()->user()->user_type == 1)
					{
						var baseUrl = "{{ url('/location-details') }}";
						var redirectUrl = baseUrl + '/'+ location_id ;
					}
					@endif
					
					@if(auth()->user()->user_type == 3)
					{
						var baseUrl = "{{ url('/los-task-status') }}";
						var redirectUrl = baseUrl + '/'+ active ;
					}
					@endif
					
					window.location.href = redirectUrl;
				}
			},
		});
	});
	
});
</script>
@endsection

