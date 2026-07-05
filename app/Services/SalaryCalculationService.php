<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\SalarySetting;
use Carbon\Carbon;

class SalaryCalculationService
{
    /**
     * Hitung slip gaji karyawan TETAP.
     * Masa kerja dihitung relatif terhadap akhir bulan periode (bukan "hari ini"),
     * supaya tetap akurat walau HR menginput slip untuk periode yang sudah lewat.
     */
    public function calculateTetap(Employee $employee, array $input, SalarySetting $setting, PayrollPeriod $period): array
    {
        $hariKerja = (int) ($input['hari_kerja'] ?? 0);
        $alfa = (int) ($input['alfa'] ?? 0);
        $izin = (int) ($input['izin'] ?? 0);
        $sakit = (int) ($input['sakit'] ?? 0);
        $off = (int) ($input['off'] ?? 0);
        $masuk = max(0, $hariKerja - $alfa - $izin - $sakit - $off);

        $lembur = (int) ($input['lembur'] ?? 0);
        $telat = (int) ($input['telat'] ?? 0);
        $harian = (int) ($input['harian'] ?? 0);

        $gajiPokok = $harian * $masuk;
        $tunjanganTransport = $setting->transport_tetap * $masuk;
        $tunjanganJabatan = (int) ($input['tunjangan_jabatan'] ?? 0);
        $tunjanganBpjs = (int) ($input['tunjangan_bpjs'] ?? 0);

        $referenceDate = Carbon::create($period->year, $period->month, 1)->endOfMonth();
        $tenureMonths = $employee->join_date->diffInMonths($referenceDate);
        $tunjanganMasaKerja = $tenureMonths >= $setting->tenure_months_threshold
            ? $setting->tenure_bonus_amount
            : 0;

        $bonusDisiplin = $setting->disiplin_bonus_tetap * $masuk;
        $bonusOmset = (int) ($input['bonus_omset'] ?? 0);
        $bonusKinerja = (int) ($input['bonus_kinerja'] ?? 0);

        $cashbond = (int) ($input['cashbond'] ?? 0);
        $tabungan = (int) ($input['tabungan'] ?? 0);

        $thp = ($lembur + $gajiPokok + $tunjanganTransport + $tunjanganJabatan + $tunjanganBpjs
                + $tunjanganMasaKerja + $bonusDisiplin + $bonusOmset + $bonusKinerja)
               - ($cashbond + $tabungan);

        $totalGaji = $thp + $tabungan + $cashbond;

        return [
            'employee_id' => $employee->id,
            'hari_kerja' => $hariKerja,
            'alfa' => $alfa,
            'izin' => $izin,
            'sakit' => $sakit,
            'off' => $off,
            'masuk' => $masuk,
            'lembur' => $lembur,
            'telat' => $telat,
            'harian' => $harian,
            'gaji_pokok' => $gajiPokok,
            'tunjangan_transport' => $tunjanganTransport,
            'tunjangan_jabatan' => $tunjanganJabatan,
            'tunjangan_bpjs' => $tunjanganBpjs,
            'tunjangan_masa_kerja' => $tunjanganMasaKerja,
            'bonus_disiplin' => $bonusDisiplin,
            'bonus_omset' => $bonusOmset,
            'bonus_kinerja' => $bonusKinerja,
            'cashbond' => $cashbond,
            'tabungan' => $tabungan,
            'thp' => $thp,
            'total_gaji' => $totalGaji,
        ];
    }

    /**
     * Hitung slip gaji TIM PARTIME.
     */
    public function calculatePartime(Employee $employee, array $input, SalarySetting $setting): array
    {
        $hariKerja = (int) ($input['hari_kerja'] ?? 0);
        $full = (int) ($input['full'] ?? 0);
        $shift = (int) ($input['shift'] ?? 0);
        $reguler = (int) ($input['reguler'] ?? 0);
        $sakit = (int) ($input['sakit'] ?? 0);
        $off = (int) ($input['off'] ?? 0);
        $tunjangan = (int) ($input['tunjangan'] ?? 0);

        $totalFull = $setting->rate_full * $full;
        $totalShift = $setting->rate_shift * $shift;
        $totalReguler = $setting->rate_reguler * $reguler;
        $totalTransport = $setting->transport_partime * ($full + $shift + $reguler);

        $bonus = (int) ($input['bonus'] ?? 0);

        $totalFee = $tunjangan + $totalFull + $totalShift + $totalReguler + $totalTransport + $bonus;

        return [
            'employee_id' => $employee->id,
            'hari_kerja' => $hariKerja,
            'full' => $full,
            'shift' => $shift,
            'reguler' => $reguler,
            'sakit' => $sakit,
            'off' => $off,
            'tunjangan' => $tunjangan,
            'total_full' => $totalFull,
            'total_shift' => $totalShift,
            'total_reguler' => $totalReguler,
            'total_transport' => $totalTransport,
            'bonus' => $bonus,
            'total_fee' => $totalFee,
        ];
    }
}