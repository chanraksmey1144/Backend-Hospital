<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->char('invoice_id', 24);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('method', 20);
            $table->date('date')->nullable();
            $table->string('reference', 50)->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade')->name('fk_payment_invoice');
            $table->index('invoice_id', 'idx_payment_invoice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
