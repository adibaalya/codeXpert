<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CodeXpert - Register</title>

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
                    roleButtons.forEach(btn => btn.classList.remove('role-btn-active'));
                    this.classList.add('role-btn-active');
                    
                    const role = this.getAttribute('data-role');
                    if (roleInput) {
                        roleInput.value = role;
                    }
                    
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
                <img src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
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
                    <img src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
                </div>
                
                <h1 class="welcome-title">Join CodeXpert</h1>
                <p class="welcome-subtitle">Start your journey from practice to pro</p>

                <form method="POST" action="{{ route('register') }}">
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
                        <input id="name" name="name" type="text" autocomplete="name" required 
                               class="form-input" 
                               placeholder="Username" value="{{ old('name') }}">
                        @error('name')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               class="form-input" 
                               placeholder="Email Address" value="{{ old('email') }}">
                        @error('email')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input id="password" name="password" type="password" autocomplete="new-password" required 
                               class="form-input" 
                               placeholder="Password">
                        @error('password')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                               class="form-input" 
                               placeholder="Confirm Password">
                    </div>

                    <button type="submit" class="btn-primary">Sign Up</button>

                    <div class="separator">
                        <span class="separator-text">Or Continue with</span>
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
                        Already have an account? 
                        <a href="{{ route('login') }}">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
