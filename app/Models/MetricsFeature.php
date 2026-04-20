<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetricsFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'enabled',
        'sample_rate',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sample_rate' => 'float',
    ];

    public function captures()
    {
        return $this->hasMany(MetricsCapture::class, 'metrics_feature_id');
    }
}
