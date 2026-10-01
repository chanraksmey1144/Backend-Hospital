<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_test_types', function (Blueprint $table) {
            $table->char('id', 24)->primary();
            $table->string('name', 150);
            $table->string('category', 100)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('unit', 40)->nullable();
            $table->string('normal_range', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_test_types');
    }
};
