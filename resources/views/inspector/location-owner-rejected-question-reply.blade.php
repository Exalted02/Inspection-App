@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($categoryData);die;
  use Carbon\Carbon;
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
	 
	$corrective_action = App\Models\Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
	$corrective_plan  = $corrective_action ? $corrective_action->lo_corrective_action_plan : '';
	
	$inspector_action_date  = $corrective_action ? $corrective_action->inspector_action_date : '';
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
		
		$corrective_action = App\Models\Task_list_corrective_action::where('task_list_id', $task_id)->where('checklist_id', $checklist_id)->first();
		$corrective_plan  = $corrective_action ? $corrective_action->lo_corrective_action_plan : '';
		
		$inspector_action_date  = $corrective_action ? $corrective_action->inspector_action_date : '';
	}
 }
 
	$taskData = App\Models\Task_lists::where('id',$task_id)->first();
	$task_location_id = $taskData ? $taskData->location_id : '';
	$task_category_id = $taskData ? $taskData->category_id : '';
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
								<div class="mt-1">
								{{ $corrective_plan ?? ''}}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="owner-checklist">
								<label>Completed By</label>
								<div class="mt-1">
								{{ Carbon::parse($inspector_action_date)->format('d M Y') }}
								</div>
							</div>
						</div>
						{{--<form id="frmreply" action="{{ route('save-lo-reply-rejected-question') }}" enctype="multipart/form-data" method="post">--}}
							<div class="row">
								<div class="owner-checklist">
									<label>Second checks</label>
									<textarea name="lo_corrective_action_plan" id="lo_corrective_action_plan" placeholder="Add some remarks (optional)" class="form-control"></textarea>
									<span id="action_plan" style="display: none; color: red;">This field is require.</span>
								</div>
							</div>
							<div class="row align-items-center">
								<div class="col-md-4">
									<label for="lo_file"></label>
									<div class="upload-wrapper">
									  <input type="file" name="lo_file[]" id="lo_file" multiple style="display: none;">
									  <label for="lo_file" class="custom-upload-label">
										<span class="upload-text">Upload image</span>
										<i class="fa fa-upload upload-icon"></i>
									  </label>
									</div>
								</div>
								<div class="col-md-8 d-flex flex-wrap gap-2" id="preview-container">
								</div>
							</div>
							<input type="hidden" id="task_id" value="{{ $task_id ?? ''}}">
							<input type="hidden" id="checklist_id" value="{{ $checklist_id  ?? ''}}">
							<input type="hidden" id="subchecklist_id" value="{{ $subchecklist_id ?? ''}}">
							<input type="hidden" id="type" value="{{ $type ?? ''}}">
							<input type="hidden" id="tab" value="{{ $tab ?? ''}}">
							<input type="hidden" id="location_id" value="{{ $task_location_id ?? ''}}">
								{{--<input type="hidden" id="category_id" value="{{ $task_category_id ?? ''}}">--}}
							
					</div>
					
				</div>
			</section>
		</div>
    </div>
	<div class="checklist-question-sticky-footer">
		<div class="clearfix"></div>
		<div class="footer-content question-navigation d-flex justify-content-between">
			<button class="reject-class-button location-owner-rejected">Reject</button>
			<button class="ms-auto location-owner-approve">Approve</button>
		</div>
	</div>
	{{--</form>--}}
@endsection 
@section('scripts')
<script src="{{ url('front-assets/css/bootstrap.min.css') }}"></script>
	{{--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>--}}
		{{--<script src="{{url('front-assets/js/moment.min.js') }}"></script>
<script src="{{url('front-assets/js/bootstrap-datetimepicker.min.js') }}"></script>--}}

<script>
/*document.getElementById('lo_file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || "Upload image";
    document.querySelector('.upload-text').textContent = fileName;
});*/
</script>

<script>
$(document).ready(function() {
	
  
let previewContainer = $('#preview-container');
let selectedFiles = [];
	
$('#lo_file').on('change', function (e) {
    //let files = e.target.files;
	let files = Array.from(e.target.files); // new
	selectedFiles = files; // new
	//selectedFiles = [...selectedFiles, ...files];
    previewContainer.empty(); // Clear previous previews

    Array.from(files).forEach((file, index) => {
      if (file && file.type.startsWith('image/')) {
        let reader = new FileReader();
		//$('#preview-container').show();
        reader.onload = function (e) {
          let imgHtml = '<div class="preview-image-wrapper" data-index="' + index +'"><img src="' + e.target.result + '" class="preview-image"><div class="remove-image" data-index="' + index +'">&times;</div></div>';
          previewContainer.append(imgHtml);
        };

        reader.readAsDataURL(file);
      }
    });
	
	//updateFileInput();
  });


  // Delegate remove button click
  previewContainer.on('click', '.remove-image', function () {
	const indexToRemove = $(this).data('index');
	//alert(indexToRemove);alert(selectedFiles);
    $(this).parent().remove();
	selectedFiles[indexToRemove] = null;
	selectedFiles = selectedFiles.filter(file => file !== null);
  });
   
   $(document).on('click','.location-owner-approve', function(){
	   //e.preventDefault();
	   var task_id = $('#task_id').val();
	   var checklist_id = $('#checklist_id').val();
	   var subchecklist_id = $('#subchecklist_id').val();
	   var type = $('#type').val();
	   //var category_id = $('#category_id').val();
	   var location_id = $('#location_id').val();
	   
	   let lo_corrective_action_plan = $('#lo_corrective_action_plan').val().trim();
	   if(lo_corrective_action_plan=='')
	   {
		   $('#action_plan').fadeIn().delay(2000).fadeOut();
		   return false;
	   }
	   
	   let files = $('#lo_file')[0].files;
	   //alert(files.length);
	    /*if (files.length === 0) {
			alert('Please select at least one image.');
			return;
		}*/
		
		
		let formData = new FormData();

		// Append all selected files to formData
		/*$.each(files, function (index, file) {
			formData.append('lo_file[]', file);
		});*/
		
		selectedFiles.forEach(file => {
			formData.append('lo_file[]', file);
		});
		
		//alert(csrfToken) // show ok 
		// Optional: Add other data
		formData.append('task_id', task_id);
		formData.append('checklist_id', checklist_id);
		formData.append('subchecklist_id', subchecklist_id);
		formData.append('type', type);
		formData.append('content', lo_corrective_action_plan);
		formData.append('_token', csrfToken);
		var URL = "{{ route('save-lo-reply-rejected-question') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: formData,
			contentType: false,
			processData: false,  
			success: function(response) {
				//alert(response.message);
				if(response.message=='success')
				{
					//history.back();
					var activeTab = 0;
					var baseUrl = "{{ url('/location-owner') }}";
					var redirectUrl = baseUrl + '/'+ location_id + '/' + task_id + '/' + activeTab ;
					window.location.href = redirectUrl;
				}
				
			},
		});
	});
});

function updateFileInput() {
  const dataTransfer = new DataTransfer();
  selectedFiles.forEach(file => {
    if (file) dataTransfer.items.add(file);
  });
  document.getElementById('lo_file').files = dataTransfer.files;
}
</script>
@endsection

