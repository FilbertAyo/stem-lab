<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the SmileyTutor credential assigned to this user (if any).
     */
    public function smileyTutorCredential()
    {
        return $this->hasOne(SmileyTutorCredential::class);
    }

    /**
     * Get all model question answers for this user.
     */
    public function modelQuestionAnswers()
    {
        return $this->hasMany(ModelQuestionAnswer::class);
    }

    /**
     * Get the clubs that this teacher belongs to (only approved memberships).
     */
    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'teacher_clubs')
            ->wherePivot('status', 'approved')
            ->withPivot(['school_id', 'status', 'is_leader'])
            ->withTimestamps();
    }

    /**
     * Get the teacher-club pivot records.
     */
    public function teacherClubs()
    {
        return $this->hasMany(TeacherClub::class);
    }

    /**
     * Get pending club join requests for this user.
     */
    public function pendingClubRequests()
    {
        return $this->hasMany(TeacherClub::class)->where('status', 'pending');
    }

    /**
     * Get clubs where this user is the leader (through TeacherClub).
     */
    public function leadClubs()
    {
        return $this->hasMany(TeacherClub::class)
            ->where('is_leader', true)
            ->with('club');
    }

    /**
     * Scope a query to only include users with a specific role.
     */
    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include users with model question answers.
     */
    public function scopeWithAnswers(Builder $query): Builder
    {
        return $query->whereHas('modelQuestionAnswers');
    }

    /**
     * Send the password reset notification (branded email).
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }
}
