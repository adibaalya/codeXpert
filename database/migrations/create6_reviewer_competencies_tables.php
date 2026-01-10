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
        Schema::create('reviewer_competencies', function (Blueprint $table) {
            $table->id('testResult_ID'); // Surrogate Primary Key for FK in Attempts

            // The original Composite Key that must be unique
            $table->foreignId('reviewer_ID')->constrained('reviewers', 'reviewer_ID')->onDelete('cascade');
            $table->timestamp('dateCompletion');
            
            // Unique Constraint to enforce one test result per reviewer per timestamp
            $table->unique(['reviewer_ID', 'dateCompletion']); 

            $table->string('language_tested', 30)->unique();
            $table->float('score');
            $table->boolean('passed_status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewer_competencies');
    }
};
