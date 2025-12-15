<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodeXpert - Test Results</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.navCSS')
    @include('layouts.competencyCSS')
    
</head>
<body class="result-body">
    <!-- Header -->
    <div class="header" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000;">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.dashboard') }}'">Dashboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.review') }}'">Review</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.generate') }}'">Generate</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.history') }}'">History</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ Auth::guard('reviewer')->user()->username }}</div>
                    <div class="user-role">Reviewer</div>
                </div>
                <div class="user-avatar" onclick="toggleUserMenu(event)">
                    {{ strtoupper(substr(Auth::guard('reviewer')->user()->username, 0, 1)) }}{{ strtoupper(substr(Auth::guard('reviewer')->user()->username, 1, 1) ?? '') }}
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header">
                        <div class="user-dropdown-avatar">
                            {{ strtoupper(substr(Auth::guard('reviewer')->user()->username, 0, 2)) }}
                        </div>
                        <div>
                            <div class="user-dropdown-name">{{ Auth::guard('reviewer')->user()->username }}</div>
                            <div class="user-dropdown-email">{{ Auth::guard('reviewer')->user()->email }}</div>
                        </div>
                    </div>
                    
                    @php
                        $competencyResult = \App\Models\CompetencyTestResult::where('reviewer_ID', Auth::guard('reviewer')->user()->reviewer_ID)
                            ->where('passed', true)
                            ->latest()
                            ->first();
                    @endphp
                    
                    @if($competencyResult)
                    <div class="verified-badge-dropdown">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Verified Reviewer</span>
                    </div>
                    @endif
                    
                    <a href="{{ route('reviewer.competency.choose') }}" class="user-dropdown-item">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Take Competency Test</span>
                    </a>
                    
                    <div class="user-dropdown-divider"></div>
                    
                    <form method="POST" action="{{ route('reviewer.logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="user-dropdown-item logout">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="result-container">
        <div class="result-card">
            <!-- Tooltip -->
            @if($result->passed)
            <div class="result-tooltip">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Great! Now let's test your coding skills
            </div>
            @endif

            <!-- Header -->
            <h1 class="result-header-title">Test Complete</h1>
            <p class="result-header-subtitle">{{ $result->language }} Competency Assessment Results</p>

            <!-- Success Icon -->
            <div class="success-icon-wrapper">
                <div class="success-icon-circle {{ $result->passed ? '' : 'failed' }}">
                    @if($result->passed)
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    @else
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    @endif
                </div>
            </div>

            <!-- Message -->
            <h2 class="result-message-title {{ $result->passed ? '' : 'failed' }}">
                @if($result->passed)
                    Congratulations!
                @else
                    Not Passed
                @endif
            </h2>

            <!-- Congrats Box -->
            <div class="result-congrats-box {{ $result->passed ? '' : 'failed' }}">
                <p class="result-congrats-text {{ $result->passed ? '' : 'failed' }}">
                    @if($result->passed)
                        You've successfully passed the {{ $result->language }} competency test! Your expertise has been verified.
                    @else
                        Unfortunately, you didn't meet the minimum requirements. Please review the material and try again.
                    @endif
                </p>
            </div>

            <!-- Scores Section -->
            <div class="result-scores-section">
                <!-- Plagiarism Detection -->
                <div class="result-score-item">
                    <div class="result-score-header">
                        <div class="result-score-label">Plagiarism Detection</div>
                        <div class="result-score-value" style="color: #10B981;">{{ $result->plagiarism_score }}%</div>
                    </div>
                    <div class="result-score-sublabel">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Originality verification
                        @if($result->passed)
                            <span style="color: #10B981; font-weight: 600;">✓ Passed</span>
                        @endif
                    </div>
                    <div class="result-progress-bar">
                        <div class="result-progress-fill" style="width: {{ $result->plagiarism_score }}%;"></div>
                    </div>
                </div>

                <!-- Correctness Score -->
                <div class="result-score-item">
                    <div class="result-score-header">
                        <div class="result-score-label">Correctness Score</div>
                        <div class="result-score-value" style="color: {{ $result->total_score >= 75 ? '#10B981' : ($result->total_score >= 50 ? '#F59E0B' : '#EF4444') }};">
                            {{ $result->total_score }}%
                        </div>
                    </div>
                    <div class="result-score-sublabel">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Answer accuracy rate
                        @if($result->passed)
                            <span style="color: #10B981; font-weight: 600;">✓ Passed</span>
                        @endif
                    </div>
                    <div class="result-progress-bar">
                        <div class="result-progress-fill {{ $result->total_score >= 75 ? '' : ($result->total_score >= 50 ? 'medium' : 'low') }}" 
                             style="width: {{ $result->total_score }}%;"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="result-actions">
                @if(!$result->passed)
                    <a href="{{ route('reviewer.competency.choose') }}" class="result-btn result-btn-secondary">
                        Test Another Language
                    </a>
                @endif
                <a href="{{ route('reviewer.dashboard') }}" class="result-btn result-btn-primary">
                    Continue to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle User Dropdown Menu
        function toggleUserMenu(event) {
            event.stopPropagation();
            const userDropdown = document.getElementById('userDropdown');
            userDropdown.classList.toggle('show');
        }

        // Close User Dropdown Menu when clicking outside
        window.onclick = function(event) {
            const userDropdown = document.getElementById('userDropdown');
            if (!event.target.matches('.user-avatar')) {
                if (userDropdown.classList.contains('show')) {
                    userDropdown.classList.remove('show');
                }
            }
        }

        // Animate progress bars on load
        window.addEventListener('load', function() {
            const progressBars = document.querySelectorAll('.result-progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html>
