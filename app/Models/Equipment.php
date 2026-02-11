<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'description',
        'affiliate_url',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'equipment_project')
            ->withPivot(['quantity', 'notes'])
            ->withTimestamps();
    }
}

