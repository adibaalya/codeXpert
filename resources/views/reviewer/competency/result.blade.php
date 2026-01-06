<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodeXpert - Test Results</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('js/navBar.js') }}"></script>
    <script src="{{ asset('js/result.js') }}"></script>
    @include('layouts.navCSS')
    @include('layouts.resultCSS')
    
</head>
<body class="result-body-reviewer">
    <!-- Header -->
    <div class="header" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000;">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert.png') }}" alt="CodeXpert Logo">
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
                <div class="user-avatar-reviewer" onclick="toggleUserMenu(event)">
                    @if($reviewer->profile_photo)
                        <img src="{{ asset('storage/' . $reviewer->profile_photo) }}" alt="{{ $reviewer->username }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr($reviewer->username, 0, 1)) }}{{ strtoupper(substr($reviewer->username, 1, 1) ?? '') }}
                    @endif
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header-reviewer">
                        <div class="user-dropdown-avatar">
                            @if($reviewer->profile_photo)
                                <img src="{{ asset('storage/' . $reviewer->profile_photo) }}" alt="{{ $reviewer->username }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                                {{ strtoupper(substr($reviewer->username, 0, 1)) }}{{ strtoupper(substr($reviewer->username, 1, 1) ?? '') }}
                            @endif
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

    <div class="result-container">
        <div class="result-card">

            <!-- Header -->
            <h1 class="result-header-title">Test Complete</h1>
            <p class="result-header-subtitle">{{ $result->language }} Competency Assessment Results</p>

            <!-- Success Icon -->
            <div class="success-icon-wrapper">
                <div class="success-icon-circle {{ $result->passed ? '' : 'failed' }}">
                    @if($result->passed)
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
            <h2 class="result-message-title {{ $result->passed ? '' : 'failed' }}">
                @if($result->passed)
                    Congratulations!
                @else
                    Not Passed
                @endif
            </h2>

            <!-- Congrats Box -->
            <div class="result-congrats-box {{ $result->passed ? '' : 'failed' }}">
                <p class="result-congrats-text {{ $result->passed ? '' : 'failed' }}">
                    @if($result->passed)
                        You've successfully passed the {{ $result->language }} competency test! Your expertise has been verified.
                    @else
                        @php
                            $plagiarismPassed = $result->plagiarism_score >= 60;
                            $correctnessPassed = $result->total_score >= 50;
                        @endphp
                        Unfortunately, you didn't meet the minimum requirements. 
                        @if(!$plagiarismPassed && !$correctnessPassed)
                            Your code failed both the plagiarism detection ({{ round($result->plagiarism_score) }}% originality) and correctness requirements ({{ round($result->total_score) }}% score).
                        @elseif(!$plagiarismPassed)
                            Your code showed signs of AI-generated content ({{ round($result->plagiarism_score) }}% originality). Please write your own solution.
                        @elseif(!$correctnessPassed)
                            Your correctness score ({{ round($result->total_score) }}%) is below the 50% minimum requirement.
                        @endif
                        Please review the material and try again.
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
                                    $plagiarismPassed = $result->plagiarism_score >= 60;
                                @endphp
                                @if(!$plagiarismPassed)
                                    <span style="background: #FEE2E2; color: #DC2626; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">FAILED</span>
                                @endif
                            </div>
                        </div>
                        <div class="result-score-value" style="color: {{ $plagiarismPassed ? '#10B981' : '#EF4444' }};">{{ round($result->plagiarism_score) }}%</div>
                    </div>
                    <div class="result-score-sublabel">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        @php
                            // Get detection method from first solution's plagiarism analysis
                            $detectionMethod = 'AI Similarity Analysis';
                            if ($result->code_solutions && is_array($result->code_solutions)) {
                                foreach($result->code_solutions as $solution) {
                                    if (isset($solution['plagiarism_analysis']['detection_method'])) {
                                        $detectionMethod = $solution['plagiarism_analysis']['detection_method'];
                                        break;
                                    }
                                }
                            }
                        @endphp
                        {{ $detectionMethod }} (60% minimum required)
                        @if($plagiarismPassed)
                            <span style="color: #10B981; font-weight: 600;">✓ Passed</span>
                        @else
                            <span style="color: #EF4444; font-weight: 600;">✗ Failed</span>
                        @endif
                    </div>
                    <div class="result-progress-bar">
                        <div class="result-progress-fill {{ $plagiarismPassed ? '' : 'low' }}" style="width: {{ $result->plagiarism_score }}%;"></div>
                    </div>
                </div>

                <!-- Correctness Score -->
                <div class="result-score-item">
                    <div class="result-score-header">
                        <div class="result-score-label">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>Correctness Score</span>
                                @php
                                    $correctnessPassed = $result->total_score >= 50;
                                @endphp
                                @if(!$correctnessPassed)
                                    <span style="background: #FEE2E2; color: #DC2626; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">FAILED</span>
                                @endif
                            </div>
                        </div>
                        <div class="result-score-value" style="color: {{ $result->total_score >= 75 ? '#10B981' : ($result->total_score >= 50 ? '#F59E0B' : '#EF4444') }};">
                            {{ round($result->total_score) }}%
                        </div>
                    </div>
                    <div class="result-score-sublabel">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Answer accuracy rate (50% minimum required)
                        @if($correctnessPassed)
                            <span style="color: #10B981; font-weight: 600;">✓ Passed</span>
                        @else
                            <span style="color: #EF4444; font-weight: 600;">✗ Failed</span>
                        @endif
                    </div>
                    <div class="result-progress-bar">
                        <div class="result-progress-fill {{ $result->total_score >= 75 ? '' : ($result->total_score >= 50 ? 'medium' : 'low') }}" 
                             style="width: {{ $result->total_score }}%;"></div>
                    </div>
                </div>
            </div>

            <!-- Detailed Plagiarism Analysis Section -->
            @if($result->code_solutions && is_array($result->code_solutions))
                @php
                    $hasPlagiarismData = false;
                    foreach($result->code_solutions as $solution) {
                        if(isset($solution['plagiarism_analysis'])) {
                            $hasPlagiarismData = true;
                            break;
                        }
                    }
                @endphp

                @if($hasPlagiarismData)
                <div style="margin-top: 30px;">
                    <div style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <svg width="28" height="28" fill="white" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <h3 style="color: white; font-size: 20px; font-weight: 700; margin: 0;">Plagiarism Analysis</h3>
                        </div>
                        <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 0;">
                            Vector Similarity Method - Comparing your code against known AI-generated solutions
                        </p>
                    </div>

                    @foreach($result->code_solutions as $questionId => $solution)
                        @if(isset($solution['plagiarism_analysis']))
                            @php
                                $analysis = $solution['plagiarism_analysis'];
                                // ai_probability is now ORIGINALITY (100 = original, 0 = plagiarized)
                                $originality = $analysis['ai_probability'] ?? 100;
                                // Get actual similarity to AI (if available)
                                $similarity = $analysis['similarity_to_ai'] ?? (100 - $originality);
                                
                                // Risk level based on similarity to AI (higher similarity = higher risk)
                                $riskLevel = $similarity >= 80 ? 'high' : ($similarity >= 60 ? 'medium' : ($similarity >= 40 ? 'low' : 'minimal'));
                                $riskColor = $similarity >= 80 ? '#EF4444' : ($similarity >= 60 ? '#F59E0B' : ($similarity >= 40 ? '#EAB308' : '#10B981'));
                                $accordionId = "plagiarism-accordion-{$loop->iteration}";
                            @endphp

                            <!-- Collapsible Card -->
                            <div style="background: white; border-radius: 12px; margin-bottom: 16px; border-left: 4px solid {{ $riskColor }}; overflow: hidden;">
                                <!-- Clickable Header -->
                                <div onclick="toggleAccordion('{{ $accordionId }}')" style="padding: 20px 24px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                            <h4 style="color: #1F2937; font-size: 18px; font-weight: 700; margin: 0;">Question {{ $loop->iteration }}</h4>
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
                        @endif
                    @endforeach
                </div>
                @endif
            @endif

            <!-- AI Feedback Section for Code Solutions -->
            @if($result->code_solutions && is_array($result->code_solutions))
                @foreach($result->code_solutions as $questionId => $solution)
                    @if(isset($solution['ai_feedback']))
                    <div style="margin-top: 30px;">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                <svg width="28" height="28" fill="white" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <h3 style="color: white; font-size: 20px; font-weight: 700; margin: 0;">AI Code Feedback - Question {{ $loop->iteration }}</h3>
                            </div>
                            <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 0;">
                                Score: {{ round($solution['score'], 1) }}/10 | Test Cases: {{ $solution['passed_tests'] }}/{{ $solution['total_tests'] }} passed
                            </p>
                        </div>

                        <!-- Correctness Section -->
                        @if(!empty($solution['ai_feedback']['correctness']))
                        <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 2px solid #E5E7EB;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                <svg width="24" height="24" fill="#3B82F6" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <h4 style="color: #1F2937; font-size: 16px; font-weight: 600; margin: 0;">Correctness</h4>
                            </div>
                            <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $solution['ai_feedback']['correctness'] }}</div>
                        </div>
                        @endif

                        <!-- Style & Readability Section -->
                        @if(!empty($solution['ai_feedback']['style']))
                        <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 2px solid #E5E7EB;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                <svg width="24" height="24" fill="#8B5CF6" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 011.414-1.414zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                <h4 style="color: #1F2937; font-size: 16px; font-weight: 600; margin: 0;">Style & Readability</h4>
                            </div>
                            <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $solution['ai_feedback']['style'] }}</div>
                        </div>
                        @endif

                        <!-- Error Analysis Section -->
                        @if(!empty($solution['ai_feedback']['errors']))
                        <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 2px solid #E5E7EB;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                <svg width="24" height="24" fill="#F59E0B" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <h4 style="color: #1F2937; font-size: 16px; font-weight: 600; margin: 0;">Error Analysis</h4>
                            </div>
                            <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $solution['ai_feedback']['errors'] }}</div>
                        </div>
                        @endif

                        <!-- Suggestions Section -->
                        @if(!empty($solution['ai_feedback']['suggestions']))
                        <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 2px solid #E5E7EB;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                <svg width="24" height="24" fill="#10B981" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <h4 style="color: #1F2937; font-size: 16px; font-weight: 600; margin: 0;">Suggestions</h4>
                            </div>
                            <div style="color: #4B5563; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $solution['ai_feedback']['suggestions'] }}</div>
                        </div>
                        @endif
                    </div>
                    @endif
                @endforeach
            @endif

            <!-- Action Buttons -->
            <div class="result-actions">
                @if(!$result->passed)
                    <a href="{{ route('reviewer.competency.choose') }}" class="result-btn result-btn-secondary">
                        Test Another Language
                    </a>
                @endif
                <a href="{{ route('reviewer.dashboard') }}" class="result-btn-reviewer result-btn-reviewer-primary">
                    Continue to Dashboard
                </a>
            </div>
        </div>
    </div>

</body>
</html>
