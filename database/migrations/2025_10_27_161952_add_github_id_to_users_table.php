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
        Schema::table('users', function (Blueprint $table) {
            // Add the google_id column. It should be nullable 
            // to support existing users who haven't logged in via Google.
            // It should be a string to hold the ID and unique.
            $table->string('github_id')->nullable()->unique()->after('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the column if the migration is rolled back
            $table->dropColumn('github_id');
        });
    }
};
