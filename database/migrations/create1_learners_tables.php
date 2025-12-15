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
        Schema::create('learners', function (Blueprint $table) {
            $table->id('learner_ID'); // Primary Key
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password', 255); // Stored password hash
            
            // Optional Social IDs
            $table->string('google_ID')->nullable();
            $table->string('github_ID')->nullable();

            $table->date('registration_date')->useCurrent();
            $table->integer('totalPoint')->default(0);
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
        Schema::dropIfExists('learners');
    }
};
