
<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EmailManagementController;
use App\Http\Controllers\Admin\EmailSettingsController;
use App\Http\Controllers\Admin\ChangePasswordController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\CommonController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\ChecklistController;
use App\Http\Controllers\Admin\SubChecklistController;
use App\Http\Controllers\Admin\InspectorController;
use App\Http\Controllers\Admin\LocationOwnerController;
use App\Http\Controllers\Admin\LocationOwnerSupervisorController;
use App\Http\Controllers\Admin\ManagementController;

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
	
	//ChangePassword
	Route::get('/change-password', [ChangePasswordController::class, 'index'])->name('change-password');
	Route::post('/change-password', [ChangePasswordController::class, 'save_data'])->name('change-password-save');

	//EmailSettings
	Route::get('/email-settings', [EmailSettingsController::class, 'index'])->name('email-settings');
	Route::post('/email-settings', [EmailSettingsController::class, 'save_data'])->name('email-settings-save');

	// Email Management Routes
	Route::get('email-management', [EmailManagementController::class,'index'])->name('email-management');
	Route::get('/email-management-edit/{id}', [EmailManagementController::class, 'email_management_edit'])->name('email-management-edit');
	Route::post('/email-management-edit-save',[EmailManagementController::class,'manage_email_management_process'])->name('email-management-edit-save');
	
	// master company name 
	Route::get('/manage-company', [MasterController::class, 'manage_company'])->name('manage-company');
	Route::post('/manage-company', [MasterController::class, 'manage_company'])->name('manage-company');
	Route::post('/save-company-name', [MasterController::class, 'save_manage_company'])->name('save-company-name');
	Route::post('/deleteManageCompanytList',[MasterController::class,'delete_manage_company_list'])->name('deleteManageCompanytList');
	Route::post('/company_name_update_status',[MasterController::class,'update_status'])->name('company_name_update_status');
	Route::post('/edit-company-name', [MasterController::class, 'edit_company'])->name('edit-company-name');
	Route::post('/getDeleteCompanyName', [MasterController::class, 'delete_company'])->name('getDeleteCompanyName');
	Route::get('/manage-company-location/{id}', [MasterController::class, 'manage_company_location'])->name('manage-company-location');
	Route::post('/manage-company-location/{id}', [MasterController::class, 'manage_location'])->name('manage-company-location');
	Route::get('/manage-company-users/{id}', [InspectorController::class, 'manage_company_users'])->name('manage-company-users');
	Route::post('/manage-company-users/{id}', [InspectorController::class, 'index'])->name('manage-company-users');
	
	Route::get('/manage-location-wise-category/{id}', [CategoryController::class, 'manage_location_wise_category'])->name('manage-location-wise-category');
	Route::post('/manage-location-wise-category/{id}', [CategoryController::class, 'index'])->name('manage-location-wise-category');
	
	Route::get('/manage-location-wise-subcategory/{id}', [SubCategoryController::class, 'manage_location_wise_subcategory'])->name('manage-location-wise-subcategory');
	Route::post('/manage-location-wise-subcategory/{id}', [SubCategoryController::class, 'index'])->name('manage-location-wise-subcategory');
	
	Route::get('/manage-location-wise-subcategory-checklist/{catid}/{subcatid}', [ChecklistController::class, 'manage_location_wise_subcategory_checklist'])->name('manage-location-wise-subcategory-checklist');
	Route::post('/manage-location-wise-subcategory-checklist/{catid}/{subcatid}', [ChecklistController::class, 'index'])->name('manage-location-wise-subcategory-checklist');
	
	// master location 
	Route::get('/manage-location', [MasterController::class, 'manage_location'])->name('manage-location');
	Route::post('/manage-location', [MasterController::class, 'manage_location'])->name('manage-location');
	Route::post('/save-location', [MasterController::class, 'save_location'])->name('save-location');
	Route::post('/location_name_update_status',[MasterController::class,'location_update_status'])->name('location_name_update_status');
	Route::post('/edit-location-name', [MasterController::class, 'edit_location'])->name('edit-location-name');
	Route::post('/getDeleteLocationName', [MasterController::class, 'delete_location'])->name('getDeleteLocationName');
	Route::post('/deleteLocationList',[MasterController::class,'delete_location_list'])->name('deleteLocationList');
	
	// category
	Route::get('/category', [CategoryController::class, 'index'])->name('category');
	Route::post('/category', [CategoryController::class, 'index'])->name('category');
	Route::post('/save-category', [CategoryController::class, 'save_category'])->name('save-category');
	Route::post('/category_update_status',[CategoryController::class,'update_status'])->name('category_update_status');
	Route::post('/getDeleteCategory', [CategoryController::class, 'delete_category'])->name('getDeleteCategory');
	Route::post('/deleteCategoryList',[CategoryController::class,'delete_list'])->name('deleteCategoryList');
	Route::post('/edit-category', [CategoryController::class, 'edit_category'])->name('edit-category');
	
	// sub category
	Route::get('/sub-category', [SubCategoryController::class, 'index'])->name('sub-category');
	Route::post('/sub-category', [SubCategoryController::class, 'index'])->name('sub-category');
	Route::post('/save-subcategory', [SubCategoryController::class, 'save_subcategory'])->name('save-subcategory');
	Route::post('/subcategory_update_status',[SubCategoryController::class,'update_status'])->name('subcategory_update_status');
	Route::post('/getDeleteSubCategory', [SubCategoryController::class, 'delete_subcategory'])->name('getDeleteSubCategory');
	Route::post('/deleteSubCategoryList',[SubCategoryController::class,'delete_list'])->name('deleteSubCategoryList');
	Route::post('/edit-subcategory', [SubCategoryController::class, 'edit_subcategory'])->name('edit-subcategory');
	
	// Checklist 
	Route::get('/checklist', [ChecklistController::class, 'index'])->name('checklist');
	Route::post('/checklist', [ChecklistController::class, 'index'])->name('checklist');
	Route::post('/save-checklist', [ChecklistController::class, 'save_checklist'])->name('save-checklist');
	Route::post('/checklist_update_status',[ChecklistController::class,'update_status'])->name('checklist_update_status');
	Route::post('/getDeletechecklist', [ChecklistController::class, 'delete_checklist'])->name('getDeletechecklist');
	Route::post('/deletechecklist',[ChecklistController::class,'delete_list'])->name('deletechecklist');
	Route::post('/edit-checklist', [ChecklistController::class, 'edit_checklist'])->name('edit-checklist');
	
	// Sub Checklist 
	Route::get('/sub-checklist', [SubChecklistController::class, 'index'])->name('sub-checklist');
	Route::post('/sub-checklist', [SubChecklistController::class, 'index'])->name('sub-checklist');
	Route::post('/save-subchecklist', [SubChecklistController::class, 'save_subchecklist'])->name('save-subchecklist');
	Route::post('/subchecklist_update_status',[SubChecklistController::class,'update_status'])->name('subchecklist_update_status');
	Route::post('/getDeletesubchecklist', [SubChecklistController::class, 'delete_subchecklist'])->name('getDeletesubchecklist');
	Route::post('/deletesubchecklist',[SubChecklistController::class,'delete_list'])->name('deletesubchecklist');
	Route::post('/edit-subchecklist', [SubChecklistController::class, 'edit_subchecklist'])->name('edit-subchecklist');
	
	// inspector
	Route::get('/inspector', [InspectorController::class, 'index'])->name('inspector');
	Route::post('/inspector', [InspectorController::class, 'index'])->name('inspector');
	Route::post('/save-inspector', [InspectorController::class, 'save_inspector'])->name('save-inspector');
	Route::post('/inspector_update_status',[InspectorController::class,'update_status'])->name('inspector_update_status');
	Route::post('/getDeleteinspector', [InspectorController::class, 'delete_inspector'])->name('getDeleteinspector');
	Route::post('/deleteinspectorList',[InspectorController::class,'delete_list'])->name('deleteinspectorList');
	Route::post('/edit-inspector', [InspectorController::class, 'edit_inspector'])->name('edit-inspector');
	
	// location owner
	Route::get('/location-owner', [LocationOwnerController::class, 'index'])->name('location-owner');
	Route::post('/location-owner', [LocationOwnerController::class, 'index'])->name('location-owner');
	Route::post('/save-location-owner', [LocationOwnerController::class, 'save_location_owner'])->name('save-location-owner');
	Route::post('/location-owner_update_status',[LocationOwnerController::class,'update_status'])->name('location_owner_update_status');
	Route::post('/getDeletelocationowner', [LocationOwnerController::class, 'delete_location_owner'])->name('getDeletelocationowner');
	Route::post('/deletelocationownerList',[LocationOwnerController::class,'delete_list'])->name('deletelocationownerList');
	Route::post('/edit-location-owner', [LocationOwnerController::class, 'edit_location_owner'])->name('edit-location-owner');
	
	// location owner supervisor
	Route::get('/location-owner-supervisor', [LocationOwnerSupervisorController::class, 'index'])->name('location-owner-supervisor');
	Route::post('/location-owner-supervisor', [LocationOwnerSupervisorController::class, 'index'])->name('location-owner-supervisor');
	Route::post('/save-location-owner-supervisor', [LocationOwnerSupervisorController::class, 'save_location_owner_supervisor'])->name('save-location-owner-supervisor');
	Route::post('/location-owner-supervisor_update_status',[LocationOwnerSupervisorController::class,'update_status'])->name('location_owner_supervisor_update_status');
	Route::post('/getDeletelocationownersupervisor', [LocationOwnerSupervisorController::class, 'delete_location_owner_supervisor'])->name('getDeletelocationownersupervisor');
	Route::post('/deletelocationownersupervisorList',[LocationOwnerSupervisorController::class,'delete_list'])->name('deletelocationownersupervisorList');
	Route::post('/edit-location-owner-supervisor', [LocationOwnerSupervisorController::class, 'edit_location_owner_supervisor'])->name('edit-location-owner-supervisor');
	
	// management
	Route::get('/management', [ManagementController::class, 'index'])->name('management');
	Route::post('/management', [ManagementController::class, 'index'])->name('management');
	Route::post('/save-management', [ManagementController::class, 'save_management'])->name('save-management');
	Route::post('/management_update_status',[ManagementController::class,'update_status'])->name('management_update_status');
	Route::post('/getDeletemanagement', [ManagementController::class, 'delete_management'])->name('getDeletemanagement');
	Route::post('/deletemanagementList',[ManagementController::class,'delete_list'])->name('deletemanagementList');
	Route::post('/edit-management', [ManagementController::class, 'edit_management'])->name('edit-management');
	
	
	Route::post('/change-multi-status',[CommonController::class,'change_multi_status'])->name('change-multi-status');
	Route::post('/delete-multi-data',[CommonController::class,'delete_multi_data'])->name('delete-multi-data');
	Route::post('/getstatebycountry',[CommonController::class,'get_state_by_country'])->name('getstatebycountry');
	Route::post('/getcitybystate',[CommonController::class,'get_city_by_state'])->name('getcitybystate');
	
	Route::post('/get-subcategory', [CommonController::class, 'get_category_by_subcategory'])->name('get-subcategory');
});

Route::prefix('admin')->group(function () {
	Route::middleware('guest_admin')->group(function () {
		Route::get('/login', [AdminController::class, 'login'])->name('admin_login');
		Route::post('/login-submit', [AdminController::class, 'login_submit'])->name('admin_login_submit');
		Route::get('/forget-password', [AdminController::class, 'forget_password'])->name('admin_forget_password');
		Route::post('/forget-password-submit', [AdminController::class, 'forget_password_submit'])->name('admin_forget_password_submit');
		Route::get('/reset-password/{token}/{email}', [AdminController::class, 'reset_password'])->name('admin_reset_password');
		Route::post('/reset-password-submit', [AdminController::class, 'reset_password_submit'])->name('admin_reset_password_submit');
	});
	Route::get('/logout', [AdminController::class, 'logout'])->name('admin_logout');
});





