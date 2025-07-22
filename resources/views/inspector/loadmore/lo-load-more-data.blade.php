@php
use Carbon\Carbon;
$j = 0;
$k = 0;
$l = 0;
$m = 0;
$location_name = App\Models\Manage_location::where('id', $location_id)->first()->location_name;

$correctiveNeddedArray = [];
$correctiveActionArray = [];
$correctivePlanArray = [];
$approvedCompletedArray = [];


if($mode == 'corrective_needed')
{
	//echo '<pre>';print_r($correctiveNeeded);die;
	foreach($correctiveNeeded as $needed)
	{
		if(($needed['inspector_action']=='' && $needed['los_action']=='') || ($needed['inspector_action']== 2 && $needed['los_action']==2))
		{
			if($needed['type'] == 'checklist')
			{
				$correctiveNeddedArray[] = [
					'type' => $needed['type'],
					'task_id' => $needed['task_id'],
					'checklist_id' => $needed['checklist_id'],
					'rejected_region' => $needed['rejected_region'],
					'image' => $needed['image'],
					'inspector_action' => $needed['inspector_action'],
					'los_action' => $needed['los_action'],
					'rejected_status' => $needed['rejected_status']
				];
			}
			else{
				$correctiveNeddedArray[] = [
					'type' => $needed['type'],
					'task_id' => $needed['task_id'],
					'checklist_id' => $needed['checklist_id'],
					'subchecklist_id' => $needed['subchecklist_id'],
					'rejected_region' => $needed['rejected_region'],
					'image' => $needed['image'],
					'inspector_action' => $needed['inspector_action'],
					'los_action' => $needed['los_action'],
					'rejected_status' => $needed['rejected_status']
				];
			}
		}
	}
	//echo $lower.' '.$upper;
	//echo "<pre>";print_r($correctiveNeddedArray);die;
	$correctiveNeddedArray = array_slice($correctiveNeddedArray, $lower, $upper);
}

if($mode == 'corrective_action')
{
	foreach($correctiveAction as $result)
	{
		if($result['lo_direct_approve'] == 1 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
		{
			if($result['type'] == 'checklist')
			{
				$correctiveActionArray[] = [
					'type' => $result['type'],
					'task_id' => $result['task_id'],
					'checklist_id' => $result['checklist_id'],
					'rejected_region' => $result['rejected_region'],
					'image' => $result['image'],
					'inspector_action' => $result['inspector_action'],
					'los_action' => $result['los_action'],
					'second_checked' => $result['second_checked'],
					'lo_direct_approve' => $result['lo_direct_approve']
				
				];
			}
			else{
				$correctiveActionArray[] = [
					'type' => $result['type'],
					'task_id' => $result['task_id'],
					'checklist_id' => $result['checklist_id'],
					'subchecklist_id' => $result['subchecklist_id'],
					'rejected_region' => $result['rejected_region'],
					'image' => $result['image'],
					'inspector_action' => $result['inspector_action'],
					'los_action' => $result['los_action'],
					'second_checked' => $result['second_checked'],
					'lo_direct_approve' => $result['lo_direct_approve']
				];
			}
		}
	}
	
	$correctiveActionArray = array_slice($correctiveActionArray, $lower, $upper);
}

if($mode == 'corrective_plan')
{
	foreach($correctivePlan as $result)
	{
		if($result['lo_direct_approve'] == 0 && ($result['inspector_action'] == 0 || $result['los_action'] == 0))
		{
			if($result['type'] == 'checklist')
			{
				$correctivePlanArray[] = [
					'type' => $result['type'],
					'task_id' => $result['task_id'],
					'checklist_id' => $result['checklist_id'],
					'rejected_region' => $result['rejected_region'],
					'image' => $result['image'],
					'inspector_action' => $result['inspector_action'],
					'los_action' => $result['los_action'],
					'second_checked' => $result['second_checked'],
					'lo_direct_approve' => $result['lo_direct_approve']
				
				];
			}
			else{
				$correctivePlanArray[] = [
					'type' => $result['type'],
					'task_id' => $result['task_id'],
					'checklist_id' => $result['checklist_id'],
					'subchecklist_id' => $result['subchecklist_id'],
					'rejected_region' => $result['rejected_region'],
					'image' => $result['image'],
					'inspector_action' => $result['inspector_action'],
					'los_action' => $result['los_action'],
					'second_checked' => $result['second_checked'],
					'lo_direct_approve' => $result['lo_direct_approve']
				];
			}
		}
	}
	
	$correctivePlanArray = array_slice($correctivePlanArray, $lower, $upper);
}

usort($approvedCompleted, function ($a, $b) {
	return strtotime($b['updated_at']) <=> strtotime($a['updated_at']);
});

if($mode == 'corrective_appr')
{
	foreach($approvedCompleted as $appr)
	{
		if($appr['inspector_action'] == 1 && $appr['los_action'] == 1)
		{
			if($appr['type'] == 'checklist')
			{
				if(isset($appr['image']))
				{
					$approvedCompletedArray[] = [
						'type' => $appr['type'],
						'task_id' => $appr['task_id'],
						'checklist_id' => $appr['checklist_id'],
						'rejected_region' => $appr['rejected_region'],
						'image' => $appr['image'],
						'inspector_action' => $appr['inspector_action'],
						'los_action' => $appr['los_action']
					];
				}
				else{
					$approvedCompletedArray[] = [
						'type' => $appr['type'],
						'task_id' => $appr['task_id'],
						'checklist_id' => $appr['checklist_id'],
						'rejected_region' => $appr['rejected_region'],
						'inspector_action' => $appr['inspector_action'],
						'los_action' => $appr['los_action']
					];
				}
			}
			else{
				if(isset($appr['image']))
				{
					$approvedCompletedArray[] = [
						'type' => $appr['type'],
						'task_id' => $appr['task_id'],
						'checklist_id' => $appr['checklist_id'],
						'subchecklist_id' => $appr['subchecklist_id'],
						'rejected_region' => $appr['rejected_region'],
						'image' => $appr['image'],
						'inspector_action' => $appr['inspector_action'],
						'los_action' => $appr['los_action']
					];
				}
				else{
					$approvedCompletedArray[] = [
						'type' => $appr['type'],
						'task_id' => $appr['task_id'],
						'checklist_id' => $appr['checklist_id'],
						'subchecklist_id' => $appr['subchecklist_id'],
						'rejected_region' => $appr['rejected_region'],
						'inspector_action' => $appr['inspector_action'],
						'los_action' => $appr['los_action']
					];
				}
			}
		}
	}
	
	$approvedCompletedArray = array_slice($approvedCompletedArray, $lower, $upper);
}

//echo "<pre>";print_r($correctiveNeddedArray);die;
@endphp
@if($mode == 'corrective_needed')					
	@foreach($correctiveNeddedArray as $result)
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
				$images = url('images/noimages/corrective-needed.png');
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
						
						$images = $filedata && $filedata->file != ''  ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-needed.png') ;
						
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
				@php 
					$url = $val['image'] ?? '';
					$extension = pathinfo($url, PATHINFO_EXTENSION);
					$extension = strtolower($extension);
				@endphp
		<div class="d-flex mb-3 task">
			<div class="date-box">
				@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
				<img src="{{ $val['image'] }}" width="50" height="50">
				@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
					<video controls src="{{ $val['image'] }}"></video>
				@endif
			</div>
			<div class="flex-grow-1">
			{{--<a href="{{ route('location-owner-subchecklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">--}}
				@if($result['inspector_action'] == 2)
				<a href="{{ route('location-owner-subchecklist-rejected-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$result['subchecklist_id'],'type' => $result['type'] ]) }}">
				@else
				<a href="{{ route('location-owner-subchecklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action', 'lid'=> $location_id ]) }}">
				@endif
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
					@if($result['rejected_status']==1)
					<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: red; color: red; padding: 2px 6px; font-size: 12px; line-height: 1;">Rejected Inspector</button>
					@elseif($result['rejected_status']==2)
					<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color:red; color: red; padding: 2px 6px; font-size: 12px; line-height: 1;">Rejected LOS</button>
					@else
						<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
					@endif
					</p>
				</a>
			</div>
		</div>
		@endforeach
		
		@else 
			@php 
				$url = $images ?? '';
				$extension = pathinfo($url, PATHINFO_EXTENSION);
				$extension = strtolower($extension);
			@endphp
			<div class="d-flex mb-3 task">
			<div class="date-box">
				@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
					<img src="{{ $images }}" width="50" height="50">
				@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
					<video controls src="{{ $images }}"></video>
				@endif
			</div>
			<div class="flex-grow-1">
			{{--<a href="{{ route('location-owner-checklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">--}}
				@if($result['inspector_action'] == 2)
				<a href="{{ route('location-owner-checklist-rejected-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'] ]) }}">
				@else
				<a href="{{ route('location-owner-checklist-question-reply',['task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action', 'lid'=> $location_id]) }}">
				@endif
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
					@if($result['rejected_status']==1)
					<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: red; color: red; padding: 2px 6px; font-size: 12px; line-height: 1;">Rejected Inspector</button>
					@elseif($result['rejected_status']==2)
					<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: red; color: red; padding: 2px 6px; font-size: 12px; line-height: 1;">Rejected LOS</button>
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
@endif

@if($mode == 'corrective_action')
	@foreach($correctiveActionArray as $result)
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
			$images = url('images/noimages/corrective-action.png');
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
					
					$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-action.png');
					
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
		@php 
			$url = $val['image'] ?? '';
			$extension = pathinfo($url, PATHINFO_EXTENSION);
			$extension = strtolower($extension);
		@endphp
	<div class="d-flex mb-3 task">
		<div class="date-box">
			@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
			<img src="{{ $val['image'] }}" width="50" height="50">
			@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
			<video controls src="{{ $val['image'] }}"></video>
			@endif
		</div>
		<div class="flex-grow-1">
			
			<a href="{{ route('lo-subchecklist-first-reply-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
			
			<h6>{{ $checklistName ?? '' }} 
			@if($val!='')
				-> {{$val['name'] ?? ''}}
			@endif
			</h6>
				<p class="text-muted mb-0">
				{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
				</p>
				@if($rejectedRegionData)	
				<p class="text-muted mb-0">
				<i class="fa fa-clock">
				{{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
				</p>
				@endif
				<p class="text-muted mb-0"  style="display: flex; align-items: center; gap: 10px;">
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
		@php 
			$url = $images ?? '';
			$extension = pathinfo($url, PATHINFO_EXTENSION);
			$extension = strtolower($extension);
		@endphp
		<div class="d-flex mb-3 task">
		<div class="date-box">
			@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
			<img src="{{ $images }}" width="50" height="50">
			@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
			<video controls src="{{ $images }}"></video>
			@endif
		</div>
		<div class="flex-grow-1">
			<a href="{{ route('lo-checklist-first-reply-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
			<h6>{{ $checklistName ?? '' }} 
			</h6>
				<p class="text-muted mb-0">
				{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
				</p>
				@if($rejectedRegionData)
				<p class="text-muted mb-0">
				<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
				</p>
				@endif
				<p class="text-muted mb-0" style="display: flex; align-items: center; gap: 10px;">
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
	@endforeach
@endif

@if($mode == 'corrective_plan')
	@foreach($correctivePlanArray as $result)
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
			$images = url('images/noimages/corrective-plan.png');
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
					
					$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-plan.png');
					
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
		@php 
			$url = $val['image'] ?? '';
			$extension = pathinfo($url, PATHINFO_EXTENSION);
			$extension = strtolower($extension);
		@endphp
	<div class="d-flex mb-3 task">
		<div class="date-box">
			@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
				<img src="{{ $val['image'] }}" width="50" height="50">
			@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
				<video controls src="{{ $val['image'] }}"></video>
			@endif
		</div>
		<div class="flex-grow-1">
			
			<a href="{{ route('lo-subchecklist-first-reply-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
			
			<h6>{{ $checklistName ?? '' }} 
			@if($val!='')
				-> {{$val['name'] ?? ''}}
			@endif
			</h6>
				<p class="text-muted mb-0">
				{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
				</p>
				@if($rejectedRegionData)	
				<p class="text-muted mb-0">
				<i class="fa fa-clock">
				{{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
				</p>
				@endif
				<p class="text-muted mb-0"  style="display: flex; align-items: center; gap: 10px;">
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
		@php 
			$url = $images ?? '';
			$extension = pathinfo($url, PATHINFO_EXTENSION);
			$extension = strtolower($extension);
		@endphp
		<div class="d-flex mb-3 task">
		<div class="date-box">
			@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
			<img src="{{ $images }}" width="50" height="50">
			@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
				<video controls src="{{ $images }}"></video>
			@endif
		</div>
		<div class="flex-grow-1">
			<a href="{{ route('lo-checklist-first-reply-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
			
			<h6>{{ $checklistName ?? '' }} 
			</h6>
				<p class="text-muted mb-0">
				{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
				</p>
				@if($rejectedRegionData)
				<p class="text-muted mb-0">
				<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
				</p>
				@endif
				<p class="text-muted mb-0" style="display: flex; align-items: center; gap: 10px;">
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
	@endforeach
@endif

@if($mode == 'corrective_appr')
@foreach($approvedCompletedArray as $result)
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

		
		//$images =url('images/noimages/noimage_region.png');
		if (isset($result['image']) && $result['image'] != '') {
			$images = $result['type'] == 'checklist'
				? url('uploads/reject-files/' . $result['image'])
				: url('uploads/reject-files/subchecklist/' . $result['image']);
		} else {
			$images = url('images/noimages/corrective-completed.png');
		}
		
		$userData = App\Models\Task_lists::with('get_user')->where('id', $result['task_id'])->first();
		
		if($result['type'] == 'subchecklist')
		{
			$subchecklistData = App\Models\Task_list_subchecklists::where('task_list_id',$result['task_id'])->where('task_list_checklist_id',$result['checklist_id'])->where('subchecklist_id',$result['subchecklist_id'])->first();
			if($subchecklistData)
			{
				//foreach($subchecklistData as $subcheck)
				//{
					//$arrSubchecklist = [];
					$subchecklistName = App\Models\Subchecklist::where('id', $subchecklistData->subchecklist_id)->first();
					
					$filedata = App\Models\Task_list_subchecklist_rejected_files::where('task_list_subchecklist_id', $subchecklistData->id)->first();
					
					$images = $filedata && $filedata->file !=''  ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-completed.png');
					//$images =url('images/noimages/noimage_region.png');
					
					$arrSubchecklist[] = [
						'id' => $subchecklistData->id,
						'name' => $subchecklistName ? $subchecklistName->name : '',
						'image' => $images,
						'subchecklist_id' => $subchecklistData->subchecklist_id,
					];
				//}
			}
		}
		//echo "<pre>";print_r($arrSubchecklist);
	@endphp
	@if(!empty($arrSubchecklist))
		@foreach($arrSubchecklist as $val)
			@php 
				$url = $val['image'] ?? '';
				$extension = pathinfo($url, PATHINFO_EXTENSION);
				$extension = strtolower($extension);
				
				//if (!empty($result['image']))
					
				if(array_key_exists('image', $result))
				{
					$route = route('lo-subchecklist-completed-approved-view', [
						'location_id' => $location_id,
						'task_id' => $result['task_id'],
						'checklist_id' => $result['checklist_id'],
						'subchecklist_id' => $val['subchecklist_id'],
						'type' => $result['type'],
						'tab' => 'corrective-action'
					]);
					$class = '';
				} else {
					$route = "javascript:void(0)";
					$class = 'list-approved-filter';
				}
			@endphp
	<div class="d-flex mb-3 task">
		<div class="date-box">
			@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
				<img src="{{ $val['image'] }}" width="50" height="50">
			@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
				<video controls src="{{ $val['image'] }}"></video>
			@endif
		</div>
		<div class="flex-grow-1">
			<a href="{{ $route }}">
			<h6>{{ $checklistName ?? '' }} 
			@if($val['name']!='')
				-> {{$val['name'] ?? ''}}
			@endif
			</h6>
				<p class="text-muted mb-0">
				{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
				</p>
				@if($rejectedRegionData)	
				<p class="text-muted mb-0">
				<i class="fa fa-clock">
				{{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
				</p>
				@endif
				<p class="text-muted mb-0">
				<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}<button type="button" class="btn btn-outline-success ms-2"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;margin-left: 8px;">By {{ $userData->get_user->name ?? ''}}</button>
				</p>
			</a>
		</div>
	</div>
	@endforeach

	@else
		@php 
			$url = $images ?? '';
			$extension = pathinfo($url, PATHINFO_EXTENSION);
			$extension = strtolower($extension);
			
			//if (!empty($result['image']))
				
			if(array_key_exists('image', $result))
			{
				$route = route('lo-checklist-completed-approved-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']);
				$class = '';
			} else {
				$route = "javascript:void(0)";
				$class = 'list-approved-filter';
			}
		@endphp		
		<div class="d-flex mb-3 task">
		<div class="date-box">
			@if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
			<img src="{{ $images }}" width="50" height="50">
			@elseif(in_array($extension, ['mp4', 'webm', 'ogg']))
			<video controls src="{{ $images }}"></video>
			@endif
		</div>
		<div class="flex-grow-1">
			<a href="{{ $route }}">
			<h6>{{ $checklistName ?? '' }} 
			</h6>
				<p class="text-muted mb-0">
				{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
				</p>
				@if($rejectedRegionData)
				<p class="text-muted mb-0">
				<i class="fa fa-clock">  {{ Carbon::parse($rejectedRegionData->created_at)->format('d M Y, h:i A') }}</i>
				</p>
				@endif
				<p class="text-muted mb-0">
				<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}<button type="button" class="btn btn-outline-success ms-2"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;margin-left: 8px;">By {{ $userData->get_user->name ?? ''}}</button>
				</p>
			</a>
		</div>
	</div>
	@endif
@endforeach

@endif