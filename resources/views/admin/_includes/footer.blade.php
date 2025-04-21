<footer class="footer">
    <div class="container-fluid">
        <div class="row text-muted">
            <div class="col-6 text-start">
                <p class="mb-0">
                    <a class="text-muted" href="#" target="_blank"><strong><?php echo date('Y')?> {{ config('app.name', 'Laravel') }}</strong></a>
                    &copy;
                </p>
            </div>
            {{-- <div class="col-6 text-end">
                <ul class="list-inline">
                    <li class="list-inline-item">
                        <a class="text-muted" href="#" target="_blank">Support</a>
                    </li>
                    <li class="list-inline-item">
                        <a class="text-muted" href="#" target="_blank">Help Center</a>
                    </li>
                    <li class="list-inline-item">
                        <a class="text-muted" href="#" target="_blank">Privacy</a>
                    </li>
                    <li class="list-inline-item">
                        <a class="text-muted" href="#" target="_blank">Terms</a>
                    </li>
                </ul>
            </div> --}}
        </div>
    </div>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	
	<!--Data table plugin start-->
	@if (request()->routeIs('admin.category','admin.make', 'admin.vehicledreven', 'admin.vehicle-edition','admin.engine-version','admin.colour', 'admin.agmodel','admin.fueltype','admin.subcategory', 'admin.vehiclestype', 'admin.cms', 'admin.email-management', 'admin.users.free', 'admin.users.abo', 'admin.users.purchase-history','admin.products','admin.post-ad-premium','admin.post-ad-free','admin.users.free-user-posts','admin.users.abo-user-posts','admin.faq','admin.users.free-user-test-drive','admin.users.free-user-monitoring','admin.users.abo-user-test-drive','admin.users.abo-user-monitoring','admin.seo','admin.news-blog'))
	<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.17.2/ckeditor.js" integrity="sha512-QuHtmTNLFyCbmk2jGlr0URK0XiNn1G0nHYMaNfbOLQgXBiO6RllC+xFkPO5YnG6zYnRVUj6b5uSXwmJeJgOLBw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
	<script>
		$(document).ready(function(){
			$('#example').DataTable( {
				"bDestroy": true,
				columnDefs: [
					{ orderable: false, targets: [0, -1, -2] }
				],
				order: [[1, 'asc']]
			});
		});
	</script>
	@endif
	<!--Data table plugin end-->
	
	<!--Delete modal start-->
		@if (request()->routeIs('admin.category','admin.make','admin.vehicledreven', 'admin.vehicle-edition','admin.engine-version','admin.vehicle-edition','admin.colour','admin.agmodel','admin.fueltype', 'admin.subcategory', 'admin.vehiclestype', 'admin.cms', 'admin.email-management', 'admin.users.free', 'admin.users.abo','admin.products','admin.post-ad-premium','admin.post-ad-free','admin.faq','admin.news-blog'))
			<!--Single Delete modal start-->
			<div class="modal fade" id="confirmDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-body">
						   Are you sure want to delete this record?
						</div>
						<div class="modal-footer">
							<button type="button" data-bs-dismiss="modal" class="btn btn-primary" id="delete">Delete</button>
							<button type="button" data-bs-dismiss="modal" class="btn btn-secondary" id="cancel">Cancel</button>
						</div>
					</div>
				</div>
			</div>
			<script>
			$(".delete-data").on('click', function (e) {
				$('#confirmDelete').modal("show");
				var id = $(this).data('id');
				var url = $(this).data('url');
				e.preventDefault();
				$('#confirmDelete').on('click', '#delete', function(e) {
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						 }
					});
					$.ajax({
						url: url,
						method: "POST",
						data: {id:id},
						dataType: 'json',
						success: function(response) {
							location.reload(true);
						}
					});
				});
			});
			</script>
			<!--Single Delete modal end-->
		@endif
		<!--Multiple Delete modal start-->
		@if (request()->routeIs('admin.category', 'admin.make','admin.vehicledreven','admin.engine-version','admin.vehicle-edition','admin.colour', 'admin.agmodel','admin.fueltype','admin.subcategory', 'admin.vehiclestype', 'admin.email-management', 'admin.users.free', 'admin.users.abo','admin.products','admin.post-ad-premium','admin.post-ad-free','admin.faq','admin.news-blog'))
			<div class="modal fade" id="confirmMultiDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						
						<div class="modal-body">
						</div>
						<div class="modal-footer">
							<input type="hidden" id="hiddenId">
				<button type="button" data-bs-dismiss="modal" class="btn btn-primary" id="delete">Delete</button>
				<button type="button"  data-bs-dismiss="modal" class="btn btn-secondary" id="cancel">Cancel</button>
						</div>
					</div>
				</div>
			</div>
			<script type="text/javascript">
			$('#delete_records').on('click', function(e) { 
				// alert(1);
				var url = $(this).data('url');
				var records = [];  
				$(".table input[name=chk_id]:checked").each(function() {  
					records.push($(this).data('emp-id'));
				});	
				if(records.length <=0)  {  
					$('#confirmChkSelect').modal("show");
				}else {
					$('#confirmMultiDelete').modal("show");	
					WRN_CAT_DELETE = "Are you sure you want to delete "+(records.length>1?"these":"this")+" row?";
					$("#confirmMultiDelete").find(".modal-body").text(WRN_CAT_DELETE);
					$('#confirmMultiDelete').on('click', '#delete', function(e) {	
						var selected_values = records.join(",");
						$.ajaxSetup({
							headers: {
								'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							 }
						});
						$.ajax({
							url: url,
							method: "POST",
							data: {id:selected_values},
							dataType: 'json',
							success: function(response) {
								location.reload(true);
							}
						});
					});		
				} 
			});
			</script>
		@endif
		<!--Multiple Delete modal end-->
	<!--Delete modal end-->
	
	<!--Status change modal start-->
		@if (request()->routeIs('admin.category', 'admin.make','admin.vehicledreven','admin.engine-version', 'admin.agmodel','admin.vehicle-edition','admin.colour','admin.fueltype','admin.subcategory', 'admin.vehiclestype', 'admin.cms', 'admin.email-management', 'admin.users.free', 'admin.users.abo','admin.products','admin.post-ad-premium','admin.post-ad-free','admin.faq','admin.users.free-user-posts','admin.news-blog'))
		<!--Single status modal start-->
		<script>
		$(".changeStatus").on('click', function (e) {
			//console.log('hello');
			var id = $(this).data('id');
			var url = $(this).data('url');
			var status_type = $(this).data('post');
			if(status_type==1)
			{
				var table = 'postAd';
			}
			else{
				var table = 'users';
			}
			
			var val = $(this).prop('checked') == true ? 1 : 0;
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			$.ajax({
				url: url,
				method: "POST",
				data: {id:id,val:val,table:table},
				dataType: 'json',
				success: function(response) {
					//location.reload(true);
					$('#message').html('<div class="alert alert-success alert-dismissible fade show"><strong>'+response.success+'</strong><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button></div>');
				}
			});
		});
		</script>
		<!--Single status modal end-->
		@endif
		@if (request()->routeIs('admin.category','admin.vehicle-edition','admin.colour', 'admin.make','admin.vehicledreven','admin.engine-version', 'admin.agmodel','admin.fueltype','admin.subcategory', 'admin.vehiclestype', 'admin.cms', 'admin.email-management', 'admin.users.free', 'admin.users.abo','admin.products','admin.post-ad-premium','admin.post-ad-free','admin.faq','admin.news-blog'))
			<div class="modal fade" id="confirmMultiStatus" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						
						<div class="modal-body">
						</div>
						<div class="modal-footer">
							<input type="hidden" id="hiddenId">
				<button type="button" data-bs-dismiss="modal" class="btn btn-primary" id="change">Submit</button>
				<button type="button" data-bs-dismiss="modal" class="btn btn-secondary" id="cancel">Cancel</button>
						</div>
					</div>
				</div>
			</div>
			<script type="text/javascript">
			$('.change_status').on('click', function(e) { 
				var status = $(this).data('id');
				var url = $(this).data('url');
				var employee = [];  
				$(".table input[name=chk_id]:checked").each(function() {  
					employee.push($(this).data('emp-id'));
				});	
				if(employee.length <=0)  {  
					$('#confirmChkSelect').modal("show");	
				}else {
					$('#confirmMultiStatus').modal("show");	
					WRN_PROFILE_DELETE = "Are you sure you want to "+(status==1?"active":"inactive")+" "+(employee.length>1?"these":"this")+" row?";
					$("#confirmMultiStatus").find(".modal-body").text(WRN_PROFILE_DELETE);
					$('#confirmMultiStatus').on('click', '#change', function(e) {	
							var selected_values = employee.join(",");
							$.ajaxSetup({
								headers: {
									'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
								 }
							});
							$.ajax({
								url: url,
								method: "POST",
								data: {id:selected_values,status:status},
								dataType: 'json',
								success: function(response) {
									location.reload(true);
								}
							});
						
					});		
				}
			});
			</script>
		@endif
	<!--Status change modal end-->
	
	@if (request()->routeIs('admin.category', 'admin.vehicle-edition','admin.colour','admin.make','admin.vehicledreven','admin.engine-version', 'admin.agmodel','admin.fueltype','admin.subcategory', 'admin.vehiclestype', 'admin.users.free', 'admin.users.abo','admin.products','admin.post-ad-premium','admin.post-ad-free','admin.faq','admin.news-blog'))
		<!--View modal image click start-->
			<script>
				function showImg(path)
				{
				   $("#imgSrc").attr('src', path);
				   $('.imgShow').show();
				}
				function hideImg()
				{
				   $("#imgSrc").attr('src', '');
				   $('.imgShow').hide();
				}
			</script>
		<!--View modal image click end-->
	@endif
	<script>
		$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip();
		$('.table').each(function() {
			return $(".table #checkAll").click(function() {
				if ($(".table #checkAll").is(":checked")) {
					return $(".table input[name=chk_id]").each(function() {
						return $(this).prop("checked", true);
					});
				} else {
					return $(".table input[name=chk_id]").each(function() {
						return $(this).prop("checked", false);
					});
				}
			});
		});
		$(".table input[name=chk_id]").click(function() {
			$(".table #checkAll").prop("checked", false);
		});
		});
	</script>
	<script>
		$(document).on('click', '.sidebar-link', function () {
			$(".simplebar-content-wrapper").css('overflow', 'scroll');
			$(".simplebar-scrollbar").css({'display':'block', 'height':$(".simplebar-placeholder").height()});
			$(".simplebar-vertical").css('visibility', 'visible');
		});
	</script>



