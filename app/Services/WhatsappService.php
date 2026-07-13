<?php

namespace App\Services;

use App\Services\Fonnte\FonnteClient;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function __construct(protected FonnteClient $fonnteClient = new FonnteClient())
    {
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        if (str_starts_with($phone, '62')) {
            return $phone;
        }

        return '62' . $phone;
    }

    public function sendMessage(string $phone, string $message): array
    {
        $token = config('services.fonnte.token');

        if (empty($token) || $token === 'your_fonnte_token_here') {
            return [
                'success' => false,
                'message' => 'Token Fonnte belum dikonfigurasi. Cek FONNTE_API_TOKEN di file .env.',
                'raw' => [],
                'debug' => null,
            ];
        }

        $target = $this->normalizePhone($phone);
        $response = $this->fonnteClient->sendText($target, $message);

        if (!$response->isSuccess()) {
            Log::warning('Fonnte gagal kirim pesan', $response->toDebugArray());
        }

        return [
            'success' => $response->isSuccess(),
            'message' => $response->humanMessage(),
            'raw' => $response->json() ?? [],
            'debug' => $response->toDebugArray(),
        ];
    }
}