<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('code', 20)->unique();
            $table->char('patient_id', 24);
            $table->char('doctor_id', 24);
            $table->char('department_id', 24);
            $table->date('date');
            $table->time('time');
            $table->enum('type', ['checkup', 'followup', 'consultation', 'emergency', 'procedure'])->default('checkup');
            $table->enum('status', ['scheduled', 'confirmed', 'checked_in', 'in_consultation', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->unsignedInteger('duration')->default(30);
            $table->unsignedInteger('queue')->nullable();
            $table->timestamps();

            $table->index('patient_id', 'idx_appt_patient');
            $table->index('doctor_id', 'idx_appt_doctor');
            $table->index('date', 'idx_appt_date');
            $table->index('status', 'idx_appt_status');
            $table->index(['doctor_id', 'date'], 'idx_appt_doc_date');
            $table->foreign('patient_id')->references('id')->on('patients')->name('fk_appts_patient');
            $table->foreign('doctor_id')->references('id')->on('doctors')->name('fk_appts_doctor');
            $table->foreign('department_id')->references('id')->on('departments')->name('fk_appts_dept');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
