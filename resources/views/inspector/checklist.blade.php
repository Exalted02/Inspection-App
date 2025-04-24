@extends('layouts.app')
@section('content')
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container checklist">
		<h2 class="checklist-title">Safety inspection checklist</h2>
		<div class="location-section">
			<div class="location-label">Location details</div>
			<div class="location-input" id="displayBox">
				Tap to add address
				<span><i class="fa-solid fa-pen"></i></span>
			</div>
			<div class="location-edit" id="editBox">
				<input type="text" id="addressInput" placeholder="Add location" />
				<button id="doneBtn">Done</button>
			</div>
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
								<div class="checklist-item">
									<div class="text">
										<div class="title">Personal protective equipments</div>
										<div class="subtitle">Completed 3 of 7</div>
									</div>
									<a href=""><div class="arrow"><i class="fa-solid fa-arrow-right"></i></div></a>
								</div>
								<div class="checklist-item">
									<div class="text">
										<div class="title">Chemical handling & storage</div>
										<div class="subtitle">Not started yet</div>
									</div>
									<a href=""><div class="arrow"><i class="fa-solid fa-arrow-right"></i></div></a>
								</div>

								<div class="checklist-item">
									<div class="text">
										<div class="title">Process gases</div>
										<div class="subtitle">3 out of 7 questions</div>
									</div>
									<a href=""><div class="arrow"><i class="fa-solid fa-arrow-right"></i></div></a>
								</div>

								<div class="checklist-item">
									<div class="text">
										<div class="title">Exhaust ventilation</div>
										<div class="subtitle">3 out of 7 questions</div>
									</div>
									<a href=""><div class="arrow"><i class="fa-solid fa-arrow-right"></i></div></a>
								</div>

								<div class="checklist-item">
									<div class="text">
										<div class="title">Machine guarding</div>
										<div class="subtitle">3 out of 7 questions</div>
									</div>
									<a href=""><div class="arrow"><i class="fa-solid fa-arrow-right"></i></div></a>
								</div>
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
@endsection

