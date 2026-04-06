<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairPrice extends Model
{
    protected $fillable = [
        'repair_subcategory_id',
        'product_number',
        'model',
        'price',
        'discount',
        'price_after_discount',
        'warranty',
        'sla',
        'is_etisalat_offer',
        'notes',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->price_after_discount = $model->price * (1 - ($model->discount / 100));
        });
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(RepairSubcategory::class, 'repair_subcategory_id');
    }
}
