<?php

namespace App\Jobs;

use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;
use App\Services\DistributionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSalarySlipWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 30;

    public function __construct(
        public string $slipType,
        public int $slipId,
        public ?int $sentBy = null,
    ) {}

    public function handle(DistributionService $distributionService): void
    {
        $slip = $this->slipType === 'tetap'
            ? SalarySlipTetap::with(['employee.branch', 'payrollPeriod'])->find($this->slipId)
            : SalarySlipPartime::with(['employee.branch', 'payrollPeriod'])->find($this->slipId);

        if (!$slip) {
            return;
        }

        $distributionService->sendWhatsapp($slip, $this->slipType, $this->sentBy);
    }
}