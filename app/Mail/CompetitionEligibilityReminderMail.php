<?php

namespace App\Mail;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompetitionEligibilityReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Competition $competition,
        public CompetitionRegistration $registration,
        public string $registerUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Complete your registration — '.$this->competition->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.competition-eligibility-reminder',
            with: [
                'teacherName' => $this->registration->teacher_name ?: 'Teacher',
                'competitionName' => $this->competition->name,
                'schoolName' => $this->registration->resolvedSchoolName(),
                'registerUrl' => $this->registerUrl,
            ],
        );
    }
}
