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
	 
	 $corrective_action_data = App\Models\Task_list_corrective_action::with('get_inspector','get_lo','get_los')->where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
	 //echo "<pre>";print_r($corrective_action_data);die;
	 
	 $lo_corrective_action_plan = $corrective_action_data ? $corrective_action_data->lo_corrective_action_plan : '';
	 
	 $corrective_action_primary_id = $corrective_action_data ? $corrective_action_data->id : '';
	 
	 $lo_direct_approve = $corrective_action_data ? $corrective_action_data->lo_direct_approve : '';
	 
	 $lo_corrective_completed_by = $corrective_action_data ? $corrective_action_data->lo_completed_by : '';
	 
	 $lo_corrective_action_plan_second_check = $corrective_action_data ? $corrective_action_data->lo_corrective_action_plan_second_check : '';
	 
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
	 
	 $corrective_action_second_file_data = App\Models\Task_list_corrective_action_file::where('task_list_corrective_actions_id', $corrective_action_primary_id)->where('status', 2)->get();
	 
	 $corrective_action_second_files = [];
	 if($corrective_action_second_file_data->isNotEmpty())
	 {
		 foreach($corrective_action_second_file_data as $corrective_files)
		 {
			$corrective_action_second_files[] = [
				'url' => url('uploads/corrective_action/' .$corrective_files->file),
			];
		 }
	 }
	 
	 $corrective_dtls_data = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->first();
	 
	// new add 13-08-2025------not previous --
		$lo_direct_approve = $corrective_dtls_data ? $corrective_dtls_data->lo_direct_approve : '';
		
		$lo_corrective_completed_by = $corrective_dtls_data ? $corrective_dtls_data->lo_completed_by : '';
	//---------------------
	 
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
		$subImages = App\Models\Task_list_subchecklist_rejected_files::where('task_list_checklist_id',$taskSubChecklist->id)->where('task_list_subchecklist_id', $taskSubChecklist->subchecklist_id)->get();
		
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
	
	$lo_direct_approve = $corrective_action_data ? $corrective_action_data->lo_direct_approve : '';
	
	$lo_corrective_completed_by = $corrective_action_data ? $corrective_action_data->lo_completed_by : '';
	
	$lo_corrective_action_plan_second_check = $corrective_action_data ? $corrective_action_data->lo_corrective_action_plan_second_check : '';
	
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
	 
	 $corrective_action_second_file_data = App\Models\Task_list_corrective_action_file::where('task_list_corrective_actions_id', $corrective_action_primary_id)->where('status', 2)->get();
	 
	 $corrective_action_second_files = [];
	 if($corrective_action_second_file_data->isNotEmpty())
	 {
		 foreach($corrective_action_second_file_data as $corrective_files)
		 {
			$corrective_action_second_files[] = [
				'url' => url('uploads/corrective_action/' .$corrective_files->file),
			];
		 }
	}
	 
	$corrective_dtls_data = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->first();
	
	// new add 13-08-2025------not previous --
	$lo_direct_approve = $corrective_dtls_data ? $corrective_dtls_data->lo_direct_approve : '';
	
	$lo_corrective_completed_by = $corrective_dtls_data ? $corrective_dtls_data->lo_completed_by : '';
	//---------------------
	
	$final_check_data = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->orderBy('id','asc')->skip(1)->take(PHP_INT_MAX)->get();
	 
	$corrective_detls_order = App\Models\Task_list_corrective_action_details::where('task_list_corrective_action_id',$corrective_action_primary_id)->orderBy('id')->skip(1)->take(PHP_INT_MAX)->get(['order']);
	$max_order = $corrective_detls_order->max('order');
 }
 //echo $lo_direct_approve;die;
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
							<div class="col-md-12 mt-1">
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
							{{--<div class="col-md-6 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (IA) {{ $userData->get_user->name ?? ''}} </span><span>{{ change_date_format($created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}</span></div>--}}
							<div class="col-md-12 text-ia-lo-los d-flex justify-content-between flex-wrap">
							<img src="{{ url('uploads/profile/'. $corrective_action_data?->get_inspector?->id . '/inspector/'. $corrective_action_data?->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
							<span>By (IA) {{ $userData->get_user->name ?? ''}}</span><span>·</span><span>{{ change_date_format($created_at, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span></div>
						</div>
						
						@if(!empty($lo_corrective_action_plan))
						<hr class="horizontal-line">
						@endif
						
						
						
						@if(!empty($lo_corrective_action_plan))
						{{--<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
								<label>Corrective</label>
								<div class="mt-1">
									<p class="text-muted mb-0">{{ $lo_corrective_action_plan ?? '' }}</p>
								</div>
							</div>
						</div>--}}
						
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
								<div class="d-flex justify-between align-items-center">
									<label>Corrective</label>
									@if((($corrective_dtls_data->approved_status == 1 || $corrective_dtls_data->approved_status == 2) && ($corrective_dtls_data->rejected_status == 0)) || ($corrective_dtls_data->approved_status == 0 && $corrective_dtls_data->rejected_status == 0))
									<div class="corrective-badge pending-badge">Pending  approval</div>
								@elseif($corrective_dtls_data->rejected_status != 0)
									<div class="corrective-badge rejected-badge">Rejected</div>
								@endif
								</div>
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
							<div class="col-md-12 mt-1">
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
						
						@if(!empty($corrective_action_data->created_at))
						{{--<div class="row">
							<div class="col-md-6 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LO) {{ $corrective_action_data->get_lo->name ?? ''}} </span><span>{{ change_date_format($corrective_action_data->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}</span></div>
						</div>--}}
						<div class="row">
							<div class="col-md-6 text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
								<img src="{{ url('uploads/profile/'. $corrective_action_data->get_lo->id . '/locationowner/'. $corrective_action_data->get_lo->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
								<span>By (LO)  {{ $corrective_action_data->get_lo->name ?? ''}}</span>
								<span>·</span>
								<span>{{ !empty($corrective_action_data->created_at) ? change_date_format($corrective_action_data->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') : ''}}</span>
							</div>
						</div>
						{{--<hr class="horizontal-line">--}}
						@endif
						
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
							@if($corrective_dtls_data)
								@if($corrective_dtls_data->approved_status == 1 || $corrective_dtls_data->approved_status == 2 || $corrective_dtls_data->rejected_status == 1 || $corrective_dtls_data->rejected_status == 2)
								<div class="row">
									<div class="col-md-12 mt-2">
										<div class="details-card">
											<div class="accordion d-flex justify-between align-items-center flex-wrap cursor-pointer">
												<label class="mb-0">Progress</label>
												<i class="fa-solid fa-chevron-up"></i>
											</div>
											<div class="experience-box mt-2">
												<ul class="experience-list">
												@if($corrective_dtls_data->approved_status == 1 && $corrective_dtls_data->rejected_status == 2)
													
												<li class="approved">
													<div class="experience-user">
														<i class="fa-solid fa-check"></i>
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title">Approved</div>
															<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id . '/inspector/'. $corrective_action_data->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}} </span>
																<span>·</span>
																<span>{{ change_date_format($corrective_dtls_data->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
															</div>
														</div>
													</div>
												</li>
												<li class="rejected">
													<div class="experience-user">
														<i class="fa-solid fa-xmark"></i>
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title">Rejected</div>
															<div class="title-reason mt-1">{{ $corrective_dtls_data->ia_los_rejected_reason ?? ''  }}</div>
															<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id . '/locationownersupervisor/'. $corrective_action_data->get_los->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}} </span>
																<span>·</span>
																<span>{{ change_date_format($corrective_dtls_data->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
															</div>
														</div>
													</div>
												</li>
												@elseif($corrective_dtls_data->approved_status == 2 && $corrective_dtls_data->rejected_status == 1)
												<li class="approved">
													<div class="experience-user">
														<i class="fa-solid fa-check"></i>
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title">Approved</div>
															<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id . '/locationownersupervisor/'. $corrective_action_data->get_los->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}} </span>
																<span>·</span>
																<span>{{ change_date_format($corrective_dtls_data->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
															</div>
														</div>
													</div>
												</li>
												<li class="rejected">
													<div class="experience-user">
														<i class="fa-solid fa-xmark"></i>
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title">Rejected</div>
															<div class="title-reason mt-1">{{ $corrective_dtls_data->ia_los_rejected_reason ?? ''  }}</div>
															<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id . '/inspector/'. $corrective_action_data->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}} </span>
																<span>·</span>
																<span>{{ change_date_format($corrective_dtls_data->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
															</div>
														</div>
													</div>
												</li>
												@elseif($corrective_dtls_data->approved_status == 1 && $corrective_dtls_data->rejected_status == 0)
													<li class="approved">
														<div class="experience-user">
															<i class="fa-solid fa-check"></i>
														</div>
														<div class="experience-content">
															<div class="timeline-content">
																<div class="title">Approved</div>
																<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																	<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id . '/inspector/'. $corrective_action_data->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																	<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}}</span>
																	<span>·</span>
																	<span>{{ change_date_format($corrective_dtls_data->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
																</div>
															</div>
														</div>
													</li>
													<li class="pending">
														<div class="experience-user">
															
														</div>
														<div class="experience-content">
															<div class="timeline-content">
																<div class="title-reason mt-1">
																Pending Approval from LOS
																</div>
															</div>
														</div>
													</li>
												@elseif($corrective_dtls_data->approved_status == 2 && $corrective_dtls_data->rejected_status == 0)
													<li class="approved">
														<div class="experience-user">
															<i class="fa-solid fa-check"></i>
														</div>
														<div class="experience-content">
															<div class="timeline-content">
																<div class="title">Approved</div>
																<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																	<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id . '/locationownersupervisor/'. $corrective_action_data->get_los->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																	<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}}</span>
																	<span>·</span>
																	<span>{{ change_date_format($corrective_dtls_data->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
																</div>
															</div>
														</div>
													</li>
													<li class="pending">
														<div class="experience-user">
															
														</div>
														<div class="experience-content">
															<div class="timeline-content">
																<div class="title-reason mt-1">
																Pending Approval from IA
																</div>
															</div>
														</div>
													</li>
												@elseif($corrective_dtls_data->approved_status == 0 && $corrective_dtls_data->rejected_status == 1)
												<li class="rejected">
													<div class="experience-user">
														<i class="fa-solid fa-xmark"></i>
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title">Rejected</div>
															<div class="title-reason mt-1">{{ $corrective_dtls_data->ia_los_rejected_reason ?? ''  }}</div>
															<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id . '/inspector/'. $corrective_action_data->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}} </span>
																<span></span>
																<span>{{ change_date_format($corrective_dtls_data->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
															</div>
														</div>
													</div>
												</li>
												@elseif($corrective_dtls_data->approved_status == 0 && $corrective_dtls_data->rejected_status == 2)
												<li class="rejected">
													<div class="experience-user">
														<i class="fa-solid fa-xmark"></i>
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title">Rejected</div>
															<div class="title-reason mt-1">{{ $corrective_dtls_data->ia_los_rejected_reason ?? ''  }}</div>
															<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id . '/locationownersupervisor/'. $corrective_action_data->get_los->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}} </span>
																<span>·</span>
																<span>{{ change_date_format($corrective_dtls_data->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
															</div>
														</div>
													</div>
												</li>
												@endif
												
												</ul>
											</div>
										</div>
									</div>
								</div>
							@endif
						@endif
						
							
						@endif
						
						@if(!empty($lo_corrective_action_plan_second_check))
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
						
						{{--<div class="row IA-IOS-get-reply">
							<div class="col-md-12"><label>Corrective</label></div>
								<div class="col-md-12"><label>{{ $final_title }} checks</label></div>
						</div>
						<div class="row">
							<div class="col-md-12"><p class="text-muted mb-0">{{ $val->lo_corrective_action_plan_final_checks ?? '' }}</p></div>
						</div>--}}
						
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
								<div class="d-flex justify-between align-items-center">
									<label>Corrective</label>
									@if((($val->approved_status == 1 || $val->approved_status == 2) && ($val->rejected_status == 0)) || ($val->approved_status == 0 && $val->rejected_status == 0))
									<div class="corrective-badge pending-badge">Pending  approval</div>
								@elseif($val->rejected_status != 0)
									<div class="corrective-badge rejected-badge">Rejected</div>
								@endif
								</div>
								<div class="mt-1">
									<p class="text-muted mb-0">{{ $val->lo_corrective_action_plan_final_checks ?? '' }}</p>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-12 mt-1">
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
						{{--<div class="row">
							<div class="col-md-6 text-ia-lo-los d-flex justify-content-between flex-wrap"><span>By (LO) {{ $corrective_action_data->get_lo->name ?? ''}} </span><span>{{ !empty($val->created_at) ? change_date_format($val->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') : ''}}</span></div>
						</div>--}}
						<div class="row">
							<div class="col-md-6 text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
								<img src="{{ url('uploads/profile/'. $corrective_action_data->get_lo->id . '/locationowner/'. $corrective_action_data->get_lo->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
								<span>By (LO)  {{ $corrective_action_data->get_lo->name ?? ''}}</span>
								<span>·</span>
								<span>{{ !empty($val->created_at) ? change_date_format($val->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') : ''}}</span>
							</div>
						</div>
						
						@if($val->lo_direct_approve == 0)
						<div class="row IA-IOS-get-reply">
							<div class="col-md-12">
							<label>Completed By</label>
								<div class="mt-1">
									{{ change_date_format($val->lo_completed_by, 'Y-m-d H:i:s', 'd M Y, h:i A')}}
								</div>
							</div>
						</div>
						@endif
						
						@if($val->approved_status == 1 || $val->approved_status == 2 || $val->rejected_status == 1 || $val->rejected_status == 2)
							<div class="row">
								<div class="col-md-12 mt-2">
									<div class="details-card">
										<div class="accordion d-flex justify-between align-items-center flex-wrap cursor-pointer">
											<label class="mb-0">Progress</label>
											<i class="fa-solid fa-chevron-up"></i>
										</div>
										<div class="experience-box mt-2">
											<ul class="experience-list">
											@if($val->approved_status == 1 && $val->rejected_status == 2)
												
											<li class="approved">
												<div class="experience-user">
													<i class="fa-solid fa-check"></i>
												</div>
												<div class="experience-content">
													<div class="timeline-content">
														<div class="title">Approved</div>
														<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
															<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id . '/inspector/'. $corrective_action_data->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
															<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}} </span>
															<span>·</span>
															<span>{{ change_date_format($val->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
														</div>
													</div>
												</div>
											</li>
											<li class="rejected">
												<div class="experience-user">
													<i class="fa-solid fa-xmark"></i>
												</div>
												<div class="experience-content">
													<div class="timeline-content">
														<div class="title">Rejected</div>
														<div class="title-reason mt-1">{{ $val->ia_los_rejected_reason ?? ''  }}</div>
														<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
															<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id . '/locationownersupervisor/'. $corrective_action_data->get_los->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
															<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}} </span>
															<span>·</span>
															<span>{{ change_date_format($val->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
														</div>
													</div>
												</div>
											</li>
											@elseif($val->approved_status == 2 && $val->rejected_status == 1)
											<li class="approved">
												<div class="experience-user">
													<i class="fa-solid fa-check"></i>
												</div>
												<div class="experience-content">
													<div class="timeline-content">
														<div class="title">Approved</div>
														<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
															<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id . '/locationownersupervisor/'. $corrective_action_data->get_los->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
															<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}} </span>
															<span>·</span>
															<span>{{ change_date_format($val->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
														</div>
													</div>
												</div>
											</li>
											<li class="rejected">
												<div class="experience-user">
													<i class="fa-solid fa-xmark"></i>
												</div>
												<div class="experience-content">
													<div class="timeline-content">
														<div class="title">Rejected</div>
														<div class="title-reason mt-1">{{ $val->ia_los_rejected_reason ?? ''  }}</div>
														<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
															<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id . '/inspector/'. $corrective_action_data->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
															<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}} </span>
															<span>·</span>
															<span>{{ change_date_format($val->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
														</div>
													</div>
												</div>
											</li>
											@elseif($val->approved_status == 1 && $val->rejected_status == 0)
												<li class="approved">
													<div class="experience-user">
														<i class="fa-solid fa-check"></i>
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title">Approved</div>
															<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id . '/inspector/'. $corrective_action_data->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}}</span>
																<span>·</span>
																<span>{{ change_date_format($val->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
															</div>
														</div>
													</div>
												</li>
												<li class="pending">
													<div class="experience-user">
														
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title-reason mt-1">
															Pending Approval from LOS
															</div>
														</div>
													</div>
												</li>
											@elseif($val->approved_status == 2 && $val->rejected_status == 0)
												<li class="approved">
													<div class="experience-user">
														<i class="fa-solid fa-check"></i>
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title">Approved</div>
															<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
																<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id . '/locationownersupervisor/'. $corrective_action_data->get_los->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
																<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}}</span>
																<span>·</span>
																<span>{{ change_date_format($val->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
															</div>
														</div>
													</div>
												</li>
												<li class="pending">
													<div class="experience-user">
														
													</div>
													<div class="experience-content">
														<div class="timeline-content">
															<div class="title-reason mt-1">
															Pending Approval from IA
															</div>
														</div>
													</div>
												</li>
											@elseif($val->approved_status == 0 && $val->rejected_status == 1)
											<li class="rejected">
												<div class="experience-user">
													<i class="fa-solid fa-xmark"></i>
												</div>
												<div class="experience-content">
													<div class="timeline-content">
														<div class="title">Rejected</div>
														<div class="title-reason mt-1">{{ $val->ia_los_rejected_reason ?? ''  }}</div>
														<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
															<img src="{{ url('uploads/profile/'. $corrective_action_data->get_inspector->id . '/inspector/'. $corrective_action_data->get_inspector->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
															<span>By (IA) {{ $corrective_action_data->get_inspector->name ?? ''}} </span>
															<span></span>
															<span>{{ change_date_format($val->inspector_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
														</div>
													</div>
												</div>
											</li>
											@elseif($val->approved_status == 0 && $val->rejected_status == 2)
											<li class="rejected">
												<div class="experience-user">
													<i class="fa-solid fa-xmark"></i>
												</div>
												<div class="experience-content">
													<div class="timeline-content">
														<div class="title">Rejected</div>
														<div class="title-reason mt-1">{{ $val->ia_los_rejected_reason ?? ''  }}</div>
														<div class="text-ia-lo-los d-flex align-items-center flex-wrap mt-1">
															<img src="{{ url('uploads/profile/'. $corrective_action_data->get_los->id . '/locationownersupervisor/'. $corrective_action_data->get_los->profile_image) }}" class="small-rounded-profile-img mb-0" alt="Profile image">
															<span>By (LOS) {{ $corrective_action_data->get_los->name ?? ''}} </span>
															<span>·</span>
															<span>{{ change_date_format($val->los_action_date, 'Y-m-d H:i:s', 'd M Y, h:i A')}}</span>
														</div>
													</div>
												</div>
											</li>
											@endif
											
											</ul>
										</div>
									</div>
								</div>
							</div>
							@endif
						<hr class="horizontal-line">
					@endforeach
					@endif	
					
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
	
	
@endsection 
@section('scripts')
<script src="{{ url('front-assets/css/bootstrap.min.css') }}"></script>
	{{--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>--}}
		{{--<script src="{{url('front-assets/js/moment.min.js') }}"></script>
<script src="{{url('front-assets/js/bootstrap-datetimepicker.min.js') }}"></script>--}}
<script>
$(document).ready(function () {
	$(document).on('click','.accordion', function(){
        let $card = $(this).closest(".details-card");
        let $box = $card.find(".experience-box");
        let $icon = $(this).find("i");

        $box.slideToggle(200); // toggle with animation
        $icon.toggleClass("fa-chevron-up fa-chevron-down"); // toggle arrow
    });
});

</script>
<script>
$(document).ready(function() {
	/*$('.datetimepicker').datetimepicker({
		format: 'YYYY-MM-DD HH:mm' // Adjust format as needed
	});*/
   
   
});
</script>
@endsection

