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
        Schema::table('questions', function (Blueprint $table) {
            // Rename 'expectedAnswer' to 'answersData' keeping it as text
            $table->renameColumn('expectedAnswer', 'answersData');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Rename back to 'expectedAnswer'
            $table->renameColumn('answersData', 'expectedAnswer');
        });
    }
};
