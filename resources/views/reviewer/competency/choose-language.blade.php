<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodeXpert - Choose Your Expertise</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('js/navBar.js') }}"></script>
    <script src="{{ asset('js/reviewer/competency.js') }}" defer></script>
    @include('layouts.app')
    @include('layouts.competencyCSS')
    @include('layouts.navCSS')
</head>
<body>
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
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ Auth::guard('reviewer')->user()->username }}</div>
                    <div class="user-role">Reviewer</div>
                </div>
                <div class="user-avatar-reviewer" onclick="toggleUserMenu(event)">
                    @if($reviewer->profile_photo)
                        <img src="{{ asset('storage/' . $reviewer->profile_photo) }}" alt="{{ $reviewer->username }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr($reviewer->username, 0, 1)) }}{{ strtoupper(substr($reviewer->username, 1, 1) ?? '') }}
                    @endif
                </div>
                
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

    <div class="container">
        <h1 class="title">Choose Your Expertise</h1>
        <p class="subtitle">Select the programming language you want to be tested on</p>
        

        <form action="{{ route('reviewer.competency.start') }}" method="POST">
            @csrf
            <div class="language-grid">
                @forelse($languageData as $index => $lang)
                    <div class="language-card {{ !$lang['isSufficient'] ? 'insufficient' : '' }}" 
                         onclick="{{ $lang['isSufficient'] ? "document.getElementById('lang-{$index}').click()" : '' }}" 
                         style="background: {{ $lang['cardBg'] }}; {{ !$lang['isSufficient'] ? 'opacity: 0.6; cursor: not-allowed;' : '' }}"
                         data-hover-bg="{{ $lang['hoverBg'] }}"
                         data-default-bg="{{ $lang['cardBg'] }}"
                         data-is-sufficient="{{ $lang['isSufficient'] ? 'true' : 'false' }}">
                        <input type="radio" name="language" id="lang-{{ $index }}" value="{{ $lang['name'] }}" style="display: none;" {{ $lang['isSufficient'] ? 'required' : 'disabled' }}>
                        <div class="language-icon-box" style="background-color: {{ $lang['iconBg'] }};">
                            <span style="color: white; font-size: 28px; font-weight: 700;">{{ $lang['icon'] }}</span>
                        </div>
                        <div class="language-name">{{ $lang['name'] }}</div>
                        <div class="language-description">{{ $lang['description'] }}</div>
                        
                        @if(!$lang['isSufficient'])
                            <div class="insufficient-badge">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="margin-right: 4px;">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Insufficient Questions
                            </div>
                            <div class="question-counts">
                                <div style="font-size: 11px; color: #666; margin-top: 8px;">
                                    MCQ: {{ $lang['questionCounts']['mcq'] }}/3 | 
                                    Evaluation: {{ $lang['questionCounts']['evaluation'] }}/3 | 
                                    Code: {{ $lang['questionCounts']['codeSolution'] }}/1
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;">
                        <p style="font-size: 18px; margin-bottom: 10px;">No languages available</p>
                        <p style="font-size: 14px;">Please contact the administrator to add competency test questions.</p>
                    </div>
                @endforelse
            </div>

            <div class="info-box">
                <div class="info-title">
                    <span>⏱️</span>
                    Test Duration: 45 minutes • Passing Score: 50%
                </div>
                <div class="info-text">
                    The competency test evaluates your expertise to become a CodeXpert Reviewer.
                    
                </div>
                <ul class="info-list">
                    <li><strong>Score ≥90%:</strong> Review all levels (Beginner, Intermediate, Advanced)</li>
                    <li><strong>Score ≥75%:</strong> Review Beginner and Intermediate levels</li>
                    <li><strong>Score ≥50%:</strong> Review Beginner level only</li>
                </ul>
            </div>

            <button class='submit-btn' type="submit">
                Start Test →
            </button>
        </form>
    </div>
</body>
</html>