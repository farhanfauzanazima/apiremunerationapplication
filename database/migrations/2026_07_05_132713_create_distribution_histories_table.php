<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('slip'); // slip_id + slip_type (SalarySlipTetap / SalarySlipPartime)
            $table->enum('channel', ['email', 'whatsapp']);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('note')->nullable();
            $table->string('public_token', 64)->unique()->nullable(); // untuk link akses PDF via WhatsApp
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_histories');
    }
};