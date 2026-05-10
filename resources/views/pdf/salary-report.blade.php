<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Gaji - {{ $period->period_name }}</title>
    <style>
        body { font-family: serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .header h1 { font-size: 18px; color: #2c3e50; margin: 0; }
        .header p { margin: 5px 0 0; color: #7f8c8d; font-size: 12px; }
        .summary { display: flex; gap: 10px; margin-bottom: 20px; }
        .summary-item { flex: 1; background: #f8f9fa; border: 1px solid #e0e0e0; padding: 10px; border-radius: 4px; text-align: center; }
        .summary-item .value { font-size: 14px; font-weight: bold; color: #2c3e50; }
        .summary-item .label { font-size: 10px; color: #7f8c8d; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #2c3e50; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .total-row td { background: #2c3e50; color: #fff; font-weight: bold; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; font-size: 10px; color: #7f8c8d; text-align: center; border-top: 1px solid #e0e0e0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENGGAJIAN</h1>
        <p>Periode: {{ $period->period_name }} |
           {{ \Carbon\Carbon::parse($period->start_date)->format('d M Y') }}
           s/d
           {{ \Carbon\Carbon::parse($period->end_date)->format('d M Y') }}
        </p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y, H:i') }} WIB</p>
    </div>

    {{-- Summary --}}
    <table>
        <tr>
            <td><strong>Total Karyawan</strong></td>
            <td>{{ $summary['total_employees'] }} orang</td>
            <td><strong>Total Gaji Bersih</strong></td>
            <td>Rp {{ number_format($summary['total_salary'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Gaji Pokok</strong></td>
            <td>Rp {{ number_format($summary['total_base_salary'], 0, ',', '.') }}</td>
            <td><strong>Total Tunjangan</strong></td>
            <td>Rp {{ number_format($summary['total_allowance'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Bonus</strong></td>
            <td>Rp {{ number_format($summary['total_bonus'], 0, ',', '.') }}</td>
            <td><strong>Total Potongan</strong></td>
            <td>Rp {{ number_format($summary['total_late_penalty'] + $summary['total_deduction'], 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- Detail per karyawan --}}
    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Karyawan</th>
                <th>Kategori</th>
                <th>Hari Masuk</th>
                <th>Terlambat</th>
                <th>Gaji Pokok</th>
                <th>Tunjangan</th>
                <th>Bonus</th>
                <th>Potongan</th>
                <th>Total Bersih</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($slips as $index => $slip)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $slip->employee->employee_code ?? '-' }}</td>
                <td>{{ $slip->employee->full_name ?? '-' }}</td>
                <td>{{ $slip->category->category_name ?? '-' }}</td>
                <td class="text-right">{{ $slip->total_working_days }}</td>
                <td class="text-right">{{ $slip->late_count }}</td>
                <td class="text-right">Rp {{ number_format($slip->base_salary_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($slip->allowance_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($slip->bonus, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($slip->late_penalty_amount + $slip->additional_deduction, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($slip->total_salary, 0, ',', '.') }}</strong></td>
                <td>{{ $slip->status === 'sent' ? 'Terkirim' : 'Draft' }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="10" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-right">Rp {{ number_format($summary['total_salary'], 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh sistem. &copy; {{ date('Y') }} {{ config('app.name') }}
    </div>
</body>
</html>