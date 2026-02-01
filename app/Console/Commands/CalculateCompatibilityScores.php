<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\StudentsInfo;
use App\Models\StudentQuestionAnswer;
use App\Models\StudentCompatibilityScore;
use App\Jobs\CalculateCompatibilityJob;
use Illuminate\Support\Facades\Log;

class CalculateCompatibilityScores extends Command
{
    protected $signature = 'calculate:compatibility {--year=}';
    // php artisan calculate:compatibility --year=2026
    // php artisan route:list | findstr booking_payments
    // php artisan queue:work
    // php artisan schedule:list
    // php artisan schedule:run
    protected $description = 'Calculates compatibility scores between all students based on preference answers';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        echo ini_get('memory_limit');
        $year = $this->option('year') ?? date('Y');
        $batchSize = 500;

        if (true) {

            $this->info("Dispatching compatibility jobs for {$year}...");

            StudentsInfo::select('id', 'gender', 'bookingId')
                ->with('booking:id,property,roomType,year')
                ->where('year', $year)
                ->chunk($batchSize, function ($students) use ($year) {
                    dispatch(new CalculateCompatibilityJob($students->toArray(), $year));
                });

            $this->info('✅ Jobs dispatched successfully.');
        }

        if (false) {
            $min_score = 30;
            $year = $this->option('year') ?? date('Y');
            $this->info("🔄 Calculating compatibility scores for academic year {$year}...");

            // Step 1: Get all students with answers for this year
            $students = StudentsInfo::query()
                ->whereIn('id', function ($q) use ($year) {
                    $q->select('student_id')
                        ->from('student_question_answers')
                        ->where('academic_year', $year)
                        ->whereNotNull('numeric_value');
                })
                ->whereNotNull('gender') // ensure gender present
                ->whereHas('booking', function ($q) use ($year) {
                    $q->where('year', $year)
                        ->whereNotNull('property') // must belong to a residence
                        ->whereHas('booking.roomType', fn($q) => $q->sharedOnly()); // Exclude any "Single" type room
                    // ->whereHas('roomType', function ($q2) {
                    //     $q2->where('name', 'not like', 'Single%'); 
                    // });
                })
                ->with(['booking.roomType'])
                ->get()
                ->map(function ($s) {
                    return (object)[
                        'student_id' => $s->id, // match answer table student_id
                        'gender'     => $s->gender,
                        'propertyId' => $s->booking->propertyId ?? null,
                        'room_type'  => $s->booking->roomType->name ?? null,
                    ];
                });
            $this->info("🔄 Found {$students->count()} students with answers for this year.");
            if ($students->count() < 2) {
                $this->warn('⚠️ Not enough students with answers to calculate compatibility.');
                return;
            }

            // 🔎 Log those excluded for transparency
            $excludedSingles = StudentsInfo::query()
                ->whereHas('booking', function ($q) use ($year) {
                    $q->where('year', $year)
                        ->whereHas('roomType', function ($q2) {
                            $q2->where('name', 'like', 'Single%');
                        });
                })
                ->count();

            if ($excludedSingles > 0) {
                $this->warn("Skipped {$excludedSingles} students in single rooms (not eligible for matching).");
                // Log::info("Skipped {$excludedSingles} single-room students in year {$year}.");
            }

            // Step 2: Clean up existing scores for this year (optional)
            StudentCompatibilityScore::where('academic_year', $year)->delete();
            $questionsCollection = Question::where('is_active', 1)->get();
            $this->info("Found {$questionsCollection->count()} questions with is_active = 1.");

            // Step 3: Compare each student with every other student
            $count = 0;
            foreach ($students as $i => $studentA) {
                foreach ($students as $j => $studentB) {
                    if ($studentA->student_id  >= $studentB->student_id) continue; // avoid duplicates/self

                    //$score = $this->calculatePairScore($studentA->id, $studentB->id, $year);

                    // ✅ Must be same residence
                    if ($studentA->propertyId !== $studentB->propertyId) continue;

                    // ✅ Must match gender rules:
                    // If you want same-gender only:
                    if ($studentA->gender !== $studentB->gender) continue;

                    // Now calculate score
                    $score = $this->calculateWeightBasedScore(
                        $studentA->student_id,
                        $studentB->student_id,
                        $year,
                        $questionsCollection
                    );
                    if ($score < $min_score) continue; // skip low matches

                    StudentCompatibilityScore::updateOrCreate(
                        [
                            'student_a_id' => $studentA->student_id,
                            'student_b_id' => $studentB->student_id,
                            'academic_year' => $year,
                        ],
                        ['compatibility_score' => $score]
                    );

                    $count++;
                }
            }
            $this->info("✅ Compatibility scores calculated for {$count} student pairs.");
        }
    }

    /**
     * Calculate compatibility between two students
     */
    protected function calculatePairScore($studentAId, $studentBId, $year)
    {
        $answersA = StudentQuestionAnswer::where('student_id', $studentAId)
            ->where('academic_year', $year)
            ->pluck('question_option_id', 'question_id')
            ->toArray();

        $answersB = StudentQuestionAnswer::where('student_id', $studentBId)
            ->where('academic_year', $year)
            ->pluck('question_option_id', 'question_id')
            ->toArray();

        if (empty($answersA) || empty($answersB)) {
            return 0;
        }

        // Get all question IDs both answered
        $commonQuestions = array_intersect(array_keys($answersA), array_keys($answersB));
        if (empty($commonQuestions)) {
            return 0;
        }

        $matches = 0;
        foreach ($commonQuestions as $qid) {
            if ($answersA[$qid] == $answersB[$qid]) {
                $matches++;
            }
        }

        $similarity = ($matches / count($commonQuestions)) * 100;
        return round($similarity, 2);
    }

    protected function calculateWeightBasedScore($studentAId, $studentBId, $year, $questionsCollection)
    {
        // Get answers
        $answersA = StudentQuestionAnswer::where('student_id', $studentAId)
            ->where('academic_year', $year)
            ->pluck('question_option_id', 'question_id')
            ->toArray();

        $answersB = StudentQuestionAnswer::where('student_id', $studentBId)
            ->where('academic_year', $year)
            ->pluck('question_option_id', 'question_id')
            ->toArray();

        if (empty($answersA) || empty($answersB)) {
            return 0;
        }

        // Questions both students answered
        $commonQuestions = array_intersect(array_keys($answersA), array_keys($answersB));
        if (empty($commonQuestions)) {
            return 0;
        }

        $totalScore = 0;
        $maxScore = 0;

        foreach ($commonQuestions as $qid) {
            $question = $questionsCollection->firstWhere("id", $qid);
            $this->info("Calculating weight based score for question {$qid}");
            $weight = $question->weight ?? 1; // default 1 if no weight
            $maxScore += $weight;

            $answerA = $answersA[$qid];
            $answerB = $answersB[$qid];

            $similarity = 0;
            $this->info("Calculating partial match for question {$qid}");
            // $this->info("allow_partial_match", [$question]);
            // Exact match
            if ($answerA == $answerB) {
                $similarity = 1;
            }
            // Partial match allowed

            // elseif ($question->allow_partial_match) {
            //     $diff = abs($answerA - $answerB);
            //     if ($diff == 1) $similarity = 0.8;
            //     elseif ($diff == 2) $similarity = 0.5;
            // }

            $totalScore += $weight * $similarity;
        }

        return round(($totalScore / $maxScore) * 100, 2);
    }
}
