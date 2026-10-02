<?php

namespace App\Services;

use App\Mail\AdminActivityMail;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\TeacherClub;
use App\Models\User;
use App\Models\VrAssessmentAttempt;
use App\Support\AdminUrl;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AdminActivityNotifier
{
    /**
     * @param  string|list<string>  $categories
     * @return list<string>
     */
    public function recipientEmails(string|array $categories = []): array
    {
        $categoryList = array_values(array_unique(array_filter(
            is_array($categories) ? $categories : [$categories],
            fn ($category) => is_string($category) && $category !== '',
        )));

        $admins = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get(['id', 'email', 'role', 'is_active', 'admin_alert_categories']);

        if ($categoryList !== []) {
            $admins = $admins->filter(function (User $admin) use ($categoryList): bool {
                foreach ($categoryList as $category) {
                    if ($admin->receivesAdminAlert($category)) {
                        return true;
                    }
                }

                return false;
            });
        }

        $support = config('mail.admin_alert_address');

        return $admins
            ->pluck('email')
            ->push($support)
            ->filter(fn ($email) => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->map(fn (string $email) => strtolower(trim($email)))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  string|list<string>  $categories
     */
    public function deliver(Mailable $mail, string|array $categories = []): void
    {
        $emails = $this->recipientEmails($categories);

        if ($emails === []) {
            throw new \RuntimeException('No admin recipients are configured for activity email.');
        }

        Mail::to($emails)->send($mail);
    }

    /**
     * @param  list<array{label: string, value: string}>  $details
     * @param  string|list<string>  $categories
     */
    public function alert(
        string $subject,
        string $headline,
        string $summary,
        array $details,
        ?string $actionUrl = null,
        string $actionLabel = 'Open in admin',
        ?string $nextStep = null,
        string|array $categories = [],
    ): void {
        try {
            $this->deliver(new AdminActivityMail(
                $subject,
                $headline,
                $summary,
                $details,
                $actionUrl,
                $actionLabel,
                $nextStep,
            ), $categories);
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function newUser(User $user): void
    {
        if ($user->role === 'admin' || blank($user->email)) {
            return;
        }

        $role = ucfirst((string) $user->role);
        $actionUrl = match ($user->role) {
            'teacher' => AdminUrl::route('admin.users.teachers', ['q' => $user->email]),
            'student' => AdminUrl::route('admin.users.students', ['q' => $user->email]),
            default => AdminUrl::route('admin.users.teachers'),
        };

        $this->alert(
            subject: "New {$role} account: {$user->name}",
            headline: 'New user registered',
            summary: "{$user->name} created a {$role} account. Follow up if they still need a school, club, or competition.",
            details: $this->rows([
                'Name' => $user->name,
                'Role' => $role,
                'Email' => $user->email,
                'Phone' => $user->phone,
            ]),
            actionUrl: $actionUrl,
            actionLabel: 'View in admin',
            nextStep: 'Confirm the account is expected and that they can reach their school or competition.',
            categories: 'needs_attention',
        );
    }

    public function teacherJoinedClub(User $user, TeacherClub $teacherClub, bool $isNewSchool, ?Competition $competition = null): void
    {
        $teacherClub->loadMissing(['user', 'club', 'school.regionModel', 'school.districtModel']);

        $school = $teacherClub->school;
        $clubName = $teacherClub->club?->name ?? 'a club';
        $schoolName = $school?->name ?? 'Unknown school';
        $teacherName = $user->name;
        $status = ucfirst((string) ($teacherClub->status ?: 'pending'));
        $role = $teacherClub->is_leader ? 'Leader' : 'Member';

        $location = collect([
            $school?->districtName(),
            $school?->ward,
            $school?->regionModel?->name,
        ])->filter()->implode(', ');

        $details = $this->rows([
            'Teacher' => $teacherName,
            'Email' => $user->email,
            'Phone' => $user->phone,
            'School' => $schoolName,
            'Location' => $location !== '' ? $location : null,
            'Club' => $clubName,
            'Role' => $role,
            'Membership' => $status,
            'Competition' => $competition?->name,
        ]);

        $schoolUrl = $school
            ? AdminUrl::route('admin.schools.show', $school)
            : null;

        if ($isNewSchool) {
            $competitionNote = $competition
                ? " They also joined {$competition->name}."
                : '';

            $this->alert(
                subject: "Action needed: review new school {$schoolName}",
                headline: 'New school needs review',
                summary: "{$teacherName} registered {$schoolName} and joined {$clubName}.{$competitionNote} The school is waiting for an admin to check it.",
                details: $details,
                actionUrl: $schoolUrl,
                actionLabel: 'Review school',
                nextStep: 'Open the school, confirm the name, location, and contact details, then mark it as reviewed.',
                categories: ['needs_attention', 'clubs'],
            );

            return;
        }

        if ($competition) {
            $this->alert(
                subject: "{$teacherName} joined {$competition->name}",
                headline: 'Teacher joined a competition',
                summary: "{$teacherName} from {$schoolName} joined {$competition->name} through {$clubName}. Membership is {$status}.",
                details: $details,
                actionUrl: $schoolUrl,
                actionLabel: 'Open school',
                nextStep: $teacherClub->status === 'pending'
                    ? 'The request is still pending the school leader. Check back if it stays pending, and watch for the competition registration.'
                    : 'Check the school roster and watch for the competition registration so the team is not left waiting.',
                categories: ['competition', 'clubs'],
            );

            return;
        }

        $this->alert(
            subject: "{$teacherName} joined {$clubName} at {$schoolName}",
            headline: 'Teacher joined a club',
            summary: "{$teacherName} joined {$clubName} at {$schoolName}. Membership is {$status}.",
            details: $details,
            actionUrl: $schoolUrl,
            actionLabel: 'Open school',
            nextStep: $teacherClub->status === 'pending'
                ? 'This request is pending the school leader. Follow up if it stays pending.'
                : 'Confirm the teacher appears on the school roster.',
            categories: 'clubs',
        );
    }

    /**
     * Notify learning admins when a student keeps failing a VR quiz, or finally passes after failing.
     */
    public function vrQuizCompleted(VrAssessmentAttempt $attempt): void
    {
        $attempt->loadMissing(['user:id,name,email,public_id,ulid', 'questionBank:id,title,public_id,ulid,passing_score']);

        if (! $attempt->isCompleted() || ! $attempt->user) {
            return;
        }

        $user = $attempt->user;
        $bank = $attempt->questionBank;
        $bankTitle = $bank?->title ?? 'VR quiz';
        $score = number_format((float) $attempt->score, 1);
        $passing = (int) ($bank?->passing_score ?? 60);

        $failedCount = VrAssessmentAttempt::query()
            ->where('user_id', $attempt->user_id)
            ->where('question_bank_id', $attempt->question_bank_id)
            ->whereNotNull('completed_at')
            ->where('passed', false)
            ->count();

        $passedBefore = VrAssessmentAttempt::query()
            ->where('user_id', $attempt->user_id)
            ->where('question_bank_id', $attempt->question_bank_id)
            ->whereNotNull('completed_at')
            ->where('passed', true)
            ->where('id', '!=', $attempt->id)
            ->exists();

        $resultsUrl = AdminUrl::route('admin.vr-assessments.results.show', $attempt);
        $failedListUrl = AdminUrl::route('admin.vr-assessments.results.index', ['status' => 'failed']);

        if (! $attempt->passed && $failedCount >= 2) {
            $this->alert(
                subject: "Student struggling: {$user->name} failed {$bankTitle} again",
                headline: 'Repeated VR quiz failure',
                summary: "{$user->name} has failed {$bankTitle} {$failedCount} times and still has not passed. Latest score: {$score}% (pass mark {$passing}%).",
                details: $this->rows([
                    'Student' => $user->name,
                    'Email' => $user->email,
                    'Quiz' => $bankTitle,
                    'Latest score' => "{$score}%",
                    'Pass mark' => "{$passing}%",
                    'Failed attempts' => (string) $failedCount,
                ]),
                actionUrl: $resultsUrl,
                actionLabel: 'View attempt',
                nextStep: 'Check whether the student needs coaching, or whether the quiz/content is too hard.',
                categories: 'learning',
            );

            return;
        }

        if ($attempt->passed && $failedCount > 0 && ! $passedBefore) {
            $this->alert(
                subject: "Progress: {$user->name} passed {$bankTitle} after failing",
                headline: 'Student passed after earlier fails',
                summary: "{$user->name} passed {$bankTitle} with {$score}% after {$failedCount} earlier failed attempt(s).",
                details: $this->rows([
                    'Student' => $user->name,
                    'Email' => $user->email,
                    'Quiz' => $bankTitle,
                    'Passing score' => "{$score}%",
                    'Earlier fails' => (string) $failedCount,
                ]),
                actionUrl: $resultsUrl ?: $failedListUrl,
                actionLabel: 'View result',
                nextStep: 'No action required unless you want to celebrate progress with the school or teacher.',
                categories: 'learning',
            );
        }
    }

    public function competitionSubmitted(
        string $kind,
        Competition $competition,
        CompetitionRegistration $registration,
        string $itemTitle,
        string $actionUrl,
    ): void {
        $schoolName = $registration->resolvedSchoolName() ?: ($registration->school_name ?: 'A school');
        $teacherName = $registration->resolvedTeacherName() ?: ($registration->teacher_name ?: 'A teacher');

        [$subject, $headline, $summary, $actionLabel, $nextStep] = match ($kind) {
            'idea' => [
                "Idea submitted: {$itemTitle}",
                'Competition idea submitted',
                "{$schoolName} submitted an idea for {$competition->name}.",
                'Review idea',
                'Read the idea and shortlist it or leave feedback so the school is not left waiting.',
            ],
            'prototype' => [
                "Prototype submitted: {$itemTitle}",
                'Prototype submitted',
                "{$schoolName} submitted a prototype for {$competition->name}.",
                'Review prototype',
                'Open the prototype and move it through shortlisting.',
            ],
            'prototype_modification' => [
                "Prototype update submitted: {$itemTitle}",
                'Prototype modification submitted',
                "{$schoolName} submitted a prototype update for {$competition->name}.",
                'Review update',
                'Review the modified prototype before pitch selection.',
            ],
            default => [
                "Action needed: {$schoolName} registered for {$competition->name}",
                'Competition registration waiting for review',
                "{$schoolName} submitted a registration for {$competition->name}.",
                'Review registration',
                'Check eligibility, then approve or reject the registration.',
            ],
        };

        $this->alert(
            subject: $subject,
            headline: $headline,
            summary: $summary,
            details: $this->rows([
                'Competition' => $competition->name,
                'School' => $schoolName,
                'Teacher' => $teacherName,
                'Email' => $registration->resolvedTeacherEmail() ?: $registration->teacher_email,
                'Item' => $kind === 'registration' ? null : $itemTitle,
                'Status' => 'Submitted',
            ]),
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            nextStep: $nextStep,
            categories: 'competition',
        );
    }

    /**
     * @param  array<string, mixed>  $pairs
     * @return list<array{label: string, value: string}>
     */
    private function rows(array $pairs): array
    {
        $rows = [];

        foreach ($pairs as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $rows[] = [
                'label' => $label,
                'value' => (string) $value,
            ];
        }

        return $rows;
    }
}
