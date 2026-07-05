<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_slip_partime', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('hari_kerja')->default(0); // manual HR
            $table->unsignedInteger('full')->default(0);       // manual HR
            $table->unsignedInteger('shift')->default(0);      // manual HR
            $table->unsignedInteger('reguler')->default(0);    // manual HR
            $table->unsignedInteger('sakit')->default(0);      // manual HR
            $table->unsignedInteger('off')->default(0);        // manual HR

            $table->unsignedBigInteger('tunjangan')->default(0); // manual HR

            $table->unsignedBigInteger('total_full')->default(0);      // auto
            $table->unsignedBigInteger('total_shift')->default(0);     // auto
            $table->unsignedBigInteger('total_reguler')->default(0);   // auto
            $table->unsignedBigInteger('total_transport')->default(0); // auto

            $table->unsignedBigInteger('bonus')->default(0); // manual HR
            $table->bigInteger('total_fee')->default(0);     // auto

            $table->timestamps();

            $table->unique(['employee_id', 'payroll_period_id']); // anti-duplikat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_slip_partime');
    }
};