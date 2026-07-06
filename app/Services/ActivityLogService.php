<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogService
{
    public function log(?User $user, string $module, string $action, ?array $oldData = null, ?array $newData = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $user?->id,
            'module' => $module,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }
}