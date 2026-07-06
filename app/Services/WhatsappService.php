<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Ubah nomor 08xxx menjadi format internasional 62xxx yang dibutuhkan Fonnte.
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone); // buang karakter non-digit

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
        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.token'),
            ])->post(config('services.fonnte.url'), [
                'target' => $this->normalizePhone($phone),
                'message' => $message,
            ]);

            $json = $response->json() ?? [];

            return [
                'success' => $response->successful() && ($json['status'] ?? false) !== false,
                'message' => $json['reason'] ?? $json['detail'] ?? 'Terkirim',
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('Fonnte WhatsApp send failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim WhatsApp: ' . $e->getMessage(),
                'raw' => [],
            ];
        }
    }
}