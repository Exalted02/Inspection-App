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
									{{ $rejected_region ?? '' }}
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
							<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap">
							<img src="{{ url('uploads/profile/'. $userData->get_user->id .'/inspector/' . $userData->get_user->profile_image) }}" class="rounded-profile-img me-2" alt="Profile image">
							<span style="margin-top:6px;">By (IA) {{ $userData->get_user->name ?? ''}}</span><span style="margin-top:6px;">{{ Carbon::parse($created_at)->format('d M, Y h:i A')}}</span></div>
						</div>
						
						@if(!empty($lo_corrective_action_plan))
						<hr class="horizontal-line">
						@endif
						
						@if(!empty($lo_corrective_action_plan))
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
								<label>Corrective</label>
								<div class="mt-1">
									{{ $lo_corrective_action_plan ?? '' }}
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
							<div class="col-md-6 text-ia-lo-los d-flex justify-content-between flex-wrap">
							<img src="{{ url('uploads/profile/'. $corrective_action_data->get_lo->id .'/locationowner/' . $corrective_action_data->get_lo->profile_image) }}" class="rounded-profile-img me-2" alt="Profile image">
							<span>By (LO) {{ $corrective_action_data->get_lo->name ?? ''}} </span><span>{{ !empty($corrective_action_data->created_at) ? change_date_format($lo_corrective_completed_by, 'Y-m-d H:i:s', 'd M Y, h:i A') : ''}}</span></div>
						</div>
						@endif
						
						@if($corrective_action_data)
							@if($corrective_action_data->approved_status == 1 || $corrective_action_data->approved_status == 2 || $corrective_action_data->rejected_status == 1 || $corrective_action_data->los_action == 2)
							<hr class="horizontal-line">
							@endif
						@endif
						
						@if($corrective_action_data)
						
						@if($corrective_action_data->approved_status == 1 || $corrective_action_data->approved_status == 2 || $corrective_action_data->rejected_status == 1 || $corrective_action_data->los_action == 2)
							<div class="row">
								<div class="col-md-12"><h4><strong>Approval</strong></h4></div>
							</div>
						@endif
						<div class="row">
							
							@if($corrective_action_data->approved_status == 1)
								<div class="col-md-12">
								<span class="show-agree-status">Approved</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap">
								<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id .'/inspector/' . $corrective_action_data->get_inspector->profile_image) }}" class="rounded-profile-img me-2" alt="Profile image">
								<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}}</span><span>{{ Carbon::parse($corrective_action_data->inspector_action_date)->format('d M, Y h:i A')}}</span></div>
								
							@elseif($corrective_action_data->approved_status == 2)
								<div class="col-md-12">
								<span class="show-agree-status">Approved</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap">
								<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id .'/locationownersupervisor/' . $corrective_action_data->get_los->profile_image) }}" class="rounded-profile-img me-2" alt="Profile image">
								<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}}</span><span>{{ Carbon::parse($corrective_action_data->los_action_date)->format('d M, Y h:i A')}}</span></div>
							
							@endif
							
							
							@if($corrective_action_data->rejected_status == 1)
								<div class="col-md-12 vertical-gap">
								<span class="show-reject-status">Rejected</span>:&nbsp;&nbsp;<span>{{ $corrective_action_data->ia_los_first_rejected_reason ?? ''  }}</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap">
								<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id .'/inspector/' . $corrective_action_data->get_inspector->profile_image) }}" class="rounded-profile-img me-2" alt="Profile image">
								<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}}</span><span>{{ Carbon::parse($corrective_action_data->inspector_action_date)->format('d M, Y h:i A')}}</span></div>
							
							@elseif($corrective_action_data->los_action == 2)
								<div class="col-md-12 vertical-gap">
								<span class="show-reject-status">Rejected</span>:&nbsp;&nbsp;<span>{{ $corrective_action_data->ia_los_first_rejected_reason ?? ''  }}</span>
								</div>
								<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap">
								<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id .'/locationownersupervisor/' . $corrective_action_data->get_los->profile_image) }}" class="rounded-profile-img me-2" alt="Profile image">
								<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}}</span><span>{{ Carbon::parse($corrective_action_data->los_action_date)->format('d M, Y h:i A')}}</span></div>
							@endif
							
						</div>
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
				</div>
			</section>
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
});
</script>
@endsection

