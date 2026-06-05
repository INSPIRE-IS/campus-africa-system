<?php

namespace App\Jobs;

use App\Models\Question;
use App\Models\StudentCompatibilityScore;
use App\Models\StudentQuestionAnswer;
use App\Models\StudentsInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateCompatibilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $students;
    protected $year;
    protected $minScore;

    public function __construct(array $students, $year, $minScore = null)
    {
        $this->students = $students;
        $this->year = $year;
        $this->minScore = $minScore ?? (float) config('compatibility.min_score', 30);
    }

    public function handle()
    {
        $chunkStudents = collect($this->students)->keyBy('id');
        if ($chunkStudents->isEmpty()) {
            return;
        }

        $questions = Question::where('is_active', 1)->get()->keyBy('id');
        if ($questions->isEmpty()) {
            return;
        }

        $eligibleStudents = $this->eligibleStudentsQuery()
            ->get()
            ->keyBy('id');

        if ($eligibleStudents->count() < 2) {
            return;
        }

        $answers = StudentQuestionAnswer::whereIn('student_id', $eligibleStudents->keys()->all())
            ->where('academic_year', $this->year)
            ->whereNotNull('question_option_id')
            ->get()
            ->groupBy('student_id');

        $upsertData = [];

        foreach ($chunkStudents as $a) {
            $studentAId = (int) ($a['id'] ?? 0);
            if (!$studentAId || !$eligibleStudents->has($studentAId)) {
                continue;
            }

            foreach ($eligibleStudents as $b) {
                $studentBId = (int) $b->id;
                if ($studentAId >= $studentBId) {
                    continue;
                }

                if (!$this->studentsCanMatch($a, $b->toArray())) {
                    continue;
                }

                $score = $this->calculateWeightScore(
                    $answers->get($studentAId, collect()),
                    $answers->get($studentBId, collect()),
                    $questions
                );

                if ($score < $this->minScore) {
                    continue;
                }

                $now = now();
                $upsertData[] = [
                    'student_a_id' => $studentAId,
                    'student_b_id' => $studentBId,
                    'academic_year' => $this->year,
                    'compatibility_score' => $score,
                    'last_calculated_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($upsertData) >= 1000) {
                    $this->upsertScores($upsertData);
                    $upsertData = [];
                }
            }
        }

        if ($upsertData) {
            $this->upsertScores($upsertData);
        }
    }

    private function eligibleStudentsQuery()
    {
        return StudentsInfo::select('id', 'gender', 'bookingId', 'year', 'rob_60_cancelled')
            ->with(['booking:id,property,roomType,year,cancelled', 'booking.roomType:id,name'])
            ->where('year', $this->year)
            ->whereNotNull('gender')
            ->where(function ($query) {
                $query->whereNull('rob_60_cancelled')
                    ->orWhere('rob_60_cancelled', '!=', 1);
            })
            ->whereIn('id', function ($query) {
                $query->select('student_id')
                    ->from('student_question_answers')
                    ->where('academic_year', $this->year)
                    ->whereNotNull('question_option_id');
            })
            ->whereHas('booking', function ($query) {
                $query->where('year', $this->year)
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

    private function studentsCanMatch(array $studentA, array $studentB)
    {
        $genderA = $studentA['gender'] ?? null;
        $genderB = $studentB['gender'] ?? null;
        if (!$genderA || !$genderB || $genderA !== $genderB) {
            return false;
        }

        $propertyA = $studentA['booking']['property'] ?? null;
        $propertyB = $studentB['booking']['property'] ?? null;
        if (!$propertyA || !$propertyB) {
            return false;
        }

        return (string) $propertyA === (string) $propertyB;
    }

    private function calculateWeightScore($answersA, $answersB, $questions)
    {
        $mapA = $answersA->pluck('question_option_id', 'question_id')->toArray();
        $mapB = $answersB->pluck('question_option_id', 'question_id')->toArray();

        $commonQuestions = array_intersect(array_keys($mapA), array_keys($mapB));
        if (empty($commonQuestions)) {
            return 0;
        }

        $totalScore = 0;
        $maxScore = 0;
        foreach ($commonQuestions as $questionId) {
            $question = $questions[$questionId] ?? null;
            if (!$question) {
                continue;
            }

            $weight = $question->weight ?? 1;
            $maxScore += $weight;

            $similarity = ($mapA[$questionId] == $mapB[$questionId]) ? 1 : 0;
            $totalScore += $weight * $similarity;
        }

        return $maxScore ? round(($totalScore / $maxScore) * 100, 2) : 0;
    }

    private function upsertScores(array $scores)
    {
        StudentCompatibilityScore::upsert(
            $scores,
            ['student_a_id', 'student_b_id', 'academic_year'],
            ['compatibility_score', 'last_calculated_at', 'updated_at']
        );
    }
}
