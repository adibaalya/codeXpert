<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer Dashboard - CodeXpert</title>
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
                <button class="nav-item active" onclick="window.location.href='{{ route('reviewer.dashboard') }}'">Dashboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.review') }}'" >Review</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.generate') }}'">Generate</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.history') }}'">History</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ $reviewer->username }}</div>
                    <div class="user-role">Reviewer</div>
                </div>
                <div class="user-avatar" onclick="toggleUserMenu(event)">
                    {{ strtoupper(substr($reviewer->username, 0, 1)) }}{{ strtoupper(substr($reviewer->username, 1, 1) ?? '') }}
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header">
                        <div class="user-dropdown-avatar">
                            {{ strtoupper(substr($reviewer->username, 0, 2)) }}
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

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome Back, {{ $reviewer->username }}</h1>
            <p class="welcome-subtitle">Here's your comprehensive review activity overview</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <!-- Pending Reviews -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon pending">⏰</div>
                    <span class="stat-badge urgent">Urgent</span>
                </div>
                <div class="stat-label">Pending Reviews</div>
                <div class="stat-value">{{ $pendingReviews }}</div>
                <div class="stat-progress">
                    <div class="stat-progress-bar" style="width: {{ min(($pendingReviews / 30) * 100, 100) }}%"></div>
                </div>
                <div class="stat-footer">{{ round(min(($pendingReviews / 30) * 100, 100), 2) }}%</div>
            </div>

            <!-- Approved Today -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon approved">✓</div>
                    <span class="stat-badge trending">↗</span>
                </div>
                <div class="stat-label">Approved Today</div>
                <div class="stat-value">{{ $approvedToday }}</div>
                <div class="stat-footer">+{{ $approvedToday > 0 ? '15' : '0' }}% from yesterday</div>
            </div>

            <!-- Total Reviewed -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon total">📄</div>
                    <span class="stat-badge info">📊</span>
                </div>
                <div class="stat-label">Total Reviewed</div>
                <div class="stat-value">{{ $totalReviewed }}</div>
                <div class="stat-footer">All-time contributions</div>
            </div>

            <!-- Accuracy Rate -->
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon accuracy">🎯</div>
                    <span class="stat-badge purple">🔒</span>
                </div>
                <div class="stat-label">Accuracy Rate</div>
                <div class="stat-value">{{ $accuracyRate }}%</div>
                <div class="stat-footer">
                    @if($accuracyRate >= 90)
                        Excellent performance
                    @elseif($accuracyRate >= 75)
                        Good performance
                    @else
                        Keep improving
                    @endif
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Weekly Activity Chart -->
            <div class="activity-card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Weekly Activity</h2>
                        <p class="card-subtitle">Review performance this week</p>
                    </div>
                    <div class="weekly-total">
                        <div class="weekly-total-label">Total This Week</div>
                        <div class="weekly-total-value">
                            {{ collect($weeklyData)->sum('count') }}
                        </div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>

            <!-- Action Cards -->
            <div class="action-cards">
                <!-- Start Reviewing -->
                <div class="action-card primary">
                    <div class="action-icon-box">
                        <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <div class="action-title">Start Reviewing</div>
                        <div class="action-description">Jump into the review queue and help validate questions</div>
                    </div>
                    <button class="action-btn" onclick="window.location.href='{{ route('reviewer.review') }}'">
                        Start Now
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                <!-- Review History -->
                <div class="action-card secondary">
                    <div class="action-icon-box purple">
                        <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <div class="action-title">Review History</div>
                        <div class="action-description">View your past contributions</div>
                    </div>
                    <button class="action-btn" onclick="window.location.href='{{ route('reviewer.history') }}'">View History</button>
                </div>

                <!-- Generate Questions -->
                <div class="action-card tertiary">
                    <div class="action-icon-box blue">
                        <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <div class="action-title">Generate Questions</div>
                        <div class="action-description">Create new practice problems</div>
                    </div>
                    <button class="action-btn" onclick="window.location.href='{{ route('reviewer.generate') }}'">Generate</button>
                </div>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="bottom-grid">
            <!-- Urgent Reviews -->
            <div class="activity-card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Urgent Reviews</h2>
                        <p class="card-subtitle">
                            @if($pendingQuestions->isNotEmpty())
                                Pending over {{ $pendingQuestions->max('days_pending') }} days
                            @else
                                No pending reviews
                            @endif
                        </p>
                    </div>
                    <button class="stat-badge urgent" onclick="window.location.href='{{ route('reviewer.review') }}'">View All</button>
                </div>
                <div class="reviews-list">
                    @forelse($pendingQuestions as $question)
                        <div class="review-item">
                            <div class="review-header">
                                <div>
                                    <div class="review-title-text">{{ $question['title'] }}</div>
                                    <div class="review-meta">
                                        <span class="review-level {{ strtolower($question['difficulty']) }}">{{ $question['difficulty'] }}</span>
                                        <span class="review-time urgent-timer" data-seconds="{{ $question['seconds_pending'] }}">
                                            ⏰ <span class="timer-value">{{ $question['days_pending'] }}d</span> ago
                                        </span>
                                        @if($question['days_pending'] >= 3)
                                            <span class="review-level priority">High Priority</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button class="review-btn" onclick="window.location.href='{{ route('reviewer.review') }}?question_id={{ $question['id'] }}'">Review Now →</button>
                        </div>
                    @empty
                        <div class="review-item">
                            <div class="review-header">
                                <div>
                                    <div class="review-title-text">No pending reviews</div>
                                    <div class="review-meta">
                                        <span class="review-level beginner">All caught up!</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Recent Activity</h2>
                        <p class="card-subtitle">Your latest review actions</p>
                    </div>
                </div>
                <div class="activity-list">
                    @forelse($recentActivities as $activity)
                        @php
                            $statusClass = 'approved';
                            $icon = '✓';
                            $actionText = 'Approved';
                            
                            if($activity['status'] === 'Rejected') {
                                $statusClass = 'rejected';
                                $icon = '✗';
                                $actionText = 'Rejected';
                            } elseif($activity['status'] === 'Generated') {
                                $statusClass = 'approved';
                                $icon = '❔';
                                $actionText = 'Generated';
                            }
                        @endphp
                        
                        <div class="activity-item {{ $statusClass }}">
                            <div class="activity-avatar {{ $statusClass }}">
                                {{ $icon }}
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong>{{ $actionText }}:</strong> {{ Str::limit($activity['title'], 50) }}
                                </div>
                                <div class="activity-time recent-timer" data-time="{{ $activity['time_ago'] }}">
                                    ⏰ <span class="timer-value">{{ $activity['time_ago'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="activity-item">
                            <div class="activity-content">
                                <div class="activity-text">No recent activity yet</div>
                                <div class="activity-time">Start reviewing to see your activity here</div>
                            </div>
                        </div>
                    @endforelse
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
                    label: 'Reviews',
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
                                return 'Reviews: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
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

        // Timer Management System
        let urgentTimers = [];
        let recentTimers = [];
        let timerInterval = null;

        // Initialize timers when page loads
        function initializeTimers() {
            // Initialize Urgent Review Timers
            document.querySelectorAll('.urgent-timer').forEach((element, index) => {
                const seconds = parseInt(element.getAttribute('data-seconds')) || 0;
                urgentTimers.push({
                    element: element.querySelector('.timer-value'),
                    startTime: Date.now() - (seconds * 1000),
                    seconds: seconds
                });
            });

            // Initialize Recent Activity Timers
            document.querySelectorAll('.recent-timer').forEach((element, index) => {
                const timeText = element.getAttribute('data-time');
                const milliseconds = parseTimeToMilliseconds(timeText);
                
                recentTimers.push({
                    element: element.querySelector('.timer-value'),
                    startTime: Date.now() - milliseconds,
                    originalText: timeText
                });
            });

            // Start the timer update interval (update every second)
            startTimerUpdates();
        }

        // Parse time text to milliseconds
        function parseTimeToMilliseconds(timeText) {
            if (!timeText) return 0;
            
            const secondsMatch = timeText.match(/(\d+)\s*second/i);
            const minutesMatch = timeText.match(/(\d+)\s*minute/i);
            const hoursMatch = timeText.match(/(\d+)\s*hour/i);
            const daysMatch = timeText.match(/(\d+)\s*day/i);
            
            let total = 0;
            if (secondsMatch) total += parseInt(secondsMatch[1]) * 1000;
            if (minutesMatch) total += parseInt(minutesMatch[1]) * 60 * 1000;
            if (hoursMatch) total += parseInt(hoursMatch[1]) * 60 * 60 * 1000;
            if (daysMatch) total += parseInt(daysMatch[1]) * 24 * 60 * 60 * 1000;
            
            return total;
        }

        // Format elapsed time
        function formatElapsedTime(milliseconds, format = 'full') {
            const seconds = Math.floor(milliseconds / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (format === 'full') {
                // Show only the most relevant unit for urgent reviews
                if (days > 0) {
                    return `${days}d`;
                } else if (hours > 0) {
                    return `${hours}h`;
                } else if (minutes > 0) {
                    return `${minutes}m`;
                } else {
                    return `${seconds}s`;
                }
            } else {
                // Smart format for recent activity
                if (days > 0) {
                    return days === 1 ? '1 day ago' : `${days} days ago`;
                } else if (hours > 0) {
                    return hours === 1 ? '1 hour ago' : `${hours} hours ago`;
                } else if (minutes > 0) {
                    return minutes === 1 ? '1 minute ago' : `${minutes} minutes ago`;
                } else {
                    return seconds <= 1 ? 'just now' : `${seconds} seconds ago`;
                }
            }
        }

        // Update all timers
        function updateTimers() {
            const now = Date.now();

            // Update Urgent Review Timers
            urgentTimers.forEach(timer => {
                const elapsed = now - timer.startTime;
                timer.element.textContent = formatElapsedTime(elapsed, 'full');
            });

            // Update Recent Activity Timers
            recentTimers.forEach(timer => {
                const elapsed = now - timer.startTime;
                timer.element.textContent = formatElapsedTime(elapsed, 'smart');
            });
        }

        // Start timer updates
        function startTimerUpdates() {
            // Clear any existing interval
            if (timerInterval) {
                clearInterval(timerInterval);
            }

            // Update immediately
            updateTimers();

            // Update every second
            timerInterval = setInterval(updateTimers, 1000);
        }

        // Stop timer updates (useful for cleanup)
        function stopTimerUpdates() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }

        // Initialize timers when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeTimers();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopTimerUpdates();
        });
    </script>
</body>
</html>
