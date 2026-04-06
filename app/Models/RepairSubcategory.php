<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairSubcategory extends Model
{
    protected $fillable = ['repair_category_id', 'name'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RepairCategory::class, 'repair_category_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RepairPrice::class);
    }
}
