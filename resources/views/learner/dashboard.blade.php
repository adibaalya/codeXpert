<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learner Dashboard - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @include('layouts.dashboardCSS')
    @include('layouts.navCSS')

</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item active" onclick="window.location.href='{{ route('learner.dashboard') }}'">Dashboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.practice') }}'">Practice</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.leaderboard') }}'">Leaderboard</button>
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
    <div class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome Back, {{ $learner->username }}</h1>
            <p class="welcome-subtitle">Continue your coding journey and master new skills</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <!-- Current Level -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-label">Current Level</div>
                    <span class="stat-badge purple">Level</span>
                </div>
                <div class="stat-value">{{ $currentLevel }}</div>
                <div class="stat-progress">
                    <div class="stat-progress-bar" style="width: {{ min($xpProgress, 100) }}%"></div>
                </div>
                <div class="stat-footer">{{ 100-number_format($xpProgress, 0) }}% to Level {{ $currentLevel + 1 }}</div>
            </div>

            <!-- XP Points -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-label">Experience Points</div>
                    <span class="stat-badge info">XP</span>
                </div>
                <div class="stat-value">{{ number_format($xpPoints) }}</div>
                <div class="stat-progress">
                    <div class="stat-progress-bar" style="width: {{ min($xpProgress, 100) }}%"></div>
                </div>
                <div class="stat-footer">{{ number_format($nextLevelXP - $xpPoints) }} XP to next level</div>
            </div>

            <!-- Current Streak -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-label">Current Streak</div>
                    <span class="stat-badge urgent">{{ $currentStreak > 0 ? 'Hot!' : 'Start!' }}</span>
                </div>
                <div class="stat-value">{{ $currentStreak }}</div>
                <div class="stat-progress">
                    <div class="stat-progress-bar" style="width: {{ min(($currentStreak / 30) * 100, 100) }}%"></div>
                </div>
                <div class="stat-footer">{{ $currentStreak > 0 ? 'days in a row' : 'Start practicing today!' }}</div>
            </div>

            <!-- Achievements -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-label">Achievements</div>
                    <span class="stat-badge trending">{{ $achievements > 0 ? '↗' : '🎖️' }}</span>
                </div>
                <div class="stat-value">{{ $achievements }}</div>
                <div class="stat-progress">
                    <div class="stat-progress-bar" style="width: {{ min(($achievements / 8) * 100, 100) }}%"></div>
                </div>
                <div class="stat-footer">{{ 8 - $achievements }} more to unlock</div>
            </div>
        </div>

         <!-- Today's Challenge -->
         <div class="todays-challenge-card">
            <div class="challenge-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <circle cx="12" cy="12" r="6"/>
                    <circle cx="12" cy="12" r="2"/>
                </svg>
            </div>
            <div class="challenge-content">
                <div class="challenge-header">
                    <h2 class="challenge-title">Today's Challenge</h2>
                    <span class="challenge-badge">✨ AI Recommended</span>
                </div>
                <h3 class="challenge-name">{{ $todaysChallenge['title'] }}</h3>
                <p class="challenge-description">
                    {{ $todaysChallenge['description'] }}
                </p>
                <div class="challenge-tags">
                    <span class="challenge-tag"><span class="tag-icon">&lt;/&gt;</span> {{ $todaysChallenge['language'] }}</span>
                    <span class="challenge-tag">{{ $todaysChallenge['difficulty'] }}</span>
                    <span class="challenge-tag">{{ $todaysChallenge['topic'] }}</span>
                    <span class="challenge-tag"><span class="tag-icon">⏱</span> {{ $todaysChallenge['estimated_time'] }}</span>
                </div>
                @if($todaysChallenge['question_id'])
                    <button class="challenge-start-btn" onclick="window.location.href='{{ route('learner.coding.show', ['questionId' => $todaysChallenge['question_id']]) }}'">
                        Start Challenge →
                    </button>
                @else
                    <button class="challenge-start-btn" onclick="window.location.href='{{ route('learner.customization') }}'">
                        Customize Learning Path →
                    </button>
                @endif
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Weekly Activity Chart -->
            <div class="activity-card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Weekly Activity</h2>
                        <p class="card-subtitle">Your practice progress this week</p>
                    </div>
                    <div class="weekly-total">
                        <div class="weekly-total-label">Total This Week</div>
                        <div class="weekly-total-value">
                            {{ collect($weeklyData)->sum('count') }}
                            <span class="trend-icon">📈</span>
                        </div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>

            <!-- Language Progress -->
            <div class="language-progress-card">
                <div class="language-progress-header">
                    <div>
                        <h2 class="language-progress-title">Language Progress</h2>
                        <p class="language-progress-subtitle">Track your mastery across languages</p>
                    </div>
                </div>
                
                <div class="language-list">
                    @forelse($proficiencies as $proficiency)
                        @php
                            // Calculate percentage based on questions solved / total questions
                            $solvedCount = $proficiency->solved ?? 0;
                            $totalCount = $proficiency->total ?? 1; // Avoid division by zero
                            $questionPercentage = $totalCount > 0 ? round(($solvedCount / $totalCount) * 100) : 0;
                            
                            // Color mapping for different languages
                            $colorMap = [
                                'Python' => '#4C6EF5',
                                'JavaScript' => '#F59E0B',
                                'Java' => '#F97316',
                                'C++' => '#FF6B35',
                                'C#' => '#8B5CF6',
                                'Ruby' => '#CC342D',
                                'PHP' => '#6366F1',
                                'C' => '#EF4444',
                            ];
                            
                            $color = $colorMap[$proficiency->language] ?? '#6B7280';
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
                                        <h4 style="font-size: 18px; font-weight: 700; color: #1F2937; margin: 0 0 2px 0;">{{ $proficiency->language }}</h4>
                                        <p style="font-size: 12px; color: #6B7280; margin: 0;">{{ $proficiency->solved }}/{{ $proficiency->total }} problems</p>
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
                
                <button onclick="window.location.href='{{ route('learner.customization') }}'" style="width: 100%; padding: 16px; background: white; border: 2px dashed #D1D5DB; border-radius: 12px; color: #6B7280; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s ease; margin-top: 20px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add Language
                </button>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="bottom-grid">
            <!-- Hackathons Section -->
            <div class="activity-card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Hackathons</h2>
                        <p class="card-subtitle">Upcoming competitions</p>
                    </div>
                    <span class="stat-badge urgent">{{ $hackathons->count() }} active</span>
                </div>
                
                @forelse($hackathons as $hackathon)
                    <div class="hackathon-item {{ $hackathon['status'] }}">
                        @if($hackathon['status'] === 'live')
                            <div class="hackathon-badge live-badge">
                                <span class="live-dot"></span>
                                LIVE NOW
                            </div>
                        @else
                            <div class="hackathon-badge upcoming-badge">
                                UPCOMING
                            </div>
                        @endif
                        
                        <h3 class="hackathon-title">{{ $hackathon['name'] }}</h3>
                        <p class="hackathon-description">{{ Str::limit($hackathon['description'], 100) }}</p>
                        
                        <div class="hackathon-stats">
                            <div class="hackathon-stat">
                                <div class="hackathon-stat-icon">💰</div>
                                <div>
                                    <div class="hackathon-stat-label">Prize Pool</div>
                                    <div class="hackathon-stat-value">RM {{ $hackathon['prize'] }}</div>
                                </div>
                            </div>
                            <div class="hackathon-stat">
                                <div class="hackathon-stat-icon">{{ $hackathon['status'] === 'live' ? '⏰' : '📅' }}</div>
                                <div>
                                    <div class="hackathon-stat-label">{{ $hackathon['status'] === 'live' ? 'Time Left' : 'Starts In' }}</div>
                                    <div class="hackathon-stat-value {{ $hackathon['status'] === 'live' ? 'red' : 'blue' }}">
                                        {{ $hackathon['days'] }} {{ $hackathon['daysLabel'] }}
                                    </div>
                                </div>
                            </div>
                            <div class="hackathon-stat">
                                <div class="hackathon-stat-icon">👥</div>
                                <div>
                                    <div class="hackathon-stat-label">{{ $hackathon['status'] === 'live' ? 'Teams' : 'Interested' }}</div>
                                    <div class="hackathon-stat-value">{{ $hackathon['participants'] }}</div>
                                </div>
                            </div>
                        </div>
                        
                        @if($hackathon['link'])
                            <a href="{{ $hackathon['link'] }}" target="_blank" class="hackathon-register-btn" style="display: block; text-align: center; text-decoration: none;">
                                Register Now →
                            </a>
                        @else
                            <button class="hackathon-register-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
                                Link Not Available
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="hackathon-item upcoming">
                        <div class="hackathon-badge upcoming-badge">
                            📅 NO HACKATHONS
                        </div>
                        <h3 class="hackathon-title">No Active Hackathons</h3>
                        <p class="hackathon-description">There are currently no active or upcoming hackathons. Check back soon for exciting competitions!</p>
                    </div>
                @endforelse
            </div>

            <!-- Leaderboard Section -->
            <div class="activity-card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Leaderboard</h2>
                        <p class="card-subtitle">Top performers this month</p>
                    </div>
                </div>
                
                <div class="leaderboard-list">
                    @foreach($leaderboardData['top_five'] as $leader)
                        <div class="leaderboard-item {{ $leader['rank_class'] }} {{ $leader['is_current_user'] ? 'highlighted-user' : '' }}">
                            <div class="leaderboard-rank rank-{{ $leader['position'] }}">{{ $leader['position'] }}</div>
                            <div class="leaderboard-info">
                                <div class="leaderboard-name">
                                    {{ $leader['is_current_user'] ? $leader['username']  : $leader['username'] }}
                                </div>
                                <div class="leaderboard-title">{{ $leader['title'] }}</div>
                            </div>
                            <div class="leaderboard-xp">
                                <span class="xp-value">{{ number_format($leader['xp']) }}</span>
                                <span class="xp-label">XP</span>
                            </div>
                        </div>
                    @endforeach
                    
                    @if($leaderboardData['current_user'])
                        <div class="leaderboard-divider">
                            <span>Your Position</span>
                        </div>
                        
                        <!-- Current User Position -->
                        <div class="leaderboard-item current-user highlighted-user">
                            <div class="leaderboard-rank rank-current">{{ $leaderboardData['current_user']['position'] }}</div>
                            <div class="leaderboard-info">
                                <div class="leaderboard-name">You ({{ $leaderboardData['current_user']['username'] }})</div>
                                <div class="leaderboard-trending">
                                    @if($leaderboardData['current_user']['rank_change'] > 0)
                                        <span class="trending-up">↗ {{ $leaderboardData['current_user']['rank_change_label'] }}</span>
                                    @elseif($leaderboardData['current_user']['rank_change'] < 0)
                                        <span class="trending-down">↘ {{ $leaderboardData['current_user']['rank_change'] }}</span>
                                    @else
                                        <span class="trending-neutral">→ 0</span>
                                    @endif
                                    <span class="trending-label">this week</span>
                                </div>
                            </div>
                            <div class="leaderboard-xp">
                                <span class="xp-value">{{ number_format($leaderboardData['current_user']['xp']) }}</span>
                                <span class="xp-label">XP</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Weekly Activity Chart
        const ctx = document.getElementById('weeklyChart').getContext('2d');
        const weeklyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($weeklyData)->pluck('day')) !!},
                datasets: [{
                    label: 'Practice Sessions',
                    data: {!! json_encode(collect($weeklyData)->pluck('count')) !!},
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        
                        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                        gradient.addColorStop(0, '#FFB83D');
                        gradient.addColorStop(1, '#FF6B35');
                        return gradient;
                    },
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 0
                },
                hover: {
                    mode: null
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        borderRadius: 8,
                        titleColor: '#F9FAFB',
                        bodyColor: '#F9FAFB',
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Sessions: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display : false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6B7280',
                            font: {
                                size: 12,
                                weight: 600
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6B7280',
                            font: {
                                size: 12,
                                weight: 600
                            }
                        }
                    }
                }
            }
        });

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
    </script>
</body>
</html>
