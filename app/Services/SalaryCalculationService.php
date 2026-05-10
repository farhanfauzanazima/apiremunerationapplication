<?php

namespace App\Services;

use App\Models\SalaryCategory;
use App\Models\SalarySlip;

class SalaryCalculationService
{
    /**
     * Hitung total gaji berdasarkan komponen yang diinput
     * 
     * Rumus:
     * Total = Gaji Pokok + Tunjangan + Bonus
     *       - (Jumlah Terlambat × Potongan per Keterlambatan)
     *       - Potongan Tambahan
     */
    public function calculate(SalaryCategory $category, array $data): array
    {
        $baseSalary          = (float) $category->base_salary;
        $allowance           = (float) $category->allowance;
        $latePenaltyPerCount = (float) $category->late_penalty;

        $lateCount           = (int) ($data['late_count'] ?? 0);
        $bonus               = (float) ($data['bonus'] ?? 0);
        $additionalDeduction = (float) ($data['additional_deduction'] ?? 0);

        // Hitung potongan keterlambatan
        $latePenaltyAmount = $lateCount * $latePenaltyPerCount;

        // Hitung total gaji
        $totalSalary = $baseSalary
            + $allowance
            + $bonus
            - $latePenaltyAmount
            - $additionalDeduction;

        // Total tidak boleh negatif
        $totalSalary = max(0, $totalSalary);

        return [
            'base_salary_amount'  => $baseSalary,
            'allowance_amount'    => $allowance,
            'late_penalty_amount' => $latePenaltyAmount,
            'total_salary'        => $totalSalary,
        ];
    }

    /**
     * Buat atau update slip gaji
     */
    public function createOrUpdateSlip(array $data, int $createdBy): SalarySlip
    {
        $category = SalaryCategory::findOrFail($data['category_id']);

        // Hitung komponen gaji
        $calculation = $this->calculate($category, $data);

        // Buat atau update slip (berdasarkan period_id + employee_id)
        $slip = SalarySlip::updateOrCreate(
            [
                'period_id'   => $data['period_id'],
                'employee_id' => $data['employee_id'],
            ],
            [
                'category_id'          => $data['category_id'],
                'total_working_days'   => $data['total_working_days'] ?? 0,
                'late_count'           => $data['late_count'] ?? 0,
                'bonus'                => $data['bonus'] ?? 0,
                'additional_deduction' => $data['additional_deduction'] ?? 0,
                'notes'                => $data['notes'] ?? null,
                'base_salary_amount'   => $calculation['base_salary_amount'],
                'allowance_amount'     => $calculation['allowance_amount'],
                'late_penalty_amount'  => $calculation['late_penalty_amount'],
                'total_salary'         => $calculation['total_salary'],
                'status'               => 'draft',
                'created_by'           => $createdBy,
            ]
        );

        return $slip;
    }
}