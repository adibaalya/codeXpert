<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Learner;
use App\Models\Reviewer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed competency test questions automatically
        $this->call([
            CompetencyTestSeeder::class,
        ]);
    }
}
