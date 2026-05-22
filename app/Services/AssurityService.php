<?php

namespace App\Services;

use App\Jobs\SendAssurityMail;
use App\Models\ActionList;
use App\Models\AssurityToken;
use App\Models\Booking;
use App\Models\Lookups;
use App\Models\Residence;
use App\Models\StudentsInfo;
use App\Models\UserFiles;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\PDF;

class AssurityService
{
    /**
     * Request Debt Assurity signature (generate PDF, create token, email sponsor + notify student).
     *
     * Status model (aligned to temp-lease semantics):
     * - rob_65_debt: 2 = Pending (requested/signed awaiting admin), 1 = Approved, 0 = Rejected, 3 = Not Set/Not Required
     */
    public function requestAssurity($studentId, $year, array $actor = [], string $context = 'auto')
    {
        $student = StudentsInfo::where('userUid', $studentId)->where('year', $year)->first();
        if (!$student) {
            Log::warning('ASSURITY: requestAssurity missing student', ['studentId' => $studentId, 'year' => $year, 'context' => $context]);
            return ['ok' => false, 'message' => 'Student not found.'];
        }

        if ($student->studentType !== 'Cash / Direct') {
            return ['ok' => false, 'message' => 'Debt Assurity is only required for Cash students.'];
        }

        $sponsorEmail = trim((string) ($student->sponEmail ?? ''));
        if ($sponsorEmail === '') {
            return ['ok' => false, 'message' => 'Sponsor email not found.'];
        }

        $booking = Booking::where('userId', $studentId)->where('year', $year)->first();
        if (!$booking) {
            return ['ok' => false, 'message' => 'Booking not found.'];
        }

        $residence = Residence::where('id', $booking->property)->first();

        $userIdentification = $student->userId ?: $student->userPassport;
        if (!$userIdentification) {
            $userIdentification = 'user_' . $student->userUid;
        }

        $fileNameToSave = $booking->year . '_ASSURITY_' . $booking->booking_code . '_' . date('YmdHis') . '.pdf';
        $pdf = PDF::loadView('PDF.assurity', [
            'student' => $student,
            'booking' => $booking,
            'residence' => $residence,
            'signature' => null,
            'signed_at' => null,
        ]);

        $content = $pdf->download($fileNameToSave)->getOriginalContent();
        Storage::put('public/files/' . $userIdentification . '/' . $fileNameToSave, $content);

        $existing = UserFiles::where('fileId', 'assurity_doc')
            ->where('userUid', $studentId)
            ->where('year', $year)
            ->first();

        $uploadedBy = $actor['display_name'] ?? 'System';
        $createdAt = date('Y-m-d h:i:s');

        // Keep user_files.status as "Rejected" until sponsor uploads a signed version.
        $data = [
            'userUid' => $studentId,
            'fileName' => $fileNameToSave,
            'fileUrl' => 'files/' . $userIdentification . '/' . $fileNameToSave,
            'status' => 'Rejected',
            'uploaded_by' => $uploadedBy,
            'created_at' => $createdAt,
            'year' => $year,
        ];

        if ($existing) {
            UserFiles::where('id', $existing->id)->update($data);
            $actionType = 'Regenerated Debt Assurity';
        } else {
            $data['fileId'] = 'assurity_doc';
            UserFiles::insert($data);
            $actionType = 'Generated Debt Assurity';
        }

        $actorUid = $actor['user_uid'] ?? $studentId;
        $actorRole = $actor['role'] ?? 'Applicant';

        ActionList::create([
            'userUid' => $studentId,
            'action' => $actionType,
            'actionUserUid' => $actorUid,
            'actionUserDisplayName' => $uploadedBy,
            'actionUserRole' => $actorRole,
            'year' => $year,
        ]);

        // Pending once requested (or re-requested).
        StudentsInfo::where('userUid', $studentId)
            ->where('year', $year)
            ->update(['rob_65_debt' => 2]);

        AssurityToken::where('userUid', $studentId)
            ->where('year', $year)
            ->where('active', 1)
            ->update(['active' => 0]);

        $token = AssurityToken::create([
            'userUid' => $studentId,
            'assurity_token' => Str::random(40),
            'active' => 1,
            'signed' => 0,
            'role' => 'sponsor',
            'year' => $year,
        ]);

        $signUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/sign-assurity/' . $token->assurity_token;

        Log::info('ASSURITY: requested', [
            'studentId' => $studentId,
            'year' => $year,
            'token' => $token->assurity_token,
            'context' => $context,
            'by' => $actorUid,
        ]);

        SendAssurityMail::dispatch($studentId, $year, $signUrl);

        ActionList::create([
            'userUid' => $studentId,
            'action' => 'Debt Assurity link sent to sponsor',
            'actionUserUid' => $actorUid,
            'actionUserDisplayName' => $uploadedBy,
            'actionUserRole' => $actorRole,
            'year' => $year,
        ]);

        ActionList::create([
            'userUid' => $studentId,
            'action' => 'Debt Assurity reminder sent to student',
            'actionUserUid' => $actorUid,
            'actionUserDisplayName' => $uploadedBy,
            'actionUserRole' => $actorRole,
            'year' => $year,
        ]);

        return ['ok' => true, 'token' => $token->assurity_token];
    }

    public function maybeAutoRequestAfterStep5($studentId, $year, array $actor = [], string $context = 'step5')
    {
        $student = StudentsInfo::where('userUid', $studentId)->where('year', $year)->first();
        if (!$student || $student->studentType !== 'Cash / Direct') {
            return ['ok' => false, 'skipped' => true];
        }

        $status = $student->rob_65_debt;
        // If already pending or approved, don't spam sponsor/student.
        if ((int) $status === 2 || (int) $status === 1) {
            return ['ok' => false, 'skipped' => true];
        }

        // Only auto-request when "not set" or rejected.
        if ((int) $status !== 3 && (int) $status !== 0 && $status !== null) {
            return ['ok' => false, 'skipped' => true];
        }

        return $this->requestAssurity($studentId, $year, $actor, $context);
    }
}

