@extends('layouts.app')
@section('content')
@php 
 //echo "<pre>";print_r($categoryData);die;
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist">
		<h2 class="checklist-title">{{ $categoryData[0]->name ?? '' }}</h2>
		<div class="location-section">
			<div class="location-label">Location details</div>
			<div class="location-input" id="displayBox">
				Tap to add address
				<span><i class="fa-solid fa-pen"></i></span>
			</div>
			<span id="successMessage" style="display: none; color: green;">
				Details saved successfully!
			</span>
			<span id="errorMessage" style="display: none; color: red;">
				Please enter details.
			</span>
			<div class="location-edit" id="editBox">
				<input type="text" id="addressInput" placeholder="Add location" value="{{ $location_details ?? ''}}"/>
				<button id="doneBtn" class="donesubmit">Done</button>
			</div>
			<input type="hidden" id="location_id" value="{{ $location_id ?? ''}}">
			<input type="hidden" id="category_id" value="{{ $categoryData[0]->id ?? '' }}">
			<input type="hidden" id="taskid">
		</div>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container1">
					<div class="custom-tab">
						<!-- Tabs -->
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#uncomplete_tab" aria-controls="uncomplete_tab" role="tab" data-toggle="tab">12 Uncompleted</a></li>
							<li role="presentation"><a href="#reject_tab" aria-controls="reject_tab" role="tab" data-toggle="tab">6 Rejected</a></li>
						</ul>
						<!-- Tab panes -->
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane active" id="uncomplete_tab">
								@foreach($categoryData[0]->get_subcategory as $subcategories)
								@php 
									$tot_checklist = App\Models\Checklist::where('category_id', $categoryData[0]->id)->where('subcategory_id', $subcategories->id)->count();
									$tot_checklist_completed = App\Models\Task_list_checklists::where('task_list_id',$task_id)->where('task_list_subcategory_id', $subcategories->id)->count();
									$tot_subchecklist_completed = App\Models\Task_list_subchecklists::where('task_list_id', $task_id)
																	->where('task_list_subcategory_id', $subcategories->id)
																	->distinct('task_list_checklist_id')
																	->count();
									$tot_completed_task = $tot_checklist_completed+$tot_subchecklist_completed ;

								@endphp
								<div class="checklist-item">
									<div class="text">
										<div class="title">{{ $subcategories->name ?? ''}}</div>
										<div class="subtitle">Completed {{ $tot_completed_task ?? ''}} of {{ $tot_checklist ?? ''}}</div>
									</div>
									{{--<a href="{{route('checklist-question' ,['cat_id'=>$categoryData[0]->id, 'subcat_id'=>$subcategories->id])}}"><div class="arrow"><i class="fa-solid fa-arrow-right"></i></div></a>--}}
									<a href="jacascript:void(0);" class="chk-task-id" data-cat="{{ $categoryData[0]->id ?? ''}}" data-subcat="{{ $subcategories->id ?? '' }}" data-location="{{ $location_id ?? ''}}"><div class="arrow"><i class="fa-solid fa-arrow-right"></i></div></a>
								</div>
								@endforeach
								
								<div class="sticky-footer">
									<button>Submit checklist</button>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane" id="reject_tab">
								Not have any data
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
    </div>
@endsection 
@section('scripts')
<script>
	const displayBox = document.getElementById("displayBox");
	const editBox = document.getElementById("editBox");
	const addressInput = document.getElementById("addressInput");
	const doneBtn = document.getElementById("doneBtn");

	displayBox.addEventListener("click", () => {
		displayBox.style.display = "none";
		editBox.style.display = "flex";
		addressInput.focus();
	});

	doneBtn.addEventListener("click", () => {
		const value = addressInput.value.trim();
		if (value !== "") {
			displayBox.innerHTML = `
				${value}
				<span class="add_address"><i class="fa-solid fa-pen"></i></span>
			`;
		} else {
			displayBox.innerHTML = `
				Tap to add address
				<span class="add_address"><i class="fa-solid fa-pen"></i></span>
			`;
		}
		displayBox.style.display = "flex";
		editBox.style.display = "none";
	});
</script>
<script>
$(document ).ready(function() {
   $(document).on('click','.donesubmit', function(){
		var location_id = $('#location_id').val();
		var category_id = $('#category_id').val();
		var details  = $('#addressInput').val();
		if(details=='')
		{
			$('#errorMessage').fadeIn().delay(2000).fadeOut();
			return false;
		}
	    //alert(location_id);alert(category_id);alert(details);
		var URL = "{{ route('send-location-details') }}";
		
		$.ajax({
			url: URL,
			type: "POST",
			data: {location_id:location_id,category_id:category_id,details:details, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response);
				$('#addressInput').val('');
				$('#successMessage').fadeIn().delay(2000).fadeOut();
			},
		});
   });
   
   $(document).on('click','.chk-task-id', function(){
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
	   
   });
});
</script>
@endsection

