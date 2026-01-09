<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\StudentCompatibilityScore;
use App\Models\StudentQuestionAnswer;
use App\Models\Question;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateCompatibilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $students;
    protected $year;
    const MIN_SCORE = 0;

    public function __construct(array $students, $year)
    {
        $this->students = $students;
        $this->year = $year;
    }

    public function handle()
    {
        $questions = Question::where('is_active', 1)->get()->keyBy('id');
        $answers = StudentQuestionAnswer::whereIn('student_id', array_column($this->students, 'id'))
            ->where('academic_year', $this->year)
            ->get()
            ->groupBy('student_id');

        $insertData = [];

        foreach ($this->students as $i => $a) {
            foreach ($this->students as $j => $b) {
                if ($a['id'] >= $b['id']) continue;
                Log::info("Comparing {$a['id']} with {$b['id']}");
                $agender = $a['gender'] ?? null;
                $bgender = $b['gender'] ?? null;
                Log::info("Genders: {$agender}, {$bgender}");

                if (!$agender || !$bgender || $agender !== $bgender) continue;

                $aproperty = $a['booking']['property'] ?? null;
                $bproperty = $b['booking']['property'] ?? null;
                Log::info("Properties: {$aproperty}, {$bproperty}");

                if (!$aproperty || !$bproperty) continue;
                if ($aproperty !== $bproperty) continue;

                $score = $this->calculateWeightScore(
                    $answers[$a['id']] ?? collect(),
                    $answers[$b['id']] ?? collect(),
                    $questions
                );

                if ($score >= self::MIN_SCORE) {
                    $insertData[] = [
                        'student_a_id' => $a['id'],
                        'student_b_id' => $b['id'],
                        'academic_year' => $this->year,
                        'compatibility_score' => $score,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (count($insertData) >= 1000) {
                    StudentCompatibilityScore::insert($insertData);
                    $insertData = [];
                }
            }
        }

        if ($insertData) {
            StudentCompatibilityScore::insert($insertData);
        }
    }

    private function calculateWeightScore($answersA, $answersB, $questions)
    {
        $mapA = $answersA->pluck('question_option_id', 'question_id')->toArray();
        $mapB = $answersB->pluck('question_option_id', 'question_id')->toArray();

        $common = array_intersect(array_keys($mapA), array_keys($mapB));
        if (empty($common)) return 0;

        $total = 0;
        $max = 0;
        foreach ($common as $qid) {
            $q = $questions[$qid] ?? null;
            if (!$q) continue;
            $w = $q->weight ?? 1;
            $max += $w;

            $similarity = ($mapA[$qid] == $mapB[$qid]) ? 1 : 0;
            $total += $w * $similarity;
        }

        return $max ? round(($total / $max) * 100, 2) : 0;
    }
}
