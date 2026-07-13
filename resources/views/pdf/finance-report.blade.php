<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        /* ============================================================
       NILAI-NILAI FONT & SPASI YANG BISA ANDA UBAH SENDIRI:

       1. body { font-size }        -> ukuran dasar teks umum (di luar tabel)
       2. table.data { font-size }  -> ukuran huruf ISI SEL tabel (angka/teks data)
       3. table.data th { font-size } -> ukuran huruf HEADER kolom tabel
       4. table.data th/td { padding } -> jarak dalam sel (naikkan sedikit kalau
          mau lebih lega, turunkan kalau ingin lebih rapat)
       5. line-height di th/td      -> jarak antar baris teks kalau ada teks panjang
          yang terpaksa turun ke baris ke-2 (misal nama panjang/nama bank panjang)

       Urutan yang saya sarankan kalau masih kurang nyaman dibaca:
       - Naikkan dulu table.data font-size (misal dari 6.5px -> 7px)
       - Kalau jadi kepotong lagi, turunkan padding sedikit sebagai kompensasi
       - Kalau masih kurang lega, pertimbangkan ukuran kertas custom lebih besar
         dari A3 (lihat catatan di PDFService.php)
       ============================================================ */
        @page {
            margin: 10px 12px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 4px;
        }

        .header img {
            height: 55px;
        }

        .company-address {
            text-align: center;
            font-size: 9px;
            line-height: 1.4;
        }

        .divider {
            border-top: 2px double #333;
            margin: 6px 0 10px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .branch-name {
            text-align: center;
            font-size: 10px;
            margin-bottom: 12px;
            color: #555;
        }

        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin: 12px 0 6px;
            padding: 4px 6px;
            background: #f7c948;
        }

        table.data {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.data th,
        table.data td {
            border: 1px solid #999;
            padding: 2px 2px;
            /* <-- jarak dalam sel, kecilkan/besarkan di sini */
            text-align: right;
            overflow-wrap: break-word;
            word-break: break-word;
            line-height: 1.3;
            /* <-- jarak antar baris kalau teks turun 2 baris */
        }

        table.data {
            font-size: 5px;
        }

        /* <-- UKURAN FONT ISI SEL, ini yang paling penting diubah */
        table.data th {
            font-size: 5px;
            background: #eee;
            text-align: center;
        }

        /* <-- UKURAN FONT HEADER */
        table.data td.text-left {
            text-align: left;
        }

        table.data td.center {
            text-align: center;
        }

        table.summary {
            width: 55%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        table.summary td {
            padding: 5px 8px;
            font-weight: bold;
            background: #f7c948;
            border: 1px solid #d9a900;
            font-size: 9px;
        }

        table.summary td.value {
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="header">
        @if (file_exists($logoPath))
            <img src="{{ $logoPath }}">
        @endif
    </div>
    <div class="company-address">
        {{ $branch->address }} @if ($branch->phone)
            &nbsp;|&nbsp; Telp: {{ $branch->phone }}
        @endif
    </div>

    <div class="divider"></div>

    <div class="title">LAPORAN HR UNTUK KEUANGAN</div>
    <div class="subtitle">PERIODE {{ strtoupper($bulanIndo[$period->month] ?? $period->month) }} {{ $period->year }}
    </div>
    <div class="branch-name">Cabang: {{ strtoupper($branch->name) }}</div>

    {{-- ============== KARYAWAN TETAP (28 kolom) ============== --}}
    <div class="section-title">KARYAWAN TETAP ({{ $totals['total_karyawan_tetap'] }} Orang)</div>
    <table class="data">
        <colgroup>
            <col style="width:1.5%"> {{-- No --}}
            <col style="width:7%"> {{-- Nama --}}
            <col style="width:3.5%"> {{-- Bergabung --}}
            <col style="width:5%"> {{-- Jabatan --}}
            <col style="width:2.3%"> {{-- Hari Kerja --}}
            <col style="width:2.3%"> {{-- Alfa --}}
            <col style="width:2.3%"> {{-- Izin --}}
            <col style="width:2.3%"> {{-- Sakit --}}
            <col style="width:2.3%"> {{-- Off --}}
            <col style="width:2.3%"> {{-- Masuk --}}
            <col style="width:3.85%"> {{-- Lembur --}}
            <col style="width:2.3%"> {{-- Telat --}}
            <col style="width:3.85%"> {{-- Harian --}}
            <col style="width:3.85%"> {{-- Gaji Pokok --}}
            <col style="width:3.85%"> {{-- Transport --}}
            <col style="width:3.85%"> {{-- T.Jabatan --}}
            <col style="width:3.85%"> {{-- BPJS --}}
            <col style="width:3.85%"> {{-- T.Masa Kerja --}}
            <col style="width:3.85%"> {{-- B.Disiplin --}}
            <col style="width:3.85%"> {{-- B.Omset --}}
            <col style="width:3.85%"> {{-- B.Kinerja --}}
            <col style="width:3.85%"> {{-- Cashbond --}}
            <col style="width:3.85%"> {{-- Tabungan --}}
            <col style="width:3.85%"> {{-- THP --}}
            <col style="width:3.85%"> {{-- Total --}}
            <col style="width:4.5%"> {{-- No Rek --}}
            <col style="width:5%"> {{-- Atas Nama --}}
            <col style="width:3.5%"> {{-- Bank --}}
        </colgroup>
        <thead>
            <tr>
                <th>No</th>
                <th style="text-align:left;">Nama</th>
                <th>Bergabung</th>
                <th style="text-align:left;">Jabatan</th>
                <th>Hari Kerja</th>
                <th>Alfa</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Off</th>
                <th>Masuk</th>
                <th>Lembur</th>
                <th>Telat</th>
                <th>Harian</th>
                <th>Gaji Pokok</th>
                <th>Transport</th>
                <th>T.Jabatan</th>
                <th>BPJS</th>
                <th>T.Masa Kerja</th>
                <th>B.Disiplin</th>
                <th>B.Omset</th>
                <th>B.Kinerja</th>
                <th>Cashbond</th>
                <th>Tabungan</th>
                <th>THP</th>
                <th>Total</th>
                <th style="text-align:left;">No Rek</th>
                <th style="text-align:left;">Atas Nama</th>
                <th style="text-align:left;">Bank</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tetap as $i => $slip)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="text-left">{{ $slip->employee->name }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($slip->employee->join_date)->format('d-m-Y') }}</td>
                    <td class="text-left">{{ $slip->employee->position->name ?? '-' }}</td>
                    <td>{{ $slip->hari_kerja }}</td>
                    <td>{{ $slip->alfa }}</td>
                    <td>{{ $slip->izin }}</td>
                    <td>{{ $slip->sakit }}</td>
                    <td>{{ $slip->off }}</td>
                    <td>{{ $slip->masuk }}</td>
                    <td>{{ number_format($slip->lembur, 0, ',', '.') }}</td>
                    <td>{{ $slip->telat }}</td>
                    <td>{{ number_format($slip->harian, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->tunjangan_transport, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->tunjangan_jabatan, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->tunjangan_bpjs, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->tunjangan_masa_kerja, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->bonus_disiplin, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->bonus_omset, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->bonus_kinerja, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->cashbond, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->tabungan, 0, ',', '.') }}</td>
                    <td><strong>{{ number_format($slip->thp, 0, ',', '.') }}</strong></td>
                    <td><strong>{{ number_format($slip->total_gaji, 0, ',', '.') }}</strong></td>
                    <td class="text-left">{{ $slip->employee->bank_account_number ?? '-' }}</td>
                    <td class="text-left">{{ $slip->employee->bank_account_name ?? '-' }}</td>
                    <td class="text-left">{{ $slip->employee->bank_name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="28" class="center">Tidak ada data karyawan tetap untuk periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ============== TIM PARTIME (20 kolom) ============== --}}
    <div class="section-title">TIM PARTIME ({{ $totals['total_karyawan_partime'] }} Orang)</div>
    <table class="data">
        <colgroup>
            <col style="width:2%"> {{-- No --}}
            <col style="width:10%"> {{-- Nama --}}
            <col style="width:5%"> {{-- Bergabung --}}
            <col style="width:8%"> {{-- Jabatan --}}
            <col style="width:3.5%"> {{-- Hari Kerja --}}
            <col style="width:3.5%"> {{-- Full --}}
            <col style="width:3.5%"> {{-- Shift --}}
            <col style="width:3.5%"> {{-- Reguler --}}
            <col style="width:3.5%"> {{-- Sakit --}}
            <col style="width:3.5%"> {{-- Off --}}
            <col style="width:5%"> {{-- Tunjangan --}}
            <col style="width:5%"> {{-- Total Full --}}
            <col style="width:5%"> {{-- Total Shift --}}
            <col style="width:5%"> {{-- Total Reguler --}}
            <col style="width:5%"> {{-- Total Transport --}}
            <col style="width:5%"> {{-- Bonus --}}
            <col style="width:5.5%"> {{-- Total Fee --}}
            <col style="width:6%"> {{-- No Rek --}}
            <col style="width:7%"> {{-- Atas Nama --}}
            <col style="width:5.5%"> {{-- Bank --}}
        </colgroup>
        <thead>
            <tr>
                <th>No</th>
                <th style="text-align:left;">Nama</th>
                <th>Bergabung</th>
                <th style="text-align:left;">Jabatan</th>
                <th>Hari Kerja</th>
                <th>Full</th>
                <th>Shift</th>
                <th>Reguler</th>
                <th>Sakit</th>
                <th>Off</th>
                <th>Tunjangan</th>
                <th>Total Full</th>
                <th>Total Shift</th>
                <th>Total Reguler</th>
                <th>Total Transport</th>
                <th>Bonus</th>
                <th>Total Fee</th>
                <th style="text-align:left;">No Rek</th>
                <th style="text-align:left;">Atas Nama</th>
                <th style="text-align:left;">Bank</th>
            </tr>
        </thead>
        <tbody>
            @forelse($partime as $i => $slip)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="text-left">{{ $slip->employee->name }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($slip->employee->join_date)->format('d-m-Y') }}</td>
                    <td class="text-left">{{ $slip->employee->position->name ?? '-' }}</td>
                    <td>{{ $slip->hari_kerja }}</td>
                    <td>{{ $slip->full }}</td>
                    <td>{{ $slip->shift }}</td>
                    <td>{{ $slip->reguler }}</td>
                    <td>{{ $slip->sakit }}</td>
                    <td>{{ $slip->off }}</td>
                    <td>{{ number_format($slip->tunjangan, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->total_full, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->total_shift, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->total_reguler, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->total_transport, 0, ',', '.') }}</td>
                    <td>{{ number_format($slip->bonus, 0, ',', '.') }}</td>
                    <td><strong>{{ number_format($slip->total_fee, 0, ',', '.') }}</strong></td>
                    <td class="text-left">{{ $slip->employee->bank_account_number ?? '-' }}</td>
                    <td class="text-left">{{ $slip->employee->bank_account_name ?? '-' }}</td>
                    <td class="text-left">{{ $slip->employee->bank_name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="20" class="center">Tidak ada data tim partime untuk periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ============== RINGKASAN TOTAL ============== --}}
    <table class="summary">
        <tr>
            <td>Total Karyawan Tetap</td>
            <td class="value">{{ $totals['total_karyawan_tetap'] }} orang</td>
        </tr>
        <tr>
            <td>Total Tim Partime</td>
            <td class="value">{{ $totals['total_karyawan_partime'] }} orang</td>
        </tr>
        <tr>
            <td>Total Tabungan Karyawan (Tetap)</td>
            <td class="value">Rp{{ number_format($totals['total_tabungan'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Gaji Karyawan Tetap</td>
            <td class="value">Rp{{ number_format($totals['total_gaji_tetap'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Fee Tim Partime</td>
            <td class="value">Rp{{ number_format($totals['total_fee_partime'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>TOTAL KESELURUHAN</td>
            <td class="value">Rp{{ number_format($totals['total_keseluruhan'], 0, ',', '.') }}</td>
        </tr>
    </table>

</body>

</html>
