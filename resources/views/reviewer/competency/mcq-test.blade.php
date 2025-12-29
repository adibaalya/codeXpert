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
    <div class="test-header-wrapper" style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 20px;">
        
        <div class="test-header-left">
            <div class="test-title">
                {{ session('test_language') }} Competency Test
            </div>
            <div class="test-subtitle" style="color: #666; font-size: 14px; margin-top: 4px;">
                Part 1: Multiple Choice Questions
            </div>
        </div>

        <div class="timer-card" >
            <div class="timer-label">
                Time Remaining
            </div>
            
            <div class="timer-value-wrapper" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="rgb(92, 33, 195)" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                
                <div class="timer-display" id="timer" style="font-size: 20px; font-weight: 700; color: #111827; font-feature-settings: 'tnum';">
                    45:00
                </div>
            </div>
        </div>

    </div>

    <div class="test-container">
        <!-- Sidebar -->
        <div class="sidebar">

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
