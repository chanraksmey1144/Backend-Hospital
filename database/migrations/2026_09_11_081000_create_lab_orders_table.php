<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('code', 20)->unique();
            $table->char('patient_id', 24);
            $table->char('doctor_id', 24);
            $table->char('test_type_id', 24);
            $table->enum('status', ['ordered', 'processing', 'completed_lab', 'cancelled_lab'])->default('ordered');
            $table->text('result')->nullable();
            $table->text('result_notes')->nullable();
            $table->dateTime('ordered_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->timestamps();

            $table->index('patient_id', 'idx_lab_patient');
            $table->index('status', 'idx_lab_status');
            $table->index('ordered_date', 'idx_lab_ordered_date');
            $table->foreign('patient_id')->references('id')->on('patients')->name('fk_lab_patient');
            $table->foreign('doctor_id')->references('id')->on('doctors')->name('fk_lab_doctor');
            $table->foreign('test_type_id')->references('id')->on('lab_test_types')->name('fk_lab_test');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
