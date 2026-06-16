<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesStudentClassContext;
use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\Model3d;
use App\Models\ModelQuestionAnswer;
use App\Models\Student;
use App\Models\VrAssessmentAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesStudentClassContext;

    /**
     * Display the client dashboard.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        // Get user's model question answers
        $totalAnswers = ModelQuestionAnswer::where('user_id', $userId)->count();
        $correctAnswers = ModelQuestionAnswer::where('user_id', $userId)->where('is_correct', true)->count();
        $modelsCompleted = ModelQuestionAnswer::where('user_id', $userId)->distinct('model_id')->count('model_id');

        // Get user's VR assessment attempts
        $totalVrAttempts = VrAssessmentAttempt::where('user_id', $userId)->count();
        $completedVrAttempts = VrAssessmentAttempt::where('user_id', $userId)->whereNotNull('completed_at')->count();
        $passedVrAttempts = VrAssessmentAttempt::where('user_id', $userId)->where('passed', true)->count();

        $competitions = collect();
        $competitionReminders = collect();
        $totalCompetitions = 0;
        $totalSubmissions = 0;
        $totalStudents = 0;

        if ($user->role === 'teacher') {
            $teacherClubs = $user->teacherClubs()->pluck('id');
            $teacherClubIds = $user->teacherClubs()
                ->where('status', 'approved')
                ->pluck('club_id')
                ->unique()
                ->filter()
                ->values();

            if ($teacherClubs->isNotEmpty()) {
                // Students for any approved teacher at the same club + school as this user
                $totalStudents = Student::whereIn('teacher_club_id', $user->approvedCoMemberTeacherClubIds())->count();
            }

            if ($teacherClubIds->isNotEmpty()) {
                $approvedTeacherClubs = $user->teacherClubs()
                    ->where('status', 'approved')
                    ->with(['user', 'school'])
                    ->get()
                    ->keyBy('club_id');

                $competitions = Competition::query()
                ->active()
                ->published()
                ->whereIn('club_id', $teacherClubIds)
                ->with(['phases'])
                    ->orderByDesc('competition_start')
                    ->get();

                $registrations = CompetitionRegistration::query()
                    ->where('teacher_user_id', $userId)
                    ->whereIn('competition_id', $competitions->pluck('id'))
                    ->with([
                        'idea.shortlist',
                        'prototypeSubmission.prototypeShortlist',
                        'prototypeModification',
                    ])
                    ->get()
                    ->keyBy('competition_id');

                $competitionReminders = $competitions->map(function (Competition $competition) use ($registrations) {
                $registration = $registrations->get($competition->id);
                $registrationStatus = $registration?->status;
                $idea = $registration?->idea;
                $ideaDecisionStatus = $idea?->shortlist?->status;
                if (! $ideaDecisionStatus && $idea?->status === 'shortlisted') {
                    $ideaDecisionStatus = 'shortlisted';
                }

                $prototype = $registration?->prototypeSubmission;
                $prototypeDecisionStatus = $prototype?->prototypeShortlist?->status;
                $prototypeModification = $registration?->prototypeModification;

                if ($registrationStatus === 'draft') {
                    return [
                        'priority' => 100,
                        'kind' => 'action_required',
                        'label' => 'Complete your competition registration (participants and school details).',
                        'competition' => $competition,
                        'route' => route('client.competitions.register', $competition),
                        'route_label' => 'Complete registration',
                        'badge_class' => 'bg-danger',
                        'time' => $registration->updated_at,
                    ];
                }

                if (! $registration && $competition->allowsTeacherRegistration(null)) {
                    return [
                        'priority' => 99,
                        'kind' => 'action_required',
                        'label' => 'You joined this club earlier — register your team for the competition.',
                        'competition' => $competition,
                        'route' => route('client.competitions.register', $competition),
                        'route_label' => 'Register now',
                        'badge_class' => 'bg-danger',
                        'time' => $competition->registration_deadline,
                    ];
                }

                if ($registrationStatus === 'pending') {
                    return [
                        'priority' => 90,
                        'kind' => 'pending',
                        'label' => 'Registration submitted and waiting for approval.',
                        'competition' => $competition,
                        'route' => route('client.competitions.show', $competition),
                        'route_label' => 'View status',
                        'time' => $registration->submitted_at,
                    ];
                }

                if ($registrationStatus === 'rejected' && $competition->allowsTeacherRegistration($registration)) {
                    return [
                        'priority' => 95,
                        'kind' => 'action_required',
                        'label' => 'Registration was rejected - update and resubmit.',
                        'competition' => $competition,
                        'route' => route('client.competitions.register', $competition),
                        'route_label' => 'Update registration',
                        'badge_class' => 'bg-warning text-dark',
                        'time' => $registration->updated_at,
                    ];
                }

                if ($registrationStatus === 'approved' && $competition->isPhaseOpen('idea_submission') && (! $idea || $idea->status === 'draft')) {
                    return [
                        'priority' => 85,
                        'kind' => 'action_required',
                        'label' => 'Idea submission phase is open - submit your idea.',
                        'competition' => $competition,
                        'route' => route('client.competitions.show', $competition).'#idea-submission',
                        'route_label' => 'Submit idea',
                        'time' => $competition->competition_end,
                    ];
                }

                if ($registrationStatus === 'approved' && $competition->isPhaseOpen('prototype_development') && $ideaDecisionStatus === 'shortlisted' && (! $prototype || $prototype->status === 'draft')) {
                    return [
                        'priority' => 80,
                        'kind' => 'action_required',
                        'label' => 'Prototype phase is open for shortlisted teams.',
                        'competition' => $competition,
                        'route' => route('client.competitions.show', $competition).'#prototype-development',
                        'route_label' => 'Submit prototype',
                        'time' => $competition->competition_end,
                    ];
                }

                if ($registrationStatus === 'approved' && $competition->isPhaseOpen('prototype_modification') && $prototypeDecisionStatus === 'shortlisted' && (! $prototypeModification || $prototypeModification->status === 'draft')) {
                    return [
                        'priority' => 75,
                        'kind' => 'action_required',
                        'label' => 'Prototype modification phase is open for your team.',
                        'competition' => $competition,
                        'route' => route('client.competitions.show', $competition).'#prototype-modification',
                        'route_label' => 'Submit modification',
                        'time' => $competition->competition_end,
                    ];
                }

                    return null;
                })->filter()->values();

                $totalCompetitions = $competitions->count();
                $totalSubmissions = $registrations->filter(function (CompetitionRegistration $registration) {
                    return in_array($registration->status, ['pending', 'approved'], true);
                })->count();
            }
        }

        // Get latest active models
        $latestModels = Model3d::active()
            ->with('subject')
            ->visibleToStudentClass($this->studentSchoolClassId($user))
            ->latest()
            ->take(3)
            ->get();

        // Calculate accuracy
        $accuracy = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0;

        $stats = [
            'total_answers' => $totalAnswers,
            'correct_answers' => $correctAnswers,
            'models_completed' => $modelsCompleted,
            'accuracy' => $accuracy,
            'total_vr_attempts' => $totalVrAttempts,
            'completed_vr_attempts' => $completedVrAttempts,
            'passed_vr_attempts' => $passedVrAttempts,
            'total_competitions' => $totalCompetitions,
            'total_submissions' => $totalSubmissions,
            'total_students' => $totalStudents,
        ];

        $competitionNotificationCount = 0;
        $topCompetitionReminders = collect();
        $competitionShortcuts = [];

        if ($user->role === 'teacher') {
            $competitionNotificationCount = $competitionReminders->count();
            $topCompetitionReminders = $competitionReminders
                ->sortByDesc('priority')
                ->take(4)
                ->values();
            $competitionShortcuts = [
                [
                    'label' => 'All competitions',
                    'value' => $totalCompetitions,
                    'route' => route('client.competitions.index'),
                ],
                [
                    'label' => 'Action needed',
                    'value' => $competitionReminders->where('kind', 'action_required')->count(),
                    'route' => route('client.competitions.index'),
                ],
                [
                    'label' => 'Pending approvals',
                    'value' => $competitionReminders->where('kind', 'pending')->count(),
                    'route' => route('client.competitions.index'),
                ],
            ];
        }

        $showPhoneReminder = in_array($user->role, ['teacher', 'student', 'parent'], true)
            && blank($user->phone);

        return view('client.back.dashboard', compact(
            'stats',
            'latestModels',
            'topCompetitionReminders',
            'competitionNotificationCount',
            'competitionShortcuts',
            'showPhoneReminder'
        ));
    }
}
