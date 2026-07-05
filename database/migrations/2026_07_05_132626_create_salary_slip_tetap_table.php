<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_slip_tetap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();

            // Kehadiran
            $table->unsignedInteger('hari_kerja')->default(0);   // manual HR
            $table->unsignedInteger('alfa')->default(0);         // manual HR
            $table->unsignedInteger('izin')->default(0);         // manual HR
            $table->unsignedInteger('sakit')->default(0);        // manual HR
            $table->unsignedInteger('off')->default(0);          // manual HR
            $table->integer('masuk')->default(0);                // auto: hari_kerja-alfa-izin-sakit-off

            $table->unsignedBigInteger('lembur')->default(0);    // manual HR
            $table->unsignedInteger('telat')->default(0);        // manual HR
            $table->unsignedBigInteger('harian')->default(0);    // manual HR

            $table->unsignedBigInteger('gaji_pokok')->default(0);          // auto: harian x masuk
            $table->unsignedBigInteger('tunjangan_transport')->default(0); // auto: setting x masuk
            $table->unsignedBigInteger('tunjangan_jabatan')->default(0);   // manual HR
            $table->unsignedBigInteger('tunjangan_bpjs')->default(0);      // manual HR
            $table->unsignedBigInteger('tunjangan_masa_kerja')->default(0);// auto: sesuai setting ambang
            $table->unsignedBigInteger('bonus_disiplin')->default(0);     // auto: setting x masuk
            $table->unsignedBigInteger('bonus_omset')->default(0);        // manual HR
            $table->unsignedBigInteger('bonus_kinerja')->default(0);      // manual HR
            $table->unsignedBigInteger('cashbond')->default(0);           // manual HR
            $table->unsignedBigInteger('tabungan')->default(0);           // manual HR

            $table->bigInteger('thp')->default(0);         // auto
            $table->bigInteger('total_gaji')->default(0);  // auto

            $table->timestamps();

            $table->unique(['employee_id', 'payroll_period_id']); // anti-duplikat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_slip_tetap');
    }
};