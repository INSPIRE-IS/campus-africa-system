<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\UserLeaseToken;
use App\Models\UserFiles;
use App\Models\StudentsInfo;
use App\Models\Booking;
use App\Models\Residence;

class RepairMissingTempLeases extends Command
{

    protected $signature = 'leases:temp-leases {method} {--dry-run}';
    # 1. Inspect only (safe)
    // php artisan leases:temp-leases validate-temp-leases

    # 2. Repair file index (already done, but repeatable)
    // php artisan leases:temp-leases repair-temp-leases --dry-run
    // php artisan leases:temp-leases repair-temp-leases

    # 3. Fix statuses only
    // php artisan leases:temp-leases fix-temp-lease-status --dry-run
    // php artisan leases:temp-leases fix-temp-lease-status
    protected $description = 'Recreate missing user_files entries for signed temporary leases';

    public function handle()
    {
        $method = $this->argument('method');
        $dryRun = $this->option('dry-run');

        $valid = [
            'validate-temp-leases',
            'repair-temp-leases',
            'fix-temp-lease-status',
        ];

        if (!in_array($method, $valid)) {
            $this->error('Invalid method. Use one of:');
            foreach ($valid as $v) {
                $this->line(" - {$v}");
            }
            return Command::FAILURE;
        }

        /////////////// validate-temp-leases
        if ($this->argument('method') == 'validate-temp-leases') {
            $rows = [];

            $students = StudentsInfo::whereNotNull('year')->get();

            foreach ($students as $student) {

                $token = UserLeaseToken::where('userUid', $student->userUid)
                    ->where('lease_type', 'temp_lease')
                    ->where('year', $student->year)
                    ->first();

                $userFile = UserFiles::where('fileId', 'tempMoveIn')
                    ->where('userUid', $student->userUid)
                    ->where('year', $student->year)
                    ->first();

                $expectedState = $this->deriveTempLeaseState($token, $userFile);
                // ---- PDF sanity check (independent) ----
                $fileExists = false;

                if (
                    $token &&
                    $token->signed == 1 &&
                    $token->signature_url
                ) {
                    $userIdentification = $student->userId ?: $student->userPassport;
                    $path = 'public/files/' . $userIdentification . '/' . $token->signature_url;

                    $fileExists = Storage::exists($path);
                }
                // ---- validation conditions ----
                $hasMismatch = $student->rob_51_temp_lease_approve != $expectedState;

                $missingPdf =
                    $expectedState === 2 &&
                    $token &&
                    $token->signed == 1 &&
                    !$fileExists;

                $invalidApproval =
                    $expectedState === 1 &&
                    (!$userFile || !$fileExists);

                if ($hasMismatch || $missingPdf || $invalidApproval) {

                    $rows[] = [
                        'userUid'     => $student->userUid,
                        'year'        => $student->year,
                        'current'     => $student->rob_51_temp_lease_approve,
                        'expected'    => $expectedState,
                        'token'       => $token ? 'yes' : 'no',
                        'pdf_exists'  => $fileExists ? 'yes' : 'no',
                        'user_file'   => $userFile ? $userFile->status : 'no',
                    ];
                }
            }

            if (empty($rows)) {
                $this->info('No inconsistencies found.');
                return Command::SUCCESS;
            }

            $this->table(
                ['User', 'Year', 'Current', 'Expected', 'Token', 'File', 'UserFiles'],
                $rows
            );

            $this->warn('Inconsistencies detected. Run fixer commands to repair.');
        }

        /////////////// repair-temp-leases
        if ($this->argument('method') == 'repair-temp-leases') {

            $tokens = UserLeaseToken::where('signed', 1)
                ->where('active', 0)
                ->whereNotNull('signature_url')
                ->get();

            $this->info('Scanning ' . $tokens->count() . ' signed lease tokens...');

            foreach ($tokens as $token) {

                if (!$token->userUid || !$token->year) {
                    $this->warn("Skipping token {$token->id} (missing user/year)");
                    continue;
                }

                $user = StudentsInfo::where('userUid', $token->userUid)
                    ->where('year', $token->year)
                    ->first();

                if (!$user) {
                    $this->warn("User not found for token {$token->id}");
                    continue;
                }

                $booking = Booking::where('userId', $token->userUid)
                    ->where('year', $token->year)
                    ->first();

                if (!$booking) {
                    $this->warn("Booking not found for user {$token->userUid}");
                    continue;
                }

                // Already exists?
                $exists = UserFiles::where('fileId', 'tempMoveIn')
                    ->where('userUid', $token->userUid)
                    ->where('year', $token->year)
                    ->exists();

                if ($exists) {
                    $this->line("UserFile exists ({$exists->status}) — skipping");
                    continue;
                }

                $userIdentification = $user->userId ?: $user->userPassport;
                $directory = 'public/files/' . $userIdentification;

                if (!Storage::exists($directory)) {
                    $this->warn("Directory missing: {$directory}");
                    continue;
                }

                $files = collect(Storage::files($directory))
                    ->filter(fn($file) => Str::endsWith(strtolower($file), '.pdf'));

                if ($files->isEmpty()) {
                    $this->warn("No PDFs found for {$userIdentification}");
                    continue;
                }

                // Prefer newest file
                $latest = $files->sortByDesc(
                    fn($f) =>
                    Storage::lastModified($f)
                )->first();

                $fileName = basename($latest);
                $filePath = str_replace('public/', '', $latest);

                if ($dryRun) {
                    $this->line("[DRY] Would repair {$fileName}");
                    continue;
                }

                UserFiles::create([
                    'fileId'   => 'tempMoveIn',
                    'userUid'  => $token->userUid,
                    'year'     => $token->year,
                    'fileName' => $fileName,
                    'fileUrl' => $filePath,
                    'status'  => 'Uploaded',
                ]);

                $this->info("Repaired temp lease for {$userIdentification}");
            }

            $this->info('Repair completed.');
            return Command::SUCCESS;
        }

        if ($this->argument('method') == 'fix-temp-lease-status') {
            $students = StudentsInfo::whereNotNull('year')->get();

            foreach ($students as $student) {

                $token = UserLeaseToken::where('userUid', $student->userUid)
                    ->where('year', $student->year)
                    ->first();

                $newState = 3;

                if ($token) {

                    if ($token->active == 0 && $token->signed == 0) {
                        $newState = 0;
                    }

                    if ($token->active == 0 && $token->signed == 1 && $token->signature_url) {

                        $userIdentification = $student->userId ?: $student->userPassport;
                        $path = 'public/files/' . $userIdentification . '/' . $token->signature_url;

                        if (Storage::exists($path)) {

                            $hasUserFile = UserFiles::where('fileId', 'tempMoveIn')
                                ->where('userUid', $student->userUid)
                                ->where('year', $student->year)
                                ->exists();

                            if ($hasUserFile) {
                                $newState = 1;
                            }
                        }
                    }
                }

                if ($student->rob_51_temp_lease_approve != $newState) {

                    if ($this->option('dry-run')) {
                        $this->line("[DRY] {$student->userUid}: {$student->rob_51_temp_lease_approve} → {$newState}");
                    } else {
                        StudentsInfo::where('id', $student->id)
                            ->update(['rob_51_temp_lease_approve' => $newState]);
                    }
                }
            }
            return Command::SUCCESS;
        }
    }

    function deriveTempLeaseState($token, $userFile): int
    {
        if (!$token) {
            return 3;
        }

        if ($token->signed == 0) {
            return 0;
        }

        if (!$userFile) {
            return 2;
        }

        return match ($userFile->status) {
            'Uploaded' => 2,
            'Rejected' => 0,
            'Approved' => 1,
            default => 2,
        };
    }
}
