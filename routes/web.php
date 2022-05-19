<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\LookupsController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ApplicationController;

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
Route::get('/edit-inline-data', [HomeController::class, 'edit_inline_data']);
Route::post('/save-inline-data', [HomeController::class, 'save_inline_data']);
Route::post('/approve-inline-data', [HomeController::class, 'approve_inline_data']);

Route::post('/action/file', [HomeController::class, 'action_file']);

Route::get('/action-list/{userId}/{filter}', [HomeController::class, 'action_list']);
Route::get('/document-list/{userId}/{filter}', [HomeController::class, 'document_list']);
Route::post('/approve-application', [HomeController::class, 'approve_application']);
Route::post('/reject-application', [HomeController::class, 'reject_application']);
Route::get('/check-user-status/{userId}', [HomeController::class, 'check_user_status']);
Route::get('/check-user-payment/{userId}', [HomeController::class, 'check_user_payment']);

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
Route::post("/delete-admin", [HomeController::class, 'delete_admin']);
Route::get("/student/edit/{studentId}", [HomeController::class, 'student_edit']);
Route::post("/update-student", [HomeController::class, 'edit_student']);
Route::get("/student/upload/{studentId}", [HomeController::class, 'student_upload']);




Route::get('/lookups', [LookupsController::class, 'lookups'])->name('lookups');
Route::get('/lookups/new', [LookupsController::class, 'add_lookups']);
Route::post('/new-lookup', [LookupsController::class, 'new_lookups']);
Route::get('/lookups/edit/{lookupId}', [LookupsController::class, 'edit_lookups']);
Route::post('/save-lookup', [LookupsController::class, 'save_lookups']);
Route::get('/admin-updates/landing', [LookupsController::class, 'admin_updates_landing']);
Route::get('/admin-updates/fix-data', [LookupsController::class, 'admin_updates_fix_data']);
Route::get('/admin-updates/update-properties', [LookupsController::class, 'admin_updates_update_properties']);




Route::get('/new/student', [HomeController::class, 'new_student']);
Route::post("/add-student", [HomeController::class, 'add_student']);
Route::get('/new/admin', [HomeController::class, 'new_admin']);
Route::post("/add-admin", [HomeController::class, 'add_admin']);

Route::post("/delete/lookup", [HomeController::class, 'delete_lookup']);
Route::post("/applications/filter", [HomeController::class, 'app_filter']);

Route::post("/delete/booking", [HomeController::class, 'delete_booking']);
Route::post("/cancel/booking", [HomeController::class, 'cancel_booking']);
Route::post("/restore/booking", [HomeController::class, 'restore_booking']);

Route::get('/reports', [HomeController::class, 'reports']);
Route::post('/download-report', [HomeController::class, 'download_report']);

Route::post('/email-check', [HomeController::class, 'email_check']);
Route::post('/email-check-idNo', [ApplicationController::class, 'email_check_idNo']);



Route::post('/get-booking-details', [HomeController::class, 'get_booking_details']);
Route::post('/change-booking', [HomeController::class, 'change_booking']);
Route::post('/change-lease', [HomeController::class, 'change_lease']);
Route::post('/unallocate-bed', [HomeController::class, 'unallocate_bed']);

Route::get('/manage-properties', [HomeController::class, 'manage_properties']);
Route::post("/manage-properties-filter", [HomeController::class, 'properties_filter']);
Route::get("/manage-properties-filter-v2", [HomeController::class, 'properties_filter_v2']);
Route::get('/property/edit/{propertyId}', [HomeController::class, 'property_edit']);
Route::post('/save-property', [HomeController::class, 'save_property']);
Route::post('/delete-bed', [HomeController::class, 'delete_bed']);
Route::post('/get-booking-info', [HomeController::class, 'get_booking_info']);

Route::get('/properties-import', [HomeController::class, 'properties_import']);
Route::post('/import/data', [HomeController::class, 'import_data']);

Route::post('/createLease/{studentId}', [PDFController::class, 'createPDF']);
Route::get('/properties-info', [HomeController::class, 'properties_info']);
Route::get('/residence/edit/{residenceId}', [HomeController::class, 'residence_edit']);
Route::post('/save-residence', [HomeController::class, 'save_residence']);

Route::post('/action-data', [HomeController::class, 'action_data']);
Route::post('/approve-app-data', [HomeController::class, 'approve_app_data']);

Route::get('/room-types', [HomeController::class, 'room_types']);
Route::get('/room-type/edit/{id}', [HomeController::class, 'room_type_edit']);
Route::post('/save-room-type', [HomeController::class, 'save_room_type']);

Route::get('/nsfas-approval-upload', [HomeController::class, 'nsfas_approval_upload']);
Route::post('/import/nsfas-students', [HomeController::class, 'import_nsfas_approval']);
Route::get('/import/newData', [HomeController::class, 'new_import_data']);

Route::get('/download/docs/{id}', [HomeController::class, 'download_docs']);
Route::post('/waive-booking-fee', [HomeController::class, 'waive_booking_fee']);
Route::post('/undo-waive-booking-fee', [HomeController::class, 'undo_waive_booking_fee']);

Route::get('/update/booking-fees', [HomeController::class, 'update_booking_fees']);
Route::get('/update/nsfas', [HomeController::class, 'update_nsfas']);
Route::get('/update/processing-status', [HomeController::class, 'update_processing_status']);
Route::post('/update/lease-dates', [HomeController::class, 'update_lease_dates']);
Route::post('/get-upload-info', [HomeController::class, 'get_upload_info']);
Route::get('/download/matched-unmatched/{id}', [HomeController::class, 'download_matched_unmatched']);
Route::get('download-nsfas-report', [HomeController::class, 'download_nsfas_report']);


Route::post('/get-comments', [HomeController::class, 'get_comment']);
Route::post('/add-comment', [HomeController::class, 'add_comment']);
Route::post('/change-nsfsbursar', [HomeController::class, 'change_nsfsbursar']);
Route::get('/update-user-status/{user_id}', [HomeController::class, 'update_user_status']);
Route::get('/all_student_update_status', [HomeController::class, 'all_student_update_status']);

Route::post('/set-page-session', [HomeController::class, 'set_page_session']);
Route::post('/set-page-session-order', [HomeController::class, 'set_page_session_order']);

Route::post('/cancel-lease', [HomeController::class, 'cancel_lease']);


//Documents
Route::get('/documents/view-document/{id}', [DocumentsController::class, 'view_document']);
Route::get('/documents/edit-data/{id}/{fileId}', [DocumentsController::class, 'edit_data_form']);
Route::post('/documents/save-data', [DocumentsController::class, 'edit_data_save']);
Route::post('/move-in-form-admin', [DocumentsController::class, 'adminFileUpload']);

Route::post('/add-other-document', [DocumentsController::class, 'add_other_document']);
Route::post('/remove-document-upload', [DocumentsController::class, 'remove_document_upload']);

//New user / email change user password update
Route::get('/reset-password/{id}', [ResetPasswordController::class, 'resetPasswordUser']);
Route::post('/update-password/{id}', [ResetPasswordController::class, 'updatePasswordUser']);

Route::get('/check-rooms/{id}', [HomeController::class, 'check_rooms']);


//User Online application
Route::get('/online-application/{property}/{roomType}', [ApplicationController::class, 'step_1']);
Route::get("/online-application-verify/{applicationToken}", [ApplicationController::class, 'step_verify']);
Route::post("/veryfiy-user", [ApplicationController::class, 'veryfiy_user']);
Route::get("/step-2/online-application/{applicationToken}", [ApplicationController::class, 'step_2']);
Route::get("/step-2-spon/{applicationToken}", [ApplicationController::class, 'step_2_spon']);
Route::get("/step-3/online-application/{applicationToken}", [ApplicationController::class, 'step_3']);