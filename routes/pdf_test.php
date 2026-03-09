<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\LeaseContactService;

// PDF test routes (admin-only, fake data)
$ensureTestRole = function () {
    if (!Auth::check() || (Auth::user()->role_super != 1 && Auth::user()->role_sys_admin != 1)) {
        abort(403);
    }
};

$buildLeaseTestData = function () use ($ensureTestRole) {
    $ensureTestRole();

    $year = date('Y');

    $data = (object) [
        'userFirstName' => 'Test',
        'userLastname' => 'Student',
        'userId' => '9901010000001',
        'userPassport' => 'P1234567',
        'userNationality' => 'South Africa',
        'studentNo' => 'STU123456',
        'userEmail' => 'test.student@example.com',
        'userCellCode' => '+27',
        'userCell' => '0820000000',
        'userDob' => '1999-01-01',
        'userAddress1' => '1 Test Street',
        'userAddress2' => 'Braamfontein',
        'userAddressCity' => 'Johannesburg',
        'userAddressPostal' => '2001',
        'pAddress1' => '1 Test Street',
        'pAddress2' => 'Braamfontein',
        'pAddressCity' => 'Johannesburg',
        'pAddressPostal' => '2001',
        'year' => $year,
        'studentType' => 'Cash / Direct',
        'kinFirstName' => 'Test',
        'kinLastName' => 'Guardian',
        'kinEmail' => 'guardian@example.com',
        'kinCellCode' => '+27',
        'kinCellNo' => '0820000001',
        'nsfas_bursar' => 0,
        'sponFirstName' => '',
        'sponLastName' => '',
        'sponId' => '',
    ];

    $booking = (object) [
        'lease_from_date' => '2026-02-01',
        'lease_from_time' => '12:00',
        'lease_to_date' => '2026-11-30',
        'lease_to_time' => '12:00',
        'booking_code' => 'BOOK-TEST',
        'bed_code' => 'A-101',
        'property' => 1,
        'roomType' => 1,
        'floor' => '1',
        'unit' => 'A',
        'room' => '101',
        'bed' => '1',
        'room_rate' => '0',
        'year' => $year,
    ];

    $propertyData = (object) [
        'residence' => 'Test Residence',
        'bed_code' => 'A-101',
    ];

    $residence = (object) [
        'residence' => 'Test Residence',
        'owner' => 'Test Owner (Pty) Ltd',
        'address' => '1 Test Street, Johannesburg, 2001',
        'company_reg' => '2026/000000/07',
        'company_vat' => '4000000000',
    ];

    $leaseAdminFee = 0;
    $paymentBooking = (object) [
        'monthly_rental' => 5500,
        'rob_loan_waived' => '0',
        'loan_admin_fee' => 450,
        'rob_deposit_waived' => '0',
        'deposit' => 1000,
        'rob_b_waived' => '0',
    ];
    $signature = public_path('images/lease/test_student_001.png');
    $owner = public_path('images/lease/juanita-cronje.jpg');
    $signed_at = 'Test';
    $agent = new \Jenssegers\Agent\Agent();

    $leaseContact = LeaseContactService::resolve($data);

    return compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'signature', 'owner', 'paymentBooking', 'signed_at', 'agent', 'leaseContact');
};

$buildLeaseBlankData = function () use ($ensureTestRole) {
    $ensureTestRole();

    $placeholder = '__________';

    $data = (object) [
        'userFirstName' => $placeholder,
        'userLastname' => $placeholder,
        'userId' => $placeholder,
        'userPassport' => $placeholder,
        'userNationality' => $placeholder,
        'studentNo' => $placeholder,
        'userEmail' => $placeholder,
        'userCellCode' => $placeholder,
        'userCell' => $placeholder,
        'userDob' => $placeholder,
        'userAddress1' => $placeholder,
        'userAddress2' => $placeholder,
        'userAddressCity' => $placeholder,
        'userAddressPostal' => $placeholder,
        'pAddress1' => $placeholder,
        'pAddress2' => $placeholder,
        'pAddressCity' => $placeholder,
        'pAddressPostal' => $placeholder,
        'year' => date('Y'),
        'studentType' => $placeholder,
        'kinFirstName' => $placeholder,
        'kinLastName' => $placeholder,
        'kinEmail' => $placeholder,
        'kinCellCode' => $placeholder,
        'kinCellNo' => $placeholder,
        'nsfas_bursar' => 0,
        'sponFirstName' => $placeholder,
        'sponLastName' => $placeholder,
        'sponId' => $placeholder,
    ];

    $booking = (object) [
        'lease_from_date' => $placeholder,
        'lease_from_time' => $placeholder,
        'lease_to_date' => $placeholder,
        'lease_to_time' => $placeholder,
        'booking_code' => $placeholder,
        'bed_code' => $placeholder,
        'property' => 0,
        'roomType' => 0,
        'floor' => $placeholder,
        'unit' => $placeholder,
        'room' => $placeholder,
        'bed' => $placeholder,
        'room_rate' => '0',
        'year' => date('Y'),
    ];

    $propertyData = (object) [
        'residence' => $placeholder,
        'bed_code' => $placeholder,
    ];

    $residence = (object) [
        'residence' => $placeholder,
        'owner' => $placeholder,
        'address' => $placeholder,
        'company_reg' => $placeholder,
        'company_vat' => $placeholder,
    ];

    $leaseAdminFee = 0;
    $paymentBooking = (object) [
        'monthly_rental' => 0,
        'rob_loan_waived' => '0',
        'loan_admin_fee' => 0,
        'rob_deposit_waived' => '0',
        'deposit' => 0,
        'rob_b_waived' => '0',
    ];
    $signature = public_path('images/lease/test_student_001.png');
    $owner = public_path('images/lease/juanita-cronje.jpg');
    $signed_at = $placeholder;
    $agent = new \Jenssegers\Agent\Agent();

    $leaseContact = LeaseContactService::resolve($data);

    return compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'signature', 'owner', 'paymentBooking', 'signed_at', 'agent', 'leaseContact');
};

Route::get('/test-lease-sign-pdf', function () use ($buildLeaseTestData) {
    $vars = $buildLeaseTestData();
    return \PDF::loadView('PDF.leaseSign', $vars)->stream('lease-sign-test.pdf');
});

Route::get('/test-lease-sign-dev-pdf', function () use ($buildLeaseTestData) {
    $vars = $buildLeaseTestData();
    return \PDF::loadView('PDF.leaseSignDev', $vars)->stream('lease-sign-dev-test.pdf');
});

Route::get('/test-lease-pdf', function () use ($buildLeaseTestData) {
    $vars = $buildLeaseTestData();
    return \PDF::loadView('PDF.leases', $vars)->stream('lease-test.pdf');
});

Route::get('/test-lease-blank-pdf', function () use ($buildLeaseBlankData) {
    $vars = $buildLeaseBlankData();
    return \PDF::loadView('PDF.blank-leases', $vars)->stream('lease-blank.pdf');
});

Route::get('/test-lease-screen-pdf', function () use ($buildLeaseTestData) {
    $vars = $buildLeaseTestData();
    return \PDF::loadView('PDF.leasesForScreen', $vars)->stream('lease-screen-test.pdf');
});

Route::get('/test-temp-lease-pdf', function () use ($ensureTestRole) {
    $ensureTestRole();

    $data = [
        'tenantName' => 'Test Student',
        'tenantId' => '9901010000001',
        'propertyName' => 'Test Residence',
    ];
    $pdfdata = [
        'tenantName' => 'Test Student',
        'tenantId' => '9901010000001',
        'propertyName' => 'Test Residence',
        'propertyOwner' => 'Test Owner (Pty) Ltd',
        'propertyAddress' => '1 Test Street, Johannesburg, 2001',
        'CompanyReg' => '2026/000000/07',
        'CompanyVat' => '4000000000',
    ];

    $signed_at = 'Test';
    $signature = pdfImagePath('images/lease/test_student_001.png');
    $owner = pdfImagePath('images/lease/juanita-cronje.jpg');
    $logo = pdfImagePath('images/logo/temp_lease_logo.png');

    $pdf = PDF::loadView('PDF.tempLeaseSign', compact('pdfdata', 'signed_at', 'signature', 'owner', 'logo'));

    return $pdf->stream('temp-lease-sign-test.pdf');
});
