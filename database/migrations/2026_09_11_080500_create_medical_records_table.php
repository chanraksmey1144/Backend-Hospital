<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('code', 20)->unique();
            $table->char('patient_id', 24);
            $table->char('doctor_id', 24);
            $table->date('visit_date');
            $table->text('chief_complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->json('vitals')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();

            $table->index('patient_id', 'idx_rec_patient');
            $table->index('doctor_id', 'idx_rec_doctor');
            $table->index('visit_date', 'idx_rec_visit_date');
            $table->foreign('patient_id')->references('id')->on('patients')->name('fk_rec_patient');
            $table->foreign('doctor_id')->references('id')->on('doctors')->name('fk_rec_doctor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
