<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color:#222;">
    <p>Halo {{ $employee->name }},</p>
    <p>
        Berikut kami lampirkan slip gaji Anda untuk periode
        <strong>{{ $bulanIndo[$period->month] ?? $period->month }} {{ $period->year }}</strong>.
    </p>
    <p>Silakan buka file PDF terlampir untuk melihat rincian lengkap.</p>
    <br>
    <p>Hormat kami,<br>HR Dept</p>
</body>
</html>