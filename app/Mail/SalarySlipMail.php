<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalarySlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $slip,
        public string $type,
        public $pdf,
        public array $bulanIndo,
    ) {}

    public function envelope(): Envelope
    {
        $period = $this->slip->payrollPeriod;
        $bulan = $this->bulanIndo[$period->month] ?? $period->month;

        return new Envelope(
            subject: "Slip Gaji Bulan {$bulan} {$period->year} - " . $this->slip->employee->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.salary-slip',
            with: [
                'employee' => $this->slip->employee,
                'period' => $this->slip->payrollPeriod,
                'bulanIndo' => $this->bulanIndo,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdf->output(), 'slip-gaji-' . $this->slip->employee->name . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}