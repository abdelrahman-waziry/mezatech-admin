<?php

namespace App\Models;

use App\Enums\Analytics\Condition;
use App\Enums\Analytics\EventName;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public $timestamps = false; 

    protected $fillable = [
        'event_name',
        'user_id',
        'brand',
        'model',
        'condition',
        'quoted_price',
        'country',
        'city',
        'area',
        'district',
        'device_brand',
        'device_model',
        'device_os_version',
        'created_at',
    ];

    protected $casts = [
        'event_name' => EventName::class,
        'condition' => Condition::class,
        'quoted_price' => 'decimal:2',
        'created_at' => 'datetime',
    ];
}
