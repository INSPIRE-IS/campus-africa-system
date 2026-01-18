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
    protected $signature = 'leases:repair-temp-leases {--dry-run}';
    // php artisan leases:repair-temp-leases --dry-run
    protected $description = 'Recreate missing user_files entries for signed temporary leases';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $tokens = UserLeaseToken::where('signed', 1)->get();

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

            UserFiles::updateOrCreate(
                [
                    'fileId' => 'tempMoveIn',
                    'userUid' => $token->userUid,
                    'year' => $token->year,
                ],
                [
                    'fileName' => $fileName,
                    'fileUrl' => $filePath,
                    'status' => 'Uploaded',
                ]
            );

            $this->info("Repaired temp lease for {$userIdentification}");
        }

        $this->info('Repair completed.');
        return Command::SUCCESS;
    }
}
