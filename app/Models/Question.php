<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';

    // protected $casts = [
    //     'is_active' => 'boolean',
    //     'allow_partial_match' => 'boolean',
    // ];

    protected $fillable = [
        'category',
        'question_text',
        'input_type',
        'min_value',
        'max_value',
        'step',
        'is_active',
        'weight',
        'allow_partial_match',
    ];


    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }
}
