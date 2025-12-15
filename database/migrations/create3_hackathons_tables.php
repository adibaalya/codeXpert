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
        Schema::create('hackathons', function (Blueprint $table) {
            $table->id('hackathon_ID'); // Primary Key
            $table->string('hackathonName', 100);
            $table->text('hackathonDetail');
            $table->float('totalPrize')->nullable();
            $table->string('targetedParticipant', 50);
            $table->string('hackathonLink')->nullable();
            $table->string('hackathonCategory', 50);
            $table->date('hackathonDate');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hackathons');
    }
};
