<?php

namespace App\Mail;

use App\Models\SalarySlip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalarySlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SalarySlip $slip
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Slip Gaji - '
                . $this->slip->employee->full_name
                . ' - '
                . $this->slip->period->period_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.salary-slip',
            with: [
                'slip' => $this->slip,
            ],
        );
    }

    public function attachments(): array
    {
        // Generate PDF langsung dari DomPDF tanpa perlu file di storage
        $pdf = Pdf::loadView('pdf.salary-slip', ['slip' => $this->slip])
            ->setPaper('a4', 'portrait');

        $fileName = 'slip-gaji-'
            . ($this->slip->employee->employee_code ?? $this->slip->employee_id)
            . '-' . str_replace(' ', '-', strtolower($this->slip->period->period_name))
            . '.pdf';

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                $fileName
            )->withMime('application/pdf'),
        ];
    }
}