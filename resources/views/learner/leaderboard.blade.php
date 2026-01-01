<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('js/navBar.js') }}"></script>
    @include('layouts.learner.leaderboardCSS')
    @include('layouts.navCSS')
    
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert.png') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item" onclick="window.location.href='{{ route('learner.dashboard') }}'">Dashboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.practice') }}'">Practice</button>
                <button class="nav-item active" onclick="window.location.href='{{ route('learner.leaderboard') }}'">Leaderboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.hackathon') }}'">Hackathon</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ $learner->username }}</div>
                    <div class="user-role">Learner</div>
                </div>
                <div class="user-avatar" onclick="toggleUserMenu(event)">
                    @if($learner->profile_photo)
                        <img src="{{ asset('storage/' . $learner->profile_photo) }}" alt="{{ $learner->username }}">
                    @else
                        {{ strtoupper(substr($learner->username, 0, 1)) }}{{ strtoupper(substr($learner->username, 1, 1) ?? '') }}
                    @endif
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header">
                        <div class="user-dropdown-avatar">
                            @if($learner->profile_photo)
                                <img src="{{ asset('storage/' . $learner->profile_photo) }}" alt="{{ $learner->username }}">
                            @else
                                {{ strtoupper(substr($learner->username, 0, 2)) }}
                            @endif
                        </div>
                        <div>
                            <div class="user-dropdown-name">{{ $learner->username }}</div>
                            <div class="user-dropdown-email">{{ $learner->email }}</div>
                        </div>
                    </div>
                    
                    @if($learner->badge)
                    <div class="verified-badge-dropdown">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span>{{ $learner->badge }} Badge</span>
                    </div>
                    @endif
                    
                    <a href="{{ route('learner.customization') }}" class="user-dropdown-item">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Customize Learning Path</span>
                    </a>
                    
                    <div class="user-dropdown-divider"></div>
                    
                    <form method="POST" action="{{ route('learner.logout') }}" style="margin: 0;">
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

    <!-- Main Content -->
    <div class="leaderboard-container">
        <!-- Page Header -->
        <div class="leaderboard-header">
            <h1 class="leaderboard-main-title">Leaderboard</h1>
            <p class="leaderboard-subtitle">Compete with learners worldwide and climb to the top!</p>
        </div>

        <!-- User Current Rank Card -->
        <div class="user-rank-card">
            <div class="user-rank-info">
                <div class="user-rank-icon">
                    @if($learner->profile_photo)
                        <img src="{{ asset('storage/' . $learner->profile_photo) }}" alt="{{ $learner->username }}">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @endif
                </div>
                <div class="user-rank-details">
                    <h3>Your Current Rank</h3>
                    <p class="user-rank-number">#{{ $currentUserRank }}</p>
                </div>
            </div>
            
            <div class="user-stats-row">
                <div class="user-stat-item">
                    <h3>Your Total XP</h3>
                    <p class="user-stat-value">{{ number_format($currentUserXP) }}</p>
                </div>
                <div class="user-stat-item">
                    <h3>This Week</h3>
                    <p class="user-stat-value positive">+{{ number_format($currentUserWeeklyXP) }}</p>
                </div>
            </div>
        </div>

        <!-- Top Performers Section -->
        <div class="top-performers-section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Top Performers</h2>
                    <p class="section-subtitle">Current season rankings</p>
                </div>
            </div>

            <!-- Leaderboard Table -->
            <div class="leaderboard-table">
                <!-- Table Header -->
                <div class="leaderboard-table-header">
                    <div class="table-header-cell">Rank</div>
                    <div class="table-header-cell">Username</div>
                    <div class="table-header-cell align-right">Total XP</div>
                    <div class="table-header-cell align-right">This Week</div>
                </div>

                <!-- Leaderboard Rows -->
                @foreach($leaderboardData as $user)
                    @php
                        $rankClass = '';
                        $badgeClass = 'regular';
                        
                        if ($user['rank'] == 1) {
                            $rankClass = 'rank-1';
                            $badgeClass = 'top-1';
                        } elseif ($user['rank'] == 2) {
                            $rankClass = 'rank-2';
                            $badgeClass = 'top-2';
                        } elseif ($user['rank'] == 3) {
                            $rankClass = 'rank-3';
                            $badgeClass = 'top-3';
                        }
                    @endphp
                    
                    <div class="leaderboard-row {{ $rankClass }}">
                        <div class="rank-badge-cell">
                            <div class="rank-badge {{ $badgeClass }}">
                                @if($user['rank'] <= 3)
                                    {{ $user['rank'] }}
                                @else
                                    #{{ $user['rank'] }}
                                @endif
                            </div>
                        </div>
                        <div class="username-cell">{{ $user['username'] }}</div>
                        <div class="xp-cell">
                            <span class="xp-amount">{{ number_format($user['xp']) }}</span>
                            <span class="xp-label-small">XP</span>
                        </div>
                        <div class="week-cell">
                            <span class="week-gain positive">{{ number_format($user['weeklyXP']) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>
