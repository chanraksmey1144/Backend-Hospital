<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->char('prescription_id', 24);
            $table->char('medicine_id', 24);
            $table->string('medicine_name', 150)->nullable();
            $table->string('dosage', 50)->nullable();
            $table->string('frequency', 50)->nullable();
            $table->string('duration', 50)->nullable();
            $table->string('route', 50)->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedInteger('quantity')->default(1);

            $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('cascade')->name('fk_rx_item_prescription');
            $table->foreign('medicine_id')->references('id')->on('medicines')->name('fk_rx_item_medicine');
            $table->index('medicine_id', 'idx_rx_item_medicine');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
