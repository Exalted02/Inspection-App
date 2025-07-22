@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($categoryData);die;
 
 use Carbon\Carbon;
 $rejected_region = '';
 $image_arr = [];
 
 $lo_corrective_action_plan = '';
 $lo_corrective_completed_by = '';
 
 $userData = App\Models\Task_lists::with('get_user')->where('id', $task_id)->first();
 //echo "<pre>";print_r($userData);die;
 $created_at = '';
 
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
	 
	 $corrective_action_data = App\Models\Task_list_corrective_action::with('get_lo','get_los')->where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
	 //echo "<pre>";print_r($corrective_action_data);die;
	 //echo $corrective_action_data->created_at; die;
	 
	 $lo_corrective_action_plan = $corrective_action_data ? $corrective_action_data->lo_corrective_action_plan : '';
	 
	 $corrective_action_primary_id = $corrective_action_data ? $corrective_action_data->id : '';
	 
	 $lo_corrective_completed_by = $corrective_action_data ? $corrective_action_data->lo_completed_by : '';
	 
	 $corrective_action_file_data = App\Models\Task_list_corrective_action_file::where('task_list_corrective_actions_id', $corrective_action_primary_id)->where('status', 1)->get();
	 
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
	 
	 $lo_corrective_action_plan_second_check = $corrective_action_data ? $corrective_action_data->lo_corrective_action_plan_second_check : '';
	 
	 $corrective_dtls_data = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->first();
	 
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
				'url'=> url('uploads/reject-files/subchecklist/' .$image->file),
			 ];
		}
	}
	
	$corrective_action_data = App\Models\Task_list_corrective_action::with('get_lo','get_los')->where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->where('subchecklist_id',  $taskSubChecklist->subchecklist_id)->first();
	 
	$lo_corrective_action_plan = $corrective_action_data ? $corrective_action_data->lo_corrective_action_plan : '';
	 
	$lo_corrective_completed_by = $corrective_action_data ? $corrective_action_data->lo_completed_by : '';
	
	
	$corrective_action_primary_id = $corrective_action_data ? $corrective_action_data->id : '';
	
	$corrective_action_file_data = App\Models\Task_list_corrective_action_file::where('task_list_corrective_actions_id', $corrective_action_primary_id)->where('status', 1)->get();
	 
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
	 
	 $corrective_dtls_data = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->first();
 }
 //echo $lo_corrective_completed_by;die;
 //echo "<pre>";print_r($image_arr);die;
 //echo "<pre>";print_r($corrective_action_files);die;
 
 $loopCnt = 0;
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
							<div class="col-md-12">
								<label>Reason</label>
								<div class="mt-1">
									<p class="text-muted mb-0">{{ $rejected_region ?? '' }}</p>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-12">
								@if(!empty($image_arr))
								
									@foreach($image_arr as $key => $url)
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
										<video controls src="{{ $url['url'] ?? '' }}"width="90" height="90"></video>
										@endif
									</div>
									@endforeach
								
								@endif
							</div>
							<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (IA) {{ $userData->get_user->name ?? ''}}</span><span>{{ Carbon::parse($created_at)->format('d M, Y h:i A')}}</span></div>
						</div>
						
						@if(!empty($lo_corrective_action_plan))
						<hr class="horizontal-line">
						@endif
						
						@if(!empty($lo_corrective_action_plan))
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
								<label>Corrective</label>
								<div class="mt-1">
									<p class="text-muted mb-0">{{ $lo_corrective_action_plan ?? '' }}</p>
								</div>
							</div>
						</div>
						@endif
						
						@php 
						$loopCnt = 0;
						@endphp
						
						@if(!empty($corrective_action_files))
						<div class="row">
							<div class="col-md-12">
								@if(!empty($corrective_action_files))
									<div class="d-flex flex-wrap gap-3">
										@foreach($corrective_action_files as $fileurl)
											@php 
												$url = $fileurl['url'] ?? '';
												$extension = pathinfo($url, PATHINFO_EXTENSION);
												$extension = strtolower($extension);
												$loopCnt++;
											@endphp
											@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) 
											<div class="cheklist-reply-images">
												<img src="{{ $fileurl['url'] ?? '' }}" style="max-width: 150px; height: auto; border: 1px solid #ccc; padding: 5px;" target="_blank">
											</div>
											@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<div class="cheklist-reply-images">
											
											<video src="{{ $fileurl['url'] ?? '' }}" controls style="max-width: 100px; height: auto; border: 1px solid #ccc; padding: 5px;" target="_blank"></video>
											</div>
											@endif
										@endforeach
									</div>
								@endif
							</div>
						</div>
						@endif
						
						@if(!empty($lo_corrective_completed_by))
						{{--<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
							<label>Completed By</label>
								<div class="mt-1">
									{{ Carbon::parse($lo_corrective_completed_by)->format('d M Y')}}
								</div>
							</div>
						</div>--}}
						@endif
						
						@if($corrective_action_data)
						<div class="row">
							<div class="col-md-6 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LO) {{ $corrective_action_data->get_lo->name ?? ''}} </span><span>{{ !empty($corrective_action_data->created_at) ? change_date_format($lo_corrective_completed_by, 'Y-m-d H:i:s', 'd M Y, h:i A') : ''}}</span></div>
						</div>
						@endif
						
						@if($corrective_dtls_data)
						
						@if($corrective_dtls_data->approved_status == 1 || $corrective_dtls_data->approved_status == 2 || $corrective_dtls_data->rejected_status == 1 || $corrective_dtls_data->rejected_status == 2)
							{{--<hr class="horizontal-line">--}}
							@endif
						@endif
						
						@if($corrective_action_data)
						
						@if($corrective_dtls_data->approved_status == 1 || $corrective_dtls_data->approved_status == 2 || $corrective_dtls_data->rejected_status == 1 || $corrective_dtls_data->rejected_status == 2)
							<div class="row">
								<div class="col-md-12"><h4><strong>Approval</strong></h4></div>
							</div>
						@endif
						<div class="row">
							
							@if($corrective_action_data->approved_status == 1)
								<div class="col-md-12">
								<span class="show-agree-status">Approved</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}}</span><span>{{ Carbon::parse($corrective_action_data->inspector_action_date)->format('d M, Y h:i A')}}</span></div>
								
							@elseif($corrective_action_data->approved_status == 2)
								<div class="col-md-12">
								<span class="show-agree-status">Approved</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}}</span><span>{{ Carbon::parse($corrective_action_data->los_action_date)->format('d M, Y h:i A')}}</span></div>
							
							@endif
							
							
							@if($corrective_action_data->rejected_status == 1)
								<div class="col-md-12 vertical-gap">
								<span class="show-reject-status">Rejected</span>:&nbsp;&nbsp;<span>{{ $corrective_action_data->ia_los_first_rejected_reason ?? ''  }}</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}}</span><span>{{ Carbon::parse($corrective_action_data->inspector_action_date)->format('d M, Y h:i A')}}</span></div>
							
							@elseif($corrective_action_data->los_action == 2)
								<div class="col-md-12 vertical-gap">
								<span class="show-reject-status">Rejected</span>:&nbsp;&nbsp;<span>{{ $corrective_action_data->ia_los_first_rejected_reason ?? ''  }}</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}}</span><span>{{ Carbon::parse($corrective_action_data->los_action_date)->format('d M, Y h:i A')}}</span></div>
							@endif
							
						</div>
						@endif
						
						@if($corrective_action_data)
							@if($corrective_action_data->approved_status == 1 || $corrective_action_data->approved_status == 2 || $corrective_action_data->rejected_status == 1 || $corrective_action_data->los_action == 2)
							<hr class="horizontal-line">
							@endif
						@endif
						
						
						{{--<div class="row">
							<div class="col-12 col-md-12">
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
			<button class="ms-auto inspector-agree">Agree</button>
		</div>
	</div>
	@endif
	
	@if(auth()->user()->user_type == 3 && $los_action == 0)
	<div class="checklist-question-sticky-footer">
		<div class="clearfix"></div>
		<div class="footer-content question-navigation d-flex justify-content-between">
			<button class="reject-class-button inspector-rejected">Reject</button>
			<button class="ms-auto inspector-agree">Agree</button>
		</div>
	</div>
	@endif
	
	<!-- =-=-=-=-=-=-= Rejected reason =-=-=-=-=-=-= -->
      <div class="modal fade" id="rejected_reason" tabindex="-1" role="dialog" aria-hidden="true">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                  <h3 class="modal-title" id="lineModalLabel">{{ __('Rejected reason') }}</h3>
               </div>
               <div class="modal-body">
                  
                  <!-- content goes here -->
                  <form>
					<input type="hidden" id="id" name="id">
					<input type="hidden" id="location_id" name="location_id" value="{{ $location_id ?? ''}}">
					@csrf
                    
                    <div class="form-group  col-md-12  col-sm-12">
                        <label>{{ __('Reason') }}</label>
                        <textarea name="ia_los_first_rejected_reason" id="ia_los_first_rejected_reason" class="form-control"></textarea>
						<span id="reason_error" style="display:none;  color: red;"></span>
                    </div>
					<div class="clearfix"></div>
                    <div class="col-md-12  col-sm-12 form-group">
                        <button type="button" class="btn1 btn-block inspector-rejected-submit button-color popup-submit-btn">Submit</button>
                    </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
	
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
	   var tab = $('#tab').val();
	   //alert(tab);
	   //alert(lo_direct_approve);
	   var URL = "{{ route('submit-inspector-status') }}";
	   $.ajax({
			url: URL,
			type: "POST",
			data: {task_id:task_id,checklist_id:checklist_id,subchecklist_id:subchecklist_id, inspector_action:inspector_action,_token: csrfToken},
			dataType: 'json',
			success: function(response) {
				// alert(response.ins_action);
				// alert(response.los_action);
				if(response.message=='success')
				{
					if(tab == 'corrective-action')
					{
						localStorage.setItem('insActionApproved', 1);
					}
					
					if(tab == 'corrective-plan')
					{
						localStorage.setItem('insPlanApproved', 1);
					}
					
					var active = 0;
					@if(auth()->user()->user_type == 1)
					{
						//var baseUrl = "{{ url('/location-details') }}";
						//var redirectUrl = baseUrl + '/'+ location_id;
						var baseUrl = "{{ url('/inspector-filter') }}";
						var redirectUrl = baseUrl + '/'+ location_id + '/' +active  ;
					}
					@endif
					
					@if(auth()->user()->user_type == 3)
					{
						var baseUrl = "{{ url('/los-task-status') }}";
						var redirectUrl = baseUrl + '/'+ location_id + '/' +active  ;
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
	
	$(document).on('click','.inspector-rejected', function(){
		$('#rejected_reason').modal('show');
	});
	
	$(document).on('click','.inspector-rejected-submit', function(){
	   var task_id = $('#task_id').val();
	   var checklist_id = $('#checklist_id').val();
	   var subchecklist_id = $('#subchecklist_id').val();
	   var location_id = $('#location_id').val();
	   var inspector_action = 2;
	   var tab = $('#tab').val();
	   var first_rejected_reason = $('#ia_los_first_rejected_reason').val();
	   
	   if (first_rejected_reason === '') {
			$('#reason_error').text('Please enter reason').fadeIn().delay(2000).fadeOut();
			return false;
		}
	   
	   
	    if ($(this).prop('disabled')) {
			return;
		}
	   $('.inspector-rejected-submit').prop('disabled', true);
	   $('.inspector-rejected-submit').html('<i class="fas fa-spinner fa-spin"></i>&nbsp;&nbsp;Submitting...').prop('disabled', true);
	   //alert(lo_direct_approve);
	   var URL = "{{ route('submit-inspector-status') }}";
	   $.ajax({
			url: URL,
			type: "POST",
			data: {task_id:task_id,checklist_id:checklist_id,subchecklist_id:subchecklist_id,first_rejected_reason:first_rejected_reason,inspector_action:inspector_action, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				if(response.message=='success')
				{
					if(tab == 'corrective-action')
					{
						localStorage.setItem('insActionRejected', 1);
					}
					if(tab == 'corrective-plan')
					{
						localStorage.setItem('insPlanRejected', 1);
					}
					
					var active = 1;
					@if(auth()->user()->user_type == 1)
					{
						//var baseUrl = "{{ url('/location-details') }}";
						//var redirectUrl = baseUrl + '/'+ location_id ;
						var baseUrl = "{{ url('/inspector-filter') }}";
						var redirectUrl = baseUrl + '/'+ location_id + '/' +active  ;
					}
					@endif
					
					@if(auth()->user()->user_type == 3)
					{
						var baseUrl = "{{ url('/los-task-status') }}";
						var redirectUrl = baseUrl + '/'+ location_id + '/' +active  ;
					}
					@endif
					
					window.location.href = redirectUrl;
				}
			},
			complete: function() {
				$('.inspector-rejected-submit').html('Submit');
				//$('.inspector-rejected-submit').prop('disabled', false);
			}
		});
	});
});
</script>
@endsection

