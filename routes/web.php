<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\HomeController;

Route::middleware('guest')->group(function () {
    // Redirect to Provider (e.g., /auth/google)
    Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('auth.google');
    Route::get('/auth/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('auth.github');

    // Handle Callback from Provider (e.g., /auth/google/callback)
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('auth.google.callback');
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('auth.github.callback');
});

Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Social Authentication Routes
Route::get('/auth/github', [SocialAuthController::class, 'redirectToGithub'])->name('auth.github');
Route::get('/auth/github/callback', [SocialAuthController::class, 'handleGithubCallback']);
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Dashboard route (protected)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

// Route to display the personalization form
Route::get('/personalization', [HomeController::class, 'showPersonalization'])
    ->middleware(['auth'])
    ->name('personalization.show');

// Your existing POST route for saving data remains the same
Route::post('/personalization/save', [PersonalizationController::class, 'save'])
    ->middleware(['auth'])
    ->name('personalization.save');

// Your main dashboard route
Route::get('/dashboard', [HomeController::class, 'index'])->middleware(['auth'])->name('dashboard');
