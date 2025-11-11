<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\StudentQuestionAnswer;
use App\Models\StudentsInfo;
use Illuminate\Console\Command;

class PreferenceSeeder extends Command
{
    protected $signature = 'seed:lifestyle-questions';
    // php artisan seed:lifestyle-questions
    protected $description = 'Seeds or updates lifestyle preference questions and options';

    public function handle()
    {
        $questions = [
            [
                'category' => 'Noise Preferences',
                'question_text' => 'Preferred noise level where you live?',
                'options' => [
                    ['option_text' => 'Very quiet', 'option_value' => 1],
                    ['option_text' => 'Moderate noise', 'option_value' => 2],
                    ['option_text' => 'Lively and social', 'option_value' => 3],
                    ['option_text' => 'I can adjust easily', 'option_value' => 4],
                ],
                'weight' => 1.0,
                'allow_partial_match' => false,
            ],
            [
                'category' => 'Beliefs',
                'question_text' => 'How do your beliefs guide you?',
                'options' => [
                    ['option_text' => 'Strongly faith-based', 'option_value' => 1],
                    ['option_text' => 'Somewhat spiritual/open-minded', 'option_value' => 2],
                    ['option_text' => 'Not religious but respectful', 'option_value' => 3],
                    ['option_text' => 'Prefer not to say', 'option_value' => 4],
                ],
                'weight' => 1.0,
                'allow_partial_match' => false,
            ],
            [
                'category' => 'Daily Routine',
                'question_text' => 'What’s your usual schedule?',
                'options' => [
                    ['option_text' => 'Early bird', 'option_value' => 1],
                    ['option_text' => 'Night owl', 'option_value' => 2],
                    ['option_text' => 'Flexible', 'option_value' => 3],
                    ['option_text' => 'No fixed routine', 'option_value' => 4],
                ],
                'weight' => 1.0,
                'allow_partial_match' => false,
            ],
            [
                'category' => 'Roommate Relationship',
                'question_text' => 'How do you prefer to relate to your roommate?',
                'options' => [
                    ['option_text' => 'Keep things private', 'option_value' => 1],
                    ['option_text' => 'Friendly but independent', 'option_value' => 2],
                    ['option_text' => 'Close friends', 'option_value' => 3],
                    ['option_text' => 'Open to anything', 'option_value' => 4],
                ],
                'weight' => 1.0,
                'allow_partial_match' => false,
            ],
        ];
        // this seeds the questions and options probably a one time thing
        if (false) {
            foreach ($questions as $qData) {
                $question = Question::updateOrCreate(
                    ['question_text' => $qData['question_text']],
                    ['category' => $qData['category']]
                );

                foreach ($qData['options'] as $opt) {
                    QuestionOption::updateOrCreate(
                        ['question_id' => $question->id, 'option_text' => $opt['option_text']],
                        ['option_value' => $opt['option_value']]
                    );
                }
            }
        }
        // we never want to run this seeder on live
        if (false) {
            $this->info('✅ Lifestyle questions and options seeded successfully.');
            $year = 2026;
            $questions = Question::whereNotNull("is_active")->pluck("id");

            $students = StudentsInfo::query()
                ->whereHas("booking", fn($q) => $q->where("year", $year))
                ->get();
            foreach ($students as $student) {
                foreach ($questions as $questionId) {
                    $random = rand(1, 4);
                    $questionOption =  QuestionOption::where("question_id", $questionId)->where("option_value", $random)->first();
                    $this->info($questionOption->id);
                    $this->info("Seeding answer for {$student->userUid} for question {$questionId} with option {$random}");
                    StudentQuestionAnswer::updateOrCreate(
                        [
                            "student_id" => $student->id,
                            "question_id" => $questionId,
                            "academic_year" => $year
                        ],
                        [
                            "question_option_id" => $questionOption->id,
                            "numeric_value" => $random, // <-- random scale 1–5
                            "text_value" => null
                        ]
                    );
                }
            }
        }
    }
}
