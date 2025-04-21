$(document).ready(function() {
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

function change_multi_status(status, model, url, additional_table = null) {
	var employee = [];
	$(".table input[name=chk_id]:checked").each(function() {  
		employee.push($(this).data('emp-id'));
	});
	if(employee.length <=0)  {
		$('#confirmChkSelect').modal("show");	
	}else {
		$('#confirmMultiStatus').modal("show");	
		WRN_PROFILE_STATUS = "Are you sure you want to "+(status==1?"active":"inactive")+" "+(employee.length>1?"these":"this")+" row?";
		$("#confirmMultiStatus").find("h3").text(WRN_PROFILE_STATUS);
		$('#confirmMultiStatus').on('click', '#change', function(e) {	
			var selected_values = employee.join(",");
			/*$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': csrfToken 
				 }
			});*/
			$.ajax({
				url: url,
				method: "POST",
				data: {id:selected_values,status:status,model:'App\\Models\\'+model,additional_table:additional_table, _token: csrfToken},
				dataType: 'json',
				success: function(response) {
					$('#success_status_msg').modal('show');
					setTimeout(() => {
						window.location.reload();
					}, "2000");
				},
				error: function(xhr) {
					if(xhr.status == 403){
						$('#module_permission').modal('show');
						setTimeout(() => {
							window.location.reload();
						}, "2000");
					}
				}
			});
			
		});
	}
}
function delete_multi_data(model, url, additional_table = null) {
	var employee = [];  
	$(".table input[name=chk_id]:checked").each(function() {  
		employee.push($(this).data('emp-id'));
	});
	
	if(employee.length <=0)  {
		$('#confirmChkSelect').modal("show");	
	}else {
		$('#confirmMultiDelete').modal("show");	
		WRN_PROFILE_STATUS = "Are you sure you want to delete "+(employee.length>1?"these":"this")+" row?";
		$("#confirmMultiDelete").find("h3").text(WRN_PROFILE_STATUS);
		$('#confirmMultiDelete').on('click', '#change_delete', function(e) {	
			var selected_values = employee.join(",");
			/*$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': csrfToken 
				 }
			});*/
			$.ajax({
				url: url,
				method: "POST",
				data: {id:selected_values,status:status,model:'App\\Models\\'+model,additional_table:additional_table, _token: csrfToken},
				dataType: 'json',
				success: function(response) {
					$('#success_delete_msg').modal('show');
					setTimeout(() => {
						window.location.reload();
					}, "2000");
				},
				error: function(xhr) {
					if(xhr.status == 403){
						$('#module_permission').modal('show');
						setTimeout(() => {
							window.location.reload();
						}, "2000");
					}
				}
			});
			
		});
	}
}