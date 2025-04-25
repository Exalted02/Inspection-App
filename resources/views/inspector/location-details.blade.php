@extends('layouts.app')
@section('content')
@php 
//echo "<pre>";print_r($location_categories);die;
@endphp
    <!-- =-=-=-=-=-=-= Breadcrumb =-=-=-=-=-=-= -->
	<div class="container location-details">
		<div class="d-flex align-items-center location-header mb-3">
			<img src="{{url('uploads/location/' . $location_categories[0]->image )}}" alt="Location" />
			<div>
				<div class="title">{{ $location_categories[0]->location_name ?? ''}}</div>
				<small class="text-muted"><i class="fa fa-location-dot mr-5px"></i>Mandai Road 23, 532012</small>
			</div>
		</div>
		<!-- =-=-=-=-=-=-= Breadcrumb End =-=-=-=-=-=-= --> 
		<!-- =-=-=-=-=-=-= Main Content Area =-=-=-=-=-=-= -->
		<div class="main-content-area clearfix">
			<section class="custom-padding1">
				<div class="container">
					<div class="row custom-tab">
						<!-- Tabs -->
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#inprogress_tab" aria-controls="inprogress_tab" role="tab" data-toggle="tab">4 In progress</a></li>
							<li role="presentation"><a href="#completed_tab" aria-controls="completed_tab" role="tab" data-toggle="tab">Completed</a></li>
						</ul>
						<!-- Tab panes -->
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane active" id="inprogress_tab">
								@foreach($location_categories[0]->category_by_location as $categories)
								@php 
								   $categoryData = App\Models\Category::where('id', $categories->category_id)->first();
								@endphp
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<div class="date">
											<div class="day">FEB</div>
											<div class="dow">15</div>
											<div class="dod">FRI</div>
										</div>
									</div>
									<div class="flex-grow-1">
										<a href="{{ route('category', ['location_id'=>$categories->location_id, 'cat_id' => $categoryData->id]) }}">
											<img src="{{url('uploads/category/' .$categoryData->image )}}" alt="Task"/>
											<h6>{{ $categoryData->name ?? '' }}</h6>
											<p class="text-muted mb-0">Set corrective actions</p>
										</a>
									</div>
								</div>
								@endforeach
							</div>
							<div role="tabpanel" class="tab-pane" id="completed_tab">
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<div class="date">
											<div class="day">JAN</div>
											<div class="dow">31</div>
											<div class="dod">FRI</div>
										</div>
									</div>
									<div class="flex-grow-1">
										<a href="{{route('category', ['location_id'=>1,'cat_id'=>1])}}">
											<img src="{{url('front-assets/static-image/3.jpg')}}" alt="Task" />
											<h6>Respirator user has a training sticker on employee badge</h6>
											<p class="text-muted mb-0">Set corrective actions</p>
										</a>
									</div>
								</div>
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<div class="date">
											<div class="day">JULY</div>
											<div class="dow">11</div>
											<div class="dod">TUE</div>
										</div>
									</div>
									<div class="flex-grow-1">
										<a href="{{route('category', ['location_id'=>1,'cat_id'=>1]) }}">
											<img src="{{url('front-assets/static-image/2.jpg')}}" alt="Task" />
											<h6>Respirator user has a training sticker on employee badge</h6>
											<p class="text-muted mb-0">Set corrective actions</p>
										</a>
									</div>
								</div>
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<div class="date">
											<div class="day">FEB</div>
											<div class="dow">15</div>
											<div class="dod">FRI</div>
										</div>
									</div>
									<div class="flex-grow-1">
										<a href="{{route('category', ['location_id'=>1,'cat_id'=>1])}}">
											<img src="{{url('front-assets/static-image/1.jpg')}}" alt="Task" />
											<h6>Respirator user has a training sticker on employee badge</h6>
											<p class="text-muted mb-0">Set corrective actions</p>
										</a>
									</div>
								</div>
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

