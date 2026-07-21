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

class CompetitionIdeaSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Competition $competition,
        public CompetitionRegistration $registration,
        public CompetitionIdea $idea,
        public string $competitionUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations — idea submitted · '.$this->competition->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.competition-idea-submitted',
            with: [
                'teacherName' => $this->registration->resolvedTeacherName() ?: 'Teacher',
                'competitionName' => $this->competition->name,
                'schoolName' => $this->registration->school_name,
                'problemTitle' => $this->idea->problem_title,
                'competitionUrl' => $this->competitionUrl,
            ],
        );
    }
}
