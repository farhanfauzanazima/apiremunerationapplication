<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\SalaryTrendService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected SalaryTrendService $salaryTrendService,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $allowed = $user->allowedBranchIds(); // null = akses semua cabang
        $includeTrend = $user->isOwner(); // grafik tren HANYA dikirim untuk Owner

        $data = $this->dashboardService->build($allowed, $includeTrend, $this->salaryTrendService);

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data' => $data,
        ]);
    }
}