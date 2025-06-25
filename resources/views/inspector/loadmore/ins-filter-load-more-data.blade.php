@php
use Carbon\Carbon;
$j = 0;
$k = 0;
$l = 0;
$m = 0;
//echo "<pre>";print_r($correctiveActionArray);die;
@endphp
@if($mode == 'corrective_needed')					
	@foreach($correctiveNeddedArray as $result)
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
			{{--<a href="{{ route('inspector-subchecklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">--}}
					<a href="{{ route('inspector-subchecklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
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
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
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
				<a href="{{ route('inspector-checklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
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
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
					@endif
					</p>
				</a>
			</div>
		</div>
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
				@if($result['second_checked'] == '')
				<a href="{{ route('inspector-subchecklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
				@else
				<a href="{{ route('inspector-subchecklist-second-approve-by-lo',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
				@endif
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
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
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
				@if($result['second_checked'] == '')
				<a href="{{ route('inspector-checklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
				@else
				<a href="{{ route('inspector-checklist-second-approve-by-lo',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
				@endif
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
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
					@endif
					</p>
				</a>
			</div>
		</div>
		@endif
	@endforeach
@endif

@if($mode == 'corrective_plan')
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
				@if($result['second_checked'] == '')
				<a href="{{ route('inspector-subchecklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
				@else
				<a href="{{ route('inspector-subchecklist-second-approve-by-lo',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
				@endif
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
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
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
				@if($result['second_checked'] == '')
				<a href="{{ route('inspector-checklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
				@else
				<a href="{{ route('inspector-checklist-second-approve-by-lo',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
				@endif
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
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success"  style="pointer-events: none; background-color: transparent; border-color: #198754; color: #198754; padding: 2px 6px; font-size: 12px; line-height: 1;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning" style="pointer-events: none; background-color: transparent; border-color: #ffc107; color: #ffc107; padding: 2px 6px; font-size: 12px; line-height: 1;">Pending</button>
						@endif
					@endif
					</p>
				</a>
			</div>
		</div>
		@endif
	@endforeach
@endif