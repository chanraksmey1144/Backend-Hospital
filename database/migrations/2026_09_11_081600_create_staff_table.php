<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('code', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->enum('role', ['ADMIN', 'DOCTOR', 'NURSE', 'RECEPTIONIST', 'PHARMACIST', 'ACCOUNTANT']);
            $table->char('department_id', 24)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('hire_date')->nullable();
            $table->enum('status', ['active', 'on_leave', 'inactive'])->default('active');
            $table->decimal('salary', 10, 2)->default(0);
            $table->timestamps();

            $table->index('role', 'idx_staff_role');
            $table->index('department_id', 'idx_staff_department');
            $table->foreign('department_id')->references('id')->on('departments')->name('fk_staff_department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
