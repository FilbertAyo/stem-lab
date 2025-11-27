<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VrQuestion;
use App\Models\VrQuestionBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VrQuestionController extends Controller
{
    /**
     * Show the form for creating a new question.
     */
    public function create(VrQuestionBank $questionBank): View
    {
        return view('admin.vr-assessments.questions.create', compact('questionBank'));
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request, VrQuestionBank $questionBank): RedirectResponse
    {
        $validated = $request->validate([
            'question_type' => ['required', 'in:mcq,true_false,short_answer'],
            'question_text' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['required', 'string'],
            'correct_answer' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        // Get the next order value
        $maxOrder = $questionBank->questions()->max('order') ?? 0;
        
        $validated['question_bank_id'] = $questionBank->id;
        $validated['order'] = $maxOrder + 1;

        // For MCQ, ensure options are provided
        if ($validated['question_type'] === 'mcq' && empty($validated['options'])) {
            return back()->withErrors(['options' => 'MCQ questions require at least 2 options.'])->withInput();
        }

        // For true/false, set default options
        if ($validated['question_type'] === 'true_false') {
            $validated['options'] = ['True', 'False'];
        }

        // For short_answer, clear options
        if ($validated['question_type'] === 'short_answer') {
            $validated['options'] = null;
        }

        VrQuestion::create($validated);

        return redirect()->route('admin.vr-assessments.question-banks.show', $questionBank)
            ->with('success', 'Question added successfully.');
    }

    /**
     * Show the form for editing a question.
     */
    public function edit(VrQuestionBank $questionBank, VrQuestion $question): View
    {
        // Ensure question belongs to this bank
        if ($question->question_bank_id !== $questionBank->id) {
            abort(404);
        }
        
        return view('admin.vr-assessments.questions.edit', compact('questionBank', 'question'));
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, VrQuestionBank $questionBank, VrQuestion $question): RedirectResponse
    {
        // Ensure question belongs to this bank
        if ($question->question_bank_id !== $questionBank->id) {
            abort(404);
        }

        $validated = $request->validate([
            'question_type' => ['required', 'in:mcq,true_false,short_answer'],
            'question_text' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['required', 'string'],
            'correct_answer' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        // For MCQ, ensure options are provided
        if ($validated['question_type'] === 'mcq' && empty($validated['options'])) {
            return back()->withErrors(['options' => 'MCQ questions require at least 2 options.'])->withInput();
        }

        // For true/false, set default options
        if ($validated['question_type'] === 'true_false') {
            $validated['options'] = ['True', 'False'];
        }

        // For short_answer, clear options
        if ($validated['question_type'] === 'short_answer') {
            $validated['options'] = null;
        }

        $question->update($validated);

        return redirect()->route('admin.vr-assessments.question-banks.show', $questionBank)
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified question.
     */
    public function destroy(VrQuestionBank $questionBank, VrQuestion $question): RedirectResponse
    {
        // Ensure question belongs to this bank
        if ($question->question_bank_id !== $questionBank->id) {
            abort(404);
        }

        $question->delete();

        return redirect()->route('admin.vr-assessments.question-banks.show', $questionBank)
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Update the order of questions.
     */
    public function updateOrder(Request $request, VrQuestionBank $questionBank): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer'],
        ]);

        foreach ($request->order as $index => $questionId) {
            VrQuestion::where('id', $questionId)
                ->where('question_bank_id', $questionBank->id)
                ->update(['order' => $index + 1]);
        }

        return redirect()->route('admin.vr-assessments.question-banks.show', $questionBank)
            ->with('success', 'Question order updated successfully.');
    }
}

