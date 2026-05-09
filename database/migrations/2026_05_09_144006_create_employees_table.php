<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('salary_categories')->onDelete('restrict');
            $table->string('full_name', 100);
            $table->string('employee_code', 20)->unique()->nullable();
            $table->string('email', 100)->unique();
            $table->string('phone', 15);
            $table->date('join_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};