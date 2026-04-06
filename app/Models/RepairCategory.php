<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairCategory extends Model
{
    protected $fillable = ['name'];

    public function subcategories(): HasMany
    {
        return $this->hasMany(RepairSubcategory::class);
    }
}
