<?php

namespace App\Mail;

use App\Models\Competition;
use App\Models\CompetitionIdea;
use App\Models\CompetitionRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompetitionIdeaShortlistedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Competition $competition,
        public CompetitionRegistration $registration,
        public CompetitionIdea $idea,
        public string $competitionUrl,
        public bool $isReminder = false,
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->isReminder
            ? 'Reminder: your idea is shortlisted'
            : 'Your idea was shortlisted';

        return new Envelope(
            subject: $prefix.' · '.$this->competition->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.competition-idea-shortlisted',
            with: [
                'teacherName' => $this->registration->resolvedTeacherName() ?: 'Teacher',
                'competitionName' => $this->competition->name,
                'schoolName' => $this->registration->resolvedSchoolName(),
                'ideaTitle' => $this->idea->problem_title ?: 'your idea',
                'competitionUrl' => $this->competitionUrl,
                'isReminder' => $this->isReminder,
            ],
        );
    }
}
