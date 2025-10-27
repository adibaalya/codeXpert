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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id'); // Using custom PK name user_id
            $table->string('username', 255)->unique();
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->enum('role', ['Learner', 'Reviewer', 'Learner&Reviewer'])->default('Learner');
            $table->string('badge', 50)->nullable();
            $table->integer('streak')->default(0);
            $table->rememberToken(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
