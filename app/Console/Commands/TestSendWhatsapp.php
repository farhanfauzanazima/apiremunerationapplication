<?php

namespace App\Console\Commands;

use App\Services\WhatsappService;
use Illuminate\Console\Command;

class TestSendWhatsapp extends Command
{
    protected $signature = 'whatsapp:test {phone} {--message=Ini adalah pesan uji coba dari sistem remunerasi}';
    protected $description = 'Uji kirim pesan WhatsApp langsung via Fonnte tanpa lewat alur distribusi gaji, dengan debug lengkap';

    public function handle(WhatsappService $whatsappService): int
    {
        $phone = $this->argument('phone');
        $message = $this->option('message');

        $this->info("Nomor asli   : {$phone}");
        $this->info("Dinormalisasi: " . $whatsappService->normalizePhone($phone));
        $this->newLine();

        $result = $whatsappService->sendMessage($phone, $message);

        $this->line('Sukses       : ' . ($result['success'] ? 'YA' : 'TIDAK'));
        $this->line('Pesan        : ' . $result['message']);
        $this->newLine();

        if (!empty($result['debug'])) {
            $this->line('--- DEBUG ---');
            foreach ($result['debug'] as $key => $value) {
                $this->line("{$key}: " . (is_string($value) ? $value : json_encode($value)));
            }
        }

        $this->newLine();
        $this->line('Raw response : ' . json_encode($result['raw']));

        return $result['success'] ? 0 : 1;
    }
}