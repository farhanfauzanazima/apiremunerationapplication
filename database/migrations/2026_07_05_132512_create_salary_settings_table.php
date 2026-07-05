<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Kategorikal global: satu baris berlaku untuk SEMUA cabang.
    public function up(): void
    {
        Schema::create('salary_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_tetap')->default(0);      // per kehadiran, karyawan tetap
            $table->unsignedBigInteger('transport_partime')->default(0);    // per kehadiran, tim partime
            $table->unsignedInteger('tenure_months_threshold')->default(6); // ambang bulan masa kerja
            $table->unsignedBigInteger('tenure_bonus_amount')->default(0);  // nominal jika masa kerja >= ambang
            $table->unsignedBigInteger('disiplin_bonus_tetap')->default(0); // per kehadiran, karyawan tetap
            $table->unsignedBigInteger('rate_full')->default(0);            // tim partime
            $table->unsignedBigInteger('rate_shift')->default(0);           // tim partime
            $table->unsignedBigInteger('rate_reguler')->default(0);         // tim partime
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_settings');
    }
};