<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HardwareReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'summary' => 'array',
        'battery_data' => 'array',
        'display_data' => 'array',
        'components_data' => 'array',
        'timestamp' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(DiagnosticDevice::class, 'diagnostic_device_id');
    }
}
