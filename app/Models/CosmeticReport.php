<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CosmeticReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'defect_summary' => 'array',
        'image_scores' => 'array',
        'images' => 'array',
        'timestamp' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(DiagnosticDevice::class, 'diagnostic_device_id');
    }
}
