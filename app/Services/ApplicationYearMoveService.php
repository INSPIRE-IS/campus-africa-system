<?php

namespace App\Services;

use App\Models\ActionList;
use App\Models\AssurityToken;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Lookups;
use App\Models\Property;
use App\Models\Residence;
use App\Models\StudentCompatibilityScore;
use App\Models\StudentQuestionAnswer;
use App\Models\StudentsInfo;
use App\Models\SupplementaryDocument;
use App\Models\User;
use App\Models\UserFiles;
use App\Models\UserLeaseToken;
use Exception;
use Illuminate\Support\Facades\DB;

class ApplicationYearMoveService
{
    protected $generatedFileIds = [
        'moveIn',
        'tempMoveIn',
        'assurity_doc',
        'quote',
        'invoice',
        'MDA',
    ];

    public function preview($userId, $fromYear = null, $toYear = null)
    {
        $userId = (int) $userId;
        $fromYear = $this->normalizeYear($fromYear ?: Lookups::activeYearValue());
        $toYear = $this->normalizeYear($toYear ?: ((int) $fromYear - 1));

        $blockers = [];
        $warnings = [];

        if ($userId <= 0) {
            $blockers[] = $this->message('invalid_user', 'Provide a valid user id.');
        }

        if (!$fromYear || !$toYear || $fromYear === $toYear) {
            $blockers[] = $this->message('invalid_years', 'Provide valid, different source and target years.');
        }

        if ($fromYear && $toYear && ((int) $fromYear - 1) !== (int) $toYear) {
            $blockers[] = $this->message('non_adjacent_years', 'This move is only allowed from the following year into the previous year.', [
                'from_year' => $fromYear,
                'to_year' => $toYear,
            ]);
        }

        $sourceYearLookup = $fromYear ? Lookups::where('group_id', 4)->where('lookup_name', $fromYear)->first() : null;
        $targetYearLookup = $toYear ? Lookups::where('group_id', 4)->where('lookup_name', $toYear)->first() : null;

        if ($fromYear && !$sourceYearLookup) {
            $blockers[] = $this->message('missing_source_year_lookup', "Source year {$fromYear} does not exist in Year lookups.");
        }

        if ($toYear && !$targetYearLookup) {
            $blockers[] = $this->message('missing_target_year_lookup', "Target year {$toYear} does not exist in Year lookups.");
        }

        $user = $userId > 0 ? User::withTrashed()->where('id', $userId)->first() : null;
        if ($userId > 0 && !$user) {
            $blockers[] = $this->message('missing_user', "No users row found for id {$userId}.");
        }

        $sourceStudents = collect();
        $sourceBookings = collect();
        $sourceStudent = null;
        $sourceBooking = null;

        if ($userId > 0 && $fromYear) {
            $sourceStudents = StudentsInfo::withTrashed()->where('userUid', $userId)->where('year', $fromYear)->get();
            $sourceBookings = Booking::withTrashed()->where('userId', $userId)->where('year', $fromYear)->get();
            $sourceStudent = $sourceStudents->first();
            $sourceBooking = $sourceBookings->first();
        }

        if ($fromYear && $sourceStudents->count() === 0) {
            $blockers[] = $this->message('missing_source_student', "No students_info row found for userId={$userId}, year={$fromYear}.");
        } elseif ($sourceStudents->count() > 1) {
            $blockers[] = $this->message('multiple_source_students', "More than one students_info row exists for userId={$userId}, year={$fromYear}. Resolve this manually first.", [
                'ids' => $sourceStudents->pluck('id')->values()->all(),
            ]);
        } elseif ($sourceStudent && $sourceStudent->deleted_at) {
            $blockers[] = $this->message('source_student_deleted', "The source students_info row is soft-deleted and cannot be moved from app profile.", [
                'id' => $sourceStudent->id,
                'deleted_at' => (string) $sourceStudent->deleted_at,
            ]);
        }

        if ($fromYear && $sourceBookings->count() === 0) {
            $blockers[] = $this->message('missing_source_booking', "No bookings_data row found for userId={$userId}, year={$fromYear}.");
        } elseif ($sourceBookings->count() > 1) {
            $blockers[] = $this->message('multiple_source_bookings', "More than one bookings_data row exists for userId={$userId}, year={$fromYear}. Resolve this manually first.", [
                'ids' => $sourceBookings->pluck('id')->values()->all(),
            ]);
        } elseif ($sourceBooking && $sourceBooking->deleted_at) {
            $blockers[] = $this->message('source_booking_deleted', "The source bookings_data row is soft-deleted and cannot be moved from app profile.", [
                'id' => $sourceBooking->id,
                'deleted_at' => (string) $sourceBooking->deleted_at,
            ]);
        }

        $targetStudents = collect();
        $targetBookings = collect();
        $targetFiles = collect();
        $targetSupplementaryDocuments = collect();
        $targetLeaseTokens = collect();
        $targetAssurityTokens = collect();

        if ($userId > 0 && $toYear) {
            $targetStudents = StudentsInfo::withTrashed()->where('userUid', $userId)->where('year', $toYear)->get();
            $targetBookings = Booking::withTrashed()->where('userId', $userId)->where('year', $toYear)->get();
            $targetFiles = UserFiles::withTrashed()->where('userUid', $userId)->where('year', $toYear)->get();
            $targetSupplementaryDocuments = SupplementaryDocument::withTrashed()->where('user_uid', $userId)->where('year', $toYear)->get();
            $targetLeaseTokens = UserLeaseToken::withTrashed()->where('userUid', $userId)->where('year', $toYear)->get();
            $targetAssurityTokens = AssurityToken::withTrashed()->where('userUid', $userId)->where('year', $toYear)->get();
        }

        if ($targetStudents->count() > 0 || $targetBookings->count() > 0 || $targetFiles->count() > 0 || $targetSupplementaryDocuments->count() > 0) {
            $blockers[] = $this->message('target_not_empty', "Target year {$toYear} already has application records. This move will not merge or overwrite target-year data.", [
                'students_info_ids' => $targetStudents->pluck('id')->values()->all(),
                'booking_ids' => $targetBookings->pluck('id')->values()->all(),
                'user_file_ids' => $targetFiles->pluck('id')->values()->all(),
                'supplementary_document_ids' => $targetSupplementaryDocuments->pluck('id')->values()->all(),
            ]);
        }

        if ($targetLeaseTokens->count() > 0 || $targetAssurityTokens->count() > 0) {
            $blockers[] = $this->message('target_tokens_exist', "Target year {$toYear} already has lease/assurity tokens for this user. This move will not merge target-year token data.", [
                'lease_token_ids' => $targetLeaseTokens->pluck('id')->values()->all(),
                'assurity_token_ids' => $targetAssurityTokens->pluck('id')->values()->all(),
            ]);
        }

        $newBookingCode = null;
        if ($sourceBooking && $toYear) {
            $newBookingCode = $this->generateBookingCode($sourceBooking, $toYear);
            if (!$newBookingCode) {
                $blockers[] = $this->message('missing_booking_prefix', 'Cannot regenerate the booking code because the residence/building prefix is missing.', [
                    'booking_id' => $sourceBooking->id,
                    'property' => $sourceBooking->property,
                ]);
            } else {
                $existingCode = Booking::withTrashed()
                    ->where('booking_code', $newBookingCode)
                    ->where('id', '!=', $sourceBooking->id)
                    ->first();

                if ($existingCode) {
                    $blockers[] = $this->message('booking_code_exists', "Generated booking code {$newBookingCode} is already used by another booking.", [
                        'existing_booking_id' => $existingCode->id,
                    ]);
                }
            }
        }

        $sourceFiles = collect();
        $sourceSupplementaryDocuments = collect();
        $sourceGeneratedFiles = collect();
        $sourceApprovedFiles = collect();
        $sourcePayments = collect();
        $sourceActionLogs = collect();
        $sourceLeaseTokens = collect();
        $sourceAssurityTokens = collect();
        $sourceQuestionAnswers = collect();
        $sourceCompatibilityCount = 0;
        $bedDetails = null;

        if ($userId > 0 && $fromYear) {
            $sourceFiles = UserFiles::withTrashed()->where('userUid', $userId)->where('year', $fromYear)->orderBy('id')->get();
            $sourceSupplementaryDocuments = SupplementaryDocument::withTrashed()->where('user_uid', $userId)->where('year', $fromYear)->orderBy('id')->get();
            $sourceGeneratedFiles = $sourceFiles->whereIn('fileId', $this->generatedFileIds)->values();
            $sourceApprovedFiles = $sourceFiles->filter(function ($file) {
                return in_array($file->status, ['Approved', 'XApproved'], true);
            })->values();

            $sourceActionLogs = ActionList::where('userUid', $userId)->where('year', $fromYear)->get();
            $sourceLeaseTokens = UserLeaseToken::withTrashed()->where('userUid', $userId)->where('year', $fromYear)->get();
            $sourceAssurityTokens = AssurityToken::withTrashed()->where('userUid', $userId)->where('year', $fromYear)->get();
        }

        if ($sourceBooking) {
            $sourcePayments = BookingPayment::withTrashed()->where('bookingId', $sourceBooking->id)->get();
            $bedDetails = $this->bedDetails($sourceBooking, $userId, $toYear);

            if ($sourceBooking->property_data_id || $sourceBooking->bed_code || $sourceBooking->bed) {
                $warnings[] = $this->message('bed_allocated', 'This booking has bed data. The bed assignment will be kept on the moved booking and must be reviewed.', $bedDetails ?: []);
            }

            if ($bedDetails && isset($bedDetails['bed_user_id_room']) && $bedDetails['bed_user_id_room'] && (int) $bedDetails['bed_user_id_room'] !== $userId) {
                $warnings[] = $this->message('bed_assigned_to_other_user', 'The linked bed is assigned to another user in property_data. Review before relying on the allocation.', $bedDetails);
            }

            if ($bedDetails && array_key_exists('valid_for_target_year', $bedDetails) && !$bedDetails['valid_for_target_year']) {
                $warnings[] = $this->message('bed_not_valid_for_target_year', 'The linked bed row does not look valid for the target year based on property_data year/year_end.', $bedDetails);
            }
        }

        if ($sourceStudent && $fromYear) {
            $sourceQuestionAnswers = StudentQuestionAnswer::where('student_id', $sourceStudent->id)->where('academic_year', $fromYear)->get();
            $sourceCompatibilityCount = StudentCompatibilityScore::where('academic_year', $fromYear)
                ->where(function ($query) use ($sourceStudent) {
                    $query->where('student_a_id', $sourceStudent->id)
                        ->orWhere('student_b_id', $sourceStudent->id);
                })
                ->count();

            if ($sourceStudent->bookingId && $sourceBooking && (int) $sourceStudent->bookingId !== (int) $sourceBooking->id) {
                $warnings[] = $this->message('student_booking_mismatch', 'students_info.bookingId does not match the source booking row. The move will realign bookingId/bookingRef to the moved booking.', [
                    'students_info_id' => $sourceStudent->id,
                    'student_booking_id' => $sourceStudent->bookingId,
                    'source_booking_id' => $sourceBooking->id,
                ]);
            }
        }

        if ($sourceGeneratedFiles->count() > 0) {
            $warnings[] = $this->message('generated_files_exist', 'Generated/critical files exist and will move to the target year. Review and regenerate if their content still references the wrong year.', [
                'files' => $this->fileSummary($sourceGeneratedFiles),
            ]);
        }

        if ($sourceApprovedFiles->count() > 0) {
            $warnings[] = $this->message('approved_files_exist', 'Approved files exist and will remain approved after the year move.', [
                'files' => $this->fileSummary($sourceApprovedFiles),
            ]);
        }

        if ($sourcePayments->count() > 0) {
            $warnings[] = $this->message('booking_payment_exists', 'Booking payment rows are linked by bookingId and will stay linked. Recheck pricing after moving because payment totals are not recalculated here.', [
                'booking_payment_ids' => $sourcePayments->pluck('id')->values()->all(),
            ]);
        }

        if ($sourceCompatibilityCount > 0) {
            $warnings[] = $this->message('compatibility_scores_exist', 'Existing compatibility scores for the source year will be deleted and should be recalculated for the target year.', [
                'count' => $sourceCompatibilityCount,
            ]);
        }

        if ($sourceLeaseTokens->count() > 0 || $sourceAssurityTokens->count() > 0) {
            $warnings[] = $this->message('source_tokens_exist', 'Lease/assurity tokens exist for the source year and will be moved to the target year.', [
                'lease_token_ids' => $sourceLeaseTokens->pluck('id')->values()->all(),
                'assurity_token_ids' => $sourceAssurityTokens->pluck('id')->values()->all(),
            ]);
        }

        if ($user && $user->year && (string) $user->year !== $fromYear) {
            $warnings[] = $this->message('user_year_not_source', 'users.year is not the source year, so it will not be changed automatically.', [
                'users_year' => $user->year,
                'from_year' => $fromYear,
                'to_year' => $toYear,
            ]);
        }

        return [
            'ok' => empty($blockers),
            'user_id' => $userId,
            'from_year' => $fromYear,
            'to_year' => $toYear,
            'blockers' => $blockers,
            'warnings' => $warnings,
            'records' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'email' => $user->email,
                    'year' => $user->year,
                    'deleted_at' => $user->deleted_at ? (string) $user->deleted_at : null,
                ] : null,
                'students_info' => $sourceStudent ? [
                    'id' => $sourceStudent->id,
                    'year' => $sourceStudent->year,
                    'bookingId' => $sourceStudent->bookingId,
                    'bookingRef' => $sourceStudent->bookingRef,
                    'deleted_at' => $sourceStudent->deleted_at ? (string) $sourceStudent->deleted_at : null,
                ] : null,
                'booking' => $sourceBooking ? [
                    'id' => $sourceBooking->id,
                    'year' => $sourceBooking->year,
                    'booking_code' => $sourceBooking->booking_code,
                    'new_booking_code' => $newBookingCode,
                    'property' => $sourceBooking->property,
                    'roomType' => $sourceBooking->roomType,
                    'property_data_id' => $sourceBooking->property_data_id,
                    'bed_code' => $sourceBooking->bed_code,
                    'lease_from_date' => $sourceBooking->lease_from_date,
                    'lease_to_date' => $sourceBooking->lease_to_date,
                    'deleted_at' => $sourceBooking->deleted_at ? (string) $sourceBooking->deleted_at : null,
                ] : null,
                'target_existing' => [
                    'students_info' => $targetStudents->count(),
                    'bookings_data' => $targetBookings->count(),
                    'user_files' => $targetFiles->count(),
                    'supplementary_documents' => $targetSupplementaryDocuments->count(),
                    'lease_tokens' => $targetLeaseTokens->count(),
                    'assurity_tokens' => $targetAssurityTokens->count(),
                ],
                'counts' => [
                    'source_files' => $sourceFiles->count(),
                    'supplementary_documents' => $sourceSupplementaryDocuments->count(),
                    'generated_files' => $sourceGeneratedFiles->count(),
                    'approved_files' => $sourceApprovedFiles->count(),
                    'booking_payments' => $sourcePayments->count(),
                    'action_logs' => $sourceActionLogs->count(),
                    'lease_tokens' => $sourceLeaseTokens->count(),
                    'assurity_tokens' => $sourceAssurityTokens->count(),
                    'question_answers' => $sourceQuestionAnswers->count(),
                    'compatibility_scores' => $sourceCompatibilityCount,
                ],
                'bed' => $bedDetails,
            ],
        ];
    }

    public function apply($userId, $fromYear = null, $toYear = null, array $actor = [])
    {
        $preview = $this->preview($userId, $fromYear, $toYear);
        if (!$preview['ok']) {
            $preview['applied'] = false;
            return $preview;
        }

        $result = null;

        DB::transaction(function () use ($preview, $actor, &$result) {
            $userId = (int) $preview['user_id'];
            $fromYear = $preview['from_year'];
            $toYear = $preview['to_year'];

            $user = User::withTrashed()->where('id', $userId)->lockForUpdate()->first();
            $student = StudentsInfo::where('userUid', $userId)->where('year', $fromYear)->lockForUpdate()->first();
            $booking = Booking::where('userId', $userId)->where('year', $fromYear)->lockForUpdate()->first();
            $targetStudent = StudentsInfo::withTrashed()->where('userUid', $userId)->where('year', $toYear)->lockForUpdate()->first();
            $targetBooking = Booking::withTrashed()->where('userId', $userId)->where('year', $toYear)->lockForUpdate()->first();
            $targetFilesCount = UserFiles::withTrashed()->where('userUid', $userId)->where('year', $toYear)->count();
            $targetSupplementaryDocumentsCount = SupplementaryDocument::withTrashed()->where('user_uid', $userId)->where('year', $toYear)->count();
            $targetLeaseTokensCount = UserLeaseToken::withTrashed()->where('userUid', $userId)->where('year', $toYear)->count();
            $targetAssurityTokensCount = AssurityToken::withTrashed()->where('userUid', $userId)->where('year', $toYear)->count();

            if (!$user || !$student || !$booking) {
                throw new Exception('Source user/student/booking changed before the move could be applied. Run the preview again.');
            }

            if ($targetStudent || $targetBooking || $targetFilesCount > 0 || $targetSupplementaryDocumentsCount > 0 || $targetLeaseTokensCount > 0 || $targetAssurityTokensCount > 0) {
                throw new Exception('Target year is no longer empty. Run the preview again.');
            }

            $targetYearLookup = Lookups::where('group_id', 4)->where('lookup_name', $toYear)->first();
            if (!$targetYearLookup) {
                throw new Exception("Target year {$toYear} does not exist in Year lookups.");
            }

            $oldStudent = $this->snapshot($student, ['id', 'userUid', 'year', 'bookingId', 'bookingRef']);
            $oldBooking = $this->snapshot($booking, ['id', 'userId', 'year', 'booking_code', 'lease_from_date', 'lease_from_time', 'lease_to_date', 'lease_to_time', 'property', 'roomType', 'property_data_id', 'floor', 'unit', 'room', 'bed', 'bed_code', 'room_rate']);
            $oldUserYear = $user->year;
            $newBookingCode = $this->generateBookingCode($booking, $toYear);

            if (!$newBookingCode) {
                throw new Exception('Cannot regenerate booking code because the residence/building prefix is missing.');
            }

            $booking->year = $toYear;
            $booking->booking_code = $newBookingCode;
            $booking->lease_from_date = $targetYearLookup->lease_from_date;
            $booking->lease_from_time = $targetYearLookup->lease_from_time;
            $booking->lease_to_date = $targetYearLookup->lease_to_date;
            $booking->lease_to_time = $targetYearLookup->lease_to_time;
            $booking->save();

            $student->year = $toYear;
            if ((int) $student->bookingId !== (int) $booking->id) {
                $student->bookingId = $booking->id;
            }
            if ((string) $student->bookingRef !== (string) $booking->id) {
                $student->bookingRef = $booking->id;
            }
            $student->save();

            $filesMoved = UserFiles::withTrashed()
                ->where('userUid', $userId)
                ->where('year', $fromYear)
                ->update(['year' => $toYear, 'updated_at' => now()]);

            $supplementaryDocumentsMoved = SupplementaryDocument::withTrashed()
                ->where('user_uid', $userId)
                ->where('year', $fromYear)
                ->update(['year' => $toYear, 'updated_at' => now()]);

            $actionsMoved = ActionList::where('userUid', $userId)
                ->where('year', $fromYear)
                ->update(['year' => $toYear, 'updated_at' => now()]);

            $leaseTokensMoved = UserLeaseToken::withTrashed()
                ->where('userUid', $userId)
                ->where('year', $fromYear)
                ->update(['year' => $toYear, 'updated_at' => now()]);

            $assurityTokensMoved = AssurityToken::withTrashed()
                ->where('userUid', $userId)
                ->where('year', $fromYear)
                ->update(['year' => $toYear, 'updated_at' => now()]);

            $questionAnswersMoved = StudentQuestionAnswer::where('student_id', $student->id)
                ->where('academic_year', $fromYear)
                ->update(['academic_year' => $toYear, 'updated_at' => now()]);

            $compatibilityScoresDeleted = StudentCompatibilityScore::where('academic_year', $fromYear)
                ->where(function ($query) use ($student) {
                    $query->where('student_a_id', $student->id)
                        ->orWhere('student_b_id', $student->id);
                })
                ->delete();

            $userYearUpdated = false;
            if ((string) $user->year === (string) $fromYear) {
                $user->year = $toYear;
                $user->save();
                $userYearUpdated = true;
            }

            $audit = ActionList::create([
                'userUid' => $userId,
                'action' => "Moved application year from {$fromYear} to {$toYear}",
                'actionUserUid' => $actor['id'] ?? null,
                'actionUserDisplayName' => $actor['display_name'] ?? 'System',
                'actionUserRole' => $actor['role'] ?? null,
                'year' => $toYear,
                'meta' => [
                    'flow' => 'application:move-year',
                    'from_year' => $fromYear,
                    'to_year' => $toYear,
                    'warnings_shown' => $preview['warnings'],
                    'before' => [
                        'students_info' => $oldStudent,
                        'booking' => $oldBooking,
                        'users_year' => $oldUserYear,
                    ],
                    'after' => [
                        'students_info' => $this->snapshot($student, ['id', 'userUid', 'year', 'bookingId', 'bookingRef']),
                        'booking' => $this->snapshot($booking, ['id', 'userId', 'year', 'booking_code', 'lease_from_date', 'lease_from_time', 'lease_to_date', 'lease_to_time', 'property', 'roomType', 'property_data_id', 'floor', 'unit', 'room', 'bed', 'bed_code', 'room_rate']),
                        'users_year' => $user->year,
                    ],
                    'counts' => [
                        'user_files_moved' => $filesMoved,
                        'supplementary_documents_moved' => $supplementaryDocumentsMoved,
                        'action_logs_moved' => $actionsMoved,
                        'lease_tokens_moved' => $leaseTokensMoved,
                        'assurity_tokens_moved' => $assurityTokensMoved,
                        'question_answers_moved' => $questionAnswersMoved,
                        'compatibility_scores_deleted' => $compatibilityScoresDeleted,
                        'users_year_updated' => $userYearUpdated,
                    ],
                ],
            ]);

            $result = [
                'ok' => true,
                'applied' => true,
                'user_id' => $userId,
                'from_year' => $fromYear,
                'to_year' => $toYear,
                'warnings' => $preview['warnings'],
                'blockers' => [],
                'audit_id' => $audit->id,
                'records' => [
                    'students_info' => [
                        'id' => $student->id,
                        'year' => $student->year,
                        'bookingId' => $student->bookingId,
                    ],
                    'booking' => [
                        'id' => $booking->id,
                        'year' => $booking->year,
                        'booking_code' => $booking->booking_code,
                    ],
                    'counts' => [
                        'user_files_moved' => $filesMoved,
                        'supplementary_documents_moved' => $supplementaryDocumentsMoved,
                        'action_logs_moved' => $actionsMoved,
                        'lease_tokens_moved' => $leaseTokensMoved,
                        'assurity_tokens_moved' => $assurityTokensMoved,
                        'question_answers_moved' => $questionAnswersMoved,
                        'compatibility_scores_deleted' => $compatibilityScoresDeleted,
                        'users_year_updated' => $userYearUpdated,
                    ],
                ],
            ];
        });

        return $result;
    }

    protected function normalizeYear($year)
    {
        $normalized = (string) (int) $year;
        return $normalized === '0' ? null : $normalized;
    }

    protected function message($code, $message, array $context = [])
    {
        return [
            'code' => $code,
            'message' => $message,
            'context' => $context,
        ];
    }

    protected function generateBookingCode(Booking $booking, $toYear)
    {
        $prefix = Residence::where('id', $booking->property)->value('building_prefix');
        if (!$prefix) {
            return null;
        }

        return substr((string) $toYear, -2) . $prefix . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
    }

    protected function bedDetails(Booking $booking, $userId, $targetYear)
    {
        $details = [
            'booking_property_data_id' => $booking->property_data_id,
            'booking_floor' => $booking->floor,
            'booking_unit' => $booking->unit,
            'booking_room' => $booking->room,
            'booking_bed' => $booking->bed,
            'booking_bed_code' => $booking->bed_code,
        ];

        if (!$booking->property_data_id) {
            return $details;
        }

        $bed = Property::where('id', $booking->property_data_id)->first();
        if (!$bed) {
            $details['bed_row_found'] = false;
            return $details;
        }

        $yearStart = (int) $bed->year;
        $yearEnd = (int) $bed->year_end;
        $targetYear = (int) $targetYear;
        $validForTarget = $yearStart <= $targetYear && ($yearEnd === 0 || $yearEnd === $targetYear);

        return array_merge($details, [
            'bed_row_found' => true,
            'bed_year' => $bed->year,
            'bed_year_end' => $bed->year_end,
            'bed_user_id_room' => $bed->user_id_room,
            'bed_room_available' => $bed->room_available,
            'valid_for_target_year' => $validForTarget,
            'assigned_to_this_user' => (int) $bed->user_id_room === (int) $userId,
        ]);
    }

    protected function fileSummary($files)
    {
        return $files->map(function ($file) {
            return [
                'id' => $file->id,
                'fileId' => $file->fileId,
                'status' => $file->status,
                'deleted_at' => $file->deleted_at ? (string) $file->deleted_at : null,
            ];
        })->values()->all();
    }

    protected function snapshot($model, array $fields)
    {
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $model->{$field};
        }

        return $data;
    }
}
