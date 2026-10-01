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
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->char('user_id', 24);
            $table->string('code', 10); // e.g. 6-digit code: 123456
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            // Constraints
            $table->unique('user_id', 'uq_email_verify_user');
            $table->foreign('user_id', 'fk_email_verify_user')
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
        Schema::dropIfExists('email_verifications');
    }
};
