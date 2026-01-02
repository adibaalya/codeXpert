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
    <script src="{{ asset('js/navBar.js') }}"></script>
    <script src="{{ asset('js/result.js') }}"></script>
    @include('layouts.navCSS')
    @include('layouts.resultCSS')
    
</head>
<body class="result-body">
    <!-- Header -->
    <div class="header" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000;">
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
                <!-- Plagiarism Detection -->
                <div class="result-score-item">
                    <div class="result-score-header">
                        <div class="result-score-label">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>Code Originality</span>
                                @php
                                    $plagiarismScore = $submission['plagiarism_analysis']['ai_probability'] ?? 100;
                                    $plagiarismPassed = $plagiarismScore >= 60;
                                @endphp
                                @if(!$plagiarismPassed)
                                    <span style="background: #FEE2E2; color: #DC2626; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">FAILED</span>
                                @endif
                            </div>
                        </div>
                        <div class="result-score-value" style="color: {{ $plagiarismPassed ? '#10B981' : '#EF4444' }};">{{ round($plagiarismScore) }}%</div>
                    </div>
                    <div class="result-score-sublabel">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        TF-IDF Vector Similarity Analysis (60% minimum required)
                        @if($plagiarismPassed)
                            <span style="color: #10B981; font-weight: 600;">✓ Passed</span>
                        @else
                            <span style="color: #EF4444; font-weight: 600;">✗ Failed</span>
                        @endif
                    </div>
                    <div class="result-progress-bar">
                        <div class="result-progress-fill {{ $plagiarismPassed ? '' : 'low' }}" style="width: {{ $plagiarismScore }}%;"></div>
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

            <!-- AI Plagiarism Detection Section -->
            @if(isset($submission['plagiarism_analysis']))
                <div style="margin-bottom: 30px; margin-top: 40px;">
                    <div style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <svg width="28" height="28" fill="white" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <h3 style="color: white; font-size: 20px; font-weight: 700; margin: 0;">Plagiarism Analysis</h3>
                        </div>
                        <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 0;">
                            TF-IDF Vector Similarity Method - Comparing your code against known AI-generated solutions
                        </p>
                    </div>

                    @php
                        $analysis = $submission['plagiarism_analysis'];
                        // ai_probability is now ORIGINALITY (100 = original, 0 = plagiarized)
                        $originality = $analysis['ai_probability'] ?? 100;
                        // Get actual similarity to AI (if available)
                        $similarity = $analysis['similarity_to_ai'] ?? (100 - $originality);
                        
                        // Risk level based on similarity to AI (higher similarity = higher risk)
                        $riskLevel = $similarity >= 80 ? 'high' : ($similarity >= 60 ? 'medium' : ($similarity >= 40 ? 'low' : 'minimal'));
                        $riskColor = $similarity >= 80 ? '#EF4444' : ($similarity >= 60 ? '#F59E0B' : ($similarity >= 40 ? '#EAB308' : '#10B981'));
                        $accordionId = "plagiarism-accordion-single";
                    @endphp

                    <!-- Collapsible Card -->
                    <div style="background: white; border-radius: 12px; margin-bottom: 16px; border-left: 4px solid {{ $riskColor }}; overflow: hidden;">
                        <!-- Clickable Header -->
                        <div onclick="toggleAccordion('{{ $accordionId }}')" style="padding: 20px 24px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                    <h4 style="color: #1F2937; font-size: 18px; font-weight: 700; margin: 0;">Your Submission</h4>
                                    <span style="background: {{ $riskColor }}; color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                        {{ strtoupper($riskLevel) }} RISK
                                    </span>
                                    <span style="background: #10B981; color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                        {{ round($originality) }}% Original
                                    </span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 16px; font-size: 13px; color: #6B7280;">
                                    <span><strong>Similarity:</strong> {{ round($similarity) }}%</span>
                                    @if(isset($analysis['confidence']))
                                    <span><strong>Confidence:</strong> {{ ucfirst($analysis['confidence']) }}</span>
                                    @endif
                                </div>
                            </div>
                            <svg id="{{ $accordionId }}-icon" width="24" height="24" fill="none" stroke="#6B7280" viewBox="0 0 24 24" style="transition: transform 0.3s; flex-shrink: 0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>

                        <!-- Collapsible Content -->
                        <div id="{{ $accordionId }}" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out;">
                            <div style="padding: 0 24px 24px 24px; border-top: 1px solid #E5E7EB;">
                                
                                <!-- Matched Solution Info -->
                                @if(isset($analysis['matched_solution']) && $analysis['matched_solution'])
                                <div style="background: #F3F4F6; border-radius: 8px; padding: 16px; margin-top: 16px; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                        <svg width="20" height="20" fill="#6B7280" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <span style="color: #374151; font-weight: 600; font-size: 14px;">Matched Reference Solution</span>
                                    </div>
                                    <div style="color: #6B7280; font-size: 13px; font-family: 'Courier New', monospace; background: white; padding: 8px 12px; border-radius: 6px;">
                                        {{ $analysis['matched_solution'] }}
                                    </div>
                                </div>
                                @endif

                                <!-- Detection Method Info -->
                                <div style="background: #EFF6FF; border-left: 3px solid #3B82F6; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: start; gap: 10px;">
                                        <svg width="20" height="20" fill="#3B82F6" viewBox="0 0 20 20" style="flex-shrink: 0; margin-top: 2px;">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div style="flex: 1;">
                                            <div style="color: #1E40AF; font-weight: 600; font-size: 13px; margin-bottom: 4px;">TF-IDF Vector Similarity Analysis</div>
                                            <div style="color: #3B82F6; font-size: 12px; line-height: 1.5;">
                                                Your code was analyzed using Term Frequency-Inverse Document Frequency vectorization, comparing token patterns against a database of known AI-generated solutions (ChatGPT, GitHub Copilot, etc.).
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Analysis Reason -->
                                @if(isset($analysis['reason']))
                                <div style="margin-bottom: 16px;">
                                    <h5 style="color: #374151; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Analysis Summary</h5>
                                    <p style="color: #6B7280; font-size: 14px; line-height: 1.6; margin: 0;">{{ $analysis['reason'] }}</p>
                                </div>
                                @endif

                                <!-- Detection Indicators -->
                                @if(isset($analysis['indicators']) && is_array($analysis['indicators']))
                                <div>
                                    <h5 style="color: #374151; font-size: 14px; font-weight: 600; margin-bottom: 12px;">Detection Indicators</h5>
                                    <ul style="margin: 0; padding-left: 20px; list-style: none;">
                                        @foreach($analysis['indicators'] as $indicator)
                                        <li style="color: #6B7280; font-size: 13px; line-height: 1.8; margin-bottom: 8px; position: relative; padding-left: 24px;">
                                            <svg width="16" height="16" fill="{{ $riskColor }}" viewBox="0 0 20 20" style="position: absolute; left: 0; top: 4px;">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $indicator }}
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            <!-- AI Feedback Section -->
            @if(isset($submission['ai_feedback']))
            <div style="margin-top: 30px;">
                @if(isset($submission['ai_feedback']['rate_limited']) && $submission['ai_feedback']['rate_limited'])
                <!-- Rate Limit Warning Banner -->
                <div style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); border-radius: 12px; padding: 24px; margin-bottom: 20px; border: 2px solid #FBBF24;">
                    <div style="display: flex; align-items: start; gap: 16px;">
                        <svg width="32" height="32" fill="white" viewBox="0 0 20 20" style="flex-shrink: 0;">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div style="flex: 1;">
                            <h3 style="color: white; font-size: 18px; font-weight: 700; margin: 0 0 8px 0;">AI Feedback Temporarily Unavailable</h3>
                            <p style="color: rgba(255,255,255,0.95); font-size: 14px; line-height: 1.6; margin: 0;">
                                The Gemini API has reached its request limit. Your submission has been saved successfully, but AI-generated feedback cannot be generated at this time. Please check back later or contact support if this issue persists.
                            </p>
                        </div>
                    </div>
                </div>
                @else
                <!-- Normal AI Feedback Header -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <svg width="28" height="28" fill="white" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <h3 style="color: white; font-size: 20px; font-weight: 700; margin: 0;">AI Code Feedback</h3>
                    </div>
                    <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 0;">
                        Personalized insights to help you improve. Click each section to expand.
                    </p>
                </div>
                @endif

                <!-- Correctness Section (Collapsible) -->
                @if(!empty($submission['ai_feedback']['correctness']))
                <button onclick="toggleFeedbackSection('correctness')" class="feedback-toggle-btn">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg width="24" height="24" fill="#3B82F6" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div style="text-align: left;">
                            <div style="font-weight: 600; color: #1F2937; font-size: 15px;">Correctness</div>
                            <div style="font-size: 13px; color: #6B7280;">{{ Str::limit($submission['ai_feedback']['correctness'], 80) }}</div>
                        </div>
                    </div>
                    <svg class="chevron-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="feedback-correctness" class="feedback-content">
                    <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $submission['ai_feedback']['correctness'] }}</div>
                </div>
                @endif

                <!-- Style & Readability Section (Collapsible) -->
                @if(!empty($submission['ai_feedback']['style']))
                <button onclick="toggleFeedbackSection('style')" class="feedback-toggle-btn">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg width="24" height="24" fill="#8B5CF6" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        <div style="text-align: left;">
                            <div style="font-weight: 600; color: #1F2937; font-size: 15px;">Style & Readability</div>
                            <div style="font-size: 13px; color: #6B7280;">{{ Str::limit($submission['ai_feedback']['style'], 80) }}</div>
                        </div>
                    </div>
                    <svg class="chevron-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="feedback-style" class="feedback-content">
                    <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $submission['ai_feedback']['style'] }}</div>
                </div>
                @endif

                <!-- Error Analysis Section (Collapsible) -->
                @if(!empty($submission['ai_feedback']['errors']))
                <button onclick="toggleFeedbackSection('errors')" class="feedback-toggle-btn">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg width="24" height="24" fill="#F59E0B" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div style="text-align: left;">
                            <div style="font-weight: 600; color: #1F2937; font-size: 15px;">Error Analysis</div>
                            <div style="font-size: 13px; color: #6B7280;">{{ Str::limit($submission['ai_feedback']['errors'], 80) }}</div>
                        </div>
                    </div>
                    <svg class="chevron-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="feedback-errors" class="feedback-content">
                    <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $submission['ai_feedback']['errors'] }}</div>
                </div>
                @endif

                <!-- Suggestions Section (Collapsible) -->
                @if(!empty($submission['ai_feedback']['suggestions']))
                <button onclick="toggleFeedbackSection('suggestions')" class="feedback-toggle-btn">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg width="24" height="24" fill="#10B981" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div style="text-align: left;">
                            <div style="font-weight: 600; color: #1F2937; font-size: 15px;">Suggestions</div>
                            <div style="font-size: 13px; color: #6B7280;">{{ Str::limit($submission['ai_feedback']['suggestions'], 80) }}</div>
                        </div>
                    </div>
                    <svg class="chevron-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="feedback-suggestions" class="feedback-content">
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
                    Next Question
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
</body>
</html>
