<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('code', 20)->unique();
            $table->char('patient_id', 24);
            $table->char('appointment_id', 24)->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'partial', 'paid', 'cancelled_inv', 'refunded'])->default('pending');
            $table->string('payment_method', 20)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index('patient_id', 'idx_inv_patient');
            $table->index('appointment_id', 'idx_inv_appt');
            $table->index('status', 'idx_inv_status');
            $table->index('issue_date', 'idx_inv_issue_date');
            $table->foreign('patient_id')->references('id')->on('patients')->name('fk_inv_patient');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null')->name('fk_inv_appt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
