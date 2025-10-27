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
        Schema::create('hackathon', function (Blueprint $table) {
            $table->id('hackathon_id');
            $table->string('hackathon_name', 255);
            $table->text('hackathon_detail')->nullable();
            $table->string('total_prize', 100)->nullable();
            $table->string('targeted_participant', 100)->nullable();
            $table->string('hackathon_link', 255)->nullable();
            $table->string('hackathon_category', 100)->nullable();
            $table->date('hackathon_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hackathon');
    }
};
