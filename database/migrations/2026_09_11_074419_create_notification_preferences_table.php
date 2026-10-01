<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->char('user_id', 24);
            $table->boolean('email')->default(true);
            $table->boolean('push')->default(true);
            $table->boolean('appointments')->default(true);
            $table->boolean('laboratory')->default(false);
            $table->boolean('billing')->default(true);
            $table->boolean('inventory')->default(true);
            $table->boolean('system')->default(false);
            $table->timestamps();
            // Constraints
            $table->unique('user_id', 'uq_notif_pref_user');
            $table->foreign('user_id', 'fk_notif_pref_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
