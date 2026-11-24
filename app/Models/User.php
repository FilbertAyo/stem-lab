<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasPublicShortId;
use App\Models\Concerns\HasPublicUlid;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasPublicShortId {
        HasPublicShortId::getRouteKeyName insteadof HasPublicUlid;
    }
    use HasPublicUlid;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'login_id',
        'uses_pin',
        'phone',
        'date_of_birth',
        'gender',
        'school_class_id',
        'student_onboarding_completed_at',
        'role',
        'subscription_tier',
        'club_linked_at',
        'subscription_expires_at',
        'trial_used_at',
        'subscription_renewal_reminded_at',
        'password',
        'is_active',
        'admin_alert_categories',
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
            'date_of_birth' => 'date',
            'student_onboarding_completed_at' => 'datetime',
            'club_linked_at' => 'datetime',
            'subscription_expires_at' => 'datetime',
            'trial_used_at' => 'datetime',
            'subscription_renewal_reminded_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'uses_pin' => 'boolean',
            'admin_alert_categories' => 'array',
        ];
    }

    /**
     * Alert category keys this admin should receive.
     * Null/empty means every category (safe rollout for existing admins).
     *
     * @return list<string>
     */
    public function adminAlertCategories(): array
    {
        $categories = $this->admin_alert_categories;

        if (! is_array($categories) || $categories === []) {
            return array_keys(config('admin-alerts.categories', []));
        }

        $allowed = array_keys(config('admin-alerts.categories', []));

        return array_values(array_intersect(
            array_map('strval', $categories),
            $allowed,
        ));
    }

    public function receivesAdminAlert(string $category): bool
    {
        if ($this->role !== 'admin' || ! $this->is_active) {
            return false;
        }

        return in_array($category, $this->adminAlertCategories(), true);
    }

    public function usesPinLogin(): bool
    {
        return $this->uses_pin === true;
    }

    public function hasEmailLogin(): bool
    {
        return filled($this->email) && ! $this->usesPinLogin();
    }

    public function linkedRosterStudent()
    {
        return $this->hasOne(Student::class);
    }

    private ?bool $clubLinkedMemo = null;

    private ?bool $approvedClubMembershipMemo = null;

    public function isClubLinked(): bool
    {
        return $this->clubLinkedMemo ??= (
            $this->club_linked_at !== null
            && $this->linkedRosterStudent()->exists()
        );
    }

    public function hasPublicSchoolAccess(): bool
    {
        return $this->subscription_tier === 'public_school' && $this->isClubLinked();
    }

    public function hasApprovedClubMembership(): bool
    {
        if ($this->relationLoaded('teacherClubs')) {
            return $this->teacherClubs->contains(fn (TeacherClub $membership) => $membership->status === 'approved');
        }

        return $this->approvedClubMembershipMemo ??= $this->teacherClubs()->where('status', 'approved')->exists();
    }

    /**
     * Subsidised access via a linked STEM club (students) or approved club membership (teachers).
     */
    public function hasSubsidizedSchoolAccess(): bool
    {
        if ($this->hasPublicSchoolAccess()) {
            return true;
        }

        return $this->role === 'teacher' && $this->hasApprovedClubMembership();
    }

    public function hasActivePrepaidAccess(): bool
    {
        if (! in_array($this->subscription_tier, ['trial', 'individual', 'pro'], true)) {
            return false;
        }

        return $this->subscription_expires_at !== null
            && $this->subscription_expires_at->isFuture();
    }

    public function hasPlatformAccess(): bool
    {
        if ($this->hasSubsidizedSchoolAccess()) {
            return true;
        }

        return $this->hasActivePrepaidAccess();
    }

    public function platformPlanLabel(): string
    {
        if ($this->hasSubsidizedSchoolAccess()) {
            return 'Free';
        }

        if ($this->hasActivePrepaidAccess() && in_array($this->subscription_tier, ['individual', 'pro'], true)) {
            return 'Pro';
        }

        if ($this->hasActivePrepaidAccess() && $this->subscription_tier === 'trial') {
            return 'Trial';
        }

        return 'No plan';
    }

    public function platformPlanBadgeClass(): string
    {
        if ($this->hasSubsidizedSchoolAccess()) {
            return 'bg-success-subtle text-success-emphasis border border-success-subtle';
        }

        if ($this->hasActivePrepaidAccess() && in_array($this->subscription_tier, ['individual', 'pro'], true)) {
            return 'bg-dark text-white';
        }

        if ($this->hasActivePrepaidAccess() && $this->subscription_tier === 'trial') {
            return 'bg-light text-dark border';
        }

        return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    }

    /**
     * Whether the user already has subsidised access and should not change packages.
     */
    public function hasLockedSubscriptionPlan(): bool
    {
        return $this->hasSubsidizedSchoolAccess();
    }

    public function activeSubscriptionPackageSlug(): ?string
    {
        if ($this->hasPublicSchoolAccess()) {
            return 'public-school';
        }

        if ($this->role === 'teacher' && $this->hasApprovedClubMembership()) {
            return 'public-school';
        }

        if (! $this->hasActivePrepaidAccess() && ! $this->hasSubsidizedSchoolAccess()) {
            return null;
        }

        return match ($this->subscription_tier) {
            'trial' => 'free-trial',
            'individual', 'pro' => 'individual',
            'public_school' => 'public-school',
            default => null,
        };
    }

    public function subscriptionOrders()
    {
        return $this->hasMany(SubscriptionOrder::class);
    }

    /**
     * School name(s) linked via approved teacher club or student roster membership.
     */
    public function resolvedSchoolLabel(): string
    {
        if ($this->role === 'teacher') {
            $names = $this->relationLoaded('teacherClubs')
                ? $this->teacherClubs
                : $this->teacherClubs()->with('school')->get();

            $labels = $names
                ->where('status', 'approved')
                ->map(fn (TeacherClub $membership) => $membership->school?->name)
                ->filter()
                ->unique()
                ->values();

            return $labels->isEmpty() ? '—' : $labels->implode(', ');
        }

        if ($this->role === 'student') {
            $roster = $this->relationLoaded('linkedRosterStudent')
                ? $this->linkedRosterStudent
                : $this->linkedRosterStudent()->with('teacherClub.school')->first();

            return $roster?->teacherClub?->school?->name ?: '—';
        }

        return '—';
    }

    /**
     * Short plan label for admin list tables.
     */
    public function adminPlanLabel(): string
    {
        if ($this->hasSubsidizedSchoolAccess()) {
            return 'Free (public school)';
        }

        if ($this->hasActivePrepaidAccess()) {
            return match ($this->subscription_tier) {
                'trial' => 'Trial',
                'individual', 'pro' => 'Individual',
                default => $this->platformPlanLabel(),
            };
        }

        if (in_array($this->subscription_tier, ['trial', 'individual', 'pro'], true)
            && $this->subscription_expires_at
            && $this->subscription_expires_at->isPast()
        ) {
            return 'Expired';
        }

        return 'No plan';
    }

    public function hasActiveSubscriptionPackage(string $slug): bool
    {
        return $this->activeSubscriptionPackageSlug() === $slug;
    }

    /**
     * Get the school class associated with this user (student accounts).
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * The class used to personalize STEM resources for this student.
     */
    public function resolvedSchoolClassId(): ?int
    {
        if ($this->role !== 'student') {
            return null;
        }

        if ($this->school_class_id !== null) {
            return (int) $this->school_class_id;
        }

        $linkedClassId = $this->relationLoaded('linkedRosterStudent')
            ? $this->linkedRosterStudent?->school_class_id
            : $this->linkedRosterStudent()->value('school_class_id');

        return $linkedClassId !== null ? (int) $linkedClassId : null;
    }

    public function shouldFilterContentByClass(): bool
    {
        return $this->role === 'student' && $this->resolvedSchoolClassId() !== null;
    }

    /**
     * Whether a student must complete gender and date of birth before using the app.
     */
    public function needsStudentOnboarding(): bool
    {
        return $this->role === 'student'
            && (blank($this->gender) || blank($this->date_of_birth));
    }

    /**
     * Current onboarding step (1 = gender, 2 = date of birth, 3 = class).
     */
    public function studentOnboardingStep(): int
    {
        if ($this->role !== 'student') {
            return 0;
        }

        if (blank($this->gender)) {
            return 1;
        }

        if (blank($this->date_of_birth)) {
            return 2;
        }

        return 3;
    }

    /**
     * Mark optional class step as complete.
     */
    public function completeStudentOnboarding(): void
    {
        $this->forceFill([
            'student_onboarding_completed_at' => now(),
        ])->save();
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
     * All approved teacher_clubs.id values for the same club + school as any of this user's approved memberships.
     * Used so every approved teacher at that school sees and can manage the shared student roster.
     */
    public function approvedCoMemberTeacherClubIds(): Collection
    {
        $memberships = $this->teacherClubs()
            ->where('status', 'approved')
            ->get(['club_id', 'school_id']);

        if ($memberships->isEmpty()) {
            return collect();
        }

        return TeacherClub::query()
            ->where('status', 'approved')
            ->where(function ($query) use ($memberships): void {
                foreach ($memberships as $membership) {
                    $query->orWhere(function ($inner) use ($membership): void {
                        $inner->where('club_id', $membership->club_id);

                        if ($membership->school_id === null) {
                            $inner->whereNull('school_id');
                        } else {
                            $inner->where('school_id', $membership->school_id);
                        }
                    });
                }
            })
            ->pluck('id')
            ->unique()
            ->values();
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
