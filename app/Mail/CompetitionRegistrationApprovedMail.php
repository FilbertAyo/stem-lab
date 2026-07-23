<?php

namespace App\Mail;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompetitionRegistrationApprovedMail extends Mailable
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
            subject: 'Congratulations — you advanced to idea submission · '.$this->competition->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.competition-registration-approved',
            with: [
                'teacherName' => $this->registration->resolvedTeacherName() ?: 'Teacher',
                'competitionName' => $this->competition->name,
                'schoolName' => $this->registration->resolvedSchoolName(),
                'competitionUrl' => $this->competitionUrl,
            ],
        );
    }
}
