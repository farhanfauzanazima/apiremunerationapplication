<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $slip->period->period_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 1px;
        }

        .header p {
            margin: 8px 0 0;
            font-size: 13px;
            opacity: 0.8;
        }

        .content {
            padding: 30px;
        }

        .greeting {
            font-size: 15px;
            color: #333;
            margin-bottom: 15px;
        }

        .message {
            font-size: 13px;
            color: #555;
            line-height: 1.7;
            margin-bottom: 25px;
        }

        .summary-box {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .summary-box h3 {
            font-size: 13px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #e0e0e0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            color: #333;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row .label {
            color: #7f8c8d;
        }

        .summary-row .value {
            font-weight: bold;
            color: #2c3e50;
        }

        .total-box {
            background: #2c3e50;
            color: #fff;
            padding: 15px 20px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .total-box .total-label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .total-box .total-amount {
            font-size: 20px;
            font-weight: bold;
        }

        .note {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 12px 15px;
            font-size: 12px;
            color: #795548;
            border-radius: 0 4px 4px 0;
            margin-bottom: 25px;
        }

        .footer {
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            padding: 20px 30px;
            text-align: center;
            font-size: 11px;
            color: #7f8c8d;
        }

        .footer a {
            color: #2c3e50;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        {{-- Header --}}
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Slip Gaji Periode {{ $slip->period->period_name }}</p>
        </div>

        {{-- Content --}}
        <div class="content">

            <p class="greeting">
                Yth. <strong>{{ $slip->employee->full_name }}</strong>,
            </p>

            <p class="message">
                Berikut adalah slip gaji Anda untuk periode
                <strong>{{ $slip->period->period_name }}</strong>
                ({{ \Carbon\Carbon::parse($slip->period->start_date)->format('d M Y') }}
                s/d
                {{ \Carbon\Carbon::parse($slip->period->end_date)->format('d M Y') }}).
                <br><br>
                Slip gaji ini dikirimkan secara otomatis oleh sistem. Jika terdapat
                pertanyaan mengenai rincian gaji Anda, silakan hubungi admin atau kepala toko.
            </p>

            {{-- Ringkasan Gaji --}}
            <div class="summary-box">
                <h3>Rincian Gaji</h3>

                <div class="summary-row">
                    <span class="label">Kategori</span>
                    <span class="value">{{ $slip->category->category_name }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Hari Masuk Kerja</span>
                    <span class="value">{{ $slip->total_working_days }} hari</span>
                </div>
                <div class="summary-row">
                    <span class="label">Gaji Pokok</span>
                    <span class="value">Rp {{ number_format($slip->base_salary_amount, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Tunjangan</span>
                    <span class="value">Rp {{ number_format($slip->allowance_amount, 0, ',', '.') }}</span>
                </div>
                @if($slip->bonus > 0)
                <div class="summary-row">
                    <span class="label">Bonus</span>
                    <span class="value">Rp {{ number_format($slip->bonus, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($slip->late_penalty_amount > 0)
                <div class="summary-row">
                    <span class="label">Potongan Terlambat ({{ $slip->late_count }}x)</span>
                    <span class="value" style="color: #e74c3c;">
                        - Rp {{ number_format($slip->late_penalty_amount, 0, ',', '.') }}
                    </span>
                </div>
                @endif
                @if($slip->additional_deduction > 0)
                <div class="summary-row">
                    <span class="label">Potongan Lainnya</span>
                    <span class="value" style="color: #e74c3c;">
                        - Rp {{ number_format($slip->additional_deduction, 0, ',', '.') }}
                    </span>
                </div>
                @endif
            </div>

            {{-- Total --}}
            <div class="total-box">
                <span class="total-label">Total Gaji Bersih</span>
                <span class="total-amount">
                    Rp {{ number_format($slip->total_salary, 0, ',', '.') }}
                </span>
            </div>

            {{-- Catatan --}}
            <div class="note">
                📎 Slip gaji dalam format PDF terlampir pada email ini.
                Silakan simpan sebagai arsip pribadi Anda.
            </div>

        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            <p style="margin-top: 8px;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>

    </div>
</body>
</html>