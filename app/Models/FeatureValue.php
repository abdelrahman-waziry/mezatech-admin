<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureValue extends Model
{
    protected $table = 'feature_values';

    protected $fillable = [
        'feature_id',
        'value',
        'image',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
