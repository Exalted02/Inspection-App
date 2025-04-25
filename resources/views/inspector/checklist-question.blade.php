@extends('layouts.app')
@section('content')
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist-question">
		<div class="single-checklist d-none1">
			<div class="question-header">{{ $checklistdata->get_subcategory->name ?? '' }}</div>
			<div class="question-text">
				{{ $checklistdata->name ?? '' }}
				<input type="hidden" id="current_id" value="{{ $checklistdata->id ?? '' }}">
				<input type="hidden" id="category_id" value="{{ $checklistdata->category_id ?? '' }}">
				<input type="hidden" id="subcategory_id" value="{{ $checklistdata->subcategory_id ?? '' }}">
			</div>
			<div class="reject-form mb-3" id="rejectForm-1">
				<textarea placeholder="State why you rejected this..."></textarea>

				<label class="upload-box">
					<input type="file" id="fileInput-1" multiple style="display: none;" onchange="previewFiles(1)">
					<span>Upload files</span>
				</label>

				<div class="preview-container" id="previewContainer-1"></div>
			</div>
			<div class="action-buttons-without-text">
				<button class="rejected" id="question-reject-1" onclick="handleReject(1)"><i class="fa-solid fa-xmark"></i></button>
				<button class="approved" id="question-approve-1" onclick="handleApprove(1)"><i class="fa-solid fa-check"></i></button>
			</div>
		</div>
		<div class="sub-checklist">
			<div class="question-header">Personal Protective Equipments</div>
			<div class="question-text">
				PPE used during Chemical pouring and handling waste:
			</div>
			<div class="sub-checklist-question">
				<div class="action-buttons">
					<span class="d-flex align-items-center">Safety goggles</span>
					<div class="btn-div">
						<button class="rejected" id="question-reject-2" onclick="handleReject(2)"><i class="fa-solid fa-xmark"></i></button>
						<button class="approved" id="question-approve-2" onclick="handleApprove(2)"><i class="fa-solid fa-check"></i></button>
					</div>
				</div>
				<div class="reject-form mb-3" id="rejectForm-2">
					<textarea placeholder="State why you rejected this..."></textarea>

					<label class="upload-box">
						<input type="file" id="fileInput-2" multiple style="display: none;" onchange="previewFiles(2)">
						<span>Upload files</span>
					</label>

					<div class="preview-container" id="previewContainer-2"></div>
				</div>
			</div>
			<div class="sub-checklist-question">
				<div class="action-buttons">
					<span class="d-flex align-items-center">Face shield</span>
					<div class="btn-div">
						<button class="rejected" id="question-reject-3" onclick="handleReject(3)"><i class="fa-solid fa-xmark"></i></button>
						<button class="approved" id="question-approve-3" onclick="handleApprove(3)"><i class="fa-solid fa-check"></i></button>
					</div>
				</div>
				<div class="reject-form mb-3" id="rejectForm-3">
					<textarea placeholder="State why you rejected this..."></textarea>

					<label class="upload-box">
						<input type="file" id="fileInput-3" multiple style="display: none;" onchange="previewFiles(3)">
						<span>Upload files</span>
					</label>

					<div class="preview-container" id="previewContainer-3"></div>
				</div>
			</div>
		</div>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->		
    </div>
	<div class="checklist-question-sticky-footer">
		<div class="progress-bar">
			<span style="width: 40%;"></span>
		</div>
		<div class="clearfix"></div>
		<div class="footer-content">
			<button>Back</button>
			<button class="next_question">Next</button>
		</div>
	</div>
@endsection 
@section('scripts')
<script>
function handleReject(id) {
	document.getElementById('rejectForm-'+id).style.display = 'flex';
	
	document.getElementById("question-approve-"+id).classList.remove("active");
	document.getElementById("question-reject-"+id).classList.add("active");
}
function handleApprove(id) {
	document.getElementById('rejectForm-'+id).style.display = 'none';
	
	document.getElementById("question-reject-"+id).classList.remove("active");
	document.getElementById("question-approve-"+id).classList.add("active");
}

function previewFiles(id) {
	const container = document.getElementById('previewContainer-'+id);
	const input = document.getElementById('fileInput-'+id);
	container.innerHTML = ""; // Clear previous previews

	Array.from(input.files).forEach(file => {
		const reader = new FileReader();
		reader.onload = function (e) {
			const img = document.createElement('img');
			img.src = e.target.result;
			container.appendChild(img);
		}
		reader.readAsDataURL(file);
	});
}
</script>
<script>
$(document ).ready(function() {
    $(document).on('click','.next_question', function(){
		var current_id = $('#current_id').val();
		var category_id = $('#category_id').val();
		var subcategory_id = $('#subcategory_id').val();
		var URL = "{{ route('checklist-next-question') }}";
		$.ajax({
			url: URL,
			type: "POST",
			data: {current_question_id:current_id,category_id:category_id,subcategory_id:subcategory_id, _token: csrfToken},
			dataType: 'json',
			success: function(response) {
				alert(response);
				//$('#addressInput').val('');
				//$('#successMessage').fadeIn().delay(2000).fadeOut();
			},
		});
	})
});
</script>
@endsection

