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
Route::post('/reject-application', [HomeController::class, 'reject_application']);

Route::get('/my-profile', [ProfileController::class, 'index'])->name('my-profile');
Route::post('/update-profile', [ProfileController::class, 'update']);

Route::get('/my-documents', [HomeController::class, 'documents']);

Route::get('/leases', [HomeController::class, 'leases']);
Route::get('/mailing', [HomeController::class, 'mailing']);
Route::get('/invoices', [HomeController::class, 'invoices']);

Route::post('/activate_mailer', [HomeController::class, 'activate_mailer']);
Route::post('/inactivate_mailer', [HomeController::class, 'inactivate_mailer']);

Route::post("/saveMailTy", [HomeController::class, 'saveMailTy']);
Route::post("/saveMailAa", [HomeController::class, 'saveMailAa']);
Route::post("/saveMailAav", [HomeController::class, 'saveMailAav']);
Route::post("/saveMailAD", [HomeController::class, 'saveMailAD']);
Route::post("/saveMailADd", [HomeController::class, 'saveMailADd']);
Route::post("/saveMailAR", [HomeController::class, 'saveMailAR']);

Route::get("/users", [HomeController::class, 'users'])->name('users');
Route::get("/user/edit/{userId}", [HomeController::class, 'user_edit']);
Route::post("/update-admin", [HomeController::class, 'edit_admin']);
Route::get("/student/edit/{studentId}", [HomeController::class, 'student_edit']);
Route::post("/update-student", [HomeController::class, 'edit_student']);
Route::get("/student/upload/{studentId}", [HomeController::class, 'student_upload']);
Route::post('/move-in-form-admin', [HomeController::class, 'adminFileUpload']);

Route::get('/lookups', [HomeController::class, 'lookups'])->name('lookups');
Route::get('/lookups/new', [HomeController::class, 'add_lookups']);
Route::post('/new-lookup', [HomeController::class, 'new_lookups']);
Route::get('/lookups/edit/{lookupId}', [HomeController::class, 'edit_lookups']);
Route::post('/save-lookup', [HomeController::class, 'save_lookups']);

Route::get('/new/student', [HomeController::class, 'new_student']);
Route::post("/add-student", [HomeController::class, 'add_student']);
Route::get('/new/admin', [HomeController::class, 'new_admin']);
Route::post("/add-admin", [HomeController::class, 'add_admin']);

Route::post("/delete/lookup", [HomeController::class, 'delete_lookup']);
Route::post("/applications/filter", [HomeController::class, 'app_filter']);
