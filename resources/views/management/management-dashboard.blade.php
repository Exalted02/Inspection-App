@extends('layouts.app')
@section('content')
    <div class="container">
		<h2 class="page-title">Welcome to your overview</h2>
		<div class="page-subtitle">Check out how your factory is performing</div>
		<div class="pt-3 pb-3">
			<div class="row">
				<div class="col-md-4 col-sm-4 col-xs-4">
					<div class="bg small-card">
						<div class="bg small-card-title">No. of inspections</div>
						<div class="bg small-card-counter">42</div>
						<div class="bg small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4">
					<div class="bg small-card">
						<div class="bg small-card-title">No. of observations</div>
						<div class="bg small-card-counter">8</div>
						<div class="bg small-card-counter-title">WEEKLY</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4">
					<div class="bg small-card">
						<div class="bg small-card-title">Time to close observation</div>
						<div class="bg small-card-counter">2</div>
						<div class="bg small-card-counter-title">DAYS</div>
					</div>
				</div>
			</div>
		</div>
    </div>
@endsection 
@section('scripts')

@endsection

