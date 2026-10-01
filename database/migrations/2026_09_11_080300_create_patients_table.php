<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('code', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->enum('gender', ['male', 'female', 'other'])->default('other');
            $table->date('birth_date')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->json('allergies')->nullable();
            $table->string('insurance_no', 100)->nullable();
            $table->string('emergency_contact', 100)->nullable();
            $table->string('emergency_phone', 30)->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('occupation', 100)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('last_visit')->nullable();
            $table->timestamps();

            $table->index(['last_name', 'first_name'], 'idx_patients_name');
            $table->index('status', 'idx_patients_status');
            $table->index('gender', 'idx_patients_gender');
            $table->index('blood_group', 'idx_patients_blood');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
