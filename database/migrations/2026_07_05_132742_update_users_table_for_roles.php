<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role'); // role lama owner/head/admin dihapus
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['owner', 'hr'])->after('email')->default('hr');
            $table->boolean('has_all_branch_access')->after('role')->default(false);
            $table->boolean('must_change_password')->after('has_all_branch_access')->default(false);
        });

        // Pivot: cabang mana saja yang boleh dikelola HR (jika has_all_branch_access = false)
        Schema::create('user_branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_branch');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'has_all_branch_access', 'must_change_password']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin');
        });
    }
};