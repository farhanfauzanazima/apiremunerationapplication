<?php

namespace App\Http\Requests\SalarySlip;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalarySlipTetapRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'hari_kerja' => ['required', 'integer', 'min:0'],
            'alfa' => ['nullable', 'integer', 'min:0'],
            'izin' => ['nullable', 'integer', 'min:0'],
            'sakit' => ['nullable', 'integer', 'min:0'],
            'off' => ['nullable', 'integer', 'min:0'],
            'hari_shift' => ['nullable', 'integer', 'min:0'],
            'hari_full' => ['nullable', 'integer', 'min:0'],
            'hari_parsial' => ['nullable', 'integer', 'min:0'],
            'nominal_shift' => ['nullable', 'integer', 'min:0'],
            'nominal_full' => ['nullable', 'integer', 'min:0'],
            'nominal_parsial' => ['nullable', 'integer', 'min:0'],
            'jam_lembur' => ['nullable', 'integer', 'min:0', 'max:5'],
            'telat' => ['nullable', 'integer', 'min:0'],
            'tunjangan_jabatan' => ['nullable', 'integer', 'min:0'],
            'tunjangan_bpjs' => ['nullable', 'integer', 'min:0'],
            'bonus_omset' => ['nullable', 'integer', 'min:0'],
            'bonus_kinerja' => ['nullable', 'integer', 'min:0'],
            'cashbond' => ['nullable', 'integer', 'min:0'],
            'tabungan' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $masuk = (int) $this->input('hari_shift', 0) + (int) $this->input('hari_full', 0) + (int) $this->input('hari_parsial', 0);
            $telat = (int) $this->input('telat', 0);

            if ($telat > $masuk) {
                $validator->errors()->add('telat', "Telat tidak boleh melebihi jumlah hari masuk ({$masuk} hari).");
            }
        });
    }
}