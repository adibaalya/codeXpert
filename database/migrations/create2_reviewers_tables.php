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
        Schema::create('reviewers', function (Blueprint $table) {
            $table->id('reviewer_ID'); // Primary Key
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password', 255); // Stored password hash

            // Optional Social IDs
            $table->string('google_ID')->nullable();
            $table->string('github_ID')->nullable();

            $table->date('registrationDate')->useCurrent();
            $table->boolean('isQualified')->default(false); // Qualification status from test

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewers');
    }
};
