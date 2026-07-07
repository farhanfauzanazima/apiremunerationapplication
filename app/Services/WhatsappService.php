<?php

namespace App\Services;

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

    /**
     * Memakai PHP Streams native (bukan ekstensi cURL), untuk menghindari
     * bug/fingerprint TLS spesifik pada build cURL 8.7.0-DEV di lingkungan
     * lokal Windows/Laragon yang terbukti ditolak (405) oleh server Fonnte,
     * padahal token & format data sudah benar (terverifikasi via curl.exe manual).
     */
    public function sendMessage(string $phone, string $message): array
    {
        $token = config('services.fonnte.token');
        $url = config('services.fonnte.url');

        if (empty($token) || $token === 'your_fonnte_token_here') {
            return [
                'success' => false,
                'message' => 'Token Fonnte belum dikonfigurasi. Cek FONNTE_API_TOKEN di file .env.',
                'raw' => [],
                'debug' => null,
            ];
        }

        $target = $this->normalizePhone($phone);
        $boundary = '----FonnteBoundary' . bin2hex(random_bytes(16));

        $body = $this->buildMultipartBody($boundary, [
            'target' => $target,
            'message' => $message,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Authorization: ' . $token,
                    'Content-Type: multipart/form-data; boundary=' . $boundary,
                    'User-Agent: PHP-Stream-Client/1.0',
                ]),
                'content' => $body,
                'timeout' => 30,
                'ignore_errors' => true, // supaya body tetap terbaca meski status bukan 200
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $responseBody = @file_get_contents($url, false, $context);
        $responseHeaders = $http_response_header ?? [];

        $httpStatus = 0;
        if (!empty($responseHeaders[0]) && preg_match('#HTTP/\S+\s(\d{3})#', $responseHeaders[0], $m)) {
            $httpStatus = (int) $m[1];
        }

        $debug = [
            'http_status' => $httpStatus,
            'headers' => $responseHeaders,
            'body_raw' => mb_substr((string) $responseBody, 0, 1000),
        ];

        if ($responseBody === false) {
            $error = error_get_last()['message'] ?? 'Tidak diketahui';
            Log::error('Fonnte stream request failed: ' . $error);

            return [
                'success' => false,
                'message' => 'Gagal koneksi ke Fonnte (stream): ' . $error,
                'raw' => [],
                'debug' => $debug,
            ];
        }

        $json = json_decode($responseBody, true);

        if ($json === null) {
            Log::warning('Fonnte response bukan JSON valid (stream)', $debug);

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
    }

    protected function buildMultipartBody(string $boundary, array $fields): string
    {
        $body = '';

        foreach ($fields as $name => $value) {
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
            $body .= "{$value}\r\n";
        }

        $body .= "--{$boundary}--\r\n";

        return $body;
    }
}