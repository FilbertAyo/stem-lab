<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'district',
        'region',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
