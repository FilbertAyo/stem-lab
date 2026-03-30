<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionRegistrationStudent extends Model
{
    use HasFactory, HasPublicUlid;

    protected $fillable = [
        'competition_registration_id',
        'student_id',
        'role',
        'is_representative',
    ];

    protected $casts = [
        'is_representative' => 'boolean',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(CompetitionRegistration::class, 'competition_registration_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
