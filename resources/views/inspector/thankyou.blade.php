<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Thank You</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background-color: #002b5c; /* Bootstrap blue */
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
        }

        .thank-you-content {
            max-width: 500px;
        }

        .checkmark-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: white;
            color: #28a745;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 48px;
        }
		
		.thank-you-home {
            max-width: 200px;
			background-color: white;
			color: black;
			font-weight: bold;
			padding: 10px 20px;
			text-align: center;
			border-radius: 8px;
			margin: 200px auto 20px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
			cursor:pointer;
        }
    </style>
</head>
@php 
	use Carbon\Carbon;
	$datetimeExists = App\Models\Task_list_subcategories::where('task_list_id', $task_id)->first();
	$datetime = $datetimeExists ? $datetimeExists->created_at : '';
@endphp
<body>
    <div class="thank-you-content">
        <div class="checkmark-circle">
            <i class="fas fa-check"></i>
        </div>
        <h1>Thank {{ auth()->user()->name ?? '' }}!</h1>
        <p>Thanks for your hardwork ! Your safety</br>inspection has been submitted</p>
		<p>{{ Carbon::parse($datetime)->format('d M Y, H:i'); }}</p>
		<div class="thank-you-home">
			<span id="inspector-dashboard">Home</span>
		</div>
    </div>

<script src="{{ url('front-assets/js/jquery.min.js') }}"></script>
<script>
$(document).ready(function() {
	 $(document).on('click','#inspector-dashboard', function(){
		 window.location.href = "{{ route('inspector-dashboard') }}";
	 });
});
</script>	
	
</body>
</html>
