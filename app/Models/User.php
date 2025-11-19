<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
     * Get all model question answers for this user.
     */
    public function modelQuestionAnswers()
    {
        return $this->hasMany(ModelQuestionAnswer::class);
    }

    /**
     * Get the clubs that this teacher belongs to.
     */
    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'teacher_clubs')
            ->withPivot(['school_id'])
            ->withTimestamps();
    }

    /**
     * Get the teacher-club pivot records.
     */
    public function teacherClubs()
    {
        return $this->hasMany(TeacherClub::class);
    }
}
