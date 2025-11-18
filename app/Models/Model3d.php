<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Model3d extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_id',
        'name',
        'file',
        'thumbnail',
        'description',
        'tags',
    ];

    /**
     * Get the subject that owns the 3D model.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get all questions for this 3D model.
     */
    public function questions()
    {
        return $this->hasMany(ModelQuestion::class, 'model_id')->orderBy('order');
    }

    /**
     * Get all answers for this 3D model.
     */
    public function answers()
    {
        return $this->hasMany(ModelQuestionAnswer::class, 'model_id');
    }
}
