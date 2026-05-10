<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Catat aktivitas user
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        array $oldData = null,
        array $newData = null
    ): void {
        try {
            $request = app(Request::class);

            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => $action,
                'module'      => $module,
                'description' => $description,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'old_data'    => $oldData,
                'new_data'    => $newData,
            ]);
        } catch (\Exception $e) {
            // Jangan sampai log error mengganggu flow utama
            \Log::error('ActivityLog error: ' . $e->getMessage());
        }
    }
}