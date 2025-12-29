<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CodeXpert - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @include('layouts.app')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleButtons = document.querySelectorAll('.role-btn');
            const roleInput = document.getElementById('role');
            const socialButtons = document.querySelectorAll('.btn-social');
            
            roleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // 1. Remove 'active' from all buttons
                    roleButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // 2. Add 'active' to clicked button
                    this.classList.add('active');
                    
                    // 3. Update hidden input
                    const role = this.getAttribute('data-role');
                    if (roleInput) {
                        roleInput.value = role;
                    }
                    
                    // 4. Update social URL params
                    updateSocialButtonUrls(role);
                });
            });
            
            function updateSocialButtonUrls(role) {
                socialButtons.forEach(button => {
                    const currentUrl = button.getAttribute('href');
                    const url = new URL(currentUrl, window.location.origin);
                    url.searchParams.set('role', role);
                    button.setAttribute('href', url.toString());
                });
            }
            
            updateSocialButtonUrls('learner');
        });
    </script>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="{{ asset('assets/images/codeXpert.png') }}" alt="CodeXpert Logo">
            </div>
            <span class="logo-text">CodeXpert</span>
        </div>
    </div>
    <div class="main-container">
        <!-- Left Hero Section -->
        <div class="hero-section">
            <div>
                <div class="hero-content">
                    <div class="ai-badge">AI-Powered Learning Platform</div>
                    
                    <h1 class="hero-title">Master Coding<br>From Practice <br>to Pro</h1>
                    
                    <p class="hero-subtitle">
                        Join thousands of developers improving their coding skills with our AI-powered platform. Practice, compete, and grow your expertise.
                    </p>

                    <div class="feature-grid">
                        <div class="feature-card">
                            <div class="feature-title">2000+ Challenges</div>
                            <div class="feature-description">Practice with real-world coding problems</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-title">AI Assistant</div>
                            <div class="feature-description">Get instant feedback and hints</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-title">Hackathons</div>
                            <div class="feature-description">Compete in exciting coding contests</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-title">Track Progress</div>
                            <div class="feature-description">Monitor your improvement journey</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form Section -->
        <div class="form-section">
            <div class="form-container">
                <div class="form-logo">
                    <img src="{{ asset('assets/images/codeXpert.png') }}" alt="CodeXpert Logo">
                </div>
                
                <h1 class="welcome-title">Welcome Back!</h1>
                <p class="welcome-subtitle">Sign in to continue your coding journey</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <input type="hidden" name="role" id="role" value="learner">
                    
                    <label class="role-label">Select Your Role</label>
                    <div class="role-buttons">
                        <button type="button" class="role-btn role-btn-active" data-role="learner">
                            Learner
                        </button>
                        <button type="button" class="role-btn" data-role="reviewer">
                            Reviewer
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Username or Email</label>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               class="form-input" 
                               placeholder="Enter your username" value="{{ old('email') }}">
                        @error('email')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                               class="form-input" 
                               placeholder="Enter your password">
                        @error('password')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        <div class="forgot-link">
                            <a href="#">Forgot password?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Sign In</button>

                    <div class="separator">
                        <span class="separator-text">or</span>
                    </div>

                    <div class="social-buttons">
                        <a href="{{ route('auth.provider', ['provider' => 'github']) }}" class="btn-social">
                            Github
                        </a>
                        <a href="{{ route('auth.provider', ['provider' => 'google']) }}" class="btn-social">
                            Google
                        </a>
                    </div>

                    <div class="auth-link">
                        Don't have an account? 
                        <a href="{{ route('register') }}">Sign up now</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
