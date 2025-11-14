<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCompatibilityScore extends Model
{
    use HasFactory;

    protected $table = 'student_compatibility_scores';

    protected $fillable = [
        'student_a_id',
        'student_b_id',
        'compatibility_score',
        'academic_year',
        'last_calculated_at',
    ];


    public function studentA()
    {
        return $this->belongsTo(\App\Models\StudentsInfo::class, 'student_a_id');
    }

    public function studentB()
    {
        return $this->belongsTo(\App\Models\StudentsInfo::class, 'student_b_id');
    }
}
