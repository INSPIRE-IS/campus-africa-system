<?php

namespace App\Console\Commands;

use App\Models\ActionList;
use App\Models\Booking;
use App\Models\Lookups;
use App\Models\StudentsInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RestoreBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:restore {userId} {--year=} {--reason=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a cancelled booking/application (does not reassign bed).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('userId');
        $yearOption = $this->option('year');
        $reason = $this->option('reason') ?: 'Restored cancelled booking via artisan command';
        $dryRun = (bool) $this->option('dry-run');

        $lookup = Lookups::where('group_id', '4')->first();
        $defaultYear = $lookup ? $lookup->lookup_name : null;
        $thisYear = $yearOption ?: $defaultYear;

        if (empty($thisYear)) {
            $this->error('No year available (missing lookup group_id=4). Provide --year.');
            return 1;
        }

        $booking = Booking::where('userId', $userId)->where('year', $thisYear)->first();
        if (!$booking) {
            $this->warn("No booking found for userId={$userId} in year {$thisYear}.");
            return 0;
        }

        $this->info("Restoring booking for userId={$userId}, year={$thisYear}.");

        if ($dryRun) {
            $this->info('Dry run: no changes applied.');
            return 0;
        }

        StudentsInfo::where('userUid', $userId)->where('year', $thisYear)->update([
            'rob_60_cancelled' => 0,
        ]);

        Booking::where('userId', $userId)->where('year', $thisYear)->update([
            'cancelled' => null,
        ]);

        ActionList::create([
            'userUid' => $userId,
            'action' => $reason,
            'actionUserUid' => null,
            'actionUserDisplayName' => 'System',
            'actionUserRole' => 'system',
            'year' => $thisYear,
        ]);

        Log::info('booking:restore completed', [
            'userId' => $userId,
            'year' => $thisYear,
        ]);

        $this->info('Done.');
        return 0;
    }
}
