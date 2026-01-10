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
        Schema::create('attempts', function (Blueprint $table) {
            $table->id('attempt_id'); // Primary Key

            // FK - QUESTION (NOT NULL)
            $table->foreignId('question_ID')->constrained('questions', 'question_ID')->onDelete('cascade');

            // FK - LEARNER (for practice attempts)
            $table->foreignId('learner_ID')->nullable()->constrained('learners', 'learner_ID')->onDelete('cascade');

            $table->text('submittedCode');
            $table->string('language')->nullable(); // Programming language used
            $table->float('plagiarismScore')->default(0);
            $table->float('accuracyScore');
            $table->text('aiFeedback')->nullable();
            $table->timestamp('dateAttempted')->useCurrent();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
