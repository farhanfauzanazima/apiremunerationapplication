<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationSetting\UpdateNotificationSettingRequest;
use App\Models\NotificationSetting;
use App\Services\ActivityLogService;

class NotificationSettingController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService)
    {
    }

    public function show()
    {
        return response()->json([
            'success' => true,
            'message' => 'Pengaturan template notifikasi berhasil diambil',
            'data' => [
                'setting' => NotificationSetting::current(),
                'available_placeholders' => ['{nama}', '{bulan}', '{tahun}', '{link}'],
            ],
        ]);
    }

    public function update(UpdateNotificationSettingRequest $request)
    {
        $setting = NotificationSetting::current();
        $oldData = $setting->toArray();

        $setting->update($request->validated());

        $this->activityLogService->log($request->user(), 'notification_setting', 'update', $oldData, $setting->fresh()->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Template pesan WhatsApp berhasil diperbarui',
            'data' => $setting->fresh(),
        ]);
    }
}