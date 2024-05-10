<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

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

//public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jobs', [JobController::class, 'index'])->name('jobs');
Route::get('/jobs/details/{id}', [JobController::class, 'detail'])->name('jobDetail');
Route::post('/apply-job', [JobController::class, 'applyJob'])->name('applyJob');
Route::post('/saved-job', [JobController::class,'saveJob'])->name('saveJob');

//Route group for Admin
Route::group(['prefix' => 'admin','middleware' => 'isAdmin'], function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/users/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users', [UserController::class, 'deleteUser'])->name('admin.users.delete');
});

//Route group for Account
Route::group(['prefix' => 'account'], function () {

    //Guest routes
    Route::group(['middleware' => 'guest'], function () {

        //Registration and Login related routes
        Route::get('/register', [AccountController::class, 'registration'])->name('account.registration');
        Route::post('/process-register', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
        Route::get('/login', [AccountController::class, 'login'])->name('account.login');
        Route::post('/authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate');
    });

    //Authenticated routes
    Route::group(['middleware' => 'auth'], function () {

        //Profile related routes
        Route::get('/profile', [AccountController::class, 'profile'])->name('account.profile');
        Route::put('/update-profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');
        Route::post('/update-profile-pic', [AccountController::class, 'updateProfilePic'])->name('account.updateProfilePic');
        Route::get('/logout', [AccountController::class, 'logout'])->name('account.logout');
        Route::post('/update-my-password', [AccountController::class, 'updatePassword'])->name('account.updatePassword');


        //Jobs related route
        Route::get('/create-job', [AccountController::class, 'createJob'])->name('account.createJob');
        Route::post('/save-job', [AccountController::class, 'saveJob'])->name('account.saveJob');
        Route::get('/my-jobs', [AccountController::class, 'myJobs'])->name('account.myJobs');
        Route::get('/edit-job/{jobId}', [AccountController::class, 'editJob'])->name('account.editJob');
        Route::put('/update-job/{jobId}', [AccountController::class, 'updateJob'])->name('account.updateJob');
        Route::post('/delete-job', [AccountController::class, 'deleteJob'])->name('account.deleteJob');
        Route::get('/my-job-applications', [AccountController::class, 'myJobApplication'])->name('account.myJobApplication');
        Route::post('/remove-job-applications', [AccountController::class, 'removeJobs'])->name('account.removeJobs');
        Route::get('/my-saved-jobs', [AccountController::class, 'savedJobs'])->name('account.savedJob');
        Route::post('/remove-my-saved-job', [AccountController::class, 'removeSavedJob'])->name('account.removeSavedJob');

    });
});
