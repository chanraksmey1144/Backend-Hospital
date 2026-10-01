<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->char('invoice_id', 24);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('type', ['consultation', 'medication', 'laboratory', 'procedure', 'other'])->default('other');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade')->name('fk_inv_item_invoice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
