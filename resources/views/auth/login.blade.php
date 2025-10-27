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
    <!-- Login Form -->
    <div class="auth-card">
        <!-- Logo -->
        <div style="text-align: center; margin-bottom: 32px;">
            <div class="main-logo">
                <img src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo" width="100" height="100">
            </div>
            <h1 class="welcome-title">Welcome to CodeXpert</h1>
            <p class="welcome-subtitle">From practice to pro — powered by AI.</p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <!-- Email Field -->
            <div class="form-group">
                <label for="email" style="display: none;">Email address</label>
                <input id="email" name="email" type="email" autocomplete="email" required 
                       class="form-input" 
                       placeholder="Username or Email" value="{{ old('email') }}">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" style="display: none;">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required 
                       class="form-input" 
                       placeholder="Password">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sign In Button -->
            <div class="form-group">
                <button type="submit" class="btn-primary">
                    Sign In
                </button>
            </div>

            <!-- Separator -->
            <!--  <div class="separator">
                <div class="separator-text">Or Continue with</div>
            </div> -->

            <!-- Social Login Buttons -->
            <!-- <div class="social-buttons">
                <a href="{{ route('auth.github') }}" class="btn-social">
                    Github
                </a>
                <a href="{{ route('auth.google') }}" class="btn-social">
                    Google
                </a>
            </div> -->

            <!-- Sign Up Link -->
            <div class="auth-link">
                <p>
                    Don't have an account? 
                    <a href="{{ route('register') }}">
                        Sign up
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
