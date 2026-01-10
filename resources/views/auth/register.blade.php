<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CodeXpert - Register</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @include('layouts.app')
    
    <style>
        /* Only added style for the error message */
        .error-text {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: none; /* Hidden by default */
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleButtons = document.querySelectorAll('.role-btn');
            const roleInput = document.getElementById('role');
            const socialButtons = document.querySelectorAll('.btn-social');
            const registerForm = document.getElementById('register-form'); // Get form
            const roleError = document.getElementById('role-error'); // Get error message
            
            roleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // 1. Remove 'active' from all buttons (Reverted to 'active')
                    roleButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // 2. Add 'active' to clicked button (Reverted to 'active')
                    this.classList.add('active');
                    
                    // 3. Update hidden input
                    const role = this.getAttribute('data-role');
                    if (roleInput) {
                        roleInput.value = role;
                    }

                    // Hide error if user selects a role (New addition)
                    if(roleError) {
                        roleError.style.display = 'none';
                    }
                    
                    // 4. Update social URL params
                    updateSocialButtonUrls(role);
                });
            });

            // Form Submit Validation (New addition)
            if(registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    if (!roleInput.value) {
                        e.preventDefault(); // Stop submission
                        if(roleError) {
                            roleError.style.display = 'block'; // Show error
                        }
                    }
                });
            }
            
            function updateSocialButtonUrls(role) {
                socialButtons.forEach(button => {
                    const currentUrl = button.getAttribute('href');
                    const url = new URL(currentUrl, window.location.origin);
                    // Only set role if it exists
                    if(role) {
                        url.searchParams.set('role', role);
                    }
                    button.setAttribute('href', url.toString());
                });
            }
            
            // Initial call
            updateSocialButtonUrls('learner');

            // Password Strength Validator (UNCHANGED)
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');
            const reqLength = document.getElementById('req-length');
            const reqMix = document.getElementById('req-mix');

            if(passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const value = passwordInput.value;
                    let strength = 0;

                    if (value.length >= 8) {
                        strength += 1;
                        reqLength.classList.remove('invalid');
                        reqLength.classList.add('valid');
                    } else {
                        reqLength.classList.remove('valid');
                        reqLength.classList.add('invalid');
                    }

                    const hasUpper = /[A-Z]/.test(value);
                    const hasLower = /[a-z]/.test(value);
                    const hasNum = /[0-9]/.test(value);
                    const hasSym = /[^A-Za-z0-9]/.test(value);

                    const typeCount = hasUpper + hasLower + hasNum + hasSym;
                    
                    if (typeCount >= 3) {
                        strength += 1;
                        reqMix.classList.remove('invalid');
                        reqMix.classList.add('valid');
                    } else {
                        reqMix.classList.remove('valid');
                        reqMix.classList.add('invalid');
                    }
                    
                    if (typeCount === 4 && value.length >= 8) {
                        strength += 1;
                    }

                    if (value.length === 0) {
                        strengthBar.style.width = '0%';
                        strengthText.innerText = '';
                    } else if (strength < 2) {
                        strengthBar.style.width = '30%';
                        strengthBar.style.backgroundColor = '#EF4444'; 
                        strengthText.style.color = '#EF4444';
                        strengthText.innerText = 'Weak - Too short or simple';
                    } else if (strength === 2) {
                        strengthBar.style.width = '60%';
                        strengthBar.style.backgroundColor = '#F59E0B'; 
                        strengthText.style.color = '#F59E0B';
                        strengthText.innerText = 'Medium - Good start';
                    } else {
                        strengthBar.style.width = '100%';
                        strengthBar.style.backgroundColor = '#10B981'; 
                        strengthText.style.color = '#10B981';
                        strengthText.innerText = 'Strong - Great job!';
                    }
                });
            }
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

        <div class="form-section">
            <div class="form-container">
                <div class="form-logo">
                    <img src="{{ asset('assets/images/codeXpert.png') }}" alt="CodeXpert Logo">
                </div>
                
                <h1 class="welcome-title">Join CodeXpert</h1>
                <p class="welcome-subtitle">Start your journey from practice to pro</p>

                <form method="POST" action="{{ route('register') }}" id="register-form">
                    @csrf
                    
                    <input type="hidden" name="role" id="role" value="">
                    
                    <label class="role-label">Select Your Role</label>
                    <div class="role-buttons">
                        <button type="button" class="role-btn" data-role="learner">
                            Learner
                        </button>
                        <button type="button" class="role-btn" data-role="reviewer">
                            Reviewer
                        </button>
                    </div>

                    <div id="role-error" class="error-text">
                        Please select a role (Learner or Reviewer) to continue.
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

                    <div class="form-group password-container">
                        <input id="password" name="password" type="password" autocomplete="new-password" required 
                               class="form-input" 
                               placeholder="Password">
                        
                        <div class="strength-meter">
                            <div id="strength-bar"></div>
                        </div>
                        <p id="strength-text"></p>

                        <ul class="requirements-list">
                            <li id="req-length" class="invalid">At least 8 characters</li>
                            <li id="req-mix" class="invalid">Mix of Upper, Lower, Number & Symbol</li>
                        </ul>

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