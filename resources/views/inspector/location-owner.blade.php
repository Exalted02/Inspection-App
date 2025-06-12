@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($userdata);die;
//echo "<pre>";print_r($correctiveCheck);die;
//echo "<pre>";print_r($correctiveAction);die;
//echo "<pre>";print_r($approvedCompleted);die;
$location_name = App\Models\Manage_location::where('id', $location_id)->first()->location_name;
use Carbon\Carbon;
$j = 0;
$k = 0;
$l = 0;
$m = 0;
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	
		<div class="profile-card">
		<div class="profile-banner" style="background-image: url( '{{url('uploads/profile/' .$userdata->id .'/locationowner/'. $userdata->background_image )}} ')"></div>
			<div class="profile-info">
				<img class="profile-avatar" src="{{ url('uploads/profile/' .$userdata->id .'/locationowner/'. $userdata->profile_image)}}" alt="Profile Picture">
				<h2 class="profile-name">{{ $userdata->name ?? ''}}</h2>
				<p class="profile-description">
					Location Owner at {{ $userdata->get_company->company_name ?? '' }},<br>
						{{ $location_name ?? '' }}
						
				</p>
			</div>
		</div>
    <!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
    <!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
	<div class="container location-owner-details">
	<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container">
					<div class="row custom-tab">
						<!-- Tabs -->
						<div class="tab-scroll-container">
							<div class="scroll-arrow left-arrow" id="scrollLeft"><i class="fa fa-chevron-left"></i></div>
								<div class="tab-scroll-wrapper" id="tabScrollWrapper">
									<ul class="nav nav-tabs custom-tab-style" role="tablist">
									{{--<li role="presentation" class=""><a class="correctiveAction" href="#inprogress_tab" aria-controls="inprogress_tab" role="tab">{{ $total_corrective_action ?? ''}} Corrective actions</a></li>
										<li role="presentation"><a class="completedtab"  href="#completed_tab" aria-controls="completed_tab" role="tab">{{ count($correctiveCheck)}} Corrective checks</a></li>
										<li role="presentation"><a class="rejectInspector"  href="#rejected_by_inspector_tab" aria-controls="completed_tab" role="tab">Rejected</a></li>
									<li role="presentation"><a class="approvedByInspector" href="#approved_by_inspector_tab" aria-controls="completed_tab" role="tab">Approved</a></li>--}}
									
									<li role="presentation" class=""><a class="correctiveAction" href="#inprogress_tab" aria-controls="inprogress_tab" role="tab">{{ $total_corrective_action ?? ''}} Corrective Needed</a></li>
										<li role="presentation"><a class="completedtab"  href="#completed_tab" aria-controls="completed_tab" role="tab">{{ count($correctiveCheck)}} Corrective Action</a></li>
										<li role="presentation"><a class="rejectInspector"  href="#rejected_by_inspector_tab" aria-controls="completed_tab" role="tab">Corrective Plan</a></li>
										<li role="presentation"><a class="approvedByInspector" href="#approved_by_inspector_tab" aria-controls="completed_tab" role="tab">Completed/Approved</a></li>
										
									</ul>
								</div>
							<div class="scroll-arrow right-arrow" id="scrollRight"><i class="fa fa-chevron-right"></i></div>
						</div>
						<!-- Tab panes -->
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane" id="inprogress_tab">
							@foreach($correctiveNeeded as $result)
							@if(($result['inspector_action']=='' && $result['inspector_action']=='') || ($result['inspector_action']== 2 && $result['inspector_action']==2))
								@php 
								    $j++;
								    $arrSubchecklist = [];
									$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
									
									/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
									
									/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
									
									$checklistName = $checklistData ? $checklistData->name : '';
									
									$rejectedRegionData = $result['type'] == 'checklist'
									? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
									: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

									if($result['image'] != '')
									{
										$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
									}
									else{
										$images = url('images/noimages/noimage_region.png');
									}
									
									if($result['type'] == 'subchecklist')
									{
										$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->where('approve',0)->first();
										if($subchecklistData)
										{
											//foreach($subchecklistData as $subcheck)
											//{
												//$arrSubchecklist = [];
												$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
												
												$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
												
												$images = $filedata && $filedata->file != ''  ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/noimage_region.png') ;
												
												$arrSubchecklist[] = [
													'id' => $subchecklistData->id,
													'name' => $subchecklistName ? $subchecklistName->name : '',
													'image' => $images,
													'subchecklist_id' => $subchecklistData->subchecklist_id,
												];
											//}
										}
									}
									
								@endphp
								@if(!empty($arrSubchecklist))
									@foreach($arrSubchecklist as $val)
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<img src="{{ $val['image'] }}" width="50" height="50">
									</div>
									<div class="flex-grow-1">
										<a href="{{ route('location-owner-subchecklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
										<h6>{{ $checklistName ?? '' }} 
										@if($val!='')
											-> {{$val['name'] ?? ''}}
										@endif
										</h6>
											<p class="text-muted mb-0">
											{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
											</p>
										</a>
									</div>
								</div>
								@endforeach
								
								@else 
									<div class="d-flex mb-3 task">
									<div class="date-box">
										<img src="{{ $images }}" width="50" height="50">
									</div>
									<div class="flex-grow-1">
										<a href="{{ route('location-owner-checklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
										<h6>{{ $checklistName ?? '' }} 
										</h6>
											<p class="text-muted mb-0">
											{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
											</p>
										</a>
									</div>
								</div>
								@endif
							@endif
							@endforeach
							</div>
							<div class="tab-pane" id="completed_tab">
							@foreach($correctiveCheck as $result)
							@if($result['lo_direct_approve'] == 1 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
								@php 
							        $k++;
								    $arrSubchecklist = [];
									$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
									
									/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
									
									/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
									
									$checklistName = $checklistData ? $checklistData->name : '';
									
									$rejectedRegionData = $result['type'] == 'checklist'
									? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
									: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

									if($result['image'] != '')
									{
										$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
									}
									else{
										$images = url('images/noimages/noimage_region.png');
									}
									
									if($result['type'] == 'subchecklist')
									{
										$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->where('approve',0)->first();
										if($subchecklistData)
										{
											//foreach($subchecklistData as $subcheck)
											//{
												$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
												
												$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
												
												$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/noimage_region.png');
												
												$arrSubchecklist[] = [
													'id' => $subchecklistData->id,
													'name' => $subchecklistName ? $subchecklistName->name : '',
													'image' => $images,
													'subchecklist_id' => $subchecklistData->subchecklist_id,
												];
											//}
										}
									}
									
									$appr_by = '';
									if($result['inspector_action']==1)
									{
										$appr_by = 'Approved Inspector';
									}
									
									if($result['los_action']==1)
									{
										$appr_by = 'Approved LOS';
									}
										
									
								@endphp
								@if(!empty($arrSubchecklist))
									@foreach($arrSubchecklist as $val)
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<img src="{{ $val['image'] }}" width="50" height="50">
									</div>
									<div class="flex-grow-1">
										<a href="javascript:void(0);">
										<h6>{{ $checklistName ?? '' }} 
										@if($val!='')
											-> {{$val['name'] ?? ''}}
										@endif
										</h6>
											<p class="text-muted mb-0">
											{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}} 
												@if($result['inspector_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved Inspector</button>
												@elseif($result['los_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved LOS</button>
												@else
													<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
												@endif
												
													{{--<button type="button" class="btn btn-outline-success btn-sm custom-small-btn" style="pointer-events: none;">{{ $appr_by }}</button>--}}
											</p>
										</a>
									</div>
								</div>
								@endforeach
								
								@else 
									<div class="d-flex mb-3 task">
									<div class="date-box">
										<img src="{{ $images }}" width="50" height="50">
									</div>
									<div class="flex-grow-1">
										<a href="javascript:void(0);">
										<h6>{{ $checklistName ?? '' }} 
										</h6>
											<p class="text-muted mb-0">
											{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
											</p>
											<p class="text-muted mb-0">
											<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}} 
												@if($result['inspector_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved Inspector</button>
												@elseif($result['los_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved LOS</button>
												@else
													<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
												@endif
											</p>
										</a>
									</div>
								</div>
								@endif
							@endif
							@endforeach
							</div>
							<div class="tab-pane" id="rejected_by_inspector_tab">
							@foreach($correctiveCheck as $result)
								@if($result['lo_direct_approve'] == 0  && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
									@php 
								        $l++;
										$arrSubchecklist = [];
										$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
										
										/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
										
										/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
										
										$checklistName = $checklistData ? $checklistData->name : '';
										
										$rejectedRegionData = $result['type'] == 'checklist'
										? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
										: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

										if($result['image'] != '')
										{
											$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
										}
										else{
											$images = url('images/noimages/noimage_region.png');
										}
										
										if($result['type'] == 'subchecklist')
										{
											$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->where('approve',0)->first();
											if($subchecklistData)
											{
												//foreach($subchecklistData as $subcheck)
												//{
													$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
													
													$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
													
													$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/noimage_region.png');
													
													$arrSubchecklist[] = [
														'id' => $subchecklistData->id,
														'name' => $subchecklistName ? $subchecklistName->name : '',
														'image' => $images,
														'subchecklist_id' => $subchecklistData->subchecklist_id,
													];
												//}
											}
										}
										
									@endphp
									@if(!empty($arrSubchecklist))
										@foreach($arrSubchecklist as $val)
										<div class="d-flex mb-3 task">
											<div class="date-box">
												<img src="{{ $val['image'] }}" width="50" height="50">
											</div>
											<div class="flex-grow-1">
											{{--<a href="{{ route('location-owner-subchecklist-rejected-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$result['subchecklist_id'],'type' => $result['type'] ]) }}">--}}
												<a href="javascript:void(0);">
												<h6>{{ $checklistName ?? '' }} 
												@if($val!='')
													-> {{$val['name'] ?? ''}}
												@endif
												</h6>
													<p class="text-muted mb-0">
													{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
													@if($result['inspector_action']==1)
													<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved Inspector</button>
													@elseif($result['los_action']==1)
													<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved LOS</button>
													@else
														<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
													@endif
														</p>
												</a>
											</div>
										</div>
										@endforeach
									
									@else 
										<div class="d-flex mb-3 task">
										<div class="date-box">
											<img src="{{ $images }}" width="50" height="50">
										</div>
										<div class="flex-grow-1">
										{{--<a href="{{ route('location-owner-checklist-rejected-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'] ]) }}">--}}
											<a href="javascript:void();">
											<h6>{{ $checklistName ?? '' }} 
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												<p class="text-muted mb-0">
												<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
												</p>
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												@if($result['inspector_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved Inspector</button>
												@elseif($result['los_action']==1)
												<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Approved LOS</button>
												@else
													<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
												@endif
												</p>
											</a>
										</div>
									</div>
									@endif
								@endif
							@endforeach
							</div>
							
							
							<div class="tab-pane" id="approved_by_inspector_tab">
							@foreach($approvedCompleted as $result)
								@if($result['inspector_action'] == 1 && $result['los_action'] == 1)
									@php 
								        $m++;
										$arrSubchecklist = [];
										$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
										
										/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
										
										/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
										
										$checklistName = $checklistData ? $checklistData->name : '';
										
										$rejectedRegionData = $result['type'] == 'checklist'
										? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
										: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

										/*if($result['image'] !='')
										{
											$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
										}
										else{
											$images = url('images/noimages/noimage_region.png');
										}*/
										
										$images = url('images/noimages/noimage_region.png');
										
										if($result['type'] == 'subchecklist')
										{
											$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->first();
											if($subchecklistData)
											{
												//foreach($subchecklistData as $subcheck)
												//{
													$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
													
													$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
													
													$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/noimage_region.png');
													  
													
													$arrSubchecklist[] = [
														'id' => $subchecklistData->id,
														'name' => $subchecklistName ? $subchecklistName->name : '',
														'image' => $images,
														'subchecklist_id' => $subchecklistData->subchecklist_id,
													];
												//}
											}
										}
										
									@endphp
									@if(!empty($arrSubchecklist))
										@foreach($arrSubchecklist as $val)
										<div class="d-flex mb-3 task">
											<div class="date-box">
												<img src="{{ $val['image'] }}" width="50" height="50">
											</div>
											<div class="flex-grow-1">
												<a href="javascript:void(0);">
												<h6>{{ $checklistName ?? '' }} 
												@if($val!='')
													-> {{$val['name'] ?? ''}}
												@endif
												</h6>
													<p class="text-muted mb-0">
													{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
													</p>
												</a>
											</div>
										</div>
										@endforeach
									
									@else 
										<div class="d-flex mb-3 task">
										<div class="date-box">
											<img src="{{ $images }}" width="50" height="50">
										</div>
										<div class="flex-grow-1">
											<a href="javascript:void(0);">
											<h6>{{ $checklistName ?? '' }} 
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												<p class="text-muted mb-0">
												<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
												</p>
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												</p>
											</a>
										</div>
									</div>
									@endif
								@endif
							@endforeach
							</div>
							
							{{--<div class="tab-pane" id="final_checked_by_inspector_tab">
							@foreach($correctiveCheck as $result)
								@if($result['inspector_action'] ==2 && $result['second_checked'] != 'null')
									@php 
										
										$arrSubchecklist = [];
										$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
										
										/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
										
										/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
										
										$checklistName = $checklistData ? $checklistData->name : '';
										
										$rejectedRegionData = $result['type'] == 'checklist'
										? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
										: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

										if($result['image'] != '')
										{
											$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
										}
										else{
											$images = url('images/noimages/noimage_region.png');
										}
										
										
										if($result['type'] == 'subchecklist')
										{
											$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->where('approve',0)->first();
											if($subchecklistData)
											{
												//foreach($subchecklistData as $subcheck)
												//{
													$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
													
													$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
													
													$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/noimage_region.png') ;
													
													$arrSubchecklist[] = [
														'id' => $subchecklistData->id,
														'name' => $subchecklistName ? $subchecklistName->name : '',
														'image' => $images,
														'subchecklist_id' => $subchecklistData->subchecklist_id,
													];
												//}
											}
										}
										
									@endphp
									@if(!empty($arrSubchecklist))
										@foreach($arrSubchecklist as $val)
										<div class="d-flex mb-3 task">
											<div class="date-box">
												<img src="{{ $val['image'] }}" width="50" height="50">
											</div>
											<div class="flex-grow-1">
												<a href="javascript:void(0);">
												<h6>{{ $checklistName ?? '' }} 
												@if($val!='')
													-> {{$val['name'] ?? ''}}
												@endif
												</h6>
													<p class="text-muted mb-0">
													{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
													</p>
													<p class="text-muted mb-0">
													<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
													</p>
												</a>
											</div>
										</div>
										@endforeach
									
									@else 
										<div class="d-flex mb-3 task">
										<div class="date-box">
											<img src="{{ $images }}" width="50" height="50">
										</div>
										<div class="flex-grow-1">
											<a href="javascript:void(0);">
											<h6>{{ $checklistName ?? '' }} 
											</h6>
												<p class="text-muted mb-0">
												{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
												</p>
												<p class="text-muted mb-0">
												<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
												</p>
												<p class="text-muted mb-0">
												<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
												</p>
											</a>
										</div>
									</div>
									@endif
								@endif
							@endforeach
							</div>--}}
						</div>
						<div class="text-left" style="display:none" id="no_record"><strong><h3>No record found</h3></strong></div>
					</div>
				</div>
			</section>
		</div>
	</div>
	<input type="hidden" value="{{ $location_id ?? ''}}" id="location_id">
	<input type="hidden" value="{{ $task_id ?? ''}}" id="task_id">
	<input type="hidden" value="{{ $isactive ?? ''}}" id="isactive">
	
	<input type="hidden" value="{{ $j ?? ''}}" id="norecord_j">
	<input type="hidden" value="{{ $k ?? ''}}" id="norecord_k">
	<input type="hidden" value="{{ $l ?? ''}}" id="norecord_l">
	<input type="hidden" value="{{ $m ?? ''}}" id="norecord_m">
	
@endsection 
@section('scripts')
<script>
$(document).ready(function() {
	var isactive = $('#isactive').val();
	
	if(isactive == 0)
	{
		const selectedTab = localStorage.getItem('selectedTab');
		
		if (selectedTab) {
			$('a[href="' + selectedTab + '"]').tab('show');
			
			if(selectedTab == '#inprogress_tab')
			{
				var norecord_j = $('#norecord_j').val();
				if(norecord_j==0)
				{
					$('#no_record').show();
				}
			}
			
			if(selectedTab == '#completed_tab')
			{
				var norecord_k = $('#norecord_k').val();
				if(norecord_k==0)
				{
					$('#no_record').show();
				}
			}
			
			if(selectedTab == '#rejected_by_inspector_tab')
			{
				var norecord_l = $('#norecord_l').val();
				if(norecord_l==0)
				{
					$('#no_record').show();
				}
				
			}
			
			if(selectedTab == '#approved_by_inspector_tab')
			{
				var norecord_m = $('#norecord_m').val();
				if(norecord_m==0)
				{
					$('#no_record').show();
				}
			}
		}
	}
	
	
	if(isactive == 1)
	{
		$('a[href="#inprogress_tab"]').tab('show');
		$('#inprogress_tab').show();
		$('#isactive').val(0);
	}
	
  
	/*const selectedTab = localStorage.getItem('selectedTab');
	if (selectedTab) {
        $('a[href="' + selectedTab + '"]').tab('show');
    }*/
	
	$(document).on('click','.correctiveAction, .completedtab, .rejectInspector, .approvedByInspector', function(){
		var location_id = $('#location_id').val();
		var task_id = $('#task_id').val();
		$('#isactive').val(0);
		const tabId = $(this).attr('href');
		
		if(tabId == '#correctiveAction')
		{
			$('.tab-pane').removeClass('active show');
			//$('#correctiveAction').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#correctiveAction"]').addClass('active');
			
		}
		
		if(tabId == '#completedtab')
		{
			$('.tab-pane').removeClass('active show');
			//$('#completedtab').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#completedtab"]').addClass('active');
			
		}
		
		if(tabId == '#rejectInspector')
		{
			$('.tab-pane').removeClass('active show');
			//$('#rejectInspector').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#rejectInspector"]').addClass('active');
			
		}
		
		if(tabId == '#approvedByInspector')
		{
			$('.tab-pane').removeClass('active show');
			//$('#approvedByInspector').addClass('active show');
			$('.nav-tabs .nav-link').removeClass('active');
			//$('.nav-tabs .nav-link[href="#approvedByInspector"]').addClass('active');
			
		}
		
		
		localStorage.setItem('selectedTab', tabId);
		const refreshUrl = "{{ url('location-owner/LOCATION_ID/TASK_ID/ISACTIVE') }}";
		const redirectUrl = refreshUrl.replace('LOCATION_ID', location_id).replace('TASK_ID', task_id).replace('ISACTIVE', 0);
		window.location.href = redirectUrl;
	});
	
	const scrollWrapper = $('#tabScrollWrapper');
    $('#scrollLeft').click(function () {
        scrollWrapper.animate({ scrollLeft: scrollWrapper.scrollLeft() - 150 }, 300);
    });

    $('#scrollRight').click(function () {
        scrollWrapper.animate({ scrollLeft: scrollWrapper.scrollLeft() + 150 }, 300);
    });
	
});


</script>

@endsection

