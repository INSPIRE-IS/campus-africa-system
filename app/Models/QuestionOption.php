<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $table = 'question_options';

    protected $fillable = [
        'question_id',
        'option_text',
        'option_value',
        'weight',
        'order',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
