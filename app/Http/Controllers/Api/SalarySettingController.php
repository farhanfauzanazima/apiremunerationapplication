<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalarySetting\UpdateSalarySettingRequest;
use App\Models\SalarySetting;
use App\Services\ActivityLogService;

class SalarySettingController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    public function show()
    {
        return response()->json([
            'success' => true,
            'message' => 'Pengaturan kategorikal berhasil diambil',
            'data' => SalarySetting::current(),
        ]);
    }

    public function update(UpdateSalarySettingRequest $request)
    {
        $setting = SalarySetting::current();
        $oldData = $setting->toArray();

        $setting->update($request->validated());

        $this->activityLogService->log(
            $request->user(),
            'salary_setting',
            'update',
            $oldData,
            $setting->fresh()->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan kategorikal berhasil diperbarui. Perubahan berlaku untuk semua cabang.',
            'data' => $setting->fresh(),
        ]);
    }
}