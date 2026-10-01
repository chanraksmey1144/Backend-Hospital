<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->text('message')->nullable();
            $table->char('user_id', 24)->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->timestamps();

            $table->index('type', 'idx_activity_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
