<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $logs = $query->paginate(30);

        return response()->json([
            'success' => true,
            'message' => 'Activity log berhasil diambil',
            'data' => $logs->items(),
            'raw' => [
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'total' => $logs->total(),
                ],
            ],
        ]);
    }

    public function show(ActivityLog $activityLog)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail activity log berhasil diambil',
            'data' => $activityLog->load('user'),
        ]);
    }
}