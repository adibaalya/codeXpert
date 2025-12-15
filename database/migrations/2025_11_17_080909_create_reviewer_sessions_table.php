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
        Schema::create('reviewer_sessions', function (Blueprint $table) {
            $table->id('session_id');
            $table->foreignId('reviewer_ID')->constrained('reviewers', 'reviewer_ID')->onDelete('cascade');
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->integer('duration_minutes')->nullable(); // Calculated when logout_at is set
            $table->timestamps();
            
            $table->index(['reviewer_ID', 'login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewer_sessions');
    }
};
