<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportNSFASApproval extends Model
{
    use HasFactory;

    protected $table = 'import_audit_log';

    protected $fillable = [
        'approved',
        'skipped',
        'not_found',
        'file_name',
        'actionUserId',
        'total_records',
        'status',
        'matched',
        'unmatched',
    ];

    public function users(){
        return $this->hasOne("App\Models\User", "id", "actionUserId");
    }
}
