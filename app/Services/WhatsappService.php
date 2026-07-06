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
        $token = config('services.fonnte.token');

        if (empty($token) || $token === 'your_fonnte_token_here') {
            Log::warning('Fonnte token belum diisi dengan token asli.');

            return [
                'success' => false,
                'message' => 'Token Fonnte belum dikonfigurasi. Cek FONNTE_API_TOKEN di file .env.',
                'raw' => [],
            ];
        }

        try {
            $response = Http::withHeaders([
                    'Authorization' => $token,
                ])
                ->asForm() // Fonnte mengharapkan form data, bukan JSON
                ->post(config('services.fonnte.url'), [
                    'target' => $this->normalizePhone($phone),
                    'message' => $message,
                ]);

            $json = $response->json() ?? [];

            $success = filter_var($json['status'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                'success' => $success,
                'message' => $json['reason'] ?? $json['detail'] ?? ($success ? 'Terkirim' : 'Gagal tanpa keterangan dari Fonnte'),
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