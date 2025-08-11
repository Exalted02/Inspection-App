@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($categoryData);die;
  use Carbon\Carbon;
 $rejected_region = '';
 $image_arr = [];
 
 $userData = App\Models\Task_lists::with('get_user')->where('id', $task_id)->first();
 
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
	 
	$corrective_action = App\Models\Task_list_corrective_action::with('get_inspector','get_lo','get_los')->where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
	$corrective_plan  = $corrective_action ? $corrective_action->lo_corrective_action_plan : '';
	
	$inspector_action_date  = $corrective_action ? $corrective_action->inspector_action_date : '';
	
	$lo_direct_approve = $corrective_action ? $corrective_action->lo_direct_approve : '';
	
	$lo_corrective_completed_by = $corrective_action ? $corrective_action->lo_completed_by : '';
	
	//---------------------------------------------
	$corrective_action_primary_id = $corrective_action ? $corrective_action->id : '';
	 
		 $corrective_first_action_file_data = App\Models\Task_list_corrective_action_file::where('task_list_corrective_actions_id', $corrective_action_primary_id)->where('status', 1)->get();
		 
		 $corrective_first_action_files = [];
		 if($corrective_first_action_file_data->isNotEmpty())
		 {
			 foreach($corrective_first_action_file_data as $corrective_files)
			 {
				$corrective_first_action_files[] = [
					'url' => url('uploads/corrective_action/' .$corrective_files->file),
				];
			 }
		 }
	 //---------------------------------------------
	$corrective_dtls_data = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->first();
	
	$final_check_data = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->orderBy('id','asc')->skip(1)->take(PHP_INT_MAX)->get();
	 
	$corrective_detls_order = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->orderBy('id')->skip(1)->take(PHP_INT_MAX)->get(['order']);
	$max_order = $corrective_detls_order->max('order');
	
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
					'url'=> url('uploads/reject-files/subchecklist/' .$image->file ),
			 ];
		}
		
		$corrective_action = App\Models\Task_list_corrective_action::with('get_inspector','get_lo','get_los')->where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
		$corrective_plan  = $corrective_action ? $corrective_action->lo_corrective_action_plan : '';
		
		$inspector_action_date  = $corrective_action ? $corrective_action->inspector_action_date : '';
		
		$lo_direct_approve = $corrective_action ? $corrective_action->lo_direct_approve : '';
		
		$lo_corrective_completed_by = $corrective_action ? $corrective_action->lo_completed_by : '';
		
		//---------------------------------------------
		$corrective_action_primary_id = $corrective_action ? $corrective_action->id : '';
	 
		 $corrective_first_action_file_data = App\Models\Task_list_corrective_action_file::where('task_list_corrective_actions_id', $corrective_action_primary_id)->where('status', 1)->get();
		 
		 $corrective_first_action_files = [];
		 if($corrective_first_action_file_data->isNotEmpty())
		 {
			 foreach($corrective_first_action_file_data as $corrective_files)
			 {
				$corrective_first_action_files[] = [
					'url' => url('uploads/corrective_action/' .$corrective_files->file),
				];
			 }
		 }
	 //---------------------------------------------
	 
		$corrective_dtls_data = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->first();
		
		$final_check_data = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->orderBy('id','asc')->skip(1)->take(PHP_INT_MAX)->get();
		 
		$corrective_detls_order = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->orderBy('id')->skip(1)->take(PHP_INT_MAX)->get(['order']);
		$max_order = $corrective_detls_order->max('order');
	}
 }
 
	$taskData = App\Models\Task_lists::where('id',$task_id)->first();
	$task_location_id = $taskData ? $taskData->location_id : '';
	$task_category_id = $taskData ? $taskData->category_id : '';
	
	//echo "<pre>";print_r($final_check_data);die;
	//echo "<pre>";print_r($corrective_first_action_files);die;
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
							<div class="col-md-12">
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
							<div class="col-md-6 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (IA) {{ $userData->get_user->name ?? ''}} </span><span>{{ change_date_format($created_at, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
						</div>
						<hr class="horizontal-line">
						
						<div class="row mt" style="margin-top: 1rem !important;">
							<div class="col-md-12">
								<label>Requirement to solve it</label>
								<p class="text-muted mb-0">{{ $corrective_plan ?? ''}}</p>
							</div>
						</div>
						@php 
						$loopCnt = 0;
						@endphp
						<div class="row">
							<div class="col-md-12">
								@if(!empty($corrective_first_action_files))
									<div class="d-flex flex-wrap gap-3">
										@foreach($corrective_first_action_files as $fileurl)
											@php 
												$url = $fileurl['url'] ?? '';
												$extension = pathinfo($url, PATHINFO_EXTENSION);
												$extension = strtolower($extension);
												$loopCnt++;
											@endphp
											
											@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<div class="cheklist-reply-images">
												<img src="{{ $fileurl['url'] ?? '' }}" style="max-width: 150px; height: auto; border: 1px solid #ccc; padding: 5px;">
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
						
						{{--<div class="row" style="margin-top: 1rem !important;">
							<div class="col-md-12">
								<label>Completed By</label>
								<div class="mt-1">
								{{ Carbon::parse($inspector_action_date)->format('d M Y') }}
								</div>
							</div>
						</div>--}}
						
						<div class="row">
							<div class="col-md-6 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LO) {{ $corrective_action->get_lo->name ?? ''}}</span><span>{{ change_date_format($corrective_action->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
						</div>
						
						@if($lo_direct_approve == 0)
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
							<label>Completed By</label>
								<div class="mt-1">
									{{ change_date_format($lo_corrective_completed_by, 'Y-m-d H:i:s', 'd M Y, h:i A')}}
								</div>
							</div>
						</div>
						@endif
						
						@if($corrective_dtls_data)
						</br>
						@if($corrective_dtls_data->approved_status == 1 || $corrective_dtls_data->approved_status == 2 || $corrective_dtls_data->rejected_status == 1 || $corrective_dtls_data->rejected_status == 2)
							<div class="row">
								<div class="col-md-12"><h4><strong>Approval</strong></h4></div>
							</div>
						@endif
						
						<div class="row">
							
							@if($corrective_dtls_data->approved_status == 1)
								<div class="col-md-12">
								<span class="show-agree-status">Approved</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (IA) {{ $corrective_action->get_inspector->name ?? ''}}</span><span>{{ change_date_format($corrective_dtls_data->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
								
							@elseif($corrective_dtls_data->approved_status == 2)
								<div class="col-md-12">
								<span class="show-agree-status">Approved</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LOS) {{ $corrective_action->get_los->name ?? ''}}</span><span>{{ change_date_format($corrective_dtls_data->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
							
							@endif
							
							
							@if($corrective_dtls_data->rejected_status == 1)
								<div class="col-md-12 vertical-gap">
								<span class="show-reject-status">Rejected</span>:<span class="reject_reply_reason">{{ $corrective_dtls_data->ia_los_rejected_reason ?? ''  }}</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (IA) {{ $corrective_action->get_inspector->name ?? ''}}</span><span>{{ change_date_format($corrective_dtls_data->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
							
							@elseif($corrective_dtls_data->rejected_status == 2)
								<div class="col-md-12 vertical-gap">
								<span class="show-reject-status">Rejected</span>:<span class="reject_reply_reason">{{ $corrective_dtls_data->ia_los_rejected_reason ?? ''  }}</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LOS) {{ $corrective_action->get_los->name ?? ''}}</span><span>{{ change_date_format($corrective_dtls_data->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
							@endif
							
						</div>
						@endif
						
						@if($final_check_data->isNotEmpty())
							<hr class="horizontal-line">
						@endif
					
						@if($final_check_data->isNotEmpty())
					@foreach($final_check_data as $val)
						@php 
							$corrective_final_files = App\Models\Task_list_corrective_action_file::where('task_list_corrective_actions_id', $val->task_list_corrective_action_id)->where('status', $val->order)->get();
							//echo "<pre>";print_r($corrective_final_files);die;
							
							$final_title = '';
							$final_title = getOrdinalTitle($val->order);
							if($max_order == $val->order)
							{
								$final_title = 'Final';
							}
							
						@endphp
						
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12"><label>Corrective</label></div>
								{{--<div class="col-md-12"><label>{{ $final_title }} checks</label></div>--}}
						</div>
						<div class="row">
							<div class="col-md-12"><p class="text-muted mb-0">{{ $val->lo_corrective_action_plan_final_checks ?? '' }}</p></div>
						</div>
						<div class="row">
							<div class="col-md-12">
								@if(!empty($corrective_final_files))
									<div class="d-flex flex-wrap gap-3">
										@foreach($corrective_final_files as $fileurl)
											@php 
												$url = url('uploads/corrective_action/' .$fileurl['file']) ?? '';
												$extension = pathinfo($url, PATHINFO_EXTENSION);
												$extension = strtolower($extension);
											@endphp
											
											@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
											<div class="cheklist-reply-images">
												<img src="{{ $url ?? '' }}" style="max-width: 150px; height: auto; border: 1px solid #ccc; padding: 5px;">
											</div>
											@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
											<div class="cheklist-reply-images">
											
											<video src="{{ $url ?? '' }}" controls style="max-width: 100px; height: auto; border: 1px solid #ccc; padding: 5px;" target="_blank"></video>
											</div>
											@endif
										@endforeach
									</div>
								@endif
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LO) {{ $corrective_action->get_lo->name ?? ''}} </span><span>{{ !empty($val->created_at) ? change_date_format($val->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') : ''}}</span></div>
						</div>
						
						@if($val->approved_status == 1 || $val->approved_status == 2 || $val->rejected_status == 1 || $val->rejected_status == 2)
							</br>
							@if($val->approved_status == 1 || $val->approved_status == 2 || $val->rejected_status == 1 || $val->rejected_status == 2)
								<div class="row">
									<div class="col-md-12"><h4><strong>Approval</strong></h4></div>
								</div>
							@endif
						@endif
						
						<div class="row">
							@if($val->approved_status == 1)
								<div class="col-md-12">
								<span class="show-agree-status">Approved</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (IA) {{ $corrective_action->get_inspector->name ?? ''}}</span><span>{{ change_date_format($val->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
							@elseif($val->approved_status == 2)
							
								<div class="col-md-12">
								<span class="show-agree-status">Approved</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LOS) {{ $corrective_action->get_los->name ?? ''}}</span><span>{{ change_date_format($val->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
							@endif
							
							@if($val->rejected_status == 1)
									<div class="col-md-12 vertical-gap">
									<span class="show-reject-status">Rejected</span>:<span class="reject_reply_reason">{{ $val->ia_los_rejected_reason ?? ''  }}</span>
									</div>
									<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (IA) {{ $corrective_action->get_inspector->name ?? ''}}</span><span>{{ change_date_format($val->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
								
								@elseif($val->rejected_status == 2)
									<div class="col-md-12 vertical-gap">
									<span class="show-reject-status">Rejected</span>:<span class="reject_reply_reason">{{ $val->ia_los_rejected_reason ?? ''  }}</span>
									</div>
									<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LOS) {{ $corrective_action->get_los->name ?? ''}}</span><span>{{ change_date_format($val->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
								@endif
						</div>
						<hr class="horizontal-line">
					@endforeach
					@endif
						
						{{--<hr class="horizontal-line">--}}
						{{--<form id="frmreply" action="{{ route('save-lo-reply-rejected-question') }}" enctype="multipart/form-data" method="post">--}}
							<div class="row" style="margin-top: 1rem !important;">
								<div class="col-md-12">
									<label>How to solve the issue ?</label>
									<textarea name="lo_corrective_action_plan" id="lo_corrective_action_plan" placeholder="Add some remarks (optional)" class="form-control"></textarea>
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
							<input type="hidden" id="location_id" value="{{ $task_location_id ?? ''}}">
								{{--<input type="hidden" id="category_id" value="{{ $task_category_id ?? ''}}">--}}
							
					</div>
					<div class="">
						<button class="sticky-footer location-owner-submit">Submit</button>
					</div>
				</div>
			</section>
		</div>
    </div>
	{{--<div class="sticky-footer">
		<button class="submitChecklist location-owner-approve">Submit checklist</button>
	</div>--}}
	{{--<div class="checklist-question-sticky-footer">
		<div class="clearfix"></div>
		<div class="footer-content question-navigation d-flex justify-content-between">
			<button class="reject-class-button location-owner-rejected">Reject</button>
			<button class="ms-auto location-owner-approve">Approve</button>
		</div>
	</div>--}}
	{{--</form>--}}
@endsection 
@section('scripts')
<script src="{{ url('front-assets/css/bootstrap.min.css') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
/*document.getElementById('lo_file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || "Upload image";
    document.querySelector('.upload-text').textContent = fileName;
});*/
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
	
$('#lo_file').on('change', function (e) {
    //let files = e.target.files;
	let files = Array.from(e.target.files); // new
	selectedFiles = files; // new
	//selectedFiles = [...selectedFiles, ...files];
    previewContainer.empty(); // Clear previous previews
	
	/*Array.from(files).forEach((file, index) => {
	  if (file) {
		let reader = new FileReader();
		reader.onload = function (e) {
			alert(e.target.result);
		  let previewHtml = '';
			
		  if (file.type.startsWith('image/')) {
			previewHtml = '<div class="preview-image-wrapper" data-index="' + index + '"><img src="' + e.target.result + '" class="preview-image" /><div class="remove-image" data-index="' + index + '">&times;</div></div>';
		  }
		  else if (file.type.startsWith('video/')) {
			previewHtml = '<div class="preview-image-wrapper" data-index="' + index + '"><video src="' + e.target.result + '" class="preview-image" controls style="max-width: 120px; max-height: 120px;"></video><div class="remove-image" data-index="' + index + '">&times;</div></div>';
		  }
		  alert(previewHtml);
		  previewContainer.append(previewHtml);
		};

		//reader.readAsDataURL(file);
	  }
	});*/
	
    Array.from(files).forEach((file, index) => {
      //if (file && file.type.startsWith('image/')) {
      if (file) {
        let reader = new FileReader();
		//$('#preview-container').show();
		let imgHtml = '';
        reader.onload = function (e) {
			//alert(file.type);
			if (file.type.startsWith('image/')) {
			  let imgHtml = '<div class="preview-image-wrapper" data-index="' + index +'"><img src="' + e.target.result + '" class="preview-image"><div class="remove-image" data-index="' + index +'">&times;</div></div>';
			  previewContainer.append(imgHtml);
			}
			else if (file.type.startsWith('video/')) {
				imgHtml = '<div class="preview-image-wrapper" data-index="' + index + '"><video src="' + e.target.result + '" class="preview-image" controls style="max-width: 120px; max-height: 120px;"></video><div class="remove-image" data-index="' + index + '">&times;</div></div>';
				previewContainer.append(imgHtml);
			}
			  //previewContainer.append(imgHtml);
        };

        reader.readAsDataURL(file);
      }
    });
	
	//updateFileInput();
  });


  // Delegate remove button click
  previewContainer.on('click', '.remove-image', function () {
	const indexToRemove = $(this).data('index');
	//alert(indexToRemove);alert(selectedFiles);
    $(this).parent().remove();
	selectedFiles[indexToRemove] = null;
	selectedFiles = selectedFiles.filter(file => file !== null);
  });
   
   $(document).on('click','.location-owner-submit', function(){
	   //e.preventDefault();
	   var task_id = $('#task_id').val();
	   var checklist_id = $('#checklist_id').val();
	   var subchecklist_id = $('#subchecklist_id').val();
	   var type = $('#type').val();
	   //var category_id = $('#category_id').val();
	   var location_id = $('#location_id').val();
	   
	   let lo_corrective_action_plan = $('#lo_corrective_action_plan').val().trim();
	   
	   let hidden_set_date = $('#hidden_set_date').val();
	   let hidden_set_time = $('#hidden_set_time').val();
	   let lo_direct_approve = $('#lo_direct_approve').is(':checked');
	   
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
	   //alert(files.length);
	    /*if (files.length === 0) {
			alert('Please select at least one image.');
			return;
		}*/
		
		
		let formData = new FormData();

		// Append all selected files to formData
		/*$.each(files, function (index, file) {
			formData.append('lo_file[]', file);
		});*/
		
		selectedFiles.forEach(file => {
			formData.append('lo_file[]', file);
		});
		
		if ($(this).prop('disabled')) {
			return;
		}
		
		$('.location-owner-submit').prop('disabled', true);
		$('.location-owner-submit').html('<i class="fas fa-spinner fa-spin"></i> &nbsp;&nbsp;Submitting...').prop('disabled', true);
		
		//alert(csrfToken) // show ok 
		// Optional: Add other data
		formData.append('task_id', task_id);
		formData.append('checklist_id', checklist_id);
		formData.append('subchecklist_id', subchecklist_id);
		formData.append('type', type);
		formData.append('content', lo_corrective_action_plan);
		formData.append('lo_direct_approve', lo_direct_approve);
		formData.append('hidden_set_date', hidden_set_date);
		formData.append('hidden_set_time', hidden_set_time);
		formData.append('inspector_action', 2);
		formData.append('los_action', 2);
		formData.append('_token', csrfToken);
		var URL = "{{ route('save-lo-reply-rejected-question') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: formData,
			contentType: false,
			processData: false,  
			success: function(response) {
				//alert(response.message);
				if(response.message=='success')
				{
					localStorage.setItem('loFinalSubmited', 1); 
					//history.back();
					var active = 1;
					var activeTab = 0;
					var baseUrl = "{{ url('/lo-task-status') }}";
					var redirectUrl = baseUrl + '/'+ location_id +  '/' + active ;
					window.location.href = redirectUrl;
				}
				
			},
			complete: function() {
				$('.location-owner-submit').html('Submit').prop('disabled', false);
				//$('.location-owner-submit').prop('disabled', false);
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
	
	$(document).on('click','.location-owner-rejected', function(){
		var task_id = $('#task_id').val();
	   var checklist_id = $('#checklist_id').val();
	   var subchecklist_id = $('#subchecklist_id').val();
	   var type = $('#type').val();
	   //var category_id = $('#category_id').val();
	   var location_id = $('#location_id').val();
	   
	   let lo_corrective_action_plan = $('#lo_corrective_action_plan').val().trim();
	   if(lo_corrective_action_plan=='')
	   {
		   $('#action_plan').fadeIn().delay(2000).fadeOut();
		   return false;
	   }
	   
	   let files = $('#lo_file')[0].files;
	   //alert(files.length);
	    /*if (files.length === 0) {
			alert('Please select at least one image.');
			return;
		}*/
		
		
		let formData = new FormData();

		// Append all selected files to formData
		/*$.each(files, function (index, file) {
			formData.append('lo_file[]', file);
		});*/
		
		selectedFiles.forEach(file => {
			formData.append('lo_file[]', file);
		});
		
		//alert(csrfToken) // show ok 
		// Optional: Add other data
		formData.append('task_id', task_id);
		formData.append('checklist_id', checklist_id);
		formData.append('subchecklist_id', subchecklist_id);
		formData.append('type', type);
		formData.append('content', lo_corrective_action_plan);
		formData.append('inspector_action', 0);
		formData.append('los_action', 0);
		formData.append('_token', csrfToken);
		var URL = "{{ route('save-lo-reply-rejected-question') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: formData,
			contentType: false,
			processData: false,  
			success: function(response) {
				//alert(response.message);
				if(response.message=='success')
				{
					//history.back();
					var activeTab = 0;
					var baseUrl = "{{ url('/location-owner') }}";
					var redirectUrl = baseUrl + '/'+ location_id + '/' + task_id + '/' + activeTab ;
					window.location.href = redirectUrl;
				}
				
			},
		});
	});
});

function updateFileInput() {
  const dataTransfer = new DataTransfer();
  selectedFiles.forEach(file => {
    if (file) dataTransfer.items.add(file);
  });
  document.getElementById('lo_file').files = dataTransfer.files;
}
</script>
@endsection

