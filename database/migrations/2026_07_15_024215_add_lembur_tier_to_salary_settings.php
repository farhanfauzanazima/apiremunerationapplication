<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('lembur_1_2_jam')->default(20000)->after('rate_reguler');
            $table->unsignedBigInteger('lembur_3_4_jam')->default(35000)->after('lembur_1_2_jam');
            $table->unsignedBigInteger('lembur_5_jam')->default(40000)->after('lembur_3_4_jam');
        });
    }

    public function down(): void
    {
        Schema::table('salary_settings', function (Blueprint $table) {
            $table->dropColumn(['lembur_1_2_jam', 'lembur_3_4_jam', 'lembur_5_jam']);
        });
    }
};