<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportNSFASStudentAuditLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'import_log_student_list';

    protected $fillable = [
        'importId',
        'student_first_name',
        'student_last_name',
        'student_email',
        'student_number',
        'student_id_number',
        'status',
        'institution',
    ];
}
