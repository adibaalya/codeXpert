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
        // Step 1: Remove the foreign key constraint and column from attempts table
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropForeign(['testResult_ID']);
            $table->dropColumn('testResult_ID');
        });

        // Step 2: Drop the reviewer_competencies table
        Schema::dropIfExists('reviewer_competencies');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate reviewer_competencies table
        Schema::create('reviewer_competencies', function (Blueprint $table) {
            $table->id('testResult_ID');
            $table->foreignId('reviewer_ID')->constrained('reviewers', 'reviewer_ID')->onDelete('cascade');
            $table->timestamp('dateCompletion');
            $table->unique(['reviewer_ID', 'dateCompletion']);
            $table->string('language_tested', 30)->unique();
            $table->float('score');
            $table->boolean('passed_status');
            $table->timestamps();
        });

        // Add back the foreign key to attempts table
        Schema::table('attempts', function (Blueprint $table) {
            $table->foreignId('testResult_ID')->nullable()->constrained('reviewer_competencies', 'testResult_ID')->onDelete('cascade');
        });
    }
};
