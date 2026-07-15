<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_slip_tetap', function (Blueprint $table) {
            $table->dropColumn('harian');

            $table->unsignedInteger('hari_shift')->default(0)->after('off');
            $table->unsignedInteger('hari_full')->default(0)->after('hari_shift');
            $table->unsignedInteger('hari_parsial')->default(0)->after('hari_full');

            $table->unsignedBigInteger('nominal_shift')->default(0)->after('hari_parsial');
            $table->unsignedBigInteger('nominal_full')->default(0)->after('nominal_shift');
            $table->unsignedBigInteger('nominal_parsial')->default(0)->after('nominal_full');

            $table->unsignedBigInteger('total_shift')->default(0)->after('nominal_parsial');
            $table->unsignedBigInteger('total_full')->default(0)->after('total_shift');
            $table->unsignedBigInteger('total_parsial')->default(0)->after('total_full');

            $table->unsignedTinyInteger('jam_lembur')->default(0)->after('total_parsial');
        });
    }

    public function down(): void
    {
        Schema::table('salary_slip_tetap', function (Blueprint $table) {
            $table->dropColumn([
                'hari_shift', 'hari_full', 'hari_parsial',
                'nominal_shift', 'nominal_full', 'nominal_parsial',
                'total_shift', 'total_full', 'total_parsial',
                'jam_lembur',
            ]);
            $table->unsignedBigInteger('harian')->default(0);
        });
    }
};