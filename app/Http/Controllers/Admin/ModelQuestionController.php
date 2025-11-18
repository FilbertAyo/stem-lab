<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Models\ModelQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModelQuestionController extends Controller
{
    /**
     * Display questions for a specific 3D model.
     */
    public function index(Model3d $model3d): View
    {
        $questions = $model3d->questions()->orderBy('order')->get();
        return view('admin.models.3d.questions.index', compact('model3d', 'questions'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Model3d $model3d): View
    {
        return view('admin.models.3d.questions.create', compact('model3d'));
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request, Model3d $model3d): RedirectResponse
    {
        $validated = $request->validate([
            'question_type' => ['required', 'in:mcq,true_false,short_answer'],
            'question_text' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['required', 'string'],
            'correct_answer' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
        ]);

        // Get the next order value
        $maxOrder = $model3d->questions()->max('order') ?? 0;
        
        $validated['model_id'] = $model3d->id;
        $validated['order'] = $maxOrder + 1;

        // For MCQ, ensure options are provided
        if ($validated['question_type'] === 'mcq' && empty($validated['options'])) {
            return back()->withErrors(['options' => 'MCQ questions require at least 2 options.'])->withInput();
        }

        // For true/false, set default options
        if ($validated['question_type'] === 'true_false') {
            $validated['options'] = ['True', 'False'];
        }

        ModelQuestion::create($validated);

        return redirect()->route('admin.models.3d.questions.index', $model3d)
            ->with('success', 'Question created successfully.');
    }

    /**
     * Show the form for editing a question.
     */
    public function edit(Model3d $model3d, ModelQuestion $question): View
    {
        // Ensure question belongs to this model
        if ($question->model_id !== $model3d->id) {
            abort(404);
        }
        
        return view('admin.models.3d.questions.edit', compact('model3d', 'question'));
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, Model3d $model3d, ModelQuestion $question): RedirectResponse
    {
        // Ensure question belongs to this model
        if ($question->model_id !== $model3d->id) {
            abort(404);
        }

        $validated = $request->validate([
            'question_type' => ['required', 'in:mcq,true_false,short_answer'],
            'question_text' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['required', 'string'],
            'correct_answer' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
        ]);

        // For MCQ, ensure options are provided
        if ($validated['question_type'] === 'mcq' && empty($validated['options'])) {
            return back()->withErrors(['options' => 'MCQ questions require at least 2 options.'])->withInput();
        }

        // For true/false, set default options
        if ($validated['question_type'] === 'true_false') {
            $validated['options'] = ['True', 'False'];
        }

        $question->update($validated);

        return redirect()->route('admin.models.3d.questions.index', $model3d)
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified question.
     */
    public function destroy(Model3d $model3d, ModelQuestion $question): RedirectResponse
    {
        // Ensure question belongs to this model
        if ($question->model_id !== $model3d->id) {
            abort(404);
        }

        $question->delete();

        return redirect()->route('admin.models.3d.questions.index', $model3d)
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Update the order of questions.
     */
    public function updateOrder(Request $request, Model3d $model3d): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer'],
        ]);

        foreach ($request->order as $index => $questionId) {
            ModelQuestion::where('id', $questionId)
                ->where('model_id', $model3d->id)
                ->update(['order' => $index + 1]);
        }

        return redirect()->route('admin.models.3d.questions.index', $model3d)
            ->with('success', 'Question order updated successfully.');
    }
}
