@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($userdata);die;
//echo "<pre>";print_r($correctiveAction);die;
$location_name = App\Models\Manage_location::where('id', $location_id)->first()->location_name;
use Carbon\Carbon;
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
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#inprogress_tab" aria-controls="inprogress_tab" role="tab" data-toggle="tab">{{ $total_corrective_action ?? ''}} Corrective actions</a></li>
							<li role="presentation"><a href="#completed_tab" aria-controls="completed_tab" role="tab" data-toggle="tab">5 Corrective checks</a></li>
						</ul>
						<!-- Tab panes -->
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane active" id="inprogress_tab">
								@foreach($correctiveAction as $result)
								@php 
								    $arrSubchecklist = [];
									$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
									
									/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
									
									/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
									
									$checklistName = $checklistData ? $checklistData->name : '';
									
									$rejectedRegionData = $result['type'] == 'checklist'
									? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
									: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

									
									$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
									
									if($result['type'] == 'subchecklist')
									{
										$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('approve',0)->get();
										if($subchecklistData->isNotEmpty())
										{
											foreach($subchecklistData as $subcheck)
											{
												$subchecklistName = App\Models\Subchecklist::where('id', $subcheck->subchecklist_id)->first();
												
												$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subcheck->id)->first();
												
												$images = $filedata ? url('uploads/reject-files/subchecklist/' . $filedata->file) : '' ;
												
												$arrSubchecklist[] = [
													'id' => $subcheck->id,
													'name' => $subchecklistName ? $subchecklistName->name : '',
													'image' => $images,
													'subchecklist_id' => $subcheck->subchecklist_id,
												];
											}
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
								@endforeach
							</div>
							<div role="tabpanel" class="tab-pane" id="completed_tab">
								@foreach($correctiveCheck as $result)
								@php 
								    $arrSubchecklist = [];
									$checklistData = App\Models\Checklist::where('id', $result['checklist_id'])->first();
									
									/*$subchecklistData = App\Models\Subchecklist::where('id', $result['checklist_id'])->first();*/
									
									/*$checklistName = $checklistData ? $checklistData->name : ($subchecklistData ?  $subchecklistData->name : '');*/
									
									$checklistName = $checklistData ? $checklistData->name : '';
									
									$rejectedRegionData = $result['type'] == 'checklist'
									? App\Models\Task_list_checklists::where('task_list_id',$result['task_id'])->where('checklist_id', $result['checklist_id'])->first()
									: App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id', $result['checklist_id'])->first();

									
									$images = $result['type'] == 'checklist' ?  url('uploads/reject-files/' . $result['image']) :  url('uploads/reject-files/subchecklist/' . $result['image']);
									
									if($result['type'] == 'subchecklist')
									{
										$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('approve',0)->get();
										if($subchecklistData->isNotEmpty())
										{
											foreach($subchecklistData as $subcheck)
											{
												$subchecklistName = App\Models\Subchecklist::where('id', $subcheck->subchecklist_id)->first();
												
												$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subcheck->id)->first();
												
												$images = $filedata ? url('uploads/reject-files/subchecklist/' . $filedata->file) : '' ;
												
												$arrSubchecklist[] = [
													'id' => $subcheck->id,
													'name' => $subchecklistName ? $subchecklistName->name : '',
													'image' => $images,
													'subchecklist_id' => $subcheck->subchecklist_id,
												];
											}
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
								@endforeach
							</div>
							{{--<div role="tabpanel" class="tab-pane" id="completed_tab">
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<div class="date">
											<div class="day">JAN</div>
											<div class="dow">31</div>
											<div class="dod">FRI</div>
										</div>
									</div>
									<div class="flex-grow-1">
										<a href="{{route('category', ['location_id'=>1,'cat_id'=>1])}}">
											<img src="{{url('front-assets/static-image/3.jpg')}}" alt="Task" />
											<h6>Respirator user has a training sticker on employee badge</h6>
											<p class="text-muted mb-0">Set corrective actions</p>
										</a>
									</div>
								</div>
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<div class="date">
											<div class="day">JULY</div>
											<div class="dow">11</div>
											<div class="dod">TUE</div>
										</div>
									</div>
									<div class="flex-grow-1">
										<a href="{{route('category', ['location_id'=>1,'cat_id'=>1]) }}">
											<img src="{{url('front-assets/static-image/2.jpg')}}" alt="Task" />
											<h6>Respirator user has a training sticker on employee badge</h6>
											<p class="text-muted mb-0">Set corrective actions</p>
										</a>
									</div>
								</div>
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<div class="date">
											<div class="day">FEB</div>
											<div class="dow">15</div>
											<div class="dod">FRI</div>
										</div>
									</div>
									<div class="flex-grow-1">
										<a href="{{route('category', ['location_id'=>1,'cat_id'=>1])}}">
											<img src="{{url('front-assets/static-image/1.jpg')}}" alt="Task" />
											<h6>Respirator user has a training sticker on employee badge</h6>
											<p class="text-muted mb-0">Set corrective actions</p>
										</a>
									</div>
								</div>
							</div>--}}
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
@endsection 
@section('scripts')

@endsection

