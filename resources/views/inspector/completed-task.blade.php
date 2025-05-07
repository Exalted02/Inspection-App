@extends('layouts.app')
@section('content')
@php 
//echo $checklistdata[0]->subcategory_id;die;
 //echo "<pre>";print_r($checklistdata);die;
 $subcategoryname = App\Models\Subcategory::where('id', $checklistdata[0]->subcategory_id)->first()->name;
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist">
	<h2 class="checklist-title">Review your checklist</h2>
	Review and check before moving on to </br>next section. Checmical bonding & storage
		<div class="location-section">
			<div class="location-label">{{ $subcategoryname ?? ''}}</div>
				
		</div>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container1">
					<div class="custom-tab">
						<!-- Tabs -->
						{{--<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#uncomplete_tab" aria-controls="uncomplete_tab" role="tab" data-toggle="tab">12 Uncompleted</a></li>
							<li role="presentation"><a href="#reject_tab" aria-controls="reject_tab" role="tab" data-toggle="tab">6 Rejected</a></li>
						</ul>--}}
						<!-- Tab panes -->
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane active" id="uncomplete_tab">
								@foreach($checklistdata as $checklists)
								
								<div class="checklist-item">
									<div class="text">
										<div class="title">{{ $checklists->name ?? ''}}</div>
										<div class="subtitle">Accepted </div>
									</div>
									<a href="javascript:void(0)"><div class="arrow get_checklist" data-checklist="{{ $checklists->id }}" data-task="{{ $task_id }}" data-cat="{{ $category_id }}" data-subcat="{{ $subcategory_id }}"><small>Edit</small></div></a>
								</div>
								@endforeach
								
								<div class="sticky-footer">
									<button class="submit_task">Submit checklist</button>
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
	<input type="text" value="{{ $task_id ?? ''}}" id="task_id">
	<input type="text" value="{{ $category_id ?? ''}}" id="category_id">
	<input type="text" value="{{ $subcategory_id ?? ''}}" id="subcategory_id">
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
   $(document).on('click','.submit_task', function(){
		var task_id  = $('#task_id').val();
		var category_id = $('#category_id').val();
		var subcategory_id = $('#subcategory_id').val();
		var URL = "{{ route('submit-completed-task') }}";
		
		$.ajax({
			url: URL,
			type: "POST",
			data: {task_id:task_id, category_id:category_id, subcategory_id:subcategory_id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response);
				//$('#addressInput').val('');
				//$('#successMessage').fadeIn().delay(2000).fadeOut();
			},
		});
   });
   
   /*$(document).on('click','.get_checklist', function(){
	   var cat_id = $(this).data('cat');
	   var subcat_id = $(this).data('subcat');
	   var task_id = $(this).data('task');
	   var checklist_id = $(this).data('checklist');
	   var URL = "{{ route('get-checklist-page') }}";
	   $.ajax({
			url: URL,
			type: "POST",
			data: {checklist_id:checklist_id, task_id:task_id, cat_id:cat_id, subcat_id:subcat_id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				//alert(response.hasData);
				if (response.html) {
					 alert(response.html);
					$('.checklist-question').html(response.html);
				}
			},
		});
	   
   });*/
});
</script>
@endsection

