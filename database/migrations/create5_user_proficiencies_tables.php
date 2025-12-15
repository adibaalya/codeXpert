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
        Schema::create('user_proficiencies', function (Blueprint $table) {
            // Composite Primary Key (Learner ID + Language)
            $table->foreignId('learner_ID')->constrained('learners', 'learner_ID')->onDelete('cascade');
            $table->string('language', 30);
            
            $table->primary(['learner_ID', 'language']);

            $table->integer('XP')->default(0);
            $table->string('level', 30)->default('Beginner'); // Calculated skill level

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_proficiencies');
    }
};
