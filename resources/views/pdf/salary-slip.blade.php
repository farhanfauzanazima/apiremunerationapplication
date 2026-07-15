<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* ============================================================
           TEMPLATE: SLIP GAJI INDIVIDUAL (satu karyawan per PDF)
           UKURAN KERTAS: A4 PORTRAIT — diatur di app/Services/PDFService.php,
           method renderSalarySlip(), baris setPaper('a4', 'portrait').
           JANGAN ubah ukuran kertas dari file blade ini — ubah di PDFService.php.
           ============================================================ */
        @page { margin: 30px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; margin-bottom: -20px; } /* Jarak teks dan logo */
        .header img { height: 170px; /* naikkan angka ini jika logo masih kurang besar, turunkan jika terlalu besar/kepenuhan */
            margin-bottom: 4px; }
        .company-address { text-align: center; font-size: 13px; line-height: 1.5; }
        .divider { border-top: 3px double #333; margin: 10px 0 14px; }
        .title { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 2px; }
        .subtitle { text-align: center; font-weight: bold; font-size: 13px; margin-bottom: 18px; }
        table.info { width: 100%; margin-bottom: 16px; }
        table.info td { padding: 2px 0; vertical-align: top; }
        table.info td.label { width: 130px; color: #333; }
        table.info td.sep { width: 15px; }
        .section-label { font-weight: bold; margin-bottom: 6px; }
        table.rincian { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.rincian td { padding: 4px 2px; }
        table.rincian td.label { width: 200px; }
        table.rincian td.sep { width: 15px; }
        table.rincian td.value { text-align: right; }
        .row-green { background-color: #cfe8d5; }
        .row-red { background-color: #f3d4d4; }
        table.total { width: 100%; margin-top: 10px; border-collapse: collapse; }
        table.total td { padding: 8px 10px; font-weight: bold; background-color: #f7c948; }
        table.total td.total-value { text-align: right; }
        .signature { margin-top: 30px; }
        .signature-name { margin-top: 55px; font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>

    <div class="header">
        @if(file_exists($logoPath))
            <img src="{{ $logoPath }}">
        @else
            <div style="font-weight:bold; font-size:16px;">{{ $branch->name }}</div>
        @endif
    </div>

    <div class="company-address">
        {{ $branch->address }}<br>
        @if($branch->phone)
            Telp : {{ $branch->phone }}
        @endif
    </div>

    <div class="divider"></div>

    <div class="title">SLIP GAJI</div>
    <div class="subtitle">BULAN {{ strtoupper($bulanIndo[$period->month]) }} {{ $period->year }}</div>

    <table class="info">
        <tr>
            <td class="label">Tanggal</td><td class="sep">:</td>
            <td>{{ now()->translatedFormat('d-M-y') }}</td>
        </tr>
        <tr>
            <td class="label">Nama</td><td class="sep">:</td>
            <td><strong>{{ strtoupper($employee->name) }}</strong></td>
        </tr>
        <tr>
            <td class="label">Jabatan</td><td class="sep">:</td>
            <td>{{ strtoupper($employee->position->name ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">Outlet Cabang</td><td class="sep">:</td>
            <td>{{ strtoupper($branch->name) }}</td>
        </tr>
    </table>

    <div class="section-label">Rincian Gaji</div>

    @if($type === 'tetap')
    <table class="rincian">
        <tr><td class="label">Total Shift</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->total_shift,0,',','.') }}</td></tr>
        <tr><td class="label">Total Full</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->total_full,0,',','.') }}</td></tr>
        <tr><td class="label">Total Parsial</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->total_parsial,0,',','.') }}</td></tr>
        <tr><td class="label"><strong>Gaji Pokok</strong></td><td class="sep">:</td><td>Rp</td><td class="value"><strong>{{ number_format($slip->gaji_pokok,0,',','.') }}</strong></td></tr>
        <tr><td class="label">Tunjangan Jabatan</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->tunjangan_jabatan,0,',','.') }}</td></tr>
        <tr><td class="label">Tunjangan Transport</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->tunjangan_transport,0,',','.') }}</td></tr>
        <tr><td class="label">Tunjangan Masa Kerja</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->tunjangan_masa_kerja,0,',','.') }}</td></tr>
        <tr><td class="label">BPJS</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->tunjangan_bpjs,0,',','.') }}</td></tr>
        <tr><td class="label">Bonus Disiplin</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->bonus_disiplin,0,',','.') }}</td></tr>
        <tr><td class="label">Bonus Omset</td><td class="sep">:</td><td>Rp</td><td class="value">{{ $slip->bonus_omset > 0 ? number_format($slip->bonus_omset,0,',','.') : '-' }}</td></tr>
        <tr><td class="label">Bonus Kinerja</td><td class="sep">:</td><td>Rp</td><td class="value">{{ $slip->bonus_kinerja > 0 ? number_format($slip->bonus_kinerja,0,',','.') : '-' }}</td></tr>
        <tr><td class="label">Lembur</td><td class="sep">:</td><td>Rp</td><td class="value">{{ $slip->lembur > 0 ? number_format($slip->lembur,0,',','.') : '-' }}</td></tr>
        <tr class="row-green"><td class="label">Tabungan Karyawan</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->tabungan,0,',','.') }}</td></tr>
        <tr class="row-red"><td class="label">Kasbon</td><td class="sep">:</td><td>Rp</td><td class="value">{{ $slip->cashbond > 0 ? number_format($slip->cashbond,0,',','.') : '-' }}</td></tr>
    </table>

    <table class="total">
        <tr><td>TAKE HOME PAY</td><td width="30" style="text-align:center">=</td><td class="total-value">Rp{{ number_format($slip->thp,0,',','.') }}</td></tr>
        <tr><td>TOTAL</td><td width="30" style="text-align:center">=</td><td class="total-value">Rp{{ number_format($slip->total_gaji,0,',','.') }}</td></tr>
    </table>
    @else
    <table class="rincian">
        <tr><td class="label">Tunjangan</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->tunjangan,0,',','.') }}</td></tr>
        <tr><td class="label">Total Full ({{ $slip->full }} hari)</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->total_full,0,',','.') }}</td></tr>
        <tr><td class="label">Total Shift ({{ $slip->shift }} hari)</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->total_shift,0,',','.') }}</td></tr>
        <tr><td class="label">Total Reguler ({{ $slip->reguler }} hari)</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->total_reguler,0,',','.') }}</td></tr>
        <tr><td class="label">Total Transport</td><td class="sep">:</td><td>Rp</td><td class="value">{{ number_format($slip->total_transport,0,',','.') }}</td></tr>
        <tr><td class="label">Bonus</td><td class="sep">:</td><td>Rp</td><td class="value">{{ $slip->bonus > 0 ? number_format($slip->bonus,0,',','.') : '-' }}</td></tr>
    </table>

    <table class="total">
        <tr><td>TOTAL FEE</td><td width="30" style="text-align:center">=</td><td class="total-value">Rp{{ number_format($slip->total_fee,0,',','.') }}</td></tr>
    </table>
    @endif

    <div class="signature">
        HR Dept,
        <div class="signature-name">{{ strtoupper($signedBy) }}</div>
    </div>

</body>
</html>