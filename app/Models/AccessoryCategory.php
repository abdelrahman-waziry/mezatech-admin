<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessoryCategory extends Model
{
    protected $fillable = ['name'];

    public function accessories(): HasMany
    {
        return $this->hasMany(Accessory::class);
    }
}
