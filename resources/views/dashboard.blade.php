@extends('layouts.app')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
    
        <!-- Page Content -->
        <div class="content container-fluid pb-0">
        
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Welcome Admin!</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
        
            <div class="row">
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
						{{--<span class="dash-widget-icon"><i class="fa-solid fa-chart-line"></i></span>--}}
                            <span class="dash-widget-icon"><i class="fa-solid fa-handshake"></i></span>
                            <div class="dash-widget-info">
                                <h3>45</h3>
                                <span>Consumer</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
						{{--<span class="dash-widget-icon"><i class="fa-regular fa-user"></i></span>--}}
                            <span class="dash-widget-icon"><i class="fa-solid fa-store"></i></span>
                            <div class="dash-widget-info">
                                <h3>25</h3>
                                <span>Retailer</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
						{{--<span class="dash-widget-icon"><i class="fa-solid fa-person-shelter"></i></span>--}}
                            <span class="dash-widget-icon"><i class="fa fa-money-bill-wave"></i></span>
                            <div class="dash-widget-info">
                                <h3>102</h3>
                                <span>Order</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="fa-solid fa-people-pulling"></i></span>
                            <div class="dash-widget-info">
                                <h3>3</h3>
                                <span>Review</span>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="fa fa-box"></i></span>
                            <div class="dash-widget-info">
                                <h3>29</h3>
                                <span>Wishlist</span>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="fa-solid fa-store"></i></span>
                            <div class="dash-widget-info">
                                <h3>40</h3>
                                <span>Products</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        </div>
        <!-- /Page Content -->

    </div>
    <!-- /Page Wrapper -->

@endsection 
@section('scripts')

@endsection

