<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Model3dController extends Controller
{
    /**
     * Display a listing of the 3D models available to students.
     */
    public function index(Request $request): View
    {
        $subjectId = $request->input('subject');

        $model3ds = Model3d::with('subject')
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->latest()
            ->get();

        $subjects = Subject::where('is_active', true)
            ->orderBy('subject_name')
            ->get();

        return view('client.back.models.3d.index', [
            'model3ds' => $model3ds,
            'subjects' => $subjects,
            'selectedSubject' => $subjectId,
        ]);
    }

    /**
     * Display a specific 3D model.
     */
    public function show(Model3d $model3d): View
    {
        $model3d->load('subject');

        return view('client.back.models.3d.show', compact('model3d'));
    }
}

