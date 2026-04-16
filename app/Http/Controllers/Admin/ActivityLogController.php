<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logScope = $request->input('log_scope', 'admin_activity');

        return $this->renderActivityLogs($request, $logScope);
    }

    private function renderActivityLogs(Request $request, string $logScope): View
    {
        $dbConnection = config('activitylog.database_connection');
        $tableName = config('activitylog.table_name', 'activity_log');

        $logTableExists = $dbConnection
            ? Schema::connection($dbConnection)->hasTable($tableName)
            : Schema::hasTable($tableName);

        $selectedLogName = $request->input('log_name');
        $event = $request->input('event');
        $search = $request->input('search');

        $availableLogNames = [];
        if ($logTableExists) {
            $namesQuery = Activity::query()->distinct()->orderBy('log_name');

            if (in_array($logScope, ['admin_activity', 'admin_all'], true)) {
                $namesQuery->where('log_name', 'like', 'admin_%');
            } elseif (in_array($logScope, ['client_activity', 'client_all'], true)) {
                $namesQuery->where('log_name', 'like', 'client_%');
            }

            $availableLogNames = $namesQuery->pluck('log_name')->values()->all();
        }

        $query = Activity::query()->with([
            'causer.teacherClubs' => function ($teacherClubQuery) {
                $teacherClubQuery
                    ->where('status', 'approved')
                    ->with('school:id,name');
            },
        ]);

        if ($logTableExists) {
            if ($logScope === 'admin_activity') {
                $query->where('log_name', 'admin_activity');
            } elseif ($logScope === 'admin_all') {
                $query->where('log_name', 'like', 'admin_%');
            } elseif ($logScope === 'client_activity') {
                $query->where('log_name', 'client_activity');
            } elseif ($logScope === 'client_all') {
                $query->where('log_name', 'like', 'client_%');
            }

            if (! empty($selectedLogName)) {
                $query->where('log_name', $selectedLogName);
            }

            if (! empty($event)) {
                $query->where('event', $event);
            }

            if (! empty($search)) {
                $query->where('description', 'like', '%'.$search.'%');
            }
        }

        $logs = $logTableExists
            ? $query->orderByDesc('id')->paginate(20)->appends($request->query())
            : collect();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'logTableExists' => $logTableExists,
            'availableLogNames' => $availableLogNames,
            'logScope' => $logScope,
            'selectedLogName' => $selectedLogName,
            'event' => $event,
            'search' => $search,
        ]);
    }
}
