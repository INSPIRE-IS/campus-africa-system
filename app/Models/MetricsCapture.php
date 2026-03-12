<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetricsCapture extends Model
{
    use HasFactory;

    protected $fillable = [
        'metrics_feature_id',
        'bucket',
        'bucket_start',
        'key',
        'count',
        'sum',
        'min',
        'max',
        'meta',
    ];

    protected $casts = [
        'bucket_start' => 'datetime',
        'meta' => 'array',
    ];

    public function feature()
    {
        return $this->belongsTo(MetricsFeature::class, 'metrics_feature_id');
    }
}
