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
        // 1. Create the Badges Table (Polymorphic: works for both users)
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->morphs('badgeable'); // Creates badgeable_id and badgeable_type
            $table->string('badge_type'); // e.g., 'active_learner', 'error_spotter'
            $table->date('earned_date')->default(now());
            $table->timestamps();
        });

        // 2. Add Stats to Learners
        Schema::table('learners', function (Blueprint $table) {
            $table->integer('total_attempts')->default(0);
            $table->integer('solved_questions')->default(0); // Accuracy == 100
            $table->integer('current_streak')->default(0);
            $table->date('last_activity_date')->nullable();
        });

        // 3. Add Stats to Reviewers
        Schema::table('reviewers', function (Blueprint $table) {
            $table->integer('total_reviews')->default(0);
            $table->integer('clean_reviews_count')->default(0);   // Marked "No Errors"
            $table->integer('errors_flagged_count')->default(0);  // Marked "Errors Found"
            $table->integer('questions_generated_count')->default(0);
        });

        // 4. Update Attempts (Required for "Language Confident" badge)
        Schema::table('attempts', function (Blueprint $table) {
            // Only add if it doesn't exist yet
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
        Schema::dropIfExists('user_badges');
        
        Schema::table('learners', function (Blueprint $table) {
            $table->dropColumn(['total_attempts', 'solved_questions', 'current_streak', 'last_activity_date']);
        });
        
        Schema::table('reviewers', function (Blueprint $table) {
            $table->dropColumn(['total_reviews', 'clean_reviews_count', 'errors_flagged_count', 'questions_generated_count']);
        });
        
        Schema::table('attempts', function (Blueprint $table) {
            if (Schema::hasColumn('attempts', 'language')) {
                $table->dropColumn('language');
            }
        });
    }
};
