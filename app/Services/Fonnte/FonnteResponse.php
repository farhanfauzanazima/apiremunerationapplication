<?php

namespace App\Services\Fonnte;

class FonnteResponse
{
    public function __construct(
        public readonly int $httpStatus,
        public readonly string $rawBody,
        public readonly ?string $curlError,
        public readonly ?string $resolvedIp,
    ) {}

    public function isConnectionError(): bool
    {
        return !empty($this->curlError);
    }

    public function json(): ?array
    {
        $decoded = json_decode($this->rawBody, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function isSuccess(): bool
    {
        if ($this->isConnectionError()) {
            return false;
        }

        $json = $this->json();

        if ($json === null) {
            return false;
        }

        return filter_var($json['status'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function humanMessage(): string
    {
        if ($this->isConnectionError()) {
            return 'Gagal koneksi: ' . $this->curlError;
        }

        $json = $this->json();

        if ($json === null) {
            return "Respons tidak dikenali dari Fonnte (HTTP {$this->httpStatus}): " . mb_substr($this->rawBody, 0, 200);
        }

        return $json['reason'] ?? $json['detail'] ?? ($this->isSuccess() ? 'Terkirim' : 'Gagal tanpa keterangan');
    }

    public function toDebugArray(): array
    {
        return [
            'http_status' => $this->httpStatus,
            'resolved_ip' => $this->resolvedIp,
            'curl_error' => $this->curlError,
            'body_raw' => mb_substr($this->rawBody, 0, 1000),
        ];
    }
}