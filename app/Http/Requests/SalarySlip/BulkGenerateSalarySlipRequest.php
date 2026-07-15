<?php

namespace App\Http\Requests\SalarySlip;

use Illuminate\Foundation\Http\FormRequest;

class BulkGenerateSalarySlipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payroll_period_id' => ['required', 'exists:payroll_periods,id'],
            'branch_id' => ['required', 'exists:branches,id'],

            'tetap' => ['array'],
            'tetap.*.employee_id' => ['required', 'exists:employees,id'],
            'tetap.*.hari_kerja' => ['required', 'integer', 'min:0'],
            'tetap.*.alfa' => ['nullable', 'integer', 'min:0'],
            'tetap.*.izin' => ['nullable', 'integer', 'min:0'],
            'tetap.*.sakit' => ['nullable', 'integer', 'min:0'],
            'tetap.*.off' => ['nullable', 'integer', 'min:0'],
            'tetap.*.hari_shift' => ['nullable', 'integer', 'min:0'],
            'tetap.*.hari_full' => ['nullable', 'integer', 'min:0'],
            'tetap.*.hari_parsial' => ['nullable', 'integer', 'min:0'],
            'tetap.*.nominal_shift' => ['nullable', 'integer', 'min:0'],
            'tetap.*.nominal_full' => ['nullable', 'integer', 'min:0'],
            'tetap.*.nominal_parsial' => ['nullable', 'integer', 'min:0'],
            'tetap.*.jam_lembur' => ['nullable', 'integer', 'min:0', 'max:5'],
            'tetap.*.telat' => ['nullable', 'integer', 'min:0'],
            'tetap.*.tunjangan_jabatan' => ['nullable', 'integer', 'min:0'],
            'tetap.*.tunjangan_bpjs' => ['nullable', 'integer', 'min:0'],
            'tetap.*.bonus_omset' => ['nullable', 'integer', 'min:0'],
            'tetap.*.bonus_kinerja' => ['nullable', 'integer', 'min:0'],
            'tetap.*.cashbond' => ['nullable', 'integer', 'min:0'],
            'tetap.*.tabungan' => ['nullable', 'integer', 'min:0'],

            'partime' => ['array'],
            'partime.*.employee_id' => ['required', 'exists:employees,id'],
            'partime.*.hari_kerja' => ['nullable', 'integer', 'min:0'],
            'partime.*.full' => ['nullable', 'integer', 'min:0'],
            'partime.*.shift' => ['nullable', 'integer', 'min:0'],
            'partime.*.reguler' => ['nullable', 'integer', 'min:0'],
            'partime.*.sakit' => ['nullable', 'integer', 'min:0'],
            'partime.*.off' => ['nullable', 'integer', 'min:0'],
            'partime.*.tunjangan' => ['nullable', 'integer', 'min:0'],
            'partime.*.bonus' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('tetap', []) as $idx => $row) {
                $masuk = (int) ($row['hari_shift'] ?? 0) + (int) ($row['hari_full'] ?? 0) + (int) ($row['hari_parsial'] ?? 0);
                $telat = (int) ($row['telat'] ?? 0);

                if ($telat > $masuk) {
                    $validator->errors()->add(
                        "tetap.$idx.telat",
                        "Telat tidak boleh melebihi jumlah hari masuk ({$masuk} hari) untuk baris karyawan ke-" . ($idx + 1)
                    );
                }
            }
        });
    }
}
