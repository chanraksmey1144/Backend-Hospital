<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->string('generic_name', 150)->nullable();
            $table->char('category_id', 24)->nullable();
            $table->string('manufacturer', 150)->nullable();
            $table->string('batch_no', 50)->nullable();
            $table->integer('quantity')->default(0);
            $table->string('unit', 20)->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->integer('min_stock')->default(0);
            $table->char('supplier_id', 24)->nullable();
            $table->enum('status', ['in_stock', 'low_stock', 'out_of_stock', 'expiring_soon', 'expired'])->nullable();
            $table->timestamps();

            $table->index('category_id', 'idx_med_category');
            $table->index('supplier_id', 'idx_med_supplier');
            $table->index('name', 'idx_med_name');
            $table->index('generic_name', 'idx_med_generic');
            $table->index('status', 'idx_med_status');
            $table->foreign('category_id')->references('id')->on('categories')->name('fk_med_category');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->name('fk_med_supplier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
