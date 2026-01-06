<?php

namespace App\Models;

use App\Enums\Analytics\AppSource;
use App\Enums\Analytics\ErrorType;
use App\Enums\Analytics\Method;
use App\Enums\Analytics\Network;
use Illuminate\Database\Eloquent\Model;

class AnalyticsRequest extends Model
{
    public $timestamps = false; // We manually set created_at or let DB do it, but we don't need updated_at

    protected $fillable = [
        'request_id',
        'endpoint',
        'method',
        'status',
        'duration_ms',
        'error_type',
        'app_source',
        'app_version',
        'device_os',
        'device_model',
        'device_network',
        'created_at',
    ];

    protected $casts = [
        'method' => Method::class,
        'app_source' => AppSource::class,
        'device_network' => Network::class,
        'error_type' => ErrorType::class,
        'created_at' => 'datetime',
    ];
}
