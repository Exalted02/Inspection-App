
<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EmailManagementController;
use App\Http\Controllers\Admin\EmailSettingsController;
use App\Http\Controllers\Admin\ChangePasswordController;

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





