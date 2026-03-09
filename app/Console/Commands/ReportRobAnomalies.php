<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Lookups;
use App\Models\RoomTypes;
use App\Models\StudentsInfo;
use App\Models\UserFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Shuchkin\SimpleXLSXGen;

class ReportRobAnomalies extends Command
{
    protected $signature = 'report:rob-anomalies {year?} {--include-cancelled} {--include-not-ready} {--sample=0} {--xlsx} {--email=}';
    // php artisan report:rob-anomalies 2022 --include-cancelled --sample=10
    // php artisan report:rob-anomalies 2026 --xlsx
    // php artisan report:rob-anomalies 2026 --xlsx --email=jason@inspireonline.co.za

    protected $description = 'Report potential ROB anomalies that block approvals (no updates performed).';

    public function handle()
    {
        $year = $this->argument('year');
        if (!$year) {
            $lookup = Lookups::where('group_id', '4')->first();
            $year = $lookup ? $lookup->lookup_name : date('Y');
        }

        $includeCancelled = $this->option('include-cancelled');
        $includeNotReady = $this->option('include-not-ready');
        $sampleLimit = (int) $this->option('sample');
        $writeXlsx = (bool) $this->option('xlsx');
        $email = $this->option('email');

        $counts = [
            'total_students' => 0,
            'checked' => 0,
            'missing_booking' => 0,
            'missing_payment' => 0,
            'rob45_mismatch' => 0,
            'rob10_fee_not_2_when_fee_not_required' => 0,
            'fee_required_pop_missing_or_not_approved' => 0,
            'bed_code_rob40_mismatch' => 0,
            'skipped_not_ready' => 0,
        ];

        $samples = [
            'rob45_mismatch' => [],
            'rob10_fee_not_2_when_fee_not_required' => [],
            'fee_required_pop_missing_or_not_approved' => [],
            'bed_code_rob40_mismatch' => [],
        ];

        $rows = [
            [
                'issue_type',
                'reason',
                'user_uid',
                'booking_id',
                'property_id',
                'property_name',
                'room_type',
                'bed_code',
                'booking_status',
                'student_type',
                'student_name',
                'student_email',
                'rob_field',
                'rob_value',
                'expected_value',
                'booking_fee',
                'booking_fee_waived',
                'rob_b_waived',
                'rob_loan_waived',
                'rob_deposit_waived',
                'proof_of_payment_status',
                'other_docs_count',
                'other_docs_worst_status',
            ],
        ];

        $excludeFileIds = [
            'proof_of_payment',
            'copyID',
            'studentCard',
            'proofRegistration',
            'bursaryApproval',
            'bankStatement',
            'moveIn',
            'tempMoveIn',
            'quote',
            'invoice',
        ];

        $propertyMap = \App\Models\Residence::all()->keyBy('id');
        $roomTypeMap = RoomTypes::all()->keyBy('id');

        StudentsInfo::where('year', $year)
            ->whereNull('deleted_at')
            ->chunk(200, function ($chunk) use (
                $year,
                $includeCancelled,
                $includeNotReady,
                $excludeFileIds,
                &$counts,
                &$samples,
                $sampleLimit,
                &$rows,
                $propertyMap,
                $roomTypeMap
            ) {
                $userIds = $chunk->pluck('userUid')->filter()->unique()->values();
                $bookingIds = $chunk->pluck('bookingId')->filter()->unique()->values();

                $bookings = Booking::whereIn('id', $bookingIds)->get()->keyBy('id');
                $payments = BookingPayment::whereIn('bookingId', $bookingIds)->get()->keyBy('bookingId');
                $files = UserFiles::whereIn('userUid', $userIds)->where('year', $year)->get()->groupBy('userUid');

                foreach ($chunk as $studentInfo) {
                    $counts['total_students']++;

                    $booking = $bookings->get($studentInfo->bookingId);
                    if (!$booking) {
                        $counts['missing_booking']++;
                        if ($includeNotReady) {
                            $this->addIssueRow(
                                $rows,
                                'MISSING_BOOKING',
                                'StudentInfo has no matching booking',
                                $studentInfo,
                                null,
                                null,
                                $propertyMap,
                                $roomTypeMap,
                                null,
                                null,
                                null,
                                null,
                                null,
                                null
                            );
                        } else {
                            $counts['skipped_not_ready']++;
                        }
                        continue;
                    }

                    if (!$includeCancelled) {
                        if ($booking->cancelled == 1 || $studentInfo->rob_60_cancelled == 1) {
                            continue;
                        }
                    }

                    $approvalReady =
                        (string) $studentInfo->rob_15_id === '2' &&
                        (string) $studentInfo->rob_20_card === '2' &&
                        (string) $studentInfo->rob_25_reg === '2' &&
                        (string) $studentInfo->rob_30_funds === '2' &&
                        (string) $studentInfo->rob_35_info === '2' &&
                        (string) $studentInfo->rob_40_bed === '2' &&
                        (string) $studentInfo->rob_50_approve !== '2';

                    $approvalReadyWithoutBed =
                        (string) $studentInfo->rob_15_id === '2' &&
                        (string) $studentInfo->rob_20_card === '2' &&
                        (string) $studentInfo->rob_25_reg === '2' &&
                        (string) $studentInfo->rob_30_funds === '2' &&
                        (string) $studentInfo->rob_35_info === '2' &&
                        (string) $studentInfo->rob_50_approve !== '2' &&
                        ((string) $studentInfo->rob_45_other === '2' || (string) $studentInfo->rob_45_other === '3');

                    if (!$includeNotReady && !$approvalReady && !$approvalReadyWithoutBed) {
                        $counts['skipped_not_ready']++;
                        continue;
                    }

                    $counts['checked']++;

                    $payment = $payments->get($studentInfo->bookingId);
                    if (!$payment) {
                        $counts['missing_payment']++;
                        if ($includeNotReady || $approvalReady) {
                            $this->addIssueRow(
                                $rows,
                                'MISSING_PAYMENT',
                                'No booking_payment row found',
                                $studentInfo,
                                $booking,
                                null,
                                $propertyMap,
                                $roomTypeMap,
                                null,
                                null,
                                null,
                                null,
                                null,
                                null
                            );
                        }
                    }

                    $userFiles = $files->get($studentInfo->userUid, collect());

                    $otherDocs = $userFiles->filter(function ($file) use ($excludeFileIds) {
                        return !in_array($file->fileId, $excludeFileIds, true);
                    });

                    $expectedRob45 = $this->computeExpectedRob45($otherDocs, $payment);
                    if ((string) $studentInfo->rob_45_other !== (string) $expectedRob45) {
                        $counts['rob45_mismatch']++;
                        $this->addIssueRow(
                            $rows,
                            'ROB45_MISMATCH',
                            'Other docs status does not match expected ROB 45 value',
                            $studentInfo,
                            $booking,
                            $payment,
                            $propertyMap,
                            $roomTypeMap,
                            'Other Documents (ROB 45)',
                            $studentInfo->rob_45_other,
                            $expectedRob45,
                            $otherDocs,
                            $userFiles,
                            null
                        );
                        if ($sampleLimit > 0 && count($samples['rob45_mismatch']) < $sampleLimit) {
                            $samples['rob45_mismatch'][] = $studentInfo->userUid;
                        }
                    }

                    $bookingFee = null;
                    if ($payment && $payment->booking_fee !== null) {
                        $bookingFee = $payment->booking_fee;
                    } elseif ($booking->room_rate !== null) {
                        $bookingFee = $booking->room_rate;
                    }
                    $bookingFeeWaived = false;
                    if ($payment) {
                        $bookingFeeWaived = ($payment->booking_fee_waived == '1' || $payment->rob_b_waived == '1');
                    }

                    $requiresBookingFeeDoc = (!$bookingFeeWaived) && ($bookingFee !== null) && ((float) $bookingFee > 0);

                    if (!$requiresBookingFeeDoc && (string) $studentInfo->rob_10_fee !== '2') {
                        $counts['rob10_fee_not_2_when_fee_not_required']++;
                        $this->addIssueRow(
                            $rows,
                            'FEE_NOT_REQUIRED_ROB10_NOT_2',
                            'Booking fee is 0/waived but ROB 10 (fee) is not approved',
                            $studentInfo,
                            $booking,
                            $payment,
                            $propertyMap,
                            $roomTypeMap,
                            'Booking Fee (ROB 10)',
                            $studentInfo->rob_10_fee,
                            2,
                            $otherDocs,
                            $userFiles,
                            $bookingFee
                        );
                        if ($sampleLimit > 0 && count($samples['rob10_fee_not_2_when_fee_not_required']) < $sampleLimit) {
                            $samples['rob10_fee_not_2_when_fee_not_required'][] = $studentInfo->userUid;
                        }
                    }

                    if ($requiresBookingFeeDoc) {
                        $pop = $userFiles->firstWhere('fileId', 'proof_of_payment');
                        $approvedStatuses = ['Approved', 'XApproved'];
                        if (!$pop || !in_array($pop->status, $approvedStatuses, true)) {
                            $counts['fee_required_pop_missing_or_not_approved']++;
                            $this->addIssueRow(
                                $rows,
                                'FEE_REQUIRED_POP_MISSING',
                                'Booking fee is required but proof_of_payment is missing or not approved',
                                $studentInfo,
                                $booking,
                                $payment,
                                $propertyMap,
                                $roomTypeMap,
                                'Proof of Payment (ROB 10)',
                                $pop ? ($pop->status ?? 'unknown') : 'missing',
                                'Approved',
                                $otherDocs,
                                $userFiles,
                                $bookingFee
                            );
                            if ($sampleLimit > 0 && count($samples['fee_required_pop_missing_or_not_approved']) < $sampleLimit) {
                                $samples['fee_required_pop_missing_or_not_approved'][] = $studentInfo->userUid;
                            }
                        }
                    }

                    $bedMismatch = !empty($booking->bed_code) && (string) $studentInfo->rob_40_bed !== '2';
                    if ($bedMismatch && ($includeNotReady || $approvalReadyWithoutBed)) {
                        $counts['bed_code_rob40_mismatch']++;
                        $this->addIssueRow(
                            $rows,
                            'BED_CODE_ROB40_MISMATCH',
                            'Bed code is set but ROB 40 (bed allocation) is not approved',
                            $studentInfo,
                            $booking,
                            $payment,
                            $propertyMap,
                            $roomTypeMap,
                            'Bed Allocation (ROB 40)',
                            $studentInfo->rob_40_bed,
                            2,
                            $otherDocs,
                            $userFiles,
                            $bookingFee
                        );
                        if ($sampleLimit > 0 && count($samples['bed_code_rob40_mismatch']) < $sampleLimit) {
                            $samples['bed_code_rob40_mismatch'][] = $studentInfo->userUid;
                        }
                    }
                }
            });

        $this->info("ROB anomaly report for year {$year}");
        $this->line("Checked (excluding cancelled: " . ($includeCancelled ? 'no' : 'yes') . "): {$counts['checked']}");
        if (!$includeNotReady) {
            $this->line("Skipped (not approval-ready): {$counts['skipped_not_ready']}");
        }
        $this->line("Missing booking: {$counts['missing_booking']}");
        $this->line("Missing booking payment: {$counts['missing_payment']}");
        $this->line("ROB 45 mismatch: {$counts['rob45_mismatch']}");
        $this->line("Fee not required but rob_10_fee != 2: {$counts['rob10_fee_not_2_when_fee_not_required']}");
        $this->line("Fee required but proof_of_payment missing/not approved: {$counts['fee_required_pop_missing_or_not_approved']}");
        $this->line("Bed code set but rob_40_bed != 2: {$counts['bed_code_rob40_mismatch']}");

        if ($sampleLimit > 0) {
            $this->line("");
            $this->line("Sample user UIDs (limit {$sampleLimit}):");
            foreach ($samples as $key => $list) {
                $this->line("- {$key}: " . (count($list) ? implode(', ', $list) : 'none'));
            }
        }

        if ($writeXlsx || $email) {
            Storage::disk('public')->makeDirectory('reports');
            $fileName = 'rob_anomalies_' . $year . '_' . now()->format('Ymd_His') . '.xlsx';
            $filePath = storage_path('app/public/reports/' . $fileName);
            SimpleXLSXGen::fromArray($rows)->saveAs($filePath);
            $this->info("XLSX report saved: {$filePath}");

            if ($email) {
                Mail::raw("ROB anomaly report attached for year {$year}.", function ($message) use ($email, $filePath, $fileName) {
                    $message->to($email)
                        ->subject('ROB anomaly report')
                        ->attach($filePath, ['as' => $fileName]);
                });
                $this->info("Emailed report to: {$email}");
            }
        }

        return 0;
    }

