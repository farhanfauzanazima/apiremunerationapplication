<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    // GET /api/activity-logs
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user:id,name,role');

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by module
        if ($request->has('module')) {
            $query->where('module', $request->module);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by tanggal
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'message' => 'Activity log berhasil diambil.',
            'data'    => $logs->map(function ($log) {
                return [
                    'id'          => $log->id,
                    'user'        => [
                        'id'   => $log->user->id ?? null,
                        'name' => $log->user->name ?? null,
                        'role' => $log->user->role ?? null,
                    ],
                    'action'      => $log->action,
                    'module'      => $log->module,
                    'description' => $log->description,
                    'ip_address'  => $log->ip_address,
                    'created_at'  => $log->created_at,
                ];
            }),
            'pagination' => [
                'total'        => $logs->total(),
                'per_page'     => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
            ],
        ], 200);
    }

    // GET /api/activity-logs/{id}
    public function show(ActivityLog $activity_log): JsonResponse
    {
        $activity_log->load('user:id,name,role');

        return response()->json([
            'success' => true,
            'message' => 'Detail activity log berhasil diambil.',
            'data'    => [
                'id'          => $activity_log->id,
                'user'        => [
                    'id'   => $activity_log->user->id ?? null,
                    'name' => $activity_log->user->name ?? null,
                    'role' => $activity_log->user->role ?? null,
                ],
                'action'      => $activity_log->action,
                'module'      => $activity_log->module,
                'description' => $activity_log->description,
                'ip_address'  => $activity_log->ip_address,
                'user_agent'  => $activity_log->user_agent,
                'old_data'    => $activity_log->old_data,
                'new_data'    => $activity_log->new_data,
                'created_at'  => $activity_log->created_at,
            ],
        ], 200);
    }
}