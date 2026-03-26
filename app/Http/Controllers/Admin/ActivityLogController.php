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
        $dbConnection = config('activitylog.database_connection');
        $tableName = config('activitylog.table_name', 'activity_log');

        $logTableExists = $dbConnection
            ? Schema::connection($dbConnection)->hasTable($tableName)
            : Schema::hasTable($tableName);

        $logScope = $request->input('log_scope', 'admin_activity'); // admin_activity|admin_all|all

        $selectedLogName = $request->input('log_name');
        $event = $request->input('event');
        $search = $request->input('search');

        $availableLogNames = [];
        if ($logTableExists) {
            $availableLogNames = Activity::query()
                ->where('log_name', 'like', 'admin_%')
                ->distinct()
                ->orderBy('log_name')
                ->pluck('log_name')
                ->values()
                ->all();
        }

        $query = Activity::query()->with('causer');

        if ($logTableExists) {
            if ($logScope === 'admin_activity') {
                $query->where('log_name', 'admin_activity');
            } elseif ($logScope === 'admin_all') {
                $query->where('log_name', 'like', 'admin_%');
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

