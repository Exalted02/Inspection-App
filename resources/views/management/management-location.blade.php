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
				<div class="container">
					<div class="pt-2 pb-2">
						<div class="row ">
							<div class="col-md-4 col-sm-4 col-xs-4 small-card-first">
								<div class="bg small-card">
									<div class="small-card-title">No. of inspections</div>
									<div class="small-card-counter">42</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-4 col-xs-4 small-card-second">
								<div class="bg small-card">
									<div class="small-card-title">No. of observations</div>
									<div class="small-card-counter">8</div>
									<div class="small-card-counter-title">WEEKLY</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-4 col-xs-4 small-card-third">
								<div class="bg small-card">
									<div class="small-card-title">Time to close observation</div>
									<div class="small-card-counter">2</div>
									<div class="small-card-counter-title">DAYS</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row custom-tab">
						<!-- Tabs -->
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#inprogress_tab" aria-controls="inprogress_tab" role="tab" data-toggle="tab">4 In progress</a></li>
							<li role="presentation"><a href="#completed_tab" aria-controls="completed_tab" role="tab" data-toggle="tab">Corrective checked</a></li>
						</ul>
						<!-- Tab panes -->
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane active" id="inprogress_tab">
							@if($task_list_data->isNotEmpty())
								@foreach($task_list_data as $tasks)
								@php
								
								   /*$categoryData = App\Models\Category::where('id', $categories->category_id)->first();*/
								   
								   $month = Carbon::parse($tasks->created_at)->format('M');
								   $day =   Carbon::parse($tasks->created_at)->format('d');
								   $week= strtoupper(Carbon::parse($tasks->created_at)->format('D'));
								   
								   //$img = $categoryData ? $categoryData->image : '';
								@endphp
								<div class="d-flex mb-3 task">
									<div class="date-box">
										<div class="date">
											<div class="day"> {{ $month ?? '' }} </div>
											<div class="dow">{{ $day ?? '' }}</div>
											<div class="dod"> {{ $week ?? '' }}</div>
										</div>
									</div>
									<div class="flex-grow-1">
									@if(auth()->user()->user_type == 1)
										<a href="{{ route('category', ['location_id'=>$tasks->location_id, 'cat_id' => $tasks->category_id ?? '']) }}">
									@elseif(auth()->user()->user_type == 2)
										<a href="{{ route('location-owner', ['location_id'=>$tasks->location_id, 'cat_id' => $tasks->category_id ?? '']) }}">
									@endif
										
										<img src="{{url('uploads/task/' . $tasks->image  )}}" alt="Task"/>
										
											<h6>{{ $tasks->task_title ?? '' }}</h6>
											<p class="text-muted mb-0">Set corrective actions</p>
										</a>
									</div>
								</div>
								@endforeach
							@else
								<div class="text-center"><strong><h3>No record found</h3></strong></div>
							@endif
							</div>
							<div role="tabpanel" class="tab-pane" id="completed_tab">
								
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

