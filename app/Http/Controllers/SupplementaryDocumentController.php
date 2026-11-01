<?php

namespace App\Http\Controllers;

use App\Models\ActionList;
use App\Models\Booking;
use App\Models\Lookups;
use App\Models\PropertyPermission;
use App\Models\StudentsInfo;
use App\Models\SupplementaryDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SupplementaryDocumentController extends Controller
{
    public function store(Request $request)
    {
        if ((int) Auth::user()->userRole !== 0) {
            abort(403, 'Only student accounts can upload additional documents.');
        }

        if (config('student_documents.additional_uploads_mode', 'disabled') !== 'review') {
            abort(403, 'Additional document uploads are disabled.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        $userId = (int) Auth::id();
        $year = (string) Session::get('year');

        $validYear = ctype_digit($year)
            && (int) $year >= (int) now()->year
            && Lookups::where('group_id', '4')->where('lookup_name', $year)->exists();

        if (!$validYear) {
            return back()->withErrors(['supplementary_upload' => 'Additional documents cannot be uploaded for the selected year.']);
        }

        $student = StudentsInfo::where('userUid', $userId)
            ->where('year', $year)
            ->first();

        if (!$student) {
            return back()->withErrors(['supplementary_upload' => 'No application was found for the selected year.']);
        }

        if (!(bool) $student->additional_document_upload_enabled) {
            return back()->withErrors(['supplementary_upload' => 'Additional document uploads are not enabled for this application.']);
        }

        if ((int) $student->rob_60_cancelled === 1) {
            return back()->withErrors(['supplementary_upload' => 'Additional documents cannot be uploaded for a cancelled application.']);
        }

        $file = $request->file('file');
        $extension = $file->guessExtension() ?: $file->extension();
        $storedName = (string) Str::uuid() . '.' . $extension;
        $directory = "files/{$userId}/supplementary";
        $storedPath = null;

        try {
            $storedPath = Storage::disk('public')->putFileAs($directory, $file, $storedName);
            if (!$storedPath) {
                throw new \RuntimeException('The supplementary document could not be stored.');
            }

            $document = DB::transaction(function () use ($validated, $file, $storedPath, $userId, $year, $student) {
                $document = SupplementaryDocument::create([
                    'user_uid' => $userId,
                    'year' => $year,
                    'title' => trim($validated['title']),
                    'original_file_name' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'uploaded_by' => $userId,
                    'status' => 'Uploaded',
                ]);

                ActionList::create([
                    'userUid' => $userId,
                    'action' => 'Uploaded an additional document',
                    'actionUserUid' => $userId,
                    'actionUserDisplayName' => trim($student->userFirstName . ' ' . $student->userLastname),
                    'actionUserRole' => Auth::user()->userRole,
                    'year' => $year,
                    'meta' => [
                        'type' => 'supplementary_document',
                        'supplementary_document_id' => $document->id,
                        'title' => $document->title,
                    ],
                ]);

                return $document;
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            Log::error('Supplementary document upload failed', [
                'user_uid' => $userId,
                'year' => $year,
                'exception' => $exception,
            ]);

            return back()->withErrors(['supplementary_upload' => 'The document could not be uploaded. Please try again.']);
        }

        return redirect()
            ->route('my-documents', ['year' => $year])
            ->with('supplementary_success', "{$document->title} was uploaded.");
    }

    public function view($documentId)
    {
        if (!$this->isAdminUser()) {
            abort(403, 'You are not authorised to view additional documents.');
        }

        $document = SupplementaryDocument::with('reviewer')
            ->findOrFail($documentId);

        if (!$document || !$this->canAdminView($document)) {
            abort(403, 'You are not authorised to view this additional document.');
        }

        $student = StudentsInfo::where('userUid', $document->user_uid)
            ->where('year', $document->year)
            ->firstOrFail();

        return view('application.document.view_supplementary_document', [
            'document' => $document,
            'student' => $student,
            'documentUrl' => route('supplementary-documents.file', $document->id),
            'canReview' => $document->status === 'Uploaded'
                && $this->canReview()
                && $this->isReviewableYear($document->year),
        ]);
    }

    public function review(Request $request, $documentId)
    {
        if (!$this->canReview()) {
            abort(403, 'You are not authorised to review additional documents.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Approved,Rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:1000', 'required_if:status,Rejected'],
        ]);

        $document = SupplementaryDocument::findOrFail($documentId);
        if (!$this->canAdminView($document)) {
            abort(403, 'You are not authorised to review this additional document.');
        }

        if (!$this->isReviewableYear($document->year)) {
            return response()->json([
                'message' => 'Historic application documents are read-only.',
            ], 422);
        }

        $reviewer = Auth::user();
        $status = $validated['status'];
        $reason = $status === 'Rejected' ? trim($validated['rejection_reason']) : null;

        $reviewed = DB::transaction(function () use ($document, $reviewer, $status, $reason) {
            $lockedDocument = SupplementaryDocument::whereKey($document->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDocument->status !== 'Uploaded') {
                return false;
            }

            $lockedDocument->update([
                'status' => $status,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            ActionList::create([
                'userUid' => $lockedDocument->user_uid,
                'action' => $status . ' an additional document',
                'actionUserUid' => $reviewer->id,
                'actionUserDisplayName' => trim($reviewer->userFirstName . ' ' . $reviewer->userLastname),
                'actionUserRole' => $reviewer->userRole,
                'year' => $lockedDocument->year,
                'meta' => [
                    'type' => 'supplementary_document',
                    'supplementary_document_id' => $lockedDocument->id,
                    'title' => $lockedDocument->title,
                    'status' => $status,
                    'rejection_reason' => $reason,
                ],
            ]);

            return true;
        });

        if (!$reviewed) {
            return response()->json([
                'message' => 'This additional document has already been reviewed.',
            ], 422);
        }

        return response()->json([
            'message' => 'The additional document was ' . strtolower($status) . '.',
            'status' => $status,
        ]);
    }

    public function file($documentId)
    {
        $document = SupplementaryDocument::findOrFail($documentId);
        $user = Auth::user();

        if ((int) $user->userRole === 0) {
            if ((int) $document->user_uid !== (int) $user->id) {
                abort(403);
            }
        } elseif (!$this->isAdminUser() || !$this->canAdminView($document)) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document file not found.');
        }

        $response = response()->file(Storage::disk('public')->path($document->file_path));
        $fallbackName = Str::ascii($document->original_file_name) ?: 'additional-document';
        $response->setContentDisposition('inline', $document->original_file_name, $fallbackName);

        return $response;
    }

    private function isAdminUser(): bool
    {
        return Auth::check() && in_array((string) Auth::user()->userRole, ['1', '2', '3'], true);
    }

    private function canReview(): bool
    {
        if (!$this->isAdminUser()) {
            return false;
        }

        $user = Auth::user();

        return (int) $user->role_approve_docs === 1
            || (int) $user->role_sys_admin === 1
            || (int) $user->role_super === 1;
    }

    private function canAdminView(SupplementaryDocument $document): bool
    {
        $student = StudentsInfo::where('userUid', $document->user_uid)
            ->where('year', $document->year)
            ->first();
        $booking = Booking::where('userId', $document->user_uid)
            ->where('year', $document->year)
            ->first();

        if (!$student || !$booking) {
            return false;
        }

        return PropertyPermission::where('userUid', Auth::id())
            ->where('property', $booking->property)
            ->exists();
    }

    private function isReviewableYear($year): bool
    {
        return ctype_digit((string) $year) && (int) $year >= (int) now()->year;
    }
}
