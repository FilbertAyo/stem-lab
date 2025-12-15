<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Model3d;
use App\Models\Competition;
use App\Models\School;
use App\Models\Club;
use App\Models\ModelQuestionAnswer;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => Student::count(),
            'total_3d_models' => Model3d::count(),
            'active_3d_models' => Model3d::where('is_active', true)->count(),
            'total_competitions' => Competition::count(),
            'active_competitions' => Competition::where('is_active', true)->count(),
            'total_schools' => School::count(),
            'total_clubs' => Club::count(),
            'total_answers' => ModelQuestionAnswer::count(),
            'total_correct_answers' => ModelQuestionAnswer::where('is_correct', true)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}

