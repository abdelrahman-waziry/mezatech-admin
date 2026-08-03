<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticDevice extends Model
{
    protected $guarded = [];

    public function hardwareReports()
    {
        return $this->hasMany(HardwareReport::class);
    }

    public function cosmeticReports()
    {
        return $this->hasMany(CosmeticReport::class);
    }
}
