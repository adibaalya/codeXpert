<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('js/navBar.js') }}"></script>
    @include('layouts.profileCSS')
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
                <button class="nav-item" onclick="window.location.href='{{ route('learner.leaderboard') }}'">Leaderboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.hackathon') }}'">Hackathon</button>
                <button class="nav-item active" onclick="window.location.href='{{ route('learner.profile') }}'">Profile</button>
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
                                <img src="{{ asset('storage/' . $learner->profile_photo) }}" alt="{{ $learner->username }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
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
    <div class="profile-container">
        <!-- Left Sidebar - Profile Card -->
        <div class="profile-sidebar">
            <div class="profile-avatar">
                @if($learner->profile_photo)
                    <img src="{{ asset('storage/' . $learner->profile_photo) }}" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                @endif
            </div>
            
            <h2 class="profile-name">{{ $learner->username }}</h2>
            <p class="profile-email">{{ $learner->email }}</p>
            
            
            <div class="profile-info-section">
                <div class="profile-info-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div class="info-content">
                        <div class="info-label">Global Rank</div>
                        <div class="info-value">#{{ $globalRank }}</div>
                    </div>
                </div>
                
                <div class="profile-info-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div class="info-content">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">{{ $learner->created_at->format('F Y') }}</div>
                    </div>
                </div>
            </div>
            
            <button class="challenge-start-btn" onclick="window.location.href='{{ route('learner.profile.edit') }}'" style="width: 100%; margin-top: 20px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Profile
            </button>
        </div>

        <!-- Right Main Content -->
        <div class="profile-main">
            <!-- Level Progress & Statistics Grid -->
            <div class="progress-stats-grid">
                <!-- Left Column: Level Progress -->
                <div class="left-column">
                    <div class="stats-section">
                        <h2 class="stats-title">Level Progress</h2>
                        <div style="background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%); border-radius: 16px; padding: 20px; color: white;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                                <div>
                                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 2px;">Level {{ $levelProgress['current_level'] }}</div>
                                    <div style="font-size: 18px; font-weight: 600;">{{ number_format($currentXP) }} / {{ number_format($nextLevelXP) }} XP</div>
                                </div>
                                <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700;">
                                    {{ $levelProgress['current_level'] }}
                                </div>
                            </div>
                            
                            <div style="margin-bottom: 10px;">
                                <div style="width: 100%; height: 12px; background: rgba(255, 255, 255, 0.3); border-radius: 12px; overflow: hidden;">
                                    <div style="height: 100%; background: white; border-radius: 12px; width: {{ round((($learner->xpPoints ?? 2847) / ($learner->nextLevelXP ?? 3000)) * 100) }}%; transition: width 1s ease;"></div>
                                </div>
                            </div>
                            
                            <div style="font-size: 13px; opacity: 0.9;">
                                {{ number_format($nextLevelXP - $currentXP) }} XP until Level {{ $currentLevel + 1 }}
                            </div>
                        </div>
                    </div>

                    <!-- Language Proficiency Section -->
                    <div class="competency-section">
                    <h2 class="stats-title">Language Proficiency</h2>
                    @forelse($proficiencies as $proficiency)
                        @php
                            // Calculate percentage based on questions solved / total questions
                            $solvedCount = $proficiency['solved'] ?? 0;
                            $totalCount = $proficiency['total'] ?? 1; // Avoid division by zero
                            $questionPercentage = $totalCount > 0 ? round(($solvedCount / $totalCount) * 100) : 0;
                            
                            // Color mapping for different languages
                            $colorMap = [
                                'Python' => '#4C6EF5',
                                'JavaScript' => '#F59E0B',
                                'Java' => '#F97316',
                                'C++' => '#EC4899',
                                'C#' => '#8B5CF6',
                                'Ruby' => '#CC342D',
                                'PHP' => '#6366F1',
                                'C' => '#EF4444',
                            ];
                            
                            $color = $colorMap[$proficiency['language']] ?? '#6B7280';
                        @endphp
                        
                        <div class="language-item" style="margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <div style="width: 36px; height: 36px; background: {{ $color }}; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                            <polyline points="16 18 22 12 16 6"></polyline>
                                            <polyline points="8 6 2 12 8 18"></polyline>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 18px; font-weight: 700; color: #1F2937; margin: 0 0 2px 0;">{{ $proficiency['language'] }}</h4>
                                        <p style="font-size: 12px; color: #6B7280; margin: 0;">{{ $proficiency['solved'] }}/{{ $proficiency['total'] }} problems</p>
                                    </div>
                                </div>
                                <div style="background: {{ $color }}; color: white; padding: 5px 15px; border-radius: 20px; font-size: 16px; font-weight: 500;">
                                    {{ $questionPercentage }}%
                                </div>
                            </div>
                            <div style="width: 100%; height: 12px; background: #E5E7EB; border-radius: 12px; overflow: hidden;">
                                <div style="height: 100%; background: {{ $color }}; border-radius: 12px; width: {{ $questionPercentage }}%; transition: width 0.5s ease;"></div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 40px 20px;">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" style="margin: 0 auto 16px;">
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            </svg>
                            <h4 style="font-size: 16px; font-weight: 600; color: #4B5563; margin-bottom: 8px;">No Languages Selected</h4>
                            <p style="font-size: 14px; color: #9CA3AF;">Start your coding journey by selecting languages</p>
                        </div>
                    @endforelse
                    </div>

                    <!-- Achievements Section -->
                    <div class="achievements-section">
                        <h2 class="section-title">Achievements ({{ $earnedBadges->count() }})</h2>
                        
                        @php
                            // Badge Configuration with icons and colors
                            $badgeConfig = [
                                // LEARNER BADGES
                                'first_problem_solved' => ['icon' => 'fa-star', 'color' => 'orange', 'title' => 'First Problem Solved', 'description' => 'Completed your first challenge'],
                                'beginner_solver' => ['icon' => 'fa-seedling', 'color' => 'green', 'title' => 'Beginner Solver', 'description' => 'Solved 10 coding problems'],
                                'active_learner' => ['icon' => 'fa-rocket', 'color' => 'blue', 'title' => 'Active Learner', 'description' => 'Solved 25 coding problems'],
                                'problem_solver' => ['icon' => 'fa-brain', 'color' => 'purple', 'title' => 'Problem Solver', 'description' => 'Solved 50 coding problems'],
                                'consistent_learner' => ['icon' => 'fa-calendar-check', 'color' => 'green', 'title' => 'Consistent Learner', 'description' => '7 day coding streak'],
                                'accuracy_improver' => ['icon' => 'fa-bullseye', 'color' => 'orange', 'title' => 'Accuracy Improver', 'description' => '80% accuracy over 20 attempts'],
                                'language_confident' => ['icon' => 'fa-code', 'color' => 'blue', 'title' => 'Language Confident', 'description' => 'Solved 30 problems in one language'],
                            ];
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
                                    <p style="font-size: 14px; color: #9CA3AF;">Start solving problems to unlock achievements!</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Right Column: Statistics -->
                <div class="stats-section">
                    <h2 class="stats-title">Statistics</h2>
                    
                    <div class="stats-list-vertical">
                        <div class="stat-item-vertical">
                            <div class="stat-label">Challenges Completed</div>
                            <div class="stat-value">{{ $stats['totalAttempts'] ?? 0 }}</div>
                        </div>
                        
                        <div class="stat-divider"></div>
                        
                        <div class="stat-item-vertical">
                            <div class="stat-label">Success Rate</div>
                            <div class="stat-value">{{ $stats['accuracyRate'] ?? 0 }}%</div>
                        </div>
                        
                        <div class="stat-divider"></div>
                        
                        <div class="stat-item-vertical">
                            <div class="stat-label">Current Streak</div>
                            <div class="stat-value">{{ $stats['currentStreak'] ?? 0 }} days</div>
                        </div>
                        
                        <div class="stat-divider"></div>
                        
                        <div class="stat-item-vertical">
                            <div class="stat-label">Total XP</div>
                            <div class="stat-value">{{ number_format($stats['totalXP'] ?? 0) }}</div>
                        </div>
                        
                        <div class="stat-divider"></div>
                        
                        <div class="stat-item-vertical">
                            <div class="stat-label">Badges Earned</div>
                            <div class="stat-value">{{ $stats['badgesEarned'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
