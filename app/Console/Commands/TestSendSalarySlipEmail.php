<?php

namespace App\Console\Commands;

use App\Models\SalarySlipPartime;
use App\Models\SalarySlipTetap;
use App\Services\DistributionService;
use Illuminate\Console\Command;

class TestSendSalarySlipEmail extends Command
{
    protected $signature = 'slip:test-email {payroll_period_id} {--type=tetap} {--employee=*} {--all}';
    protected $description = 'Uji kirim email slip gaji langsung dari backend (tanpa lewat HTTP) untuk debug performa/timeout';

    public function handle(DistributionService $distributionService): int
    {
        $periodId = $this->argument('payroll_period_id');
        $type = $this->option('type');
        $employeeIds = $this->option('employee');
        $all = $this->option('all');

        $slipClass = $type === 'tetap' ? SalarySlipTetap::class : SalarySlipPartime::class;
        $query = $slipClass::where('payroll_period_id', $periodId)->with('employee');

        if (!$all) {
            if (empty($employeeIds)) {
                $this->error('Sertakan --employee=ID (bisa diulang) atau gunakan --all');
                return 1;
            }
            $query->whereIn('employee_id', $employeeIds);
        }

        $slips = $query->get();

        if ($slips->isEmpty()) {
            $this->error('Tidak ada slip ditemukan untuk kriteria ini. Pastikan payroll_period_id benar dan karyawan sudah punya slip di periode itu (lihat Sesi 7).');
            return 1;
        }

        $this->info("Menguji kirim email untuk {$slips->count()} slip (dieksekusi langsung, tidak lewat queue)...");
        $this->newLine();

        foreach ($slips as $slip) {
            $email = $slip->employee->email ?: '(kosong — akan gagal)';
            $this->line("→ {$slip->employee->name} ({$email})");

            $start = microtime(true);
            $distribution = $distributionService->sendEmail($slip, $type, 'HR Dept (Test)');
            $duration = round(microtime(true) - $start, 2);

            $this->line("   Status : {$distribution->status}");
            $this->line("   Durasi : {$duration} detik");
            $this->line("   Catatan: " . ($distribution->note ?? '-'));
            $this->newLine();
        }

        $this->info('Selesai.');
        return 0;
    }
}