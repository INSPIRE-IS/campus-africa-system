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
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\BookingReportController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\OnlinePaymentController;
use App\Http\Controllers\InvoiceController;

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

// Route::get("/check_booking/{id}", [HomeController::class, 'check_booking']);

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

Route::post("/saveMail", [HomeController::class, 'saveMail']);
Route::get("/get-mail-list", [HomeController::class, 'get_mail_list']);
Route::get('/verify-email-account', [ApplicationController::class, 'verify_email_account']);
Route::get('/verify-new-email-account', [ApplicationController::class, 'verify_new_email_account']);
Route::get('/loginAsStudent/{studentId}', [ApplicationController::class, 'loginAsStudent']);

Route::get("/verified", [ApplicationController::class, 'verified']);


// Route::post("/saveMailTy", [HomeController::class, 'saveMailTy']);
// Route::post("/saveMailAa", [HomeController::class, 'saveMailAa']);
// Route::post("/saveMailAav", [HomeController::class, 'saveMailAav']);
// Route::post("/saveMailAD", [HomeController::class, 'saveMailAD']);
// Route::post("/saveMailADd", [HomeController::class, 'saveMailADd']);
// Route::post("/saveMailAR", [HomeController::class, 'saveMailAR']);

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
Route::get('/admin-updates/update-property-id', [LookupsController::class, 'admin_updates_update_property_id']);
Route::get('/admin-updates/update-booking-code', [LookupsController::class, 'admin_updates_update_booking_code']);
Route::get('/admin-updates/update-cancelled', [LookupsController::class, 'admin_updates_update_cancelled']);
Route::get('/admin-updates/update-new-students', [LookupsController::class, 'admin_updates_update_new_students']);
Route::get('/admin-updates/payments-update', [LookupsController::class, 'admin_updates_payments_update']);








Route::get('/new/student', [HomeController::class, 'new_student']);
Route::post("/add-student", [HomeController::class, 'add_student']);
Route::get('/new/admin', [HomeController::class, 'new_admin']);
Route::post("/add-admin", [HomeController::class, 'add_admin']);

Route::post("/delete/lookup", [HomeController::class, 'delete_lookup']);
Route::post("/delete/room", [HomeController::class, 'delete_room']);
Route::post("/applications/filter", [HomeController::class, 'app_filter']);

Route::post("/delete/booking", [HomeController::class, 'delete_booking']);
Route::post("/cancel/booking", [HomeController::class, 'cancel_booking']);
Route::post("/restore/booking", [HomeController::class, 'restore_booking']);

Route::get('/reports', [HomeController::class, 'reports']);
Route::post('/download-report', [HomeController::class, 'download_report']);

Route::post('/email-check', [HomeController::class, 'email_check']);
Route::post('/email-check-idNo', [ApplicationController::class, 'email_check_idNo']);
Route::post('/passport-check', [HomeController::class, 'passport_check']);
Route::Post('/passport-check_reg', [ApplicationController::class, 'passport_check_registration']);



Route::post('/get-booking-details', [HomeController::class, 'get_booking_details']);
Route::post('/change-booking', [HomeController::class, 'change_booking']);
// Route::post('/change-booking-building', [HomeController::class, 'change_booking_building']);
Route::post('/change-lease', [HomeController::class, 'change_lease']);
Route::post('/unallocate-bed', [HomeController::class, 'unallocate_bed']);

Route::post('/getPaymentInfo', [HomeController::class, 'getPaymentInfo']);

Route::get('/manage-properties', [HomeController::class, 'manage_properties']);
Route::post("/manage-properties-filter", [HomeController::class, 'properties_filter']);
Route::get("/manage-properties-filter-v2", [HomeController::class, 'properties_filter_v2']);
Route::get('/property/edit/{propertyId}', [HomeController::class, 'property_edit']);
Route::post('/save-property', [HomeController::class, 'save_property']);
Route::post('/delete-bed', [HomeController::class, 'delete_bed']);
Route::post('/get-booking-info', [HomeController::class, 'get_booking_info']);

Route::get('/properties-import', [HomeController::class, 'properties_import']);
Route::post('/import/data', [HomeController::class, 'import_data']);

Route::post('/resendLease', [LeaseController::class, 'resendLease']);
Route::post('/createLease/{studentId}', [PDFController::class, 'createPDF']);
Route::get('/properties-info', [HomeController::class, 'properties_info']);
Route::get('/residence/edit/{residenceId}', [HomeController::class, 'residence_edit']);
Route::post('/save-residence', [HomeController::class, 'save_residence']);

Route::post('/action-data', [HomeController::class, 'action_data']);
Route::post('/approve-app-data', [HomeController::class, 'approve_app_data']);

Route::post('/get-room-types-id', [HomeController::class, 'get_room_types_id']);
Route::post('/get-room-types', [HomeController::class, 'get_room_types']);
Route::get('/room-types', [HomeController::class, 'room_types']);
Route::get('/room-type/edit/{id}', [HomeController::class, 'room_type_edit']);
Route::post('/save-room-type', [HomeController::class, 'save_room_type']);

Route::get('/nsfas-approval-upload', [HomeController::class, 'nsfas_approval_upload']);
Route::post('/import/nsfas-students', [HomeController::class, 'import_nsfas_approval']);
Route::get('/import/newData', [HomeController::class, 'new_import_data']);

Route::get('/download/docs/{id}', [HomeController::class, 'download_docs']);
Route::post('/waive-booking-fee', [HomeController::class, 'waive_booking_fee']);
Route::post('/undo-waive-booking-fee', [HomeController::class, 'undo_waive_booking_fee']);

Route::post("/waive-info", [HomeController::class, 'waive_action']);
Route::post("/waive-fee-request", [HomeController::class, 'waive_request']);
Route::post("/waive-info-reject", [HomeController::class, 'waive_reject']);
Route::post("/download-invoice", [InvoiceController::class, 'generate']);

Route::get('/update/booking-fees', [HomeController::class, 'update_booking_fees']);
Route::get('/update/nsfas', [HomeController::class, 'update_nsfas']);
Route::get('/update/processing-status', [HomeController::class, 'update_processing_status']);
Route::post('/update/lease-dates', [HomeController::class, 'update_lease_dates']);
Route::post('/update/nsfas-amount', [LookupsController::class, 'update_nsfas_amount']);
Route::post('/get-upload-info', [HomeController::class, 'get_upload_info']);
Route::get('/download/matched-unmatched/{id}', [HomeController::class, 'download_matched_unmatched']);
Route::get('/delete/nsfas/{id}', [HomeController::class, 'delete_nsfas']);
Route::get('download-nsfas-report', [HomeController::class, 'download_nsfas_report']);


Route::post('/get-comments', [HomeController::class, 'get_comment']);
Route::post('/add-comment', [HomeController::class, 'add_comment']);
Route::post('/change-nsfsbursar', [HomeController::class, 'change_nsfsbursar']);
Route::get('/update-user-status/{user_id}', [HomeController::class, 'update_user_status']);
Route::get('/all_student_update_status', [HomeController::class, 'all_student_update_status']);

Route::post('/set-page-session', [HomeController::class, 'set_page_session']);
Route::post('/set-page-session-order', [HomeController::class, 'set_page_session_order']);

Route::post('/cancel-lease', [HomeController::class, 'cancel_lease']);

Route::post("/update-sponsor-details", [HomeController::class, 'update_sponsor_details']);
Route::post("/update-bursar-details", [HomeController::class, 'update_bursar_details']);


//Documents
Route::get('/documents/view-document/{id}', [DocumentsController::class, 'view_document']);
// Route::get('/documents/edit-data/{id}/{fileId}', [DocumentsController::class, 'edit_data_form']);
Route::post('/documents/save-data', [DocumentsController::class, 'edit_data_save']);
Route::post('/move-in-form-admin', [DocumentsController::class, 'adminFileUpload']);

Route::post('/add-other-document', [DocumentsController::class, 'add_other_document']);
Route::post('/remove-document-upload', [DocumentsController::class, 'remove_document_upload']);

//New user / email change user password update
Route::get('/reset-password/{id}', [ResetPasswordController::class, 'resetPasswordUser']);
Route::post('/update-password/{id}', [ResetPasswordController::class, 'updatePasswordUser']);

//Students to sign lease
Route::get('/sign-lease/{id}', [LeaseController::class, 'view_sign_lease']);
Route::post('/save-signed-lease/{id}', [LeaseController::class, 'save_signed_lease']);
Route::get('/check-rooms/{id}', [HomeController::class, 'check_rooms']);
Route::get('/check-all-rooms/{id}', [HomeController::class, 'check_all_rooms']);
Route::post("/check-building-prefix", [HomeController::class, 'check_building_prefix']);

Route::get('/booking-summary', [BookingReportController::class, 'booking_summary']);
Route::post('/update/room-activation', [BookingReportController::class, 'room_activation']);

Route::post('/get-room-ids', [ApplicationController::class, 'get_room_ids']);

//User Online application
Route::get('/online-application/{property}/{roomType}', [ApplicationController::class, 'step_1']);
Route::get("/online-application-verify/{applicationToken}", [ApplicationController::class, 'step_verify']);
Route::post("/veryfiy-user", [ApplicationController::class, 'veryfiy_user']);
Route::get("/step-2/online-application", [ApplicationController::class, 'step_2']);
Route::post("/step-2-spon", [ApplicationController::class, 'step_2_spon']);
Route::post("/step-2-bur", [ApplicationController::class, 'step_2_bur']);
Route::get("/step-3/online-application", [ApplicationController::class, 'step_3']);
Route::post("/step-3-kin", [ApplicationController::class, 'step_3_kin']);
Route::get("/step-4/online-application", [ApplicationController::class, 'step_4']);
Route::post("/application-file-upload", [ApplicationController::class, 'file_upload']);
Route::get("/step-5/online-application", [ApplicationController::class, 'step_5']);
Route::get("/complete/online-application", [ApplicationController::class, 'complete_online_app']);
Route::get("/verify-password", [ApplicationController::class, 'verify_password']);
Route::post("/confirm-password", [ApplicationController::class, 'confirm_password']);
Route::get("/retry/online-application", [ApplicationController::class, 'retry_payment']);

Route::get("/student-create-new-booking", [ApplicationController::class, 'new_booking_step_1']);
Route::post("/new-booking-step-1-save", [ApplicationController::class, 'new_booking_step_1_save']);
Route::get("/new-booking-step-2", [ApplicationController::class, 'new_booking_step_2']);
Route::post("/new-booking-step-3-save", [ApplicationController::class, 'new_booking_step_3_save']);
Route::get('/new-booking-step-4', [ApplicationController::class, 'new_booking_step_4']);
Route::post('/new-booking-step-2-spon-save', [ApplicationController::class, 'new_booking_step_2_spon_save']);
Route::get("/new-booking-step-3", [ApplicationController::class, 'new_booking_step_3']);
Route::post('/new-booking-step-2-bur-save', [ApplicationController::class, 'new_booking_step_2_bur_save']);



Route::post("/passwordless-login", [LoginController::class, 'passwordlessLogin']);
Route::get('/change-student-type', [ApplicationController::class, 'change_student_type']);
Route::post('/change-new-student-details', [ApplicationController::class, 'update_student_details']);
Route::post('/restore-student-booking', [HomeController::class, 'restore_student_booking']);
Route::post('/change-student-room', [HomeController::class, 'change_student_room']);
Route::post('/download-beds', [LookupsController::class, 'download_beds']);
Route::post('/download-bed-codes', [LookupsController::class, 'download_bed_codes']);

Route::post("/check-uploaded-docs", [ApplicationController::class, 'check_uploaded_docs']);

Route::get('/bed_import', [HomeController::class, 'bed_import']);
Route::post('/import/beds', [HomeController::class, 'import_beds']);

Route::post('/get-room-maps', [ApplicationController::class, 'get_room_maps']);

Route::post('/UpdatePaymentRef', [HomeController::class, 'UpdatePaymentRef']);


Route::post('/re-verify-email', [ApplicationController::class, 're_verify_email']);

Route::post('/initiate-payment', [OnlinePaymentController::class, 'initiate_payment']);
Route::post('/online-application-payment/{id}', [OnlinePaymentController::class, 'online_application_payment']);
Route::post('/online-application-payment-on-screen/{id}', [OnlinePaymentController::class, 'online_application_payment_on_screen']);
Route::post('/notify-data', [OnlinePaymentController::class, 'notify_data']);

Route::get('/create-new-booking/{id}', [HomeController::class, 'create_new_booking']);
Route::post('/new-booking', [HomeController::class, 'new_booking']);

Route::get('/get-all-properties', [HomeController::class, 'all_properties']);
Route::post('/get-nsfas-properties', [HomeController::class, 'nsfas_properties']);

Route::get('/get-all-properties-online', [ApplicationController::class, 'all_properties_online']);
Route::post('/get-nsfas-properties-online', [ApplicationController::class, 'nsfas_properties_online']);

Route::post("/mda-tenant-save", [LeaseController::class, 'mda_tenant_save']);





