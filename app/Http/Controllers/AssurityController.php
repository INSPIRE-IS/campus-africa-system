<?php

namespace App\Http\Controllers;

use App\Jobs\SendAssurityMail;
use App\Models\ActionList;
use App\Models\AssurityToken;
use App\Models\Booking;
use App\Models\Lookups;
use App\Models\Residence;
use App\Models\StudentsInfo;
use App\Models\UserFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade as PDF;
use App\Services\AssurityService;
use Jenssegers\Agent\Agent;

class AssurityController extends Controller
{
    /** @var AssurityService */
    protected $assurityService;

    public function __construct(AssurityService $assurityService)
    {
        $this->assurityService = $assurityService;
    }
    protected function resolveYear()
    {
        $this_year = Lookups::activeYearLookup();
        if (Session::has('year')) {
            $year = Session::get('year');
            $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $year)->first();
        }

        return $this_year ? $this_year->lookup_name : date('Y');
    }

    protected function ensureSysAdmin()
    {
        if (!Auth::check() || Auth::user()->role_sys_admin != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return null;
    }

    public function generate(Request $request, $studentId)
    {
        if ($resp = $this->ensureSysAdmin()) {
            return $resp;
        }

        $this_year = $this->resolveYear();
        Log::info('ASSURITY: generate requested', ['studentId' => $studentId, 'year' => $this_year, 'by' => Auth::id()]);

        $result = $this->assurityService->requestAssurity($studentId, $this_year, [
            'user_uid' => Auth::user()->id,
            'display_name' => Auth::user()->userFirstName,
            'role' => Auth::user()->userRole,
        ], 'admin-generate');

        if (!$result['ok']) {
            return response()->json(['message' => $result['message'] ?? 'Unable to generate assurity.'], 422);
        }

        return [
            UserFiles::where('fileId', 'assurity_doc')->where('userUid', $studentId)->where('year', $this_year)->first(),
            StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->first(),
        ];
    }

    public function download_unsigned(Request $request, $studentId)
    {
        if ($resp = $this->ensureSysAdmin()) {
            return $resp;
        }

        $this_year = Lookups::resolveYearForUser((int) $studentId, Session::get('year'));
        $student = StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->first();
        $booking = Booking::where('userId', $studentId)->where('year', $this_year)->first();

        if (!$student || !$booking) {
            abort(404, 'Missing student or booking for Debt Assurity.');
        }

        if ($student->studentType !== 'Cash / Direct') {
            abort(422, 'Debt Assurity is only required for Cash students.');
        }

        $residence = Residence::where('id', $booking->property)->first();
        $pdf = PDF::loadView('PDF.assurity', [
            'student' => $student,
            'booking' => $booking,
            'residence' => $residence,
            'signature' => null,
            'signed_at' => null,
            'signed_on' => null,
            'spouse_required' => false,
            'spouse_reason' => '',
            'spouse_signature' => null,
        ]);

        return $pdf->download($booking->year . '_ASSURITY_UNSIGNED_' . $booking->booking_code . '.pdf');
    }

    public function resend(Request $request)
    {
        if ($resp = $this->ensureSysAdmin()) {
            return $resp;
        }

        $studentId = $request->userId;
        if (!$studentId) {
            return response()->json(['message' => 'Missing userId.'], 422);
        }

        $this_year = $this->resolveYear();
        Log::info('ASSURITY: resend requested', ['studentId' => $studentId, 'year' => $this_year, 'by' => Auth::id()]);
        $student = StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->first();
        if (!$student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        $sponsorEmail = trim((string) ($student->sponEmail ?? ''));
        if ($sponsorEmail === '') {
            return response()->json(['message' => 'Sponsor email not found.'], 422);
        }

        $token = AssurityToken::where('userUid', $studentId)
            ->where('year', $this_year)
            ->where('active', 1)
            ->where('signed', 0)
            ->first();

        if (!$token) {
            $file = UserFiles::where('fileId', 'assurity_doc')
                ->where('userUid', $studentId)
                ->where('year', $this_year)
                ->first();

            if ($file && $file->status === 'Uploaded') {
                return response()->json(['message' => 'Debt Assurity has already been signed/uploaded and is awaiting review.'], 422);
            }

            if ($file && $file->status === 'Approved') {
                return response()->json(['message' => 'Debt Assurity has already been approved.'], 422);
            }

            $signedTokenExists = AssurityToken::where('userUid', $studentId)
                ->where('year', $this_year)
                ->where('signed', 1)
                ->exists();

            if (!$file && $signedTokenExists) {
                return response()->json(['message' => 'Debt Assurity has already been signed, but the document record is missing. Upload the signed document manually or regenerate a new surety request.'], 422);
            }

            if (!$file) {
                $result = $this->assurityService->requestAssurity($studentId, $this_year, [
                    'user_uid' => Auth::user()->id,
                    'display_name' => Auth::user()->userFirstName,
                    'role' => Auth::user()->userRole,
                ], 'admin-resend-missing-file');

                if (!$result['ok']) {
                    return response()->json(['message' => $result['message'] ?? 'Unable to generate assurity.'], 422);
                }

                return response()->json(['status' => 'ok', 'generated' => true]);
            }

            $token = AssurityToken::create([
                'userUid' => $studentId,
                'assurity_token' => Str::random(40),
                'active' => 1,
                'signed' => 0,
                'role' => 'sponsor',
                'year' => $this_year,
            ]);
        }

        $signUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/sign-assurity/' . $token->assurity_token;
        Log::info('ASSURITY: resend token', ['studentId' => $studentId, 'year' => $this_year, 'token' => $token->assurity_token]);
        SendAssurityMail::dispatch($studentId, $this_year, $signUrl);

        ActionList::create([
            'userUid' => $studentId,
            'action' => 'Resent Debt Assurity link to sponsor',
            'actionUserUid' => Auth::user()->id,
            'actionUserDisplayName' => Auth::user()->userFirstName,
            'actionUserRole' => Auth::user()->userRole,
            'year' => $this_year,
        ]);

        ActionList::create([
            'userUid' => $studentId,
            'action' => 'Debt Assurity reminder sent to student',
            'actionUserUid' => Auth::user()->id,
            'actionUserDisplayName' => Auth::user()->userFirstName,
            'actionUserRole' => Auth::user()->userRole,
            'year' => $this_year,
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function view_sign_assurity(Request $request, $token)
    {
        $assurity = AssurityToken::where('assurity_token', $token)->where('active', 1)->first();
        if (!$assurity) {
            Log::warning('ASSURITY: invalid token view', ['token' => $token]);
            return redirect('/dashboard');
        }

        $this_year = $assurity->year ?: $this->resolveYear();
        Log::info('ASSURITY: sign view', ['token' => $token, 'studentId' => $assurity->userUid, 'year' => $this_year]);

        $student = StudentsInfo::where('userUid', $assurity->userUid)->where('year', $this_year)->first();
        $booking = Booking::where('userId', $assurity->userUid)->where('year', $this_year)->first();
        $residence = $booking ? Residence::where('id', $booking->property)->first() : null;
        $file = UserFiles::where('userUid', $assurity->userUid)->where('fileId', 'assurity_doc')->where('year', $this_year)->first();

        $pdf = view('PDF.assurity', [
            'student' => $student,
            'booking' => $booking,
            'residence' => $residence,
            'signature' => null,
            'signed_at' => null,
            // Prefill the signing date on the sponsor screen (final PDF will use submitted value).
            'signed_on' => date('Y-m-d'),
            'spouse_required' => false,
            'spouse_reason' => '',
            'spouse_signature' => null,
        ]);

        $agent = new Agent();
        return view('outSession.signAssurity', [
            'headerTitle' => 'Sign Debt Assurity',
            'userCustomId' => $assurity->assurity_token,
            'user' => $student,
            'file' => $file,
            'pdf' => $pdf,
            'agent' => $agent,
            'sessionYear' => $this_year,
            'active' => 0,
            'bedCode' => null,
        ]);
    }

    public function save_signed_assurity(Request $request, $token)
    {
        $assurity = AssurityToken::where('assurity_token', $token)->where('active', 1)->first();
        if (!$assurity) {
            Log::warning('ASSURITY: invalid token submit', ['token' => $token]);
            return redirect('/login');
        }

        $this_year = $assurity->year ?: $this->resolveYear();
        Log::info('ASSURITY: submit started', ['token' => $token, 'studentId' => $assurity->userUid, 'year' => $this_year]);

        $student = StudentsInfo::where('userUid', $assurity->userUid)->where('year', $this_year)->first();
        $booking = Booking::where('userId', $assurity->userUid)->where('year', $this_year)->first();
        $residence = $booking ? Residence::where('id', $booking->property)->first() : null;

        if (!$student || !$booking) {
            return response()->json(['message' => 'Missing student or booking.'], 404);
        }

        $data = $request->signature_string;
        if (!$data) {
            return response()->json(['message' => 'Signature missing.'], 422);
        }

        list($type, $data) = explode(';', $data);
        list(, $extension) = explode('/', $type);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        $userIdentification = $student->userId ?: $student->userPassport;
        if (!$userIdentification) {
            $userIdentification = 'user_' . $student->userUid;
        }

        $new_name = $userIdentification . '_' . strtotime('now') . '_' . Str::random(20) . '.' . $extension;
        $signatureStorageRel = 'public/files/' . $userIdentification . '/' . $new_name;
        \Storage::put($signatureStorageRel, $data);

        // Keep a public-relative path for UI uses, but also compute a filesystem path for PDF embedding.
        // Some PDF renderers can't resolve "public/storage/..." if the storage symlink isn't present.
        $signature = 'storage/files/' . $userIdentification . '/' . $new_name;
        // Normalize path separators for dompdf on Windows dev environments.
        $signaturePath = str_replace('\\', '/', \Storage::path($signatureStorageRel));
        $signed_at = $request->signed_at;
        $signed_on = $request->signed_on ?: date('Y-m-d');

        $spouseSignature = null;
        $spouseSignatureFileName = null;
        $spouseRequired = $request->spouse_required == '1' || $request->spouse_required === 1;
        $spouseReason = trim((string) ($request->spouse_not_required_reason ?? ''));

        $spouseSignaturePath = null;
        if ($spouseRequired) {
            $spouseData = $request->spouse_signature_string;
            if (!$spouseData) {
                return response()->json(['message' => 'Spouse signature missing.'], 422);
            }

            // "spouse_accept" checkbox is enforced in the browser, but validate server-side too.
            if (!($request->spouse_accept == '1' || $request->spouse_accept === 1)) {
                return response()->json(['message' => 'Spouse consent is required.'], 422);
            }

            list($spType, $spData) = explode(';', $spouseData);
            list(, $spExtension) = explode('/', $spType);
            list(, $spData) = explode(',', $spData);
            $spData = base64_decode($spData);

            $spouseFile = $userIdentification . '_' . strtotime('now') . '_spouse_' . Str::random(20) . '.' . $spExtension;
            $spouseStorageRel = 'public/files/' . $userIdentification . '/' . $spouseFile;
            \Storage::put($spouseStorageRel, $spData);
            $spouseSignature = 'storage/files/' . $userIdentification . '/' . $spouseFile;
            $spouseSignaturePath = str_replace('\\', '/', \Storage::path($spouseStorageRel));
            $spouseSignatureFileName = $spouseFile;
        } else {
            if ($spouseReason === '') {
                return response()->json(['message' => 'Reason is required when spouse consent is not applicable.'], 422);
            }
        }

        $fileNameToSave = $booking->year . '_ASSURITY_SIGNED_' . $booking->booking_code . '_' . date('YmdHis') . '.pdf';
        $pdf = PDF::loadView('PDF.assurity', [
            'student' => $student,
            'booking' => $booking,
            'residence' => $residence,
            'signature' => $signature,
            'signature_path' => $signaturePath,
            'signed_at' => $signed_at,
            'signed_on' => $signed_on,
            'spouse_required' => $spouseRequired,
            'spouse_reason' => $spouseReason,
            'spouse_signature' => $spouseSignature,
            'spouse_signature_path' => $spouseSignaturePath,
        ]);

        $content = $pdf->download($fileNameToSave)->getOriginalContent();
        \Storage::put('public/files/' . $userIdentification . '/' . $fileNameToSave, $content);

        $fileData = [
            'fileName' => $fileNameToSave,
            'fileUrl' => 'files/' . $userIdentification . '/' . $fileNameToSave,
            'status' => 'Uploaded',
            'rejectReason' => '',
        ];

        UserFiles::updateOrCreate([
            'fileId' => 'assurity_doc',
            'userUid' => $student->userUid,
            'year' => $this_year,
        ], array_merge($fileData, [
            'uploaded_by' => trim((string) (($student->sponFirstName ?? '') . ' ' . ($student->sponLastName ?? ''))) ?: 'Sponsor',
        ]));

        StudentsInfo::where('userUid', $student->userUid)
            ->where('year', $this_year)
            // Align to temp-lease style numeric semantics: 2 = Pending (signed/uploaded, awaiting admin approval).
            ->update(['rob_65_debt' => 2]);
        Log::info('ASSURITY: signed PDF stored, status updated', ['studentId' => $student->userUid, 'year' => $this_year, 'fileName' => $fileNameToSave]);

        ActionList::create([
            'userUid' => $student->userUid,
            'action' => 'Debt Assurity signed, pending approval',
            'actionUserUid' => $student->userUid,
            'actionUserDisplayName' => $student->userFirstName,
            'actionUserRole' => 'Applicant',
            'year' => $this_year,
        ]);

        AssurityToken::where('assurity_token', $assurity->assurity_token)
            ->update([
                'active' => 0,
                'signed' => 1,
                'signature_url' => $new_name,
                'spouse_required' => $spouseRequired ? 1 : 0,
                'spouse_not_required_reason' => $spouseRequired ? null : $spouseReason,
                'spouse_signature_url' => $spouseSignatureFileName,
                'signed_at' => $signed_at,
                'signed_on' => $signed_on,
                'year' => $this_year,
            ]);
        Log::info('ASSURITY: token closed', ['token' => $assurity->assurity_token, 'studentId' => $student->userUid, 'year' => $this_year]);

        return 'Completed';
    }
}
