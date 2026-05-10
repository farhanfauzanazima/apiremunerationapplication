<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_slip_id')->constrained('salary_slips')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('email_to', 100);
            $table->string('subject', 255);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('sent_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_histories');
    }
};