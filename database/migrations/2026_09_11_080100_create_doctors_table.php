<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('code', 20)->unique();
            $table->string('title', 10)->default('Dr.');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->char('department_id', 24);
            $table->string('specialization', 150)->nullable();
            $table->string('license_no', 50)->nullable();
            $table->unsignedInteger('experience_years')->default(0);
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->string('qualification', 100)->nullable();
            $table->text('bio')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->enum('availability', ['available', 'on_leave', 'unavailable'])->default('available');
            $table->decimal('rating', 2, 1)->default(4.0)->nullable();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->name('fk_doctors_department');
            $table->index(['last_name', 'first_name'], 'idx_doctors_name');
            $table->index('specialization', 'idx_doctors_specialization');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('head_doctor_id')->references('id')->on('doctors')->name('fk_departments_head');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
