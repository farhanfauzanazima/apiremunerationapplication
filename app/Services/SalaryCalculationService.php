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
        // Field kehadiran lama (hari_kerja, alfa, izin, sakit, off) sekarang HANYA
        // catatan/informasi tambahan — tidak lagi memengaruhi kalkulasi apapun.
        $hariKerja = (int) ($input['hari_kerja'] ?? 0);
        $alfa = (int) ($input['alfa'] ?? 0);
        $izin = (int) ($input['izin'] ?? 0);
        $sakit = (int) ($input['sakit'] ?? 0);
        $off = (int) ($input['off'] ?? 0);

        // Gaji Pokok sekarang dari kombinasi Shift + Full + Parsial (nominal per-slip, manual)
        $hariShift = (int) ($input['hari_shift'] ?? 0);
        $hariFull = (int) ($input['hari_full'] ?? 0);
        $hariParsial = (int) ($input['hari_parsial'] ?? 0);

        $nominalShift = (int) ($input['nominal_shift'] ?? 0);
        $nominalFull = (int) ($input['nominal_full'] ?? 0);
        $nominalParsial = (int) ($input['nominal_parsial'] ?? 0);

        $totalShift = $hariShift * $nominalShift;
        $totalFull = $hariFull * $nominalFull;
        $totalParsial = $hariParsial * $nominalParsial;

        $gajiPokok = $totalShift + $totalFull + $totalParsial;

        // Masuk sekarang = total hari Shift + Full + Parsial (bukan lagi hari_kerja - alfa - izin - sakit - off)
        $masuk = $hariShift + $hariFull + $hariParsial;

        // Telat tidak boleh melebihi Masuk — validasi utama ada di Form Request,
        // ini jaring pengaman kedua di level service.
        $telat = min((int) ($input['telat'] ?? 0), $masuk);

        // Lembur sekarang berbasis tabel tier flat dari Kategorikal, maksimal 5 jam.
        $jamLembur = min((int) ($input['jam_lembur'] ?? 0), 5);
        $totalLembur = match (true) {
            $jamLembur >= 5 => $setting->lembur_5_jam,
            $jamLembur >= 3 => $setting->lembur_3_4_jam,
            $jamLembur >= 1 => $setting->lembur_1_2_jam,
            default => 0,
        };

        $tunjanganTransport = $setting->transport_tetap * $masuk;
        $tunjanganJabatan = (int) ($input['tunjangan_jabatan'] ?? 0);
        $tunjanganBpjs = (int) ($input['tunjangan_bpjs'] ?? 0);

        $referenceDate = Carbon::create($period->year, $period->month, 1)->endOfMonth();
        $tenureMonths = $employee->join_date->diffInMonths($referenceDate);
        $tunjanganMasaKerja = $tenureMonths >= $setting->tenure_months_threshold
            ? $setting->tenure_bonus_amount
            : 0;

        // Bonus disiplin sekarang dikurangi jumlah hari telat
        $bonusDisiplin = max(0, $setting->disiplin_bonus_tetap * ($masuk - $telat));

        $bonusOmset = (int) ($input['bonus_omset'] ?? 0);
        $bonusKinerja = (int) ($input['bonus_kinerja'] ?? 0);

        $cashbond = (int) ($input['cashbond'] ?? 0);
        $tabungan = (int) ($input['tabungan'] ?? 0);

        // Rumus THP & Total Gaji TIDAK BERUBAH — hanya sumber gaji_pokok & lembur yang beda cara hitungnya
        $thp = ($totalLembur + $gajiPokok + $tunjanganTransport + $tunjanganJabatan + $tunjanganBpjs
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
            'hari_shift' => $hariShift,
            'hari_full' => $hariFull,
            'hari_parsial' => $hariParsial,
            'nominal_shift' => $nominalShift,
            'nominal_full' => $nominalFull,
            'nominal_parsial' => $nominalParsial,
            'total_shift' => $totalShift,
            'total_full' => $totalFull,
            'total_parsial' => $totalParsial,
            'masuk' => $masuk,
            'jam_lembur' => $jamLembur,
            'lembur' => $totalLembur,
            'telat' => $telat,
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