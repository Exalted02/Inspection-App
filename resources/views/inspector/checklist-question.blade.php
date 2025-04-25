@extends('layouts.app')
@section('content')
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist-question">
		<div class="single-checklist d-none">
			<div class="question-header">Personal Protective Equipments</div>
			<div class="question-text">
				Use of Safety Goggles / Glasses at Sink / Clean Room
			</div>
			<div class="reject-form mb-3" id="rejectForm">
				<textarea placeholder="State why you rejected this..."></textarea>

				<label class="upload-box">
					<input type="file" id="fileInput-1" multiple style="display: none;" onchange="previewFiles(1)">
					<span>Upload files</span>
				</label>

				<div class="preview-container" id="previewContainer-1"></div>
			</div>
			<div class="action-buttons">
				<button class="rejected" id="question-reject-1" onclick="handleReject(1)"><i class="fa-solid fa-xmark"></i></button>
				<button class="approved" id="question-approve-1" onclick="handleApprove(1)"><i class="fa-solid fa-check"></i></button>
			</div>
		</div>
		<div class="sub-checklist">
			<div class="question-header">Personal Protective Equipments</div>
			<div class="question-text">
				PPE used during Chemical pouring and handling waste:
			</div>
			<div class="reject-form mb-3" id="rejectForm">
				<textarea placeholder="State why you rejected this..."></textarea>

				<label class="upload-box">
					<input type="file" id="fileInput-1" multiple style="display: none;" onchange="previewFiles(1)">
					<span>Upload files</span>
				</label>

				<div class="preview-container" id="previewContainer-1"></div>
			</div>
			<div class="action-buttons">
				<button class="rejected" id="question-reject-1" onclick="handleReject(1)"><i class="fa-solid fa-xmark"></i></button>
				<button class="approved" id="question-approve-1" onclick="handleApprove(1)"><i class="fa-solid fa-check"></i></button>
			</div>
		</div>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->		
    </div>
@endsection 
@section('scripts')
<script>
function handleReject(id) {
	document.getElementById('rejectForm').style.display = 'flex';
	
	document.getElementById("question-approve-"+id).classList.remove("active");
	document.getElementById("question-reject-"+id).classList.add("active");
}
function handleApprove(id) {
	document.getElementById('rejectForm').style.display = 'none';
	
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
@endsection

