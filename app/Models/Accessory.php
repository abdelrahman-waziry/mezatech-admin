<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accessory extends Model
{
    protected $fillable = [
        'accessory_category_id',
        'brand',
        'item_code',
        'name',
        'price',
        'discount',
        'price_after_discount',
        'notes',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->price_after_discount = $model->price * (1 - ($model->discount / 100));
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AccessoryCategory::class, 'accessory_category_id');
    }
}
