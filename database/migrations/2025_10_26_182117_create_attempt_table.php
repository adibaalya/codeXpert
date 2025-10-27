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
        Schema::create('attempt', function (Blueprint $table) {
            $table->id('attempt_id');
        
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
              ->references('user_id') // Correct PK name
              ->on('users')
              ->onDelete('cascade');

              $table->unsignedBigInteger('question_id');
              $table->foreign('question_id')
                    ->references('question_id')
                    ->on('question')
                    ->onDelete('cascade');
        
            $table->text('submitted_code');
            $table->float('plagiarism_score')->default(0.0);
            $table->float('accuracy_score')->default(0.0);
            $table->timestamp('date_attempted')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempt');
    }
};
