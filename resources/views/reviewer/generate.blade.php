<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Generate Questions with AI - CodeXpert</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="{{ asset('js/navBar.js') }}"></script>
    <script src="{{ asset('js/reviewer/generate.js') }}?v={{ time() }}"></script>
    @include('layouts.reviewer.generateCSS')
    @include('layouts.navCSS')
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert.png') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.dashboard') }}'">Dashboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.review') }}'" >Review</button>
                <button class="nav-item active-reviewer" onclick="window.location.href='{{ route('reviewer.generate') }}'">Generate</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.history') }}'">History</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ $reviewer->username }}</div>
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

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Generate Questions with AI</h1>
            <p class="page-subtitle">Use Gemini AI to create coding questions based on your preferences</p>
        </div>

        <div class="content-grid">
            <!-- Left Panel: Question Parameters -->
            <div class="card">
                <h2 class="card-title">Question Parameters</h2>
                <form id="generateForm">
                    <div class="form-group">
                        <label class="form-label">Prompt</label>
                        <textarea 
                            class="form-textarea" 
                            id="prompt" 
                            name="prompt"
                            placeholder="E.g., Create a question about implementing a binary search tree with insertion and deletion operations"
                        ></textarea>
                        <p class="form-hint">Be specific about what you want the question to cover</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Language</label>
                            <select class="form-select" id="language" name="language">
                                <option value="" selected disabled>Select Language</option>
                                @foreach($languages as $language)
                                    <option value="{{ $language }}">{{ $language }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Difficulty</label>
                            <select class="form-select" id="difficulty" name="difficulty">
                                <option value="" selected disabled>Select Difficulty</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-generate" id="generateBtn">
                        Generate Question
                    </button>
                </form>
            </div>

            <!-- Right Panel: Generated Question -->
            <div class="card result-card">
                <div class="empty-state" id="emptyState">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2L3 14h8l-1 8 10-12h-8l1-8z"/>
                    </svg>
                    <p>Your generated question will appear here</p>
                </div>

                <div class="loading" id="loadingState" style="display: none;">
                    <div class="spinner"></div>
                    <p class="loading-text" id="loadingText">Generating your question...</p>
                    <p class="loading-subtext" id="loadingSubtext" style="display: none; margin-top: 8px; color: #6B7280; font-size: 14px;"></p>
                </div>

                <div class="error-state" id="errorState" style="display: none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 64px; height: 64px; color: #EF4444; margin-bottom: 16px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p style="font-size: 18px; font-weight: 600; color: #1F2937; margin-bottom: 8px;" id="errorTitle">Generation Failed</p>
                    <p style="color: #6B7280; margin-bottom: 16px; max-width: 400px;" id="errorMessage"></p>
                    <button class="btn btn-primary" onclick="retryGeneration()" id="retryButton">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 6px; vertical-align: middle;">
                            <path d="M21 2v6h-6M3 12a9 9 0 0 1 15-6.7L21 8M3 22v-6h6m12-4a9 9 0 0 1-15 6.7L3 16"/>
                        </svg>
                        Try Again
                    </button>
                </div>

                <div class="generated-content" id="generatedContent">
                    <div class="question-header">
                        <h3 id="questionTitle" style="font-size: 20px; font-weight: 700; flex: 1;"></h3>
                    </div>

                    <div class="question-header">
                        <span class="badge badge-intermediate" id="difficultyBadge"></span>
                        <span class="badge badge-algorithms" id="topicBadge"></span>
                        <span class="badge badge-python" id="languageBadge"></span>
                    </div>

                    <div class="tabs">
                        <button class="tab active" onclick="switchTab('problem')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 6px; vertical-align: middle;">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 8v8m0 0h4m-4 0H8"/>
                            </svg>
                            Problem
                        </button>
                        <button class="tab" onclick="switchTab('tests')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 6px; vertical-align: middle;">
                                <path d="M9 12l2 2 4-4"/>
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                            Tests (<span id="testCount">3</span>)
                        </button>
                        <button class="tab" onclick="switchTab('solution')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 6px; vertical-align: middle;">
                                <polyline points="16 18 22 12 16 6"/>
                                <polyline points="8 6 2 12 8 18"/>
                            </svg>
                            Solution
                        </button>
                    </div>

                    <div class="tab-content active" id="problemTab">
                        <div class="section-title">Description</div>
                        <div class="problem-text" id="problemDescription"></div>

                        <div class="section-title">Problem Statement</div>
                        <div class="problem-text" id="problemStatement"></div>

                        <div class="section-title">Constraints</div>
                        <ul class="constraints-list" id="constraintsList"></ul>

                        <div class="section-title">Expected Approach</div>
                        <div class="problem-text" id="expectedApproach"></div>
                    </div>

                    <div class="tab-content" id="testsTab">
                        <div id="testsContent"></div>
                    </div>

                    <div class="tab-content" id="solutionTab">
                        <div class="problem-text" id="solutionContent"></div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn" onclick="regenerate()">Discard</button>
                        <button class="btn btn-primary" onclick="saveToQueue()">Save to Review Queue</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal-overlay" style="display: none;">
        <div class="success-modal-container">
            <div class="success-icon-circle">
                <svg class="success-checkmark" width="48" height="48" viewBox="0 0 48 48" fill="none">
                    <path d="M8 24L18 34L40 12" stroke="#4CAF50" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <h2 class="success-title" id="successTitle">Question Saved</h2>
            <p class="success-message" id="successMessage"></p>
            
            <button class="btn-continue" onclick="closeModal()">
                Continue
            </button>
        </div>
    </div>

    <script>
        window.generateConfig = {
            routes: {
                generate: "{{ route('reviewer.generate.question') }}",
                save: "{{ route('reviewer.generate.save') }}"
            }
        };
    </script>
</body>
</html>
