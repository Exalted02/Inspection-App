@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($categoryData);die;
 
 $rejected_region = '';
 $image_arr = [];
 
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
 }
 
 
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
							<h2 class="owner-checklist-title">{{ $checklist->name ?? ''}}</h2>
						</div>
						@if(!empty($subChecklistName))
						<div class="row">
							<div class="owner-subchecklist-title">{{ $subChecklistName ?? ''}}</div>
						</div>
						@endif
					
						
						<div class="row">
							<div class="owner-checklist-title">Reason</div>
						</div>
						<div class="row">
						<div class="owner-checklist">{{ $rejected_region ?? '' }}</div>
						</div>
						
						
						<div class="row">
							<div class="owner-checklist">
								@if(!empty($image_arr))
									@foreach($image_arr as $url)
									<div class="cheklist-reply-images">
										<img src="{{ $url['url'] ?? '' }}">
									</div>
									@endforeach
								@endif
							</div>
						</div>
						<div class="row">
							<div class="owner-checklist">
								<label>Requirement to solve it</label>
								<textarea name="lo_corrective_action_plan" id="lo_corrective_action_plan" placeholder="Input corrective action plan" class="form-control"></textarea>
								<span id="action_plan" style="display: none; color: red;">This field is require.</span>
							</div>
						</div>
						<div class="row">
							<div class="owner-checklist">
								<label class="d-block col-form-label"></label>
								<div class="status-toggle">
									<input type="checkbox" name="lo_direct_approve" id="lo_direct_approve" class="check">
									<label for="lo_direct_approve" class="checktoggle">Approve</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="owner-checklist">
							<label></label>
							<div class="cal-icon">
							<input type="date" class="form-control datetimepicker" name="lo_completed_by" id="lo_completed_by" placeholder="set timeline">
							</div>
							</div>
						</div>
					
					<input type="hidden" id="task_id" value="{{ $task_id ?? ''}}">
					<input type="hidden" id="checklist_id" value="{{ $checklist_id  ?? ''}}">
					<input type="hidden" id="subchecklist_id" value="{{ $subchecklist_id ?? ''}}">
					<input type="hidden" id="type" value="{{ $type ?? ''}}">
					<input type="hidden" id="tab" value="{{ $tab ?? ''}}">
						
					</div>
					<div class="sticky-footer">
						<button class="submitChecklist">Submit checklist</button>
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
   
   $(document).on('click','.submitChecklist', function(){
	   var task_id = $('#task_id').val();
	   var checklist_id = $('#checklist_id').val();
	   var subchecklist_id = $('#subchecklist_id').val();
	   var type = $('#type').val();
	   var tab = $('#tab').val();
	   let lo_corrective_action_plan = $('#lo_corrective_action_plan').val().trim();
	   let lo_completed_by = $('#lo_completed_by').val();
	   let lo_direct_approve = $('#lo_direct_approve').is(':checked');
	   //alert(lo_direct_approve);
	   
	   if(lo_corrective_action_plan=='')
	   {
		   $('#action_plan').fadeIn().delay(2000).fadeOut();
		   return false;
	   }
	  
		   var URL = "{{ route('submit-lo-corrective-action') }}";
		   $.ajax({
				url: URL,
				type: "POST",
				data: {type:type,task_id:task_id,checklist_id:checklist_id,subchecklist_id:subchecklist_id,tab:tab,lo_corrective_action_plan:lo_corrective_action_plan,lo_direct_approve:lo_direct_approve,lo_completed_by:lo_completed_by, _token: csrfToken},
				dataType: 'json',
				success: function(response) {
					let location_id = response.location_id;
					let category_id = response.category_id;
					
					var baseUrl = "{{ url('/location-owner') }}";
					var redirectUrl = baseUrl + '/'+ location_id + '/' + category_id;
					window.location.href = redirectUrl;
					
				},
			});
		
	   
   });
});
</script>
@endsection

