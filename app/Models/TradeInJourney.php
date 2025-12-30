<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeInJourney extends Model
{
    protected $table = 'trade_in_journeys';

    protected $fillable = [
        'user_id',
        'device_name',
        'device_serial',
        'variant_id',
        'is_functioning',
        'condition_rating',
        'parts_status',
        'survey_payload',
        'estimated_price',
        'currency',
        'pricing_context',
        'status',
        'notes',
        'logged_at',
        'processed_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'processed_at' => 'datetime',
        'parts_status' => 'array',
        'survey_payload' => 'array',
        'pricing_context' => 'array',
        'estimated_price' => 'decimal:2',
        'is_functioning' => 'boolean',
        'condition_rating' => 'integer',
    ];
}

