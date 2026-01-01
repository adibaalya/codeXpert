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
    <script src="{{ asset('js/reviewer/competency.js') }}" defer></script>
    @include('layouts.app')
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
                            // First, handle existing code blocks with triple backticks
                            $content = preg_replace_callback(
                                '/```(\w+)?\s*\n(.*?)\n```/s',
                                function($matches) {
                                    $language = $matches[1] ?? 'code';
                                    $code = htmlspecialchars($matches[2]);
                                    return '<pre class="code-block"><code>' . $code . '</code></pre>';
                                },
                                $content
                            );
                            
                            // Handle plain code blocks for Question Evaluation (code between "Question Under Review:" and the evaluation prompt)
                            // This catches multi-line code without backticks
                            if (strpos($content, 'Question Under Review:') !== false) {
                                $content = preg_replace_callback(
                                    '/Question Under Review:\s*\n(.*?)(?=\n\n|\n[A-Z][a-z]+:|\z)/s',
                                    function($matches) {
                                        $codeBlock = trim($matches[1]);
                                        // Only format as code block if it contains typical code characters
                                        if (preg_match('/[{};()=\[\]]/', $codeBlock)) {
                                            $code = htmlspecialchars($codeBlock);
                                            return 'Question Under Review:<br><pre class="code-block"><code>' . $code . '</code></pre>';
                                        }
                                        return $matches[0];
                                    },
                                    $content
                                );
                            }
                            
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
        window.testConfig = {
            remainingSeconds: {{ $remainingSeconds ?? 2700 }}
        };
    </script>
</body>
</html>
