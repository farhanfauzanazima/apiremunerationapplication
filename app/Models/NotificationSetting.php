<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = ['whatsapp_template'];

    public static function defaultTemplate(): string
    {
        return "Halo {nama}, berikut rincian gaji kamu bulan {bulan} {tahun}. Akses link dibawah ini untuk mendownload PDF-nya:\n{link}";
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['whatsapp_template' => static::defaultTemplate()]);
    }

    public function render(array $values): string
    {
        $template = $this->whatsapp_template;

        foreach ($values as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return $template;
    }
}