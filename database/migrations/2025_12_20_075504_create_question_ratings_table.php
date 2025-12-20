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
        Schema::create('question_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('learner_ID');
            $table->unsignedBigInteger('question_ID');
            $table->enum('rating', ['good', 'bad']);
            $table->timestamps();
            
            // Ensure one rating per learner per question
            $table->unique(['learner_ID', 'question_ID']);
            
            // Foreign keys
            $table->foreign('learner_ID')->references('learner_ID')->on('learners')->onDelete('cascade');
            $table->foreign('question_ID')->references('question_ID')->on('questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_ratings');
    }
};
