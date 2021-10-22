<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index']);

// Route::get('/', [LoginController::class, 'login']);
// Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::post('/registration_step_one', [RegisterController::class, 'registration_step_one']);
Route::post('/registration_step_one_update', [RegisterController::class, 'registration_step_one_update']);
Route::post('/registration_step_two', [RegisterController::class, 'registration_step_two']);
Route::post('/registration_step_three', [RegisterController::class, 'registration_step_three']);
Route::post('/register/fileUpload', [RegisterController::class, 'fileUpload']);

Auth::routes();

Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('home');
Route::get('/room-booking', [HomeController::class, 'room_booking']);
Route::get('/sign-lease', [HomeController::class, 'lease']);
Route::get('/move-in', [HomeController::class, 'move_in']);
Route::post('/move-in-form', [HomeController::class, 'fileUpload']);
Route::get('/complete-reservation', [HomeController::class, 'complete_reservation']);
Route::get('/applications', [HomeController::class, 'applications']);
Route::get('/application-profile/{userId}', [HomeController::class, 'application_profile']);

Route::post('/action/file', [HomeController::class, 'action_file']);

Route::get('/action-list/{userId}/{filter}', [HomeController::class, 'action_list']);
Route::get('/document-list/{userId}/{filter}', [HomeController::class, 'document_list']);
Route::post('/approve-application', [HomeController::class, 'approve_application']);

Route::get('/my-profile', [ProfileController::class, 'index'])->name('my-profile');
Route::post('/update-profile', [ProfileController::class, 'update']);

Route::get('/my-documents', [HomeController::class, 'documents']);

Route::get('/leases', [HomeController::class, 'leases']);
Route::get('/mailing', [HomeController::class, 'mailing']);
Route::get('/invoices', [HomeController::class, 'invoices']);
