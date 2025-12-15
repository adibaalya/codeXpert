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
        Schema::create('competency_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_ID')->constrained('reviewers', 'reviewer_ID')->onDelete('cascade');
            $table->string('language', 30); // Python, Java, JavaScript, C++
            $table->integer('mcq_score')->default(0); // Score from MCQ questions
            $table->integer('code_score')->default(0); // Score from coding questions
            $table->integer('total_score')->default(0); // Total score
            $table->integer('plagiarism_score')->default(100); // Plagiarism detection score
            $table->string('level_achieved', 30)->nullable(); // beginner, intermediate, advanced, all
            $table->boolean('passed')->default(false);
            $table->json('mcq_answers')->nullable(); // Store MCQ answers
            $table->json('code_solutions')->nullable(); // Store code solutions
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competency_test_results');
    }
};
