<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('code', 20)->unique();
            $table->char('patient_id', 24);
            $table->char('doctor_id', 24);
            $table->date('date')->nullable();
            $table->enum('status', ['issued', 'dispensed', 'partially_dispensed', 'cancelled'])->default('issued');
            $table->timestamps();
            $table->timestamp('issued_at')->nullable();

            $table->index('patient_id', 'idx_rx_patient');
            $table->index('date', 'idx_rx_date');
            $table->foreign('patient_id')->references('id')->on('patients')->name('fk_rx_patient');
            $table->foreign('doctor_id')->references('id')->on('doctors')->name('fk_rx_doctor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
