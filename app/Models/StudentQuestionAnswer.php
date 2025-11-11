<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentQuestionAnswer extends Model
{
    use HasFactory;

    protected $table = 'student_question_answers';

    protected $fillable = [
        'student_id',
        'question_id',
        'question_option_id',
        'numeric_value',
        'academic_year',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
