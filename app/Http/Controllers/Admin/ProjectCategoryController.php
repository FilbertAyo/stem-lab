<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectCategory;
use Illuminate\Http\RedirectResponse;

class ProjectCategoryController extends Controller
{
    public function destroy(ProjectCategory $projectCategory): RedirectResponse
    {
        $projectCategory->delete();

        return back()->with('success', "Category \"{$projectCategory->name}\" deleted. Projects using it are now uncategorized.");
    }
}
