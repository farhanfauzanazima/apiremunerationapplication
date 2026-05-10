<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('payroll_periods')->onDelete('restrict');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('restrict');
            $table->foreignId('category_id')->constrained('salary_categories')->onDelete('restrict');

            // Komponen input dari admin
            $table->integer('total_working_days')->default(0);
            $table->integer('late_count')->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('additional_deduction', 15, 2)->default(0);
            $table->text('notes')->nullable();

            // Komponen hasil kalkulasi (disimpan sebagai snapshot)
            $table->decimal('base_salary_amount', 15, 2)->default(0);
            $table->decimal('allowance_amount', 15, 2)->default(0);
            $table->decimal('late_penalty_amount', 15, 2)->default(0);
            $table->decimal('total_salary', 15, 2)->default(0);

            // Status slip
            $table->enum('status', ['draft', 'sent'])->default('draft');
            $table->string('pdf_path')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            // Satu karyawan hanya boleh punya satu slip per periode
            $table->unique(['period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_slips');
    }
};