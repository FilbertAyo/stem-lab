<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VrAssessmentAttempt;
use App\Models\VrQuestionBank;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VrAssessmentResultController extends Controller
{
    /**
     * Display a listing of all assessment results.
     */
    public function index(Request $request): View
    {
        $query = VrAssessmentAttempt::with([
            'user',
            'questionBank.vrExperiment.schoolClass',
            'questionBank.vrExperiment.subject'
        ])
        ->whereNotNull('completed_at')
        ->latest('completed_at');

        // Filter by question bank
        if ($request->filled('question_bank_id')) {
            $query->where('question_bank_id', $request->question_bank_id);
        }

        // Filter by pass/fail status
        if ($request->filled('status')) {
            $query->where('passed', $request->status === 'passed');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                              ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhereHas('questionBank', function ($bankQuery) use ($search) {
                    $bankQuery->where('title', 'like', '%' . $search . '%')
                              ->orWhereHas('vrExperiment', function ($expQuery) use ($search) {
                                  $expQuery->where('name', 'like', '%' . $search . '%');
                              });
                });
            });
        }

        $attempts = $query->paginate(20)->withQueryString();
        $questionBanks = VrQuestionBank::with('vrExperiment')->get();

        return view('admin.vr-assessments.results.index', compact('attempts', 'questionBanks'));
    }

    /**
     * Display details of a specific attempt.
     */
    public function show(VrAssessmentAttempt $attempt): View
    {
        $attempt->load([
            'user',
            'questionBank.vrExperiment',
            'answers.question'
        ]);

        return view('admin.vr-assessments.results.show', compact('attempt'));
    }
}

