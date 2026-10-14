<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    protected $table = 'conditions';

    protected $fillable = [
        'id',
        'name',
        'description',
        'price_modifier',
        'created_at',
        'updated_at',
    ];

    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'price_modifier' => 'float',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    protected $casts = [
        'id' => 'integer',
        'price_modifier' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
