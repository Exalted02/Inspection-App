<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CommonController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmailManagementController;
use App\Http\Controllers\EmailSettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChangePasswordController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ChallengesController;
use App\Http\Controllers\DashboardInspectorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/
Route::get('clear-cache', function () {
    \Artisan::call('config:cache');
    \Artisan::call('cache:clear');
	\Artisan::call('cache:clear');
    // \Artisan::call('route:cache');
    \Artisan::call('view:clear');
    \Artisan::call('config:cache');
    \Artisan::call('optimize:clear');
	Log::info('Clear all cache');
    dd("Cache is cleared");
});
Route::get('db-migrate', function () {
    \Artisan::call('migrate');
    dd("Database migrated");
});
Route::get('db-seed', function () {
    \Artisan::call('db:seed');
    dd("Database seeded");
});
Route::get('/', [ProfileController::class, 'welcome']);

Route::get('lang/home', [LangController::class, 'index']);
Route::get('lang/change', [LangController::class, 'change'])->name('changeLang');


	


Route::middleware(['auth', 'verified'])->group(function () {
	Route::get('/checklist-question/{taskid}/{cat_id}/{subcat_id}', [DashboardInspectorController::class, 'checklist_question'])->name('checklist-question');
	Route::post('checklist-next-question', [DashboardInspectorController::class, 'checklist_next_question'])->name('checklist-next-question');
	Route::post('checklist-previous-question', [DashboardInspectorController::class, 'checklist_previous_question'])->name('checklist-previous-question');
	Route::get('/category/{location_id}/{cat_id}', [DashboardInspectorController::class, 'category'])->name('category');
	
	Route::get('/location-details/{id}', [DashboardInspectorController::class, 'location_details'])->name('location-details');
	Route::get('/inspector-dashboard', [DashboardInspectorController::class, 'inspector_dashboard'])->name('inspector-dashboard');
	
	Route::post('send-location-details', [DashboardInspectorController::class, 'send_location_details'])->name('send-location-details');
	//check-task-id
	Route::post('check-task-id', [DashboardInspectorController::class, 'check_task_id'])->name('check-task-id');
	Route::post('reject-files', [DashboardInspectorController::class, 'single_reject_files'])->name('reject-files');
	Route::post('reject-file-delete', [DashboardInspectorController::class, 'delete_reject_file'])->name('reject-file-delete');
	Route::post('checklist-file-delete', [DashboardInspectorController::class, 'checklist_file_delete'])->name('checklist-file-delete');
	
	//------
	Route::post('reject-subchecklist-files', [DashboardInspectorController::class, 'reject_subchecklist_files'])->name('reject-subchecklist-files');
	Route::post('reject-subckecklist-file-delete', [DashboardInspectorController::class, 'reject_subckecklist_file_delete'])->name('reject-subckecklist-file-delete');
	Route::post('subchecklist-file-delete', [DashboardInspectorController::class, 'subchecklist_file_delete'])->name('subchecklist-file-delete');
	
	Route::get('completed-task/{task_id}/{cat_id}/{subcat_id}', [DashboardInspectorController::class, 'completed_task'])->name('completed-task');
	Route::post('submit-completed-task', [DashboardInspectorController::class, 'submit_completed_task'])->name('submit-completed-task');
	
	Route::post('get-checklist-page', [DashboardInspectorController::class, 'get_checklist_page'])->name('get-checklist-page');
	Route::get('thank-you', [DashboardInspectorController::class, 'thank_you'])->name('thank-you');
	//User-Accounts
	Route::get('/users', [UserController::class, 'index'])->name('users');
	
	//Challenges
	Route::get('/challenges', [ChallengesController::class, 'index'])->name('challenges');
	
	
});



require __DIR__.'/auth.php';

require __DIR__.'/backend.php';
