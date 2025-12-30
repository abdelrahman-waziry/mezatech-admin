<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequest extends Model
{
    protected $table = 'api_requests';

    protected $fillable = [
        'method',
        'endpoint',
        'status_code',
        'error_type',
        'response_time_ms',
        'recorded_at',
    ];

    protected $casts = [
        'response_time_ms' => 'integer',
        'recorded_at' => 'datetime',
    ];
}

