<?php

namespace App\Console\Commands;

use App\Models\ActionList;
use App\Models\Booking;
use App\Models\Lookups;
use App\Models\Property;
use App\Models\StudentsInfo;
use App\Models\UserFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UnallocateBed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:unallocate-bed {userId} {--year=} {--reason=} {--dry-run}';
    // php artisan booking:unallocate-bed 19501 --year=2026 --reason="test" --dry-run
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unallocate a bed for a student (clears booking bed fields and frees the property room).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('userId');
        $yearOption = $this->option('year');
        $reason = $this->option('reason') ?: 'Unallocated bed via artisan command';
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

        $bedCode = $booking->bed_code ?: 'n/a';
        $propertyDataId = $booking->property_data_id;

        $this->info("Unallocating bed for userId={$userId}, year={$thisYear}, bed_code={$bedCode}.");

        if ($dryRun) {
            $this->info('Dry run: no changes applied.');
            return 0;
        }

        Booking::where('userId', $userId)->where('year', $thisYear)->update([
            'property_data_id' => null,
            'floor' => null,
            'unit' => null,
            'room' => null,
            'bed' => null,
            'bed_code' => null,
        ]);

        if (!empty($propertyDataId)) {
            Property::where('id', $propertyDataId)->update([
                'user_id_room' => null,
                'room_available' => null,
            ]);
        }

        StudentsInfo::where('userUid', $userId)->where('year', $thisYear)->update([
            'rob_40_bed' => 1,
            'rob_50_approve' => 0,
        ]);

        UserFiles::where('userUid', $userId)->where('fileId', 'moveIn')->where('year', $thisYear)->delete();

        ActionList::create([
            'userUid' => $userId,
            'action' => $reason,
            'actionUserUid' => null,
            'actionUserDisplayName' => 'System',
            'actionUserRole' => 'system',
            'year' => $thisYear,
        ]);

        Log::info('booking:unallocate-bed completed', [
            'userId' => $userId,
            'year' => $thisYear,
            'bed_code' => $bedCode,
            'property_data_id' => $propertyDataId,
        ]);

        $this->info('Done.');
        return 0;
    }
}
