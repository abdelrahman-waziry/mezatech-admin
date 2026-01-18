<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeInRequest extends Model
{
    protected $table = 'trade_in_requests';

    protected $fillable = [
        'variant_id',
        'trade_in_quote',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'admin_comment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'trade_in_quote' => 'decimal:2',
        'variant_id' => 'integer',
        'status' => 'string',
    ];
}

