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
            // Add new columns
            $table->text('description')->nullable()->after('content');
            $table->text('problem_statement')->nullable()->after('description');
            $table->text('constraints')->nullable()->after('problem_statement');
            $table->text('expected_output')->nullable()->after('constraints');
            
            // Rename testCases to input
            $table->renameColumn('testCases', 'input');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Drop the new columns
            $table->dropColumn(['description', 'problem_statement', 'constraints', 'expected_output']);
            
            // Rename input back to testCases
            $table->renameColumn('input', 'testCases');
        });
    }
};
