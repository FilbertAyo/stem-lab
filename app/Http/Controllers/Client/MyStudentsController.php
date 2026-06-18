<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Models\ModelQuestionAnswer;
use App\Models\Student;
use App\Models\StudentLearningEvent;
use App\Models\VrAssessmentAttempt;
use App\Services\Learning\StudentProgressSummaryService;
use App\Services\Teacher\TeacherClubAnalyticsService;
use App\Services\Teacher\TeacherStudentScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyStudentsController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TeacherStudentScopeService $scopeService,
        private readonly TeacherClubAnalyticsService $analyticsService,
        private readonly StudentProgressSummaryService $summaryService,
    ) {}

    public function index(Request $request): View
    {
        $teacher = Auth::user();
        $filters = $request->only(['club', 'class', 'registration', 'activity', 'attention', 'q', 'student']);

        $teacherClubs = $this->scopeService->manageableTeacherClubs($teacher);
        $clubFilter = $filters['club'] ?? 'all';
        $teacherClubId = $clubFilter !== 'all' && filled($clubFilter) ? (int) $clubFilter : null;

        $students = $this->scopeService
            ->filteredStudentsQuery($teacher, $filters)
            ->paginate(25)
            ->withQueryString();

        $analytics = $this->analyticsService->aggregate($teacher, $teacherClubId);
        $classes = $this->analyticsService->availableClasses($teacher, $teacherClubId);

        return view('client.back.my-students.index', compact(
            'students',
            'analytics',
            'classes',
            'teacherClubs',
            'filters',
        ));
    }

    public function show(Student $student): View
    {
        $this->authorize('view', $student);

        $student->load(['schoolClass', 'progressSummary', 'user', 'teacherClub.club', 'teacherClub.school']);

        $summary = $student->progressSummary;

        if ($student->isLinked() && (! $summary || $summary->computed_at === null || $summary->computed_at->lt(now()->subMinutes(5)))) {
            $summary = $this->summaryService->recomputeForStudent($student);
            $student->setRelation('progressSummary', $summary);
        }

        $recentEvents = collect();
        $modelProgress = collect();
        $vrAttempts = collect();

        if ($student->user_id) {
            $recentEvents = StudentLearningEvent::query()
                ->where('user_id', $student->user_id)
                ->with('subject')
                ->orderByDesc('occurred_at')
                ->limit(20)
                ->get();

            $modelProgress = ModelQuestionAnswer::query()
                ->where('user_id', $student->user_id)
                ->selectRaw('
                    model_id,
                    COUNT(*) as total_questions,
                    SUM(CASE WHEN is_correct = true THEN 1 ELSE 0 END) as correct_answers,
                    MAX(updated_at) as last_answered
                ')
                ->groupBy('model_id')
                ->orderByDesc('last_answered')
                ->get()
                ->map(function ($item) {
                    $model = Model3d::query()->find($item->model_id);
                    $percentage = $item->total_questions > 0
                        ? round(($item->correct_answers / $item->total_questions) * 100, 1)
                        : 0;

                    return (object) [
                        'model' => $model,
                        'total_questions' => (int) $item->total_questions,
                        'correct_answers' => (int) $item->correct_answers,
                        'percentage' => $percentage,
                        'last_answered' => $item->last_answered,
                    ];
                })
                ->filter(fn ($item) => $item->model !== null);

            $vrAttempts = VrAssessmentAttempt::query()
                ->where('user_id', $student->user_id)
                ->whereNotNull('completed_at')
                ->with('questionBank')
                ->orderByDesc('completed_at')
                ->limit(10)
                ->get();
        }

        return view('client.back.my-students.show', compact(
            'student',
            'summary',
            'recentEvents',
            'modelProgress',
            'vrAttempts',
        ));
    }
}
