<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentJoinedClubNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Student $student,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->student->loadMissing(['schoolClass', 'teacherClub.club', 'teacherClub.school']);
        $clubName = $student->teacherClub?->club?->name ?? 'your club';
        $schoolName = $student->teacherClub?->school?->name;

        $message = (new MailMessage)
            ->subject('A student joined your club on '.config('app.name'))
            ->greeting('Hello '.$notifiable->name.',')
            ->line($student->name.' has linked their platform account to your club roster.')
            ->line('Admission number: '.$student->admission_number);

        if ($student->schoolClass) {
            $message->line('Class: '.$student->schoolClass->name);
        }

        if ($schoolName) {
            $message->line('School: '.$schoolName);
        }

        return $message
            ->action('View student progress', route('client.my-students.show', $this->student))
            ->line('You can monitor their learning progress from My Students.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->student->loadMissing(['schoolClass', 'teacherClub.club']);

        return [
            'type' => 'student_joined_club',
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'admission_number' => $this->student->admission_number,
            'class_name' => $this->student->schoolClass?->name,
            'club_name' => $this->student->teacherClub?->club?->name,
            'url' => route('client.my-students.show', $this->student),
            'message' => $this->student->name.' joined your club and linked their account.',
        ];
    }
}
