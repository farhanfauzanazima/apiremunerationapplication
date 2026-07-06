<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
    public function processQueue(string $token)
    {
        if (!hash_equals(config('app.cron_secret_token'), $token)) {
            abort(403, 'Token tidak valid');
        }

        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries' => 2,
            '--max-time' => 50,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proses queue selesai dijalankan',
            'output' => Artisan::output(),
        ]);
    }
}