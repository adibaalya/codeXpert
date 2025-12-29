<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodeXpert - Submission Feedback</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.app')
    @include('layouts.competencyCSS')
    @include('layouts.navCSS')
    <style>
        .feedback-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .feedback-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .feedback-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }

        .feedback-icon.success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
        }

        .feedback-icon.partial {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: white;
        }

        .feedback-icon.failed {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white;
        }

        .feedback-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .feedback-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .score-summary {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 30px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
        }

        .score-item {
            text-align: center;
        }

        .score-value {
            font-size: 36px;
            font-weight: 700;
            color: #4C6EF5;
        }

        .score-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .test-results {
            margin-top: 30px;
        }

        .test-results-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .test-case-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #e5e7eb;
        }

        .test-case-card.passed {
            border-left-color: #10B981;
            background: #f0fdf4;
        }

        .test-case-card.failed {
            border-left-color: #EF4444;
            background: #fef2f2;
        }

        .test-case-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .test-case-number {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 16px;
        }

        .test-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .test-badge.passed {
            background: #10B981;
            color: white;
        }

        .test-badge.failed {
            background: #EF4444;
            color: white;
        }

        .test-badge.sample {
            background: #4C6EF5;
            color: white;
            margin-left: 10px;
        }

        .test-case-detail {
            margin-top: 10px;
        }

        .test-detail-row {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .test-detail-label {
            font-weight: 600;
            color: #666;
        }

        .test-detail-value {
            color: #1a1a1a;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            word-break: break-all;
        }

        .hidden-text {
            color: #999;
            font-style: italic;
        }

        .continue-btn {
            background: linear-gradient(135deg, #4C6EF5 0%, #4338CA 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 40px auto 0;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .continue-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(76, 110, 245, 0.3);
        }

        .info-box {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 12px;
            padding: 16px;
            margin-top: 30px;
            display: flex;
            gap: 12px;
            font-size: 14px;
            color: #1E40AF;
        }

        .info-box svg {
            flex-shrink: 0;
            margin-top: 2px;
        }
    </style>
</head>
<body class="code-test-body-reviewer">
    <div class="feedback-container">
        <div class="feedback-card">
            <!-- Header with icon and title -->
            <div class="feedback-header">
                @php
                    $percentage = ($feedback['passed_tests'] / $feedback['total_tests']) * 100;
                    $iconClass = $percentage == 100 ? 'success' : ($percentage >= 50 ? 'partial' : 'failed');
                    $icon = $percentage == 100 ? '✓' : ($percentage >= 50 ? '!' : '✗');
                @endphp
                
                <div class="feedback-icon {{ $iconClass }}">
                    {{ $icon }}
                </div>
                
                <h1 class="feedback-title">{{ $feedback['question_title'] }}</h1>
                <p class="feedback-subtitle">Submission Results</p>
            </div>

            <!-- Score Summary -->
            <div class="score-summary">
                <div class="score-item">
                    <div class="score-value">{{ $feedback['passed_tests'] }}/{{ $feedback['total_tests'] }}</div>
                    <div class="score-label">Tests Passed</div>
                </div>
                <div class="score-item">
                    <div class="score-value">{{ number_format($percentage, 0) }}%</div>
                    <div class="score-label">Success Rate</div>
                </div>
                <div class="score-item">
                    <div class="score-value">{{ number_format($feedback['score'], 1) }}/10</div>
                    <div class="score-label">Points Earned</div>
                </div>
            </div>

            <!-- Test Results -->
            <div class="test-results">
                <h2 class="test-results-title">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                    </svg>
                    Test Case Results
                </h2>

                @foreach($feedback['test_results'] as $result)
                    <div class="test-case-card {{ $result['passed'] ? 'passed' : 'failed' }}">
                        <div class="test-case-header">
                            <div>
                                <span class="test-case-number">Test Case #{{ $result['test_number'] }}</span>
                                @if($result['is_sample'])
                                    <span class="test-badge sample">Sample</span>
                                @endif
                            </div>
                            <span class="test-badge {{ $result['passed'] ? 'passed' : 'failed' }}">
                                @if($result['passed'])
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Passed
                                @else
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    Failed
                                @endif
                            </span>
                        </div>

                        <div class="test-case-detail">
                            <div class="test-detail-row">
                                <div class="test-detail-label">Input:</div>
                                <div class="test-detail-value {{ $result['input'] === 'Hidden' ? 'hidden-text' : '' }}">
                                    {{ $result['input'] }}
                                </div>
                            </div>
                            <div class="test-detail-row">
                                <div class="test-detail-label">Expected:</div>
                                <div class="test-detail-value {{ $result['expected'] === 'Hidden' ? 'hidden-text' : '' }}">
                                    {{ $result['expected'] }}
                                </div>
                            </div>
                            <div class="test-detail-row">
                                <div class="test-detail-label">Your Output:</div>
                                <div class="test-detail-value {{ in_array($result['output'] ?? '', ['Hidden', 'Passed', 'Failed']) ? 'hidden-text' : '' }}">
                                    {{ $result['output'] ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Info box explaining hidden test cases -->
            <div class="info-box">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong>Note:</strong> Hidden test cases are used to verify your solution works correctly for various scenarios. 
                    You can see full details for the sample test case, but only pass/fail status for hidden test cases.
                </div>
            </div>

            <!-- Continue button -->
            <form action="{{ route('reviewer.competency.code.continue') }}" method="POST">
                @csrf
                <button type="submit" class="continue-btn">
                    @if($isLastQuestion)
                        Complete Test
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        Continue to Next Question
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    @endif
                </button>
            </form>
        </div>
    </div>
</body>
</html>
