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
        Schema::table('attempts', function (Blueprint $table) {
            // Check and drop testResult_ID column and its foreign key if exists
            if (Schema::hasColumn('attempts', 'testResult_ID')) {
                $table->dropForeign(['testResult_ID']);
                $table->dropColumn('testResult_ID');
            }
            
            // Add language column if it doesn't exist
            if (!Schema::hasColumn('attempts', 'language')) {
                $table->string('language')->nullable()->after('submittedCode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            // Remove language column
            if (Schema::hasColumn('attempts', 'language')) {
                $table->dropColumn('language');
            }
            
            // Re-add testResult_ID column (optional - only if you need to rollback)
            if (!Schema::hasColumn('attempts', 'testResult_ID')) {
                $table->foreignId('testResult_ID')->nullable()->after('learner_ID');
            }
        });
    }
};