    private function computeExpectedRob45($otherDocs, $payment): int
    {
        $waivers = [
            $payment ? (string) $payment->rob_b_waived : '0',
            $payment ? (string) $payment->rob_loan_waived : '0',
            $payment ? (string) $payment->rob_deposit_waived : '0',
        ];

        $allZero = count(array_filter($waivers, fn($v) => $v !== '0')) === 0;
        $anyOne = count(array_filter($waivers, fn($v) => $v === '1')) > 0;

        if ($otherDocs->count() === 0) {
            if ($allZero) {
                return 3;
            }
            if ($anyOne) {
                return 1;
            }
            return 2;
        }

        $approvedStatuses = ['Approved', 'XApproved'];
        $uploadedStatuses = ['Uploaded', 'AUploaded'];

        $highest = 2;
        foreach ($otherDocs as $file) {
            $status = $file->status;
            if (in_array($status, $approvedStatuses, true)) {
                $value = 2;
            } elseif (in_array($status, $uploadedStatuses, true)) {
                $value = 1;
            } else {
                $value = 0;
            }

            if ($value < $highest) {
                $highest = $value;
            }
        }

        if ($anyOne) {
            return 1;
        }

        return $highest;
    }

    private function addIssueRow(
        array &$rows,
        string $issueType,
        string $reason,
        $studentInfo,
        $booking,
        $payment,
        $propertyMap,
        $roomTypeMap,
        $robField,
        $robValue,
        $expectedValue,
        $otherDocs,
        $userFiles,
        $bookingFee
    ): void {
        $propertyId = $booking ? $booking->property : null;
        $propertyName = $propertyId && isset($propertyMap[$propertyId]) ? $propertyMap[$propertyId]->residence : null;
        $roomTypeName = null;
        if ($booking && $booking->roomType && isset($roomTypeMap[$booking->roomType])) {
            $roomTypeName = $roomTypeMap[$booking->roomType]->name;
        }
        $bedCode = $booking ? $booking->bed_code : null;
        $bookingStatus = $studentInfo->bookingStatus ?? null;

        $fullName = trim(($studentInfo->userFirstName ?? '') . ' ' . ($studentInfo->userLastname ?? ''));
        $email = $studentInfo->userEmail ?? null;

        $pop = $userFiles ? $userFiles->firstWhere('fileId', 'proof_of_payment') : null;
        $popStatus = $pop ? ($pop->status ?? 'unknown') : 'missing';

        $otherCount = $otherDocs ? $otherDocs->count() : 0;
        $otherWorst = $this->summarizeOtherDocsWorst($otherDocs);

        $rows[] = [
            $issueType,
            $reason,
            $studentInfo->userUid ?? null,
            $booking ? $booking->id : null,
            $propertyId,
            $propertyName,
            $roomTypeName,
            $bedCode,
            $bookingStatus,
            $studentInfo->studentType ?? null,
            $fullName,
            $email,
            $robField,
            $robValue,
            $expectedValue,
            $bookingFee,
            $payment ? $payment->booking_fee_waived : null,
            $payment ? $payment->rob_b_waived : null,
            $payment ? $payment->rob_loan_waived : null,
            $payment ? $payment->rob_deposit_waived : null,
            $popStatus,
            $otherCount,
            $otherWorst,
        ];
    }

    private function summarizeOtherDocsWorst($otherDocs): string
    {
        if (!$otherDocs || $otherDocs->count() === 0) {
            return 'none';
        }

        $approvedStatuses = ['Approved', 'XApproved'];
        $uploadedStatuses = ['Uploaded', 'AUploaded'];

        $highest = 2;
        foreach ($otherDocs as $file) {
            $status = $file->status;
            if (in_array($status, $approvedStatuses, true)) {
                $value = 2;
            } elseif (in_array($status, $uploadedStatuses, true)) {
                $value = 1;
            } else {
                $value = 0;
            }

            if ($value < $highest) {
                $highest = $value;
            }
        }

        if ($highest === 2) {
            return 'approved';
        }
        if ($highest === 1) {
            return 'uploaded';
        }
        return 'rejected';
    }
}
