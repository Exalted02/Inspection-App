@extends('layouts.app')
@section('content')
    <div class="container location-details">
		<div class="d-flex align-items-center location-header mb-3">
			<img src="{{url('front-assets/static-image/5.jpg')}}" alt="Location" />
			<div>
				<div class="title">Mandai Hill</div>
				<small class="text-muted"><i class="fa fa-location-dot mr-5px"></i>Mandai Road 23, 532012</small>
			</div>
		</div>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
					<div class="pt-2 pb-2">
						<div class="row ">
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-odd">
								<div class="bg small-card">
									<div class="small-card-title">No. of inspections</div>
									<div class="small-card-counter">4</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-even">
								<div class="bg small-card">
									<div class="small-card-title">No. of observations</div>
									<div class="small-card-counter">8</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-odd">
								<div class="bg small-card">
									<div class="small-card-title">Time to close observation</div>
									<div class="small-card-counter">6</div>
									<div class="small-card-counter-title">DAYS</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 small-card-even">
								<div class="bg small-card">
									<div class="small-card-title">Repeated observations found</div>
									<div class="small-card-counter">2</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
						</div>
					</div>
				<div class="container">
					<div class="row">
						<div class="d-flex mb-3 task">
							<div class="date-box">
								<div class="date">
									<div class="day">FEB</div>
									<div class="dow">15</div>
									<div class="dod">FRI</div>
								</div>
							</div>
							<div class="flex-grow-1">
								<a href="">
									<img src="{{url('front-assets/static-image/1.jpg')}}" alt="Task" />
									<h6 class="location-observation-title">Respirator user has a training sticker on employee badge</h6>
									<p class="text-muted location-observation-title mb-0">Pending LOS to approve</p>
								</a>
							</div>
						</div>
						<div class="d-flex mb-3 task">
							<div class="date-box">
								<div class="date">
									<div class="day">FEB</div>
									<div class="dow">11</div>
									<div class="dod">FRI</div>
								</div>
							</div>
							<div class="flex-grow-1">
								<a href="">
									<img src="{{url('front-assets/static-image/2.jpg')}}" alt="Task" />
									<h6 class="location-observation-title">Respirator user has a training sticker on employee badge</h6>
									<p class="text-muted location-observation-title mb-0">Pending LOS to approve</p>
								</a>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
    </div>
@endsection 
@section('scripts')

@endsection

