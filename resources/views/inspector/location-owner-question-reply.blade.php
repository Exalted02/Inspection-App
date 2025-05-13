@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($categoryData);die;
 $checklist = App\Models\Checklist::where('id', $checklist_id)->first();
 if($type == 'checklist')
 {
	 $taskChecklist = App\Models\Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
	 
	 $images = App\Models\Task_list_checklist_rejected_files::where('task_list_checklist_id', $taskChecklist->id)->get();
	 
 }
 
 $taskSubChecklist = null;
 if($type == 'subchecklist')
 {
	 
	 $taskSubChecklist = App\Models\Task_list_subchecklists::where('task_list_id', $task_id)->where('task_list_checklist_id', $checklist_id)->where('subchecklist_id', $subchecklist_id)->where('approve', 0)->first();
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
					@if($type == 'checklist')
						
						<div class="row">
							<div class="owner-checklist-title">Reason</div>
						</div>
						<div class="row">
						<div class="owner-checklist">{{ $taskChecklist->rejected_region ?? '' }}</div>
						</div>
						
						
						<div class="row">
							<div class="owner-checklist">
								@if($images->isNotEmpty())
									@foreach($images as $image)
									<div class="cheklist-reply-images">
										<img src="{{ url('uploads/reject-files/' .$image->file ) }}">
									</div>
									@endforeach
								@endif
							</div>
						</div>
						<div class="row">
							<div class="owner-checklist">
							<label>How to solve the issue ?</label>
							<textarea name="reply-question" placeholder="Input corrective action plan" class="form-control"></textarea>
							</div>
						</div>
						<div class="row">
							<div class="owner-checklist">
							<label></label>
							<div class="cal-icon">
							<input type="text" class="form-control datetimepicker" name="reply_date" placeholder="set timeline">
							</div>
							</div>
						</div>
					@else 
						@php
							$subImages = collect();
							$subChecklistName = '';
							$rejected_region = '';
							
							if ($taskSubChecklist) {
								$subImages = App\Models\Task_list_subchecklist_rejected_files::where('task_list_checklist_id',$taskSubChecklist->task_list_checklist_id)->where('task_list_subchecklist_id', $taskSubChecklist->id)->get();
								
								$subChecklistName = App\Models\Subchecklist::where('id', $taskSubChecklist->subchecklist_id)->first()->name;
								
								$rejected_region = $taskSubChecklist->rejected_region;
							}
							
						@endphp
						<div class="row">
							<div class="owner-subchecklist-title">{{ $subChecklistName ?? ''}}</div>
						</div>
						<div class="row">
							<div class="owner-checklist-title">Reason</div>
						</div>
						<div class="row">
							<div class="owner-checklist">{{ $rejected_region ?? '' }}</div>
						</div>
						
						
						<div class="row">
							<div class="owner-checklist">
								@if($subImages->isNotEmpty())
									@foreach($subImages as $image)
									<div class="cheklist-reply-images">
										<img src="{{ url('uploads/reject-files/subchecklist/' .$image->file ) }}">
									</div>
									@endforeach
								@endif
							</div>
						</div>
						<div class="row">
							<div class="owner-checklist">
							<label>How to solve the issue ?</label>
							<textarea name="reply-question" placeholder="Input corrective action plan" class="form-control"></textarea>
							</div>
						</div>
						<div class="row">
							<div class="owner-checklist">
							<label></label>
							{{--<input type="text" class="form-control datetimepicker" name="reply_date" placeholder="set timeline">--}}
							<div class="cal-icon"><input class="form-control datetimepicker" type="text" name="reply_date"></div>
							</div>
						</div>
					@endif
					
						
					</div>
					<div class="sticky-footer">
							<button>Submit checklist</button>
						</div>
				</div>
			</section>
		</div>
    </div>
@endsection 
@section('scripts')
<script src="{{url('front-assets/js/moment.min.js') }}"></script>
<script src="{{url('front-assets/js/bootstrap-datetimepicker.min.js') }}"></script>
<script>
$(document ).ready(function() {
  
   
   /*$(document).on('click','.chk-task-id', function(){
	   var cat_id = $(this).data('cat');
	   var subcat_id = $(this).data('subcat');
	   var location_id = $(this).data('location');
	   var URL = "{{ route('check-task-id') }}";
	   $.ajax({
			url: URL,
			type: "POST",
			data: {cat_id:cat_id,location_id:location_id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.hasData);
				$('#taskid').val(response.taskid);
				if(!response.hasData)
				{
					$('#errorMessage').fadeIn().delay(2000).fadeOut();
				}
				else {
					var taskid = $('#taskid').val();
					var baseUrl = "{{ url('/checklist-question') }}";
					var redirectUrl = baseUrl + '/'+ taskid + '/' + cat_id + '/' + subcat_id;
					window.location.href = redirectUrl;
				}
			},
		});
	   
   });*/
});
</script>
@endsection

