@extends('layouts.app')

@section('content')
<div class="auth-container">
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
            </div>
            <span class="logo-text">CodeXpert</span>
        </div>
    </div>

    <!-- Register Form -->
    <div class="auth-card">
        <!-- Logo -->
        <div style="text-align: center; margin-bottom: 32px;">
            <div class="main-logo">
                <img src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo" width="100" height="100">
            </div>
            <h1 class="welcome-title">Join CodeXpert</h1>
            <p class="welcome-subtitle">Start your journey from practice to pro.</p>
        </div>

        <!-- Register Form -->
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <!-- Name Field -->
            <div class="form-group">
                <label for="name" style="display: none;">Full Name</label>
                <input id="name" name="name" type="text" autocomplete="name" required 
                       class="form-input" 
                       placeholder="Full Name" value="{{ old('name') }}">
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" style="display: none;">Email address</label>
                <input id="email" name="email" type="email" autocomplete="email" required 
                       class="form-input" 
                       placeholder="Email Address" value="{{ old('email') }}">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" style="display: none;">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required 
                       class="form-input" 
                       placeholder="Password">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="password_confirmation" style="display: none;">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                       class="form-input" 
                       placeholder="Confirm Password">
            </div>

            <!-- Sign Up Button -->
            <div class="form-group">
                <button type="submit" class="btn-primary">
                    Sign Up
                </button>
            </div>

            <!-- Separator -->
            <div class="separator">
                <div class="separator-text">Or Continue with</div>
            </div> 

            <!-- Social Login Buttons -->
            <div class="social-buttons">
                <a href="{{ route('auth.github', ['provider' => 'github']) }}" class="btn-social">
                    Github
                </a>
                <a href="{{ route('auth.google', ['provider' => 'google']) }}" class="btn-social">
                    Google
                </a>
            </div> 


            <!-- Sign In Link -->
            <div class="auth-link">
                <p>
                    Already have an account? 
                    <a href="{{ route('login') }}">
                        Sign in
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
