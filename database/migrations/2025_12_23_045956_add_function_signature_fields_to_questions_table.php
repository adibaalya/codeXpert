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
            // Add return type for the function (e.g., 'int[]', 'boolean', 'void')
            $table->string('return_type')->nullable()->after('function_name');
            
            // Add parameters as JSON (e.g., {"nums": "int[]", "target": "int"})
            $table->json('function_parameters')->nullable()->after('return_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['return_type', 'function_parameters']);
        });
    }
};
