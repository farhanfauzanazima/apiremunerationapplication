<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
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

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->asMultipart() // Fonnte mengharapkan multipart/form-data, sesuai contoh resmi mereka (CURLOPT_POSTFIELDS berupa array)
                ->attach('target', $this->normalizePhone($phone))
                ->attach('message', $message)
                ->post(config('services.fonnte.url'));

            $bodyRaw = $response->body();
            $json = $response->json();

            $debug = [
                'http_status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'body_raw' => mb_substr($bodyRaw, 0, 1000),
            ];

            if ($json === null) {
                Log::warning('Fonnte response bukan JSON valid', $debug);

                return [
                    'success' => false,
                    'message' => 'Respons dari Fonnte tidak dikenali (bukan JSON). Cek log untuk detail body mentah.',
                    'raw' => [],
                    'debug' => $debug,
                ];
            }

            $success = filter_var($json['status'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                'success' => $success,
                'message' => $json['reason'] ?? $json['detail'] ?? ($success ? 'Terkirim' : 'Gagal tanpa keterangan dari Fonnte'),
                'raw' => $json,
                'debug' => $debug,
            ];
        } catch (\Throwable $e) {
            Log::error('Fonnte WhatsApp send failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim WhatsApp: ' . $e->getMessage(),
                'raw' => [],
                'debug' => ['exception' => $e->getMessage()],
            ];
        }
    }
}