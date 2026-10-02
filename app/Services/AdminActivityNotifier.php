<?php

namespace App\Services;

use App\Mail\AdminActivityMail;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\TeacherClub;
use App\Models\User;
use App\Support\AdminUrl;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AdminActivityNotifier
{
    /**
     * @return list<string>
     */
    public function recipientEmails(): array
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->whereNotNull('email')
            ->pluck('email');

        $support = config('mail.admin_alert_address');

        return $admins
            ->push($support)
            ->filter(fn ($email) => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->map(fn (string $email) => strtolower(trim($email)))
            ->unique()
            ->values()
            ->all();
    }

    public function deliver(Mailable $mail): void
    {
        $emails = $this->recipientEmails();

        if ($emails === []) {
            throw new \RuntimeException('No admin recipients are configured for activity email.');
        }

        Mail::to($emails)->send($mail);
    }

    /**
     * @param  list<array{label: string, value: string}>  $details
     */
    public function alert(
        string $subject,
        string $headline,
        string $summary,
        array $details,
        ?string $actionUrl = null,
        string $actionLabel = 'Open in admin',
        ?string $nextStep = null,
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
            ));
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
        );
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
