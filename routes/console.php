<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic weekly question generation
Schedule::command('questions:generate-weekly')
    ->weekly()
    ->sundays()
    ->at('00:00')
    ->timezone('Asia/Kuala_Lumpur');
