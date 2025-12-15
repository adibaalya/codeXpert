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
        Schema::create('hackathon_learner', function (Blueprint $table) {
            $table->foreignId('learner_ID')->constrained('learners', 'learner_ID')->onDelete('cascade');
            $table->foreignId('hackathon_ID')->constrained('hackathons', 'hackathon_ID')->onDelete('cascade');
            
            // Composite Primary Key
            $table->primary(['learner_ID', 'hackathon_ID']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hackathon_learner');
    }
};
