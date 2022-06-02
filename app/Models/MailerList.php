<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailerList extends Model
{
    use HasFactory;

    protected $table = 'mailer_list';

    protected $fillable = [
        'mailer_name',
        'message',
        'status',
        'color',
    ];
}
