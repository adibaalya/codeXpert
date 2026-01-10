<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reviewer->username ?? 'Reviewer' }} - Profile | CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('js/navBar.js') }}"></script>
    @include('layouts.profileCSS')
    @include('layouts.navCSS')
</head>
<body class="reviewer-body">
    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert.png') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.dashboard') }}'">Dashboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.review') }}'">Review</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.generate') }}'">Generate</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.history') }}'">History</button>
                <button class="nav-item active-reviewer" onclick="window.location.href='{{ route('reviewer.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ $reviewer->username }}</div>
                    <div class="user-role">Reviewer</div>
                </div>
                <div class="user-avatar-reviewer" onclick="toggleUserMenu(event)">
                    @if($reviewer->profile_photo)
                        <img src="{{ asset('storage/' . $reviewer->profile_photo) }}" alt="{{ $reviewer->username }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr($reviewer->username, 0, 1)) }}{{ strtoupper(substr($reviewer->username, 1, 1) ?? '') }}
                    @endif
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header-reviewer">
                        <div class="user-dropdown-avatar">
                            @if($reviewer->profile_photo)
                                <img src="{{ asset('storage/' . $reviewer->profile_photo) }}" alt="{{ $reviewer->username }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                                {{ strtoupper(substr($reviewer->username, 0, 1)) }}{{ strtoupper(substr($reviewer->username, 1, 1) ?? '') }}
                            @endif
                        </div>
                        <div>
                            <div class="user-dropdown-name">{{ $reviewer->username }}</div>
                            <div class="user-dropdown-email">{{ $reviewer->email }}</div>
                        </div>
                    </div>
                    
                    @php
                        $competencyResult = \App\Models\CompetencyTestResult::where('reviewer_ID', $reviewer->reviewer_ID)
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

    <div class="profile-container">
        <!-- Left Sidebar -->
        <aside class="profile-sidebar">
            <div class="profile-avatar-reviewer">
                @if($reviewer->profile_photo)
                    <img src="{{ asset('storage/' . $reviewer->profile_photo) }}" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </div>

            <h1 class="profile-name">{{ $reviewer->username }}</h1>
            <p class="profile-email">{{ $reviewer->email }}</p>

            @if($competencyResults->isNotEmpty())
            <div class="verified-badge-reviewer">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>VERIFIED REVIEWER</span>
            </div>

            <div class="profile-info-section">
                <div class="profile-info-item-reviewer">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    <div class="info-content">
                        <div class="info-label">Expertise</div>
                        <div class="info-value">
                            @foreach($competencyResults as $index => $result)
                                {{ $result->language }}@if($index < $competencyResults->count() - 1), @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="profile-info-item-reviewer">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div class="info-content">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">{{ $reviewer->created_at->format('F Y') }}</div>
                    </div>
                </div>
            </div>
            @else
            <div class="profile-info-section">
                <div class="profile-info-item-reviewer">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div class="info-content">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">{{ $reviewer->created_at->format('F Y') }}</div>
                    </div>
                </div>
            </div>
            @endif
            
            <button class="challenge-start-btn-reviewer" onclick="window.location.href='{{ route('reviewer.profile.edit') }}'" style="width: 100%; margin-top: 20px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Profile
            </button>
        </aside>

        <!-- Main Content -->
        <main class="profile-main">
            <!-- Performance Statistics -->
            <section class="stats-section">
                <h2 class="stats-title">Performance Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="stat-label">Total Reviewed</div>
                        <div class="stat-value">{{ $stats['totalReviewed'] }}</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon green">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="stat-label">Corrections Made</div>
                        <div class="stat-value">{{ $stats['correctionsMade'] ?? 0 }}</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="stat-label">Achievements</div>
                        <div class="stat-value">{{ $reviewer->badges()->count() }}</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div class="stat-label">Current Streak</div>
                        <div class="stat-value">{{ $stats['currentStreak'] }} days</div>
                    </div>
                </div>
            </section>

            <!-- Competency Test Results -->
            <section class="competency-section">
                <h2 class="section-title">Competency Test Results</h2>

                @if($competencyResults->isNotEmpty())
                @foreach($competencyResults as $competencyResult)
                <div class="certification-header" style="cursor: pointer;" onclick="window.location.href='{{ route('reviewer.certificate.download', ['id' => $competencyResult->id]) }}'">
                    <div class="certification-info">
                        <h4>{{ $competencyResult->language }} Certification</h4>
                        <p>Successfully passed on {{ $competencyResult->completed_at->format('F Y') }}</p>
                        <p style="font-size: 14px; color: #666; margin-top: 5px;">Click to download certificate</p>
                    </div>
                    <div class="certified-badge">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>CERTIFIED</span>
                    </div>
                </div>

                <div class="scores-grid">
                    <div class="score-card">
                        <div class="score-header">
                            <div>
                                <div class="score-label">Correctness</div>
                                <div class="score-value">{{ $competencyResult->total_score }}%</div>
                            </div>
                            <div class="score-icon green">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: {{ $competencyResult->total_score }}%"></div>
                        </div>
                    </div>

                    <div class="score-card">
                        <div class="score-header">
                            <div>
                                <div class="score-label">Plagiarism Detection</div>
                                <div class="score-value">{{ $competencyResult->plagiarism_score }}%</div>
                            </div>
                            <div class="score-icon blue">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar blue" style="width: {{ $competencyResult->plagiarism_score }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div style="text-align: center; padding: 40px; color: #8e8e93;">
                    <p>No competency test results available</p>
                </div>
                @endif
            </section>

            <!-- Achievements -->
            <section class="achievements-section">
                <h2 class="section-title">Achievements ({{ $reviewer->badges()->count() }})</h2>
                
                @php
                    // Badge Configuration with icons and colors for REVIEWERS
                    $badgeConfig = [
                        // REVIEWER BADGES
                        'certified_reviewer' => ['icon' => 'fa-certificate', 'color' => 'blue', 'title' => 'Certified Reviewer', 'description' => 'Passed competency test'],
                        'first_review' => ['icon' => 'fa-check', 'color' => 'green', 'title' => 'First Review', 'description' => 'Completed your first review'],
                        'active_reviewer' => ['icon' => 'fa-bolt', 'color' => 'purple', 'title' => 'Active Reviewer', 'description' => 'Completed 10 reviews'],
                        'quality_checker' => ['icon' => 'fa-star', 'color' => 'orange', 'title' => 'Quality Checker', 'description' => '25 reviews with all approved'],
                        'error_spotter' => ['icon' => 'fa-exclamation-triangle', 'color' => 'orange', 'title' => 'Error Spotter', 'description' => 'Flagged 5 errors'],
                        'question_creator' => ['icon' => 'fa-plus-circle', 'color' => 'blue', 'title' => 'Question Creator', 'description' => 'Generated 5 questions'],
                        'creative_author' => ['icon' => 'fa-pen-fancy', 'color' => 'purple', 'title' => 'Creative Author', 'description' => 'Generated 20 questions'],
                        'trusted_reviewer' => ['icon' => 'fa-shield-alt', 'color' => 'green', 'title' => 'Trusted Reviewer', 'description' => '90% approval rate (min 10 reviews)'],
                    ];
                    
                    $earnedBadges = $reviewer->badges()->orderBy('earned_date', 'desc')->get();
                @endphp
                
                <div class="achievements-grid">
                    @forelse($earnedBadges as $badge)
                        @php
                            $config = $badgeConfig[$badge->badge_type] ?? [
                                'icon' => 'fa-trophy',
                                'color' => 'purple',
                                'title' => ucwords(str_replace('_', ' ', $badge->badge_type)),
                                'description' => 'Achievement unlocked'
                            ];
                        @endphp
                        
                        <div class="achievement-card earned">
                            <div class="achievement-icon {{ $config['color'] }}">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <div class="achievement-content">
                                <h4>{{ $config['title'] }}</h4>
                                <p>{{ $config['description'] }}</p>
                                <small style="color: #9CA3AF; font-size: 12px; margin-top: 4px; display: block;">
                                    Earned: {{ \Carbon\Carbon::parse($badge->earned_date)->format('M d, Y') }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="no-achievements" style="grid-column: 1 / -1; text-align: center; padding: 40px 20px;">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" style="margin: 0 auto 16px;">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <h4 style="font-size: 18px; font-weight: 600; color: #4B5563; margin-bottom: 8px;">No Achievements Yet</h4>
                            <p style="font-size: 14px; color: #9CA3AF;">Start reviewing questions to unlock achievements!</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</body>
</html>
