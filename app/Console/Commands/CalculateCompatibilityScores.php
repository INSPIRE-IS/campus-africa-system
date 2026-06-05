<?php

namespace App\Console\Commands;

use App\Jobs\CalculateCompatibilityJob;
use App\Models\StudentCompatibilityScore;
use App\Models\StudentsInfo;
use Illuminate\Console\Command;

class CalculateCompatibilityScores extends Command
{
    protected $signature = 'calculate:compatibility {--year=}';
    protected $description = 'Calculates compatibility scores between all students based on preference answers';

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $year = $this->option('year') ?? date('Y');
        $batchSize = (int) config('compatibility.batch_size', 500);
        $minScore = (float) config('compatibility.min_score', 30);

        $this->info("Dispatching compatibility jobs for {$year} with minimum score {$minScore}...");

        $deleted = StudentCompatibilityScore::where('academic_year', $year)->delete();
        $this->info("Cleared {$deleted} existing compatibility scores for {$year}.");

        $eligibleCount = $this->eligibleStudentsQuery($year)->count();
        if ($eligibleCount < 2) {
            $this->warn('Not enough eligible students with answers to calculate compatibility.');
            return;
        }

        $jobs = 0;
        $this->eligibleStudentsQuery($year)
            ->chunkById($batchSize, function ($students) use ($year, $minScore, &$jobs) {
                dispatch(new CalculateCompatibilityJob($students->toArray(), $year, $minScore));
                $jobs++;
            });

        $this->info("Dispatched {$jobs} compatibility jobs for {$eligibleCount} eligible students.");
    }

    private function eligibleStudentsQuery($year)
    {
        return StudentsInfo::select('id', 'gender', 'bookingId', 'year', 'rob_60_cancelled')
            ->with(['booking:id,property,roomType,year,cancelled', 'booking.roomType:id,name'])
            ->where('year', $year)
            ->whereNotNull('gender')
            ->where(function ($query) {
                $query->whereNull('rob_60_cancelled')
                    ->orWhere('rob_60_cancelled', '!=', 1);
            })
            ->whereIn('id', function ($query) use ($year) {
                $query->select('student_id')
                    ->from('student_question_answers')
                    ->where('academic_year', $year)
                    ->whereNotNull('question_option_id');
            })
            ->whereHas('booking', function ($query) use ($year) {
                $query->where('year', $year)
                    ->whereNotNull('property')
                    ->where(function ($bookingQuery) {
                        $bookingQuery->whereNull('cancelled')
                            ->orWhere('cancelled', '!=', 1);
                    })
                    ->whereHas('roomType', function ($roomTypeQuery) {
                        $roomTypeQuery->sharedOnly();
                    });
            })
            ->orderBy('id');
    }
}
