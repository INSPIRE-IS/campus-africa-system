<?php

namespace App\Console\Commands;

use App\Models\ActionList;
use App\Models\Booking;
use App\Models\Lookups;
use App\Models\Property;
use App\Models\StudentsInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RestoreBookingAssignBed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:restore-assign-bed {userId} {bedCode} {--year=} {--reason=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a cancelled booking and assign a specific bed code.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('userId');
        $bedCode = trim($this->argument('bedCode'));
        $yearOption = $this->option('year');
        $reason = $this->option('reason') ?: 'Restored booking + assigned bed via artisan command';
        $dryRun = (bool) $this->option('dry-run');

        $lookup = Lookups::where('group_id', '4')->where('is_active_year', '1')->first();
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

        $propertyDataAll = Property::where('bed_code', $bedCode)->get();
        $propertyData = null;
        foreach ($propertyDataAll as $pi) {
            $yearEnd = (int) ($pi->year_end ?? 0);
            $yearStart = (int) ($pi->year ?? 0);
            if ($yearEnd === 0) {
                if ((int) $thisYear >= $yearStart) {
                    $propertyData = $pi;
                    break;
                }
            } elseif ((int) $thisYear >= $yearStart && (int) $thisYear <= $yearEnd) {
                $propertyData = $pi;
                break;
            }
        }

        if (!$propertyData) {
            $this->error("Bed code {$bedCode} not found for year {$thisYear}.");
            return 1;
        }

        if (!empty($propertyData->user_id_room) && (int) $propertyData->user_id_room !== $userId) {
            $this->error("Bed code {$bedCode} is already assigned to user {$propertyData->user_id_room}.");
            return 1;
        }

        $this->info("Restoring booking for userId={$userId}, year={$thisYear}, bed_code={$bedCode}.");

        if ($dryRun) {
            $this->info('Dry run: no changes applied.');
            return 0;
        }

        StudentsInfo::where('userUid', $userId)->where('year', $thisYear)->update([
            'rob_60_cancelled' => 0,
            'rob_40_bed' => 2,
        ]);

        Booking::where('userId', $userId)->where('year', $thisYear)->update([
            'cancelled' => null,
            'property' => $propertyData->residence,
            'roomType' => $propertyData->room_type,
            'property_data_id' => $propertyData->id,
            'floor' => $propertyData->floor,
            'unit' => $propertyData->unit,
            'room' => $propertyData->room,
            'bed' => $propertyData->bed,
            'bed_code' => $propertyData->bed_code,
        ]);

        Property::where('id', $propertyData->id)->update([
            'user_id_room' => $userId,
            'room_available' => 1,
        ]);

        ActionList::create([
            'userUid' => $userId,
            'action' => $reason . ' | Bed: ' . $bedCode,
            'actionUserUid' => null,
            'actionUserDisplayName' => 'System',
            'actionUserRole' => 'system',
            'year' => $thisYear,
        ]);

        Log::info('booking:restore-assign-bed completed', [
            'userId' => $userId,
            'year' => $thisYear,
            'bed_code' => $bedCode,
            'property_data_id' => $propertyData->id,
        ]);

        $this->info('Done.');
        return 0;
    }
}
