<?php


namespace App\Services\Fonnte;

class FonnteClient
{
    protected string $token;
    protected string $url;

    public function __construct(?string $token = null, ?string $url = null)
    {
        $this->token = $token ?? config('services.fonnte.token');
        $this->url = $url ?? config('services.fonnte.url');
    }

    /**
     * Kirim pesan teks ke satu nomor.
     * Memaksa resolusi IPv4 (CURL_IPRESOLVE_V4) karena terbukti PHP
     * secara default bisa memilih jalur IPv6 yang berbeda dari curl.exe,
     * menyebabkan 405 dari node/edge Fonnte tertentu meski kredensial benar.
     */
    public function sendText(string $target, string $message): FonnteResponse
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // <-- paksa IPv4
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; SistemRemunerasi/1.0)',
            CURLOPT_POSTFIELDS => [
                'target' => $target,
                'message' => $message,
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token,
            ],
        ]);

        $body = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $primaryIp = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
        $error = curl_error($curl);
        curl_close($curl);

        return new FonnteResponse(
            httpStatus: $httpStatus,
            rawBody: (string) $body,
            curlError: $error ?: null,
            resolvedIp: $primaryIp ?: null,
        );
    }
}