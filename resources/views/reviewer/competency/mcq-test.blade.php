<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodeXpert - Competency Test</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.app')
    @include('layouts.navCSS')
    @include('layouts.competencyCSS')
</head>
<body class="mcq-test-body">
    <!-- Header -->
    <div class="header">
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

    <div class="test-header-wrapper">
        <div class="test-header">
            <div class="test-title">{{ session('test_language') }} Competency Test</div>
            <div class="test-subtitle">Verify your programming expertise</div>
            <span class="question-header-badge">PART 1: Multiple Choice</span>
        </div>
    </div>

    <div class="test-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-card">
                <div class="timer-section">
                    <div class="timer-icon">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Time Remaining
                    </div>
                    <div class="timer-display" id="timer">45:00</div>
                </div>

                <div class="progress-section">
                    <div class="progress-header">
                        <span class="progress-title">Progress</span>
                        <span class="progress-percentage">{{ round((($currentQuestion ?? 1) / ($totalQuestions ?? 10)) * 100) }}%</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" style="width: {{ (($currentQuestion ?? 1) / ($totalQuestions ?? 10)) * 100 }}%"></div>
                    </div>
                    <div class="question-counter">Question {{ $currentQuestion ?? 1 }} of {{ $totalQuestions ?? 10 }}</div>
                </div>
            </div>

            <div class="sidebar-card">
                <div class="progress-title" style="margin-bottom: 15px;">QUESTIONS</div>
                <div class="questions-grid">
                    @for($i = 1; $i <= ($totalQuestions ?? 10); $i++)
                        <div class="question-number {{ $i == ($currentQuestion ?? 1) ? 'current' : ($i < ($currentQuestion ?? 1) ? 'answered' : 'unanswered') }}">
                            {{ $i }}
                        </div>
                    @endfor
                </div>

                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-dot current"></div>
                        <span>Current Question</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot answered"></div>
                        <span>Answered</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot unanswered"></div>
                        <span>Not Answered</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <form action="{{ route('reviewer.competency.mcq.submit') }}" method="POST" id="mcqForm">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->question_ID }}">
                
                <div class="question-card">
                    <span class="question-badge">QUESTION {{ $currentQuestion ?? 1 }} OF {{ $totalQuestions ?? 10 }}</span>
                    
                    @php
                        // Function to format code blocks in questions
                        function formatQuestionContent($content) {
                            // Replace ```language code blocks with formatted HTML
                            $content = preg_replace_callback(
                                '/```(\w+)?\s*\n(.*?)\n```/s',
                                function($matches) {
                                    $language = $matches[1] ?? 'code';
                                    $code = htmlspecialchars($matches[2]);
                                    return '<div class="code-block-wrapper"><div class="code-block-header"><span class="code-language">' . strtoupper($language) . '</span></div><pre class="code-block"><code>' . $code . '</code></pre></div>';
                                },
                                $content
                            );
                            
                            // Handle inline code with backticks
                            $content = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $content);
                            
                            return nl2br($content);
                        }
                        
                        $formattedContent = formatQuestionContent($question->content);
                    @endphp
                    
                    <div class="question-text">{!! $formattedContent !!}</div>


                    <div class="options-list">
                        @php
                            $options = $question->options ?? [];
                            $mcqAnswers = session('mcq_answers', []);
                            $previousAnswer = $mcqAnswers[$question->question_ID] ?? null;
                        @endphp
                        
                        @foreach($options as $letter => $optionText)
                            <div class="option-item {{ $previousAnswer === $letter ? 'selected' : '' }}" data-option-value="{{ $letter }}">
                                <div class="option-letter">{{ $letter }}</div>
                                <div class="option-text">{{ $optionText }}</div>
                                <div class="option-radio"></div>
                            </div>
                        @endforeach
                    </div>

                    <input type="hidden" name="answer" id="selectedAnswer" value="{{ $previousAnswer ?? '' }}">

                    <div class="navigation-buttons">
                        @if(($currentQuestion ?? 1) > 1)
                            <button type="button" class="btn btn-previous" onclick="goToPrevious()">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Previous Question
                            </button>
                        @endif

                        <button type="submit" class="btn btn-next" id="submitBtn" {{ $previousAnswer ? '' : 'disabled' }}>
                            @if(($currentQuestion ?? 1) < ($totalQuestions ?? 10))
                                Next Question
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            @else
                                Continue to Coding Questions
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            @endif
                        </button>
                    </div>
                </div>
            </form>

            <form action="{{ route('reviewer.competency.mcq.previous') }}" method="POST" id="previousForm" style="display: none;">
                @csrf
            </form>
        </div>
    </div>

    <script>
        let selectedAnswer = '{{ $previousAnswer ?? '' }}';

        // Prevent copying and other actions on question content
        document.addEventListener('DOMContentLoaded', function() {
            const questionCard = document.querySelector('.question-card');
            
            // Prevent right-click context menu
            questionCard.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Prevent copy shortcuts (Ctrl+C, Cmd+C)
            questionCard.addEventListener('copy', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Prevent cut shortcuts (Ctrl+X, Cmd+X)
            questionCard.addEventListener('cut', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Prevent drag selection
            questionCard.addEventListener('dragstart', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Prevent keyboard shortcuts
            questionCard.addEventListener('keydown', function(e) {
                // Prevent Ctrl+C, Cmd+C, Ctrl+X, Cmd+X, Ctrl+A, Cmd+A
                if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'x' || e.key === 'a')) {
                    e.preventDefault();
                    return false;
                }
            });

            // Use event delegation to handle option clicks
            const optionsList = document.querySelector('.options-list');
            
            optionsList.addEventListener('click', function(e) {
                const optionItem = e.target.closest('.option-item');
                if (!optionItem) return;
                
                // Get the option letter (A, B, C, or D)
                const answer = optionItem.getAttribute('data-option-value');
                
                // Remove selected class from all options
                document.querySelectorAll('.option-item').forEach(opt => {
                    opt.classList.remove('selected');
                });

                // Add selected class to clicked option
                optionItem.classList.add('selected');
                
                // Set the answer
                selectedAnswer = answer;
                document.getElementById('selectedAnswer').value = answer;
                
                // Enable submit button
                document.getElementById('submitBtn').disabled = false;
            });
        });

        function goToPrevious() {
            document.getElementById('previousForm').submit();
        }

        // Timer countdown - using remaining seconds from server
        let timeLeft = {{ $remainingSeconds ?? 2700 }}; // Remaining time from server
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timer').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                // Time's up, submit the form
                document.getElementById('mcqForm').submit();
            } else {
                timeLeft--;
            }
        }
        
        // Update timer immediately and then every second
        updateTimer();
        setInterval(updateTimer, 1000);

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
