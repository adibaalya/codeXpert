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
        Schema::create('questions', function (Blueprint $table) {
            $table->id('question_ID'); // Primary Key
            $table->text('content');
            $table->text('expectedAnswer');
            $table->string('status', 20)->default('Pending'); // Approved, Declined, Pending
            
            // FK to the reviewer who last approved/edited it (1:M relationship)
            $table->foreignId('reviewer_ID')->nullable()->constrained('reviewers', 'reviewer_ID')->onDelete('set null');

            $table->string('language', 30);
            $table->string('level', 30); // beginner, intermediate, advanced
            $table->string('questionCategory', 50)->nullable(); // competencyTest, practice, etc.
            $table->string('questionType', 50)->nullable(); // MCQ_Single, Code_Solution
            $table->json('options')->nullable(); // For MCQ questions store options as JSON
            $table->string('chapter', 50)->nullable();
            $table->text('hint')->nullable();
            $table->json('testCases')->nullable(); // For code questions, store test cases

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
