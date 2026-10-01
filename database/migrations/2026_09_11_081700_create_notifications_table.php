<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('title', 150);
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->char('user_id', 24)->nullable();
            $table->timestamps();

            $table->index('user_id', 'idx_notif_user');
            $table->index('is_read', 'idx_notif_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
