<?php

namespace App\Console\Commands;

use App\Services\WhatsappService;
use Illuminate\Console\Command;

class TestSendWhatsapp extends Command
{
    protected $signature = 'whatsapp:test {phone} {--message=Ini adalah pesan uji coba dari sistem remunerasi}';
    protected $description = 'Uji kirim pesan WhatsApp langsung via Fonnte tanpa lewat alur distribusi gaji';

    public function handle(WhatsappService $whatsappService): int
    {
        $phone = $this->argument('phone');
        $message = $this->option('message');

        $this->info("Mengirim ke: {$phone} (dinormalisasi jadi: " . $whatsappService->normalizePhone($phone) . ')');

        $result = $whatsappService->sendMessage($phone, $message);

        $this->line('Sukses  : ' . ($result['success'] ? 'Ya' : 'Tidak'));
        $this->line('Pesan   : ' . $result['message']);
        $this->line('Raw     : ' . json_encode($result['raw']));

        return $result['success'] ? 0 : 1;
    }
}