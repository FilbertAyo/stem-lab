<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\VrAssessmentAttempt;
use App\Models\VrQuestion;
use App\Models\VrQuestionAnswer;
use App\Models\VrQuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VrAssessmentController extends Controller
{
    /**
     * Display a listing of available quizzes.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Get all active question banks with their experiments
        $questionBanks = VrQuestionBank::with(['vrExperiment.schoolClass', 'vrExperiment.subject'])
            ->where('is_active', true)
            ->whereHas('vrExperiment', function ($query) {
                $query->where('is_active', true);
            })
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get user's attempts for progress tracking
        $userAttempts = VrAssessmentAttempt::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->get()
            ->groupBy('question_bank_id');

        return view('client.back.vr-assessments.index', compact('questionBanks', 'userAttempts'));
    }

    /**
     * Show a specific quiz details before starting.
     */
    public function show(VrQuestionBank $questionBank): View
    {
        // Ensure the bank is active
        if (!$questionBank->is_active || !$questionBank->vrExperiment->is_active) {
            abort(404);
        }

        $questionBank->load(['vrExperiment.schoolClass', 'vrExperiment.subject']);
        $questionBank->loadCount('questions');

        $user = auth()->user();

        // Get user's previous attempts
        $previousAttempts = VrAssessmentAttempt::where('user_id', $user->id)
            ->where('question_bank_id', $questionBank->id)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->take(5)
            ->get();

        // Check if there's an in-progress attempt
        $inProgressAttempt = VrAssessmentAttempt::where('user_id', $user->id)
            ->where('question_bank_id', $questionBank->id)
            ->whereNull('completed_at')
            ->first();

        return view('client.back.vr-assessments.show', compact('questionBank', 'previousAttempts', 'inProgressAttempt'));
    }

    /**
     * Start or resume a quiz attempt.
     */
    public function start(VrQuestionBank $questionBank): RedirectResponse
    {
        $user = auth()->user();

        // Check for existing in-progress attempt
        $attempt = VrAssessmentAttempt::where('user_id', $user->id)
            ->where('question_bank_id', $questionBank->id)
            ->whereNull('completed_at')
            ->first();

        if (!$attempt) {
            // Create new attempt
            $attempt = VrAssessmentAttempt::create([
                'user_id' => $user->id,
                'question_bank_id' => $questionBank->id,
                'started_at' => now(),
                'total_questions' => $questionBank->questions()->count(),
            ]);
        }

        return redirect()->route('client.vr-assessments.take', $attempt);
    }

    /**
     * Display the quiz-taking interface.
     */
    public function take(VrAssessmentAttempt $attempt): View
    {
        // Ensure the attempt belongs to the current user and is not completed
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        if ($attempt->completed_at) {
            return redirect()->route('client.vr-assessments.results', $attempt);
        }

        $attempt->load(['questionBank.vrExperiment', 'questionBank.questions' => function ($query) {
            $query->orderBy('order');
        }, 'answers']);

        // Get answered question IDs
        $answeredQuestionIds = $attempt->answers->pluck('question_id')->toArray();

        // Calculate time remaining if there's a time limit
        $timeRemaining = null;
        if ($attempt->questionBank->time_limit) {
            $endTime = $attempt->started_at->addMinutes($attempt->questionBank->time_limit);
            $timeRemaining = max(0, now()->diffInSeconds($endTime, false));
            
            // Auto-submit if time is up
            if ($timeRemaining <= 0) {
                return $this->autoSubmit($attempt);
            }
        }

        return view('client.back.vr-assessments.take', compact('attempt', 'answeredQuestionIds', 'timeRemaining'));
    }

    /**
     * Save an answer for a question.
     */
    public function saveAnswer(Request $request, VrAssessmentAttempt $attempt): JsonResponse
    {
        // Ensure the attempt belongs to the current user and is not completed
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($attempt->completed_at) {
            return response()->json(['error' => 'Assessment already completed'], 400);
        }

        $validated = $request->validate([
            'question_id' => ['required', 'exists:vr_questions,id'],
            'answer' => ['required', 'string'],
        ]);

        $question = VrQuestion::findOrFail($validated['question_id']);

        // Ensure the question belongs to this attempt's question bank
        if ($question->question_bank_id !== $attempt->question_bank_id) {
            return response()->json(['error' => 'Invalid question'], 400);
        }

        // Check if answer is correct
        $isCorrect = $question->isCorrectAnswer($validated['answer']);
        $pointsEarned = $isCorrect ? $question->points : 0;

        // Update or create answer
        VrQuestionAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'student_answer' => $validated['answer'],
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]
        );

        return response()->json([
            'success' => true,
            'answered_count' => $attempt->answers()->count(),
        ]);
    }

    /**
     * Submit the quiz and calculate results.
     */
    public function submit(VrAssessmentAttempt $attempt): RedirectResponse
    {
        // Ensure the attempt belongs to the current user and is not completed
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        if ($attempt->completed_at) {
            return redirect()->route('client.vr-assessments.results', $attempt);
        }

        $this->completeAttempt($attempt);

        return redirect()->route('client.vr-assessments.results', $attempt)
            ->with('success', 'Quiz submitted successfully!');
    }

    /**
     * Auto-submit when time is up.
     */
    private function autoSubmit(VrAssessmentAttempt $attempt): RedirectResponse
    {
        $this->completeAttempt($attempt);

        return redirect()->route('client.vr-assessments.results', $attempt)
            ->with('warning', 'Time is up! Your quiz has been automatically submitted.');
    }

    /**
     * Complete the attempt and calculate scores.
     */
    private function completeAttempt(VrAssessmentAttempt $attempt): void
    {
        $attempt->load(['answers', 'questionBank']);

        $correctAnswers = $attempt->answers->where('is_correct', true)->count();
        $totalQuestions = $attempt->total_questions;
        
        $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
        $passed = $score >= $attempt->questionBank->passing_score;

        $attempt->update([
            'completed_at' => now(),
            'correct_answers' => $correctAnswers,
            'score' => $score,
            'passed' => $passed,
        ]);
    }

    /**
     * Display the quiz results.
     */
    public function results(VrAssessmentAttempt $attempt): View
    {
        // Ensure the attempt belongs to the current user
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        // If not completed, redirect to take the quiz
        if (!$attempt->completed_at) {
            return redirect()->route('client.vr-assessments.take', $attempt);
        }

        $attempt->load([
            'questionBank.vrExperiment',
            'questionBank.questions' => function ($query) {
                $query->orderBy('order');
            },
            'answers.question'
        ]);

        return view('client.back.vr-assessments.results', compact('attempt'));
    }
}

