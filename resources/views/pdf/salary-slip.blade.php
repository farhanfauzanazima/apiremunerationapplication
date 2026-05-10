<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $slip->employee->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: serif;
            font-size: 12px;
            color: #333;
            background: #fff;
        }

        .container {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header .slip-title {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header .period-badge {
            display: inline-block;
            background: #2c3e50;
            color: #fff;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 11px;
            margin-top: 8px;
        }

        /* Employee Info */
        .employee-section {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .employee-section .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 6px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            color: #7f8c8d;
            padding: 4px 0;
            font-size: 11px;
        }

        .info-separator {
            display: table-cell;
            width: 5%;
            padding: 4px 0;
            font-size: 11px;
        }

        .info-value {
            display: table-cell;
            width: 60%;
            font-weight: bold;
            padding: 4px 0;
            font-size: 11px;
            color: #2c3e50;
        }

        /* Attendance Info */
        .attendance-section {
            margin-bottom: 20px;
        }

        .attendance-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .attendance-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            border: 1px solid #e0e0e0;
            padding: 10px;
            background: #fff;
        }

        .attendance-item .att-value {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
        }

        .attendance-item .att-label {
            font-size: 10px;
            color: #7f8c8d;
            margin-top: 3px;
            text-transform: uppercase;
        }

        /* Salary Table */
        .salary-section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .salary-table th {
            background: #2c3e50;
            color: #fff;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        .salary-table th:last-child {
            text-align: right;
        }

        .salary-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 11px;
        }

        .salary-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .salary-table tr.income td {
            color: #27ae60;
        }

        .salary-table tr.deduction td {
            color: #e74c3c;
        }

        .salary-table tr.subtotal td {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
            border-top: 1px solid #ddd;
        }

        /* Total */
        .total-section {
            background: #2c3e50;
            color: #fff;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }

        .total-label {
            display: table-cell;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .total-amount {
            display: table-cell;
            font-size: 18px;
            font-weight: bold;
            text-align: right;
        }

        /* Notes */
        .notes-section {
            background: #fffde7;
            border: 1px solid #f9a825;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 20px;
            font-size: 11px;
            color: #795548;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            width: 50%;
            font-size: 10px;
            color: #7f8c8d;
        }

        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-size: 10px;
            color: #7f8c8d;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft {
            background: #fff3e0;
            color: #e65100;
            border: 1px solid #ffb74d;
        }

        .status-sent {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #81c784;
        }

        .divider {
            border: none;
            border-top: 1px dashed #e0e0e0;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">

        {{-- Header --}}
        <div class="header">
            <div class="company-name">{{ config('app.name') }}</div>
            <div class="slip-title">Slip Gaji Karyawan</div>
            <div class="period-badge">{{ $slip->period->period_name }}</div>
        </div>

        {{-- Employee Info --}}
        <div class="employee-section">
            <div class="section-title">Informasi Karyawan</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Nama Karyawan</div>
                    <div class="info-separator">:</div>
                    <div class="info-value">{{ $slip->employee->full_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Kode Karyawan</div>
                    <div class="info-separator">:</div>
                    <div class="info-value">{{ $slip->employee->employee_code ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Kategori</div>
                    <div class="info-separator">:</div>
                    <div class="info-value">{{ $slip->category->category_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Periode</div>
                    <div class="info-separator">:</div>
                    <div class="info-value">
                        {{ \Carbon\Carbon::parse($slip->period->start_date)->format('d M Y') }}
                        s/d
                        {{ \Carbon\Carbon::parse($slip->period->end_date)->format('d M Y') }}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Cetak</div>
                    <div class="info-separator">:</div>
                    <div class="info-value">{{ \Carbon\Carbon::now()->format('d M Y, H:i') }} WIB</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-separator">:</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $slip->status }}">
                            {{ $slip->status === 'sent' ? 'Terkirim' : 'Draft' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance --}}
        <div class="attendance-section">
            <div class="section-title">Rekap Kehadiran</div>
            <div class="attendance-grid">
                <div class="attendance-item">
                    <div class="att-value">{{ $slip->total_working_days }}</div>
                    <div class="att-label">Hari Masuk</div>
                </div>
                <div class="attendance-item">
                    <div class="att-value" style="color: #e74c3c;">{{ $slip->late_count }}</div>
                    <div class="att-label">Keterlambatan</div>
                </div>
                <div class="attendance-item">
                    <div class="att-value" style="color: #27ae60;">
                        {{ $slip->bonus > 0 ? 'Ada' : '-' }}
                    </div>
                    <div class="att-label">Bonus</div>
                </div>
            </div>
        </div>

        <hr class="divider">

        {{-- Salary Breakdown --}}
        <div class="salary-section">
            <div class="section-title">Rincian Gaji</div>
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>Komponen</th>
                        <th>Keterangan</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Pendapatan --}}
                    <tr class="income">
                        <td>Gaji Pokok</td>
                        <td>{{ $slip->category->category_name }}</td>
                        <td>Rp {{ number_format($slip->base_salary_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="income">
                        <td>Tunjangan</td>
                        <td>Tunjangan Tetap</td>
                        <td>Rp {{ number_format($slip->allowance_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($slip->bonus > 0)
                    <tr class="income">
                        <td>Bonus</td>
                        <td>Bonus Tambahan</td>
                        <td>Rp {{ number_format($slip->bonus, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    {{-- Subtotal Pendapatan --}}
                    <tr class="subtotal">
                        <td colspan="2">Total Pendapatan</td>
                        <td>Rp {{ number_format($slip->base_salary_amount + $slip->allowance_amount + $slip->bonus, 0, ',', '.') }}</td>
                    </tr>

                    {{-- Potongan --}}
                    @if($slip->late_penalty_amount > 0)
                    <tr class="deduction">
                        <td>Potongan Terlambat</td>
                        <td>{{ $slip->late_count }}x × Rp {{ number_format($slip->category->late_penalty, 0, ',', '.') }}</td>
                        <td>- Rp {{ number_format($slip->late_penalty_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    @if($slip->additional_deduction > 0)
                    <tr class="deduction">
                        <td>Potongan Lainnya</td>
                        <td>Potongan Tambahan</td>
                        <td>- Rp {{ number_format($slip->additional_deduction, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    @if($slip->late_penalty_amount > 0 || $slip->additional_deduction > 0)
                    {{-- Subtotal Potongan --}}
                    <tr class="subtotal">
                        <td colspan="2">Total Potongan</td>
                        <td style="color: #e74c3c;">- Rp {{ number_format($slip->late_penalty_amount + $slip->additional_deduction, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Total --}}
        <div class="total-section">
            <div class="total-label">Total Gaji Bersih</div>
            <div class="total-amount">Rp {{ number_format($slip->total_salary, 0, ',', '.') }}</div>
        </div>

        {{-- Notes --}}
        @if($slip->notes)
        <div class="notes-section">
            <strong>Catatan:</strong> {{ $slip->notes }}
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-left">
                Dokumen ini digenerate secara otomatis oleh sistem.<br>
                Tidak memerlukan tanda tangan.
            </div>
            <div class="footer-right">
                Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}<br>
                ID Slip: #{{ str_pad($slip->id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

    </div>
</body>
</html>