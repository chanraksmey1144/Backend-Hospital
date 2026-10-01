<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->char('id', 24)->primary();  // e.g. u-admin
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->enum('role', [
                'ADMIN',
                'DOCTOR',
                'NURSE',
                'RECEPTIONIST',
                'PHARMACIST',
                'ACCOUNTANT',
                'PATIENT'
            ])->default('PATIENT');
            $table->char('department_id', 24)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('locale', ['en', 'km'])->default('en');
            $table->string('timezone', 50)->default('Asia/Phnom_Penh')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
