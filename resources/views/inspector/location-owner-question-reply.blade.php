@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($categoryData);die;
 if($type == 'checklist')
 {
	 $checklist = App\Models\Checklist::where('id', $checklist_id)->first();
	 $taskChecklist = App\Models\Task_list_checklists::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
	 
	 $images = App\Models\Task_list_checklist_rejected_files::where('task_list_checklist_id', $checklist->id)->get();
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
					<div class="custom-tab">
					@if($type == 'checklist')
						<div class="row">
							<h2 class="owner-checklist-title">{{ $checklist->name ?? ''}}</h2>
						</div>
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
							<input type="text" class="form-control" name="reply_date" placeholder="set timeline">
							</div>
						</div>
						
						<div class="sticky-footer">
							<button>Submit checklist</button>
						</div>
					
						
					@endif
					</div>
				</div>
			</section>
		</div>
    </div>
@endsection 
@section('scripts')

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

