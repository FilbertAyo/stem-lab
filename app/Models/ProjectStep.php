<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'step_number',
        'title',
        'description',
        'image',
        'code_snippet',
    ];

    protected $casts = [
        'step_number' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
