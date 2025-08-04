@php
use Carbon\Carbon;
$j = 0;
$k = 0;
$l = 0;
$m = 0;
//echo "<pre>";print_r($correctiveNeddedArray);die;
$location_name = App\Models\Manage_location::where('id', $location_id)->first()->location_name;
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
						
						$images = $filedata && $filedata->file != '' ? url('uploads/reject-files/subchecklist/' . $filedata->file) : url('images/noimages/corrective-needed.png');
						
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
					<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}
					</p>
					@endif
					<p class="text-muted mb-0" >
					<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
					@if($result['rejected_status']==1)
					<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: red; color: red;">Rejected Inspector</button>
					@elseif($result['rejected_status']==2)
					<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: red; color: red;">Rejected LOS</button>
					@else
						<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
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
				<a href="{{ route('inspector-checklist-question-reply',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']) }}">
				<h6>{{ $checklistName ?? '' }} 
				</h6>
					<p class="text-muted mb-0">
					{{ \Illuminate\Support\Str::words($rejectedRegionData->rejected_region ?? '', 30, '...') }}
					</p>
					@if($rejectedRegionData)
					<p class="text-muted mb-0">
					<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}
					</p>
					@endif
					<p class="text-muted mb-0">
					<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
					@if($result['rejected_status']==1)
					<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: red; color: red;">Rejected Inspector</button>
					@elseif($result['rejected_status']==2)
					<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: red; color: red;">Rejected LOS</button>
					@else
						<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
					@endif
					</p>
				</a>
			</div>
		</div>
		@endif
	<hr class="horizontal-line-list-page">
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
				<video controls src="{{ $val['image'] }}" width="50" height="50"></video>
				@endif
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
					<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}
					</p>
					@endif
					<p class="text-muted mb-0" >
					<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
						@endif
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
				 <video controls src="{{ $images }}" width="50" height="50"></video>
				@endif
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
					<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}
					</p>
					@endif
					<p class="text-muted mb-0">
					<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
						@endif
					@endif
					</p>
				</a>
			</div>
		</div>
		@endif
		<hr class="horizontal-line-list-page">
	@endforeach
@endif

@if($mode == 'corrective_plan')
	@foreach($correctivePlanArray as $result)
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
				<video controls src="{{ $val['image'] }}" width="50" height="50"></video>
				@endif
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
					<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}
					</p>
					@endif
					<p class="text-muted mb-0" >
					<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
						@endif
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
				<video controls src="{{ $images }}" width="50" height="50"></video>
				@endif
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
					<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}
					</p>
					@endif
					<p class="text-muted mb-0">
					<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}
					@if(auth()->user()->user_type == 1)
						@if($result['inspector_action'] == 1)
							<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
						@elseif($result['inspector_action'] == 0)
							<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
						@endif
					@endif
					
					@if(auth()->user()->user_type == 3)
						@if($result['los_action'] == 1)
							<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;">Agree</button>
						@elseif($result['los_action'] == 0)
							<button type="button" class="btn btn-warning status-outline-common-btn ml-10px" style="border-color: #ffc107; color: #ffc107;">Pending</button>
						@endif
					@endif
					</p>
				</a>
			</div>
		</div>
		@endif
		<hr class="horizontal-line-list-page">
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
						//$images =url('images/noimages/corrective-completed.png');
						
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
					$route = route('ia-los-subchecklist-completed-approved-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'subchecklist_id'=>$val['subchecklist_id'],'type' => $result['type'],'tab'=>'corrective-action']);
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
				<video controls src="{{ $val['image'] }}" width="50" height="50"></video>
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
					<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}
					</p>
					@endif
					<p class="text-muted mb-0">
					<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;margin-left: 8px;">By {{ $userData->get_user->name ?? ''}}</button>
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
					$route = route('ia-los-checklist-completed-approved-view',['location_id'=>$location_id,'task_id'=>$result['task_id'], 'checklist_id'=> $result['checklist_id'],'type' => $result['type'],'tab'=>'corrective-action']);
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
				<video controls src="{{ $images }}" width="50" height="50"></video>
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
					<i class="fa fa-clock"></i> {{ change_date_format($rejectedRegionData->created_at, 'Y-m-d H:i:s', 'd M Y, h:i A') }}
					</p>
					@endif
					<p class="text-muted mb-0">
					<i class="fa fa-map-marker"></i> {{ $location_name ?? ''}}<button type="button" class="btn btn-outline-success status-outline-common-btn ml-10px"  style="border-color: #198754; color: #198754;margin-left: 8px;">By {{ $userData->get_user->name ?? ''}}</button>
					</p>
				</a>
			</div>
		</div>
		@endif
<hr class="horizontal-line-list-page">
@endforeach

@endif