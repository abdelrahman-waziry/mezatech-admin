<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $hidden = ['password'];
}