<!--confirm checkbox selected-->

	@if(request()->routeIs('admin.category', 'admin.vehicle-edition','admin.colour','admin.make','admin.vehicledreven','admin.engine-version', 'admin.agmodel','admin.fueltype','admin.subcategory', 'admin.vehiclestype', 'admin.users.free', 'admin.users.abo','admin.products','admin.post-ad-premium','admin.post-ad-free','admin.faq','admin.news-blog'))

	<div class="modal fade" id="confirmChkSelect" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body"><strong>Please check checkbox.</strong></div>
				<div class="modal-footer">
				<button type="button" data-bs-dismiss="modal" class="btn btn-secondary" id="cancel">OK</button>
				</div>
			</div>
		</div>
	</div>
	@endif
	@if (request()->routeIs('admin.category','admin.vehicle-edition','admin.colour', 'admin.make','admin.vehicledreven', 'admin.engine-version','admin.agmodel','admin.fueltype','admin.cms','admin.email-management', 'admin.users.free', 'admin.users.abo','admin.products','admin.post-ad-premium','admin.post-ad-free','admin.faq','admin.news-blog'))
	<!-- View Modal Start -->
	<div class="modal fade" id="viewDetails" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header cmsModalHead">
					<h5 class="modal-title">View Detail</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="align-middle me-2" data-feather="x"></i><span class="align-middle"></span></button>
				</div>
				<div class="modal-body">
				</div>
				<div class="modal-footer text-end">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		$('.view-data').on('click', function(e) {
			var id = $(this).data('id');	
			var url = $(this).data('url');	
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			$.ajax({
				url: url,
				method: "POST",
				data: {id:id},
				dataType: 'json',
				success: function(response) {
					$("#viewDetails").find(".modal-body").html(response.html);
				}
			});
		});
	</script>
	<!-- View Modal End -->
	@endif


	
</footer>