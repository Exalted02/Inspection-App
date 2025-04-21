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
	Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
	
	//User-Accounts
	Route::get('/users', [UserController::class, 'index'])->name('users');
	
	//Challenges
	Route::get('/challenges', [ChallengesController::class, 'index'])->name('challenges');
	
	
});



require __DIR__.'/auth.php';

require __DIR__.'/backend.php';
