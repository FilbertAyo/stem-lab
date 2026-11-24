<?php

namespace App\Mail;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompetitionIdeaSubmissionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Competition $competition,
        public CompetitionRegistration $registration,
        public string $competitionUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Submit your idea — '.$this->competition->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.competition-idea-submission-reminder',
            with: [
                'teacherName' => $this->registration->resolvedTeacherName() ?: 'Teacher',
                'competitionName' => $this->competition->name,
                'schoolName' => $this->registration->resolvedSchoolName(),
                'competitionUrl' => $this->competitionUrl,
            ],
        );
    }
}
