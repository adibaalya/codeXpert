<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hackathon Hub - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.dashboardCSS')
    @include('layouts.navCSS')
    @include('layouts.hackathonCSS')
</head>
<body>
    <!-- Header (Same as Dashboard) -->
    <div class="header">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item" onclick="window.location.href='{{ route('learner.dashboard') }}'">Dashboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.practice') }}'">Practice</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.leaderboard') }}'">Leaderboard</button>
                <button class="nav-item active" onclick="window.location.href='{{ route('learner.hackathon') }}'">Hackathon</button>
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
    <div class="main-content">
        <!-- Hero Section -->
        <div class="page-header">
            <h1 class="page-title">Hackathon Hub</h1>
            <p class="page-subtitle">Compete, collaborate, and create amazing projects</p>
        </div>

        <!-- Featured Challenge - Nearest Hackathon (Only show if there's a hackathon) -->
        @if(isset($featuredHackathon) && $featuredHackathon)
        <div class="featured-challenge">
            <div class="featured-badge">
                <div class="live-dot"></div>
                {{ $featuredHackathon->status === 'live' ? 'Live Now' : 'Upcoming' }}
            </div>
            <h2>{{ $featuredHackathon->name }}</h2>
            <p>{{ $featuredHackathon->description }}</p>
            
            <div class="featured-stats">
                <div class="featured-stat">
                    <div class="featured-stat-icon">💰</div>
                    <div class="featured-stat-info">
                        <div class="featured-stat-label">Total Prize Pool</div>
                        <div class="featured-stat-value">RM {{ number_format($featuredHackathon->prize_pool) }}</div>
                    </div>
                </div>
                <div class="featured-stat">
                    <div class="featured-stat-icon">👥</div>
                    <div class="featured-stat-info">
                        <div class="featured-stat-label">Participants</div>
                        <div class="featured-stat-value">{{ $featuredHackathon->participants }}</div>
                    </div>
                </div>
                <div class="featured-stat">
                    <div class="featured-stat-icon">🎯</div>
                    <div class="featured-stat-info">
                        <div class="featured-stat-label">Category</div>
                        <div class="featured-stat-value">{{ $featuredHackathon->category }}</div>
                    </div>
                </div>
            </div>
            
            @if($featuredHackathon->registration_link)
                <a href="{{ $featuredHackathon->registration_link }}" target="_blank" class="featured-btn">
                    Register Now →
                </a>
            @else
                <button class="featured-btn" disabled style="opacity: 0.7; cursor: not-allowed;">
                    Registration Coming Soon
                </button>
            @endif
            
            <div class="countdown-timer">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span style="opacity: 0.9;">Time Remaining</span>
                <div style="display: flex; gap: 16px; margin-left: 8px;">
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: 700; line-height: 1;">{{ $featuredHackathon->countdown['months'] }}</div>
                        <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Months</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: 700; line-height: 1;">{{ $featuredHackathon->countdown['days'] }}</div>
                        <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Days</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: 700; line-height: 1;">{{ $featuredHackathon->countdown['hours'] }}</div>
                        <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Hours</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Section Header with Filters -->
        <div class="section-header">
            <h2 class="section-title">Hackathon Events</h2>
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="filterHackathons('all')">All</button>
                <button class="filter-tab" onclick="filterHackathons('ai')">AI/ML</button>
                <button class="filter-tab" onclick="filterHackathons('web')">Web Development</button>
                <button class="filter-tab" onclick="filterHackathons('mobile')">Mobile Apps</button>
                <button class="filter-tab" onclick="filterHackathons('security')">Security</button>
            </div>
        </div>

        <!-- Hackathons Grid with Stats Sidebar -->
        <div class="hackathon-grid-container">
            <!-- Hackathons Grid -->
            <div class="hackathon-grid">
                @forelse($hackathons ?? [] as $hackathon)
                <div class="hackathon-card" data-category="{{ strtolower($hackathon->category) }}">
                    <div class="hackathon-card-header">
                        <span class="hackathon-status-badge {{ $hackathon->status }}">
                            @if($hackathon->status === 'live')
                                <div class="live-dot"></div>
                                Live Now
                            @else
                                Upcoming
                            @endif
                        </span>
                    </div>
                    
                    <h3>{{ $hackathon->name }}</h3>
                    <p class="hackathon-card-description">{{ Str::limit($hackathon->description, 100) }}</p>
                    
                    <div class="hackathon-card-stats">
                        <div class="hackathon-card-stat">
                            <div class="hackathon-card-stat-icon">💰</div>
                            <div class="hackathon-card-stat-label">Prize Pool</div>
                            <div class="hackathon-card-stat-value">RM {{ number_format($hackathon->prize_pool) }}</div>
                        </div>
                        <div class="hackathon-card-stat">
                            <div class="hackathon-card-stat-icon">👥</div>
                            <div class="hackathon-card-stat-label">Registered</div>
                            <div class="hackathon-card-stat-value">{{ $hackathon->participants ?? 0 }}</div>
                        </div>
                        <div class="hackathon-card-stat">
                            <div class="hackathon-card-stat-icon">{{ $hackathon->status === 'live' ? '⏰' : '📅' }}</div>
                            <div class="hackathon-card-stat-label">{{ $hackathon->status === 'live' ? 'Days Left' : 'Starts In' }}</div>
                            <div class="hackathon-card-stat-value {{ $hackathon->status === 'live' ? 'highlight' : '' }}">{{ $hackathon->days_remaining }}</div>
                        </div>
                    </div>
                    
                    <div class="hackathon-card-footer">
                        <span class="hackathon-tag">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            </svg>
                            {{ $hackathon->category }}
                        </span>
                        <span class="hackathon-tag">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $hackathon->location ?? 'Virtual' }}
                        </span>
                    </div>
                    
                    @if($hackathon->registration_link)
                        <a href="{{ $hackathon->registration_link }}" target="_blank" class="hackathon-card-button primary">
                            Join Now →
                        </a>
                    @else
                        <button class="hackathon-card-button secondary" disabled>
                            Link Not Available
                        </button>
                    @endif
                </div>
                @empty
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="empty-state-icon">🎯</div>
                    <h3>No Active Hackathons</h3>
                    <p>There are currently no active or upcoming hackathons. Check back soon for exciting competitions!</p>
                </div>
                @endforelse
            </div>

            <!-- Stats Sidebar -->
            <div class="stats-sidebar">
                <div class="stats-sidebar-header">
                    <h3 class="stats-sidebar-title">Hackathon Statistic</h3>
                    <p class="stats-sidebar-subtitle">Track current opportunities</p>
                </div>
                
                <div class="stats-sidebar-divider"></div>
                
                <div class="stats-sidebar-item">
                    <span class="stats-sidebar-label">Active Hackathons</span>
                    <span class="stats-sidebar-value">{{ $activeCount ?? 0 }}</span>
                </div>
                
                <div class="stats-sidebar-item">
                    <span class="stats-sidebar-label">Total Prizes</span>
                    <span class="stats-sidebar-value">RM {{ number_format(($totalPrizes ?? 0)) }}</span>
                </div>
                
                <div class="stats-sidebar-item">
                    <span class="stats-sidebar-label">Total Participants</span>
                    <span class="stats-sidebar-value">{{ number_format($totalParticipants ?? 0) }}</span>
                </div>
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

        // Filter Hackathons
        function filterHackathons(category) {
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            // Filter cards
            const cards = document.querySelectorAll('.hackathon-card');
            cards.forEach(card => {
                if (category === 'all') {
                    card.style.display = 'block';
                } else {
                    const cardCategory = card.getAttribute('data-category');
                    if (cardCategory.includes(category)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        }
    </script>
</body>
</html>
