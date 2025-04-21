/*
Author       : Dreamstechnologies
Template Name: SmartHR - Bootstrap Admin Template
Version      : 4.0
*/

$(document).ready(function() {
	$(document).on('click','.search-data', function(){
		$('#search-order-frm').submit();
	});
	$('.search-sort-by').on('change' ,function (event) {
		//var sort_by = $(this).val();
		$('#search-sortby').submit();
	});
	$(document).on('click','.order-wishlist-search-data', function(){
		$('#search-order-wishlist-frm').submit();
	});

/*$(document).on('click','.contact-details', function(){
	var URL = $(this).data('url');
	window.location = URL;
});*/
	$(document).on('click', '.dropdown-toggle, .dropdown-menu, .dropdown-item', function(event) {
		event.stopPropagation(); 
	});
});
