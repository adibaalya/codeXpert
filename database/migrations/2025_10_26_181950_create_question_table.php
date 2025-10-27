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
        Schema::create('question', function (Blueprint $table) {
            $table->id('question_id'); // Primary key AUTO_INCREMENT
            $table->text('question_text');
            $table->text('expected_answer');
            $table->enum('status', ['Pending', 'Approved', 'Declined'])->default('Pending');
        
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')
              ->references('user_id') // Correct PK name
              ->on('users')
              ->onDelete('set null');
        
            $table->string('language', 50);
            $table->enum('level', ['Beginner', 'Intermediate', 'Advanced']);
            $table->string('chapter', 100)->nullable();
            $table->text('hint')->nullable();
            $table->string('question_type', 50)->nullable();
            $table->string('question_category', 50)->nullable();
            $table->timestamps(); // Adds created_at and updated_at
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question');
    }
};
