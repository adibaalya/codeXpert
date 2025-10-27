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
        Schema::create('reviewer_competency', function (Blueprint $table) {

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
              ->references('user_id') // Correct PK name
              ->on('users')
              ->onDelete('cascade');

            $table->string('language', 50);
            $table->boolean('passed');
            $table->integer('score')->nullable();
            $table->date('date_passed')->nullable();
        
            $table->primary(['user_id', 'language']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewer_competency');
    }
};
