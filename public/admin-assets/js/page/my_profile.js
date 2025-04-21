$(document).ready(function() {
	
	/* Add Lead Phoneno*/
	$(document).on('click', '.delete-myprofile-owner-col', function () {
		$(this).closest('.del-myprofile-col').remove();
		return false;
	});
	$(document).on('click','.add-myprofile-info',function(){
		var phnocontent = 
		'<div class="col-md-12 del-myprofile-col border-top">' +
            '<div class="row">' +
                '<div class="col-md-3 col-lg-3 col-sm-12 mt-2">' +
                    '<div class="input-block">' +
                        '<label class="col-form-label">Name</label>' +
                        '<input class="form-control" type="text" placeholder = "Enter name">' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-3 col-lg-3 col-sm-12 mt-2">' +
                    '<div class="input-block">' +
                        '<label class="col-form-label">Mobile</label>' +
                        '<input class="form-control" type="text" placeholder = "Enter mobile">' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-3 col-lg-3 col-sm-12 mt-2">' +
                    '<div class="input-block">' +
                        '<label class="col-form-label">Email</label>' +
                        '<input class="form-control" type="text" placeholder = "Enter email">' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-2 col-lg-2 col-sm-12 mt-2">' +
                    '<div class="input-block">' +
                        '<label class="col-form-label">Relations</label>' +
                        '<input class="form-control" type="text" placeholder = "Enter relations">' +
                    '</div>' +
                '</div>' +

                '<div class="col-md-1 col-lg-1 col-sm-12 mt-3">' +
                    '<div class="add-modal-row-icon input-block">' +
                    '<a href="#" class="delete-myprofile-owner-col new-delete-icon text-danger"><i class="la la-trash"></i></a>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
		setTimeout(function () {
			$('.select');
			setTimeout(function () {
				$('.select').select2({
					minimumResultsForSearch: 0,
					width: '100%'
				});
			}, 100);
		}, 100);
		$(".lead-myprofile-col").append(phnocontent);
		return false;
	});
});