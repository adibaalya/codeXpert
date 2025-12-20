<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodeXpert - Submission Results</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.navCSS')
    @include('layouts.competencyCSS')
    
</head>
<body class="result-body">
    <!-- Header -->
    <div class="header" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000;">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item" onclick="window.location.href='{{ route('learner.dashboard') }}'">Dashboard</button>
                <button class="nav-item active" onclick="window.location.href='{{ route('learner.practice') }}'">Practice</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.leaderboard') }}'">Leaderboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ Auth::guard('learner')->user()->username }}</div>
                    <div class="user-role">Learner</div>
                </div>
                <div class="user-avatar" onclick="toggleUserMenu(event)">
                    {{ strtoupper(substr(Auth::guard('learner')->user()->username, 0, 1)) }}{{ strtoupper(substr(Auth::guard('learner')->user()->username, 1, 1) ?? '') }}
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header">
                        <div class="user-dropdown-avatar">
                            {{ strtoupper(substr(Auth::guard('learner')->user()->username, 0, 2)) }}
                        </div>
                        <div>
                            <div class="user-dropdown-name">{{ Auth::guard('learner')->user()->username }}</div>
                            <div class="user-dropdown-email">{{ Auth::guard('learner')->user()->email }}</div>
                        </div>
                    </div>
                    
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

    <div class="result-container">
        <div class="result-card">
            <!-- Header -->
            <h1 class="result-header-title">Submission Complete</h1>
            <p class="result-header-subtitle">{{ $submission['question_title'] }} - {{ $submission['language'] }}</p>

            <!-- Success Icon -->
            <div class="success-icon-wrapper">
                <div class="success-icon-circle {{ $submission['score'] >= 70 ? '' : 'failed' }}">
                    @if($submission['score'] >= 70)
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    @else
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    @endif
                </div>
            </div>

            <!-- Message -->
            <h2 class="result-message-title {{ $submission['score'] >= 70 ? '' : 'failed' }}">
                @if($submission['score'] == 100)
                    Perfect Score!
                @elseif($submission['score'] >= 70)
                    Great Job!
                @else
                    Keep Trying!
                @endif
            </h2>

            <!-- Congrats Box -->
            <div class="result-congrats-box {{ $submission['score'] >= 70 ? '' : 'failed' }}">
                <p class="result-congrats-text {{ $submission['score'] >= 70 ? '' : 'failed' }}">
                    @if($submission['score'] == 100)
                        🎉 Outstanding! You passed all {{ $submission['total_tests'] }} test cases. Your solution is perfect!
                    @elseif($submission['score'] >= 70)
                        Well done! You passed {{ $submission['passed_tests'] }} out of {{ $submission['total_tests'] }} test cases. Keep up the great work!
                    @else
                        You passed {{ $submission['passed_tests'] }} out of {{ $submission['total_tests'] }} test cases. Review the failed cases and try again!
                    @endif
                </p>
            </div>

            <!-- Scores Section -->
            <div class="result-scores-section">
                <!-- Test Cases Passed -->
                <div class="result-score-item">
                    <div class="result-score-header">
                        <div class="result-score-label">Test Cases Passed</div>
                        <div class="result-score-value" style="color: {{ $submission['score'] >= 70 ? '#10B981' : '#EF4444' }};">
                            {{ $submission['passed_tests'] }}/{{ $submission['total_tests'] }}
                        </div>
                    </div>
                    <div class="result-score-sublabel">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Correctness verification
                    </div>
                    <div class="result-progress-bar">
                        <div class="result-progress-fill {{ $submission['score'] >= 70 ? '' : 'low' }}" 
                             style="width: {{ $submission['score'] }}%;"></div>
                    </div>
                </div>

                <!-- Score Percentage -->
                <div class="result-score-item">
                    <div class="result-score-header">
                        <div class="result-score-label">Overall Score</div>
                        <div class="result-score-value" style="color: {{ $submission['score'] >= 70 ? '#10B981' : ($submission['score'] >= 50 ? '#F59E0B' : '#EF4444') }};">
                            {{ round($submission['score']) }}%
                        </div>
                    </div>
                    <div class="result-score-sublabel">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        Based on test case results
                    </div>
                    <div class="result-progress-bar">
                        <div class="result-progress-fill {{ $submission['score'] >= 70 ? '' : ($submission['score'] >= 50 ? 'medium' : 'low') }}" 
                             style="width: {{ $submission['score'] }}%;"></div>
                    </div>
                </div>
            </div>

            <!-- Test Results Details (Collapsible) -->
            <div style="margin-top: 30px;">
                <button onclick="toggleTestResults()" style="width: 100%; background: #F9FAFB; border: 2px solid #E5E7EB; padding: 16px 20px; border-radius: 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 16px; font-weight: 600; color: #374151;">
                    <span>View Detailed Test Results</span>
                    <svg id="chevron" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: transform 0.3s;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <div id="testResultsDetails" style="display: none; margin-top: 20px; background: #F9FAFB; border-radius: 12px; padding: 20px;">
                    @foreach($submission['test_results'] as $testResult)
                    <div style="background: white; border-radius: 8px; padding: 16px; margin-bottom: 12px; border-left: 4px solid {{ $testResult['passed'] ? '#10B981' : '#EF4444' }};">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span style="font-weight: 600; color: #1F2937;">Test Case {{ $testResult['test_number'] }}</span>
                            <span style="padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 700; background: {{ $testResult['passed'] ? '#D1FAE5' : '#FEE2E2' }}; color: {{ $testResult['passed'] ? '#059669' : '#DC2626' }};">
                                {{ $testResult['passed'] ? '✓ PASSED' : '✗ FAILED' }}
                            </span>
                        </div>
                        
                        <div style="margin-bottom: 12px;">
                            <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;">Input:</div>
                            <pre style="background: #1E1E1E; color: #D4D4D4; padding: 12px; border-radius: 6px; overflow-x: auto; font-size: 12px; margin: 0;">{{ $testResult['input'] }}</pre>
                        </div>
                        
                        <div style="margin-bottom: 12px;">
                            <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;">Expected Output:</div>
                            <pre style="background: #1E1E1E; color: #10B981; padding: 12px; border-radius: 6px; overflow-x: auto; font-size: 12px; margin: 0;">{{ $testResult['expected'] }}</pre>
                        </div>
                        
                        <div>
                            <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;">Your Output:</div>
                            <pre style="background: #1E1E1E; color: {{ $testResult['passed'] ? '#10B981' : '#EF4444' }}; padding: 12px; border-radius: 6px; overflow-x: auto; font-size: 12px; margin: 0;">{{ $testResult['actual'] }}</pre>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- AI Feedback Section -->
            @if(isset($submission['ai_feedback']))
            <div style="margin-top: 30px;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <svg width="28" height="28" fill="white" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <h3 style="color: white; font-size: 20px; font-weight: 700; margin: 0;">AI Code Feedback</h3>
                    </div>
                    <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 0;">
                        Your code has been analyzed by AI to provide personalized insights and improvement suggestions.
                    </p>
                </div>

                <!-- AI Plagiarism Detection Section -->
                @if(isset($submission['plagiarism_analysis']))
                @php
                    $plagiarism = $submission['plagiarism_analysis'];
                    $probability = $plagiarism['ai_probability'];
                    $riskLevel = $probability >= 80 ? 'high' : ($probability >= 60 ? 'medium' : ($probability >= 40 ? 'low' : 'minimal'));
                    
                    $riskColors = [
                        'high' => ['bg' => '#FEE2E2', 'text' => '#DC2626', 'border' => '#EF4444'],
                        'medium' => ['bg' => '#FEF3C7', 'text' => '#D97706', 'border' => '#F59E0B'],
                        'low' => ['bg' => '#FEF9C3', 'text' => '#CA8A04', 'border' => '#EAB308'],
                        'minimal' => ['bg' => '#D1FAE5', 'text' => '#059669', 'border' => '#10B981']
                    ];
                    
                    $colors = $riskColors[$riskLevel];
                    
                    $riskLabels = [
                        'high' => '⚠️ High Risk',
                        'medium' => '⚡ Medium Risk',
                        'low' => '✓ Low Risk',
                        'minimal' => '✓ Minimal Risk'
                    ];
                @endphp
                
                <div style="background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; border: 2px solid {{ $colors['border'] }};">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                <svg width="24" height="24" fill="{{ $colors['text'] }}" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <h4 style="color: #1F2937; font-size: 18px; font-weight: 700; margin: 0;">AI Plagiarism Detection</h4>
                            </div>
                            <p style="color: #6B7280; font-size: 14px; margin: 0;">
                                Automated analysis of code authorship patterns
                            </p>
                        </div>
                        <div style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 700; white-space: nowrap;">
                            {{ $riskLabels[$riskLevel] }}
                        </div>
                    </div>
                    
                    <!-- AI Probability Score -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 14px; font-weight: 600; color: #374151;">AI Generation Probability</span>
                            <span style="font-size: 24px; font-weight: 800; color: {{ $colors['text'] }};">{{ $probability }}%</span>
                        </div>
                        <div style="width: 100%; height: 12px; background: #E5E7EB; border-radius: 12px; overflow: hidden;">
                            <div style="height: 100%; background: {{ $colors['border'] }}; width: {{ $probability }}%; transition: width 0.8s ease;"></div>
                        </div>
                        <div style="margin-top: 8px; padding: 12px; background: #F9FAFB; border-radius: 8px;">
                            <p style="color: #4B5563; font-size: 13px; margin: 0; line-height: 1.5;">
                                <strong>Analysis:</strong> {{ $plagiarism['reason'] }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Indicators -->
                    @if(!empty($plagiarism['indicators']))
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 10px;">Detection Indicators:</div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($plagiarism['indicators'] as $indicator)
                            <div style="display: flex; align-items: start; gap: 8px; padding: 10px; background: #F9FAFB; border-radius: 8px; border-left: 3px solid {{ $colors['border'] }};">
                                <svg width="16" height="16" fill="{{ $colors['text'] }}" viewBox="0 0 20 20" style="margin-top: 2px; flex-shrink: 0;">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span style="color: #4B5563; font-size: 13px; line-height: 1.5;">{{ $indicator }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Confidence Level -->
                    <div style="margin-top: 16px; padding: 12px; background: #F3F4F6; border-radius: 8px; border: 1px solid #D1D5DB;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 13px; color: #6B7280;">Analysis Confidence:</span>
                            <span style="font-size: 14px; font-weight: 700; color: #374151; text-transform: uppercase;">
                                {{ $plagiarism['confidence'] }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Info Note -->
                    <div style="margin-top: 16px; padding: 12px; background: #EFF6FF; border-radius: 8px; border-left: 4px solid #3B82F6;">
                        <div style="display: flex; gap: 10px;">
                            <svg width="20" height="20" fill="#3B82F6" viewBox="0 0 20 20" style="flex-shrink: 0; margin-top: 2px;">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p style="color: #1E40AF; font-size: 12px; margin: 0; line-height: 1.5;">
                                <strong>Note:</strong> This analysis detects patterns typical of AI-generated code. High scores don't necessarily indicate cheating, but may warrant further review. Good coding practices and clean code can also trigger detection.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Correctness Section -->
                @if(!empty($submission['ai_feedback']['correctness']))
                <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 2px solid #E5E7EB;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <svg width="24" height="24" fill="#3B82F6" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <h4 style="color: #1F2937; font-size: 16px; font-weight: 600; margin: 0;">Correctness</h4>
                    </div>
                    <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $submission['ai_feedback']['correctness'] }}</div>
                </div>
                @endif

                <!-- Style & Readability Section -->
                @if(!empty($submission['ai_feedback']['style']))
                <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 2px solid #E5E7EB;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <svg width="24" height="24" fill="#8B5CF6" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        <h4 style="color: #1F2937; font-size: 16px; font-weight: 600; margin: 0;">Style & Readability</h4>
                    </div>
                    <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $submission['ai_feedback']['style'] }}</div>
                </div>
                @endif

                <!-- Error Analysis Section -->
                @if(!empty($submission['ai_feedback']['errors']))
                <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 2px solid #E5E7EB;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <svg width="24" height="24" fill="#F59E0B" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <h4 style="color: #1F2937; font-size: 16px; font-weight: 600; margin: 0;">Error Analysis</h4>
                    </div>
                    <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $submission['ai_feedback']['errors'] }}</div>
                </div>
                @endif

                <!-- Suggestions Section -->
                @if(!empty($submission['ai_feedback']['suggestions']))
                <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 2px solid #E5E7EB;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <svg width="24" height="24" fill="#10B981" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <h4 style="color: #1F2937; font-size: 16px; font-weight: 600; margin: 0;">Suggestions</h4>
                    </div>
                    <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $submission['ai_feedback']['suggestions'] }}</div>
                </div>
                @endif
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="result-actions" style="margin-top: 30px;">
                <a href="{{ route('learner.practice') }}" class="result-btn result-btn-secondary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 8px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Practice
                </a>
                <a href="{{ route('learner.dashboard') }}" class="result-btn result-btn-primary">
                    Continue to Dashboard
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-left: 8px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
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

        // Toggle test results details
        function toggleTestResults() {
            const details = document.getElementById('testResultsDetails');
            const chevron = document.getElementById('chevron');
            
            if (details.style.display === 'none') {
                details.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
            } else {
                details.style.display = 'none';
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Animate progress bars on load
        window.addEventListener('load', function() {
            const progressBars = document.querySelectorAll('.result-progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html>
