<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Questions - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('js/navBar.js') }}"></script>
    <script src="{{ asset('js/reviewer/review.js') }}"></script>
    @include('layouts.reviewer.reviewCSS')
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
                <button class="nav-item active-reviewer">Review</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.generate') }}'">Generate</button>
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

    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">Questions to Review</h2>
                <span class="pending-badge">{{ count($pendingQuestions) }} pending</span>
            </div>

            <!-- Question Cards -->
            <div class="question-list">
                @forelse($pendingQuestions as $index => $question)
                    <div class="question-card {{ $index === 0 ? 'active' : '' }}" data-question-id="{{ $question['id'] }}">
                        <div class="question-card-header">
                            <span class="difficulty-badge {{ strtolower($question['difficulty']) }}">{{ $question['difficulty'] }}</span>
                            <span class="status-badge pending">{{ $question['status'] }}</span>
                        </div>
                        <h3 class="question-card-title">{{ $question['title'] }}</h3>
                        <div class="question-tags">
                            <span class="tag tag-{{ strtolower($question['language']) }}">{{ $question['language'] }}</span>
                            <span class="tag tag-topic">{{ $question['chapter'] }}</span>
                        </div>
                        <div class="question-meta">
                            {{ $question['category'] }} • {{ $question['time_ago'] }}
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p class="empty-state-text">No pending questions match your qualifications</p>
                        @if(empty($qualifiedLanguages))
                            <p class="empty-state-subtext">Complete a competency test to start reviewing</p>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-area">
            @if($currentQuestion)
                <!-- Question Header -->
                <div class="question-header">
                    <div class="question-header-left">
                        <h1 class="question-title">{{ $currentQuestion['title'] }}</h1>
                        <div class="question-badges">
                            <span class="difficulty-badge {{ strtolower($currentQuestion['difficulty']) }}">{{ $currentQuestion['difficulty'] }}</span>
                            <span class="category-badge">{{ $currentQuestion['category'] }}</span>
                            <span class="language-badge">{{ $currentQuestion['language'] }}</span>
                            <span class="topic-badge">{{ $currentQuestion['chapter'] }}</span>
                            <span class="submitted-text">Submitted {{ $currentQuestion['time_ago'] }}</span>
                        </div>
                    </div>
                    <div class="question-actions">
                        <button class="btn-edit" onclick="toggleEditMode()" id="editBtn">
                            <span class="btn-icon">✎</span>
                            Edit
                        </button>
                        <button class="btn-save" onclick="saveInlineEdit()" id="saveBtn" style="display: none;">
                            <span class="btn-icon">💾</span>
                            Save
                        </button>
                        <button class="btn-cancel" onclick="cancelEditMode()" id="cancelBtn" style="display: none;">
                            <span class="btn-icon">✕</span>
                            Cancel
                        </button>
                        <button class="btn-approve" id="approveBtn">
                            <span class="btn-icon">✓</span>
                            Approve
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab active" data-tab="problem">
                        Problem
                    </button>
                    <button class="tab" data-tab="testcases">
                        Test Cases ({{ is_array($currentQuestion['input']) ? count($currentQuestion['input']) : 0 }})
                    </button>
                    <button class="tab" data-tab="solution">
                        Solution
                    </button>
                </div>

                <!-- Content Sections -->
                <div class="content-section" id="problem-section">
                    <div class="section-header">
                        <h2 class="section-title">Description</h2>
                    </div>
                    <div class="problem-box">
                        <p class="problem-text">{!! nl2br(e($currentQuestion['description'])) !!}</p>
                    </div>

                    <div class="section-header">
                        <h2 class="section-title">Problem Statement</h2>
                    </div>
                    <div class="problem-box">
                        <p class="problem-text">{!! nl2br(e($currentQuestion['problem_statement'])) !!}</p>
                    </div>

                    <div class="section-header">
                        <h2 class="section-title">Constraints</h2>
                    </div>
                    <div class="problem-box">
                        <p class="problem-text">
                            {!! nl2br(preg_replace('/-\s*(Input parameters:|Output:|Rules:|Edge cases:)/i', '<strong>$1</strong>', e($currentQuestion['constraints']))) !!}
                        </p>
                    </div>
                    @if($currentQuestion['hint'])
                            <div class="hint-box">
                                <strong>💡 Hint:</strong> {{ $currentQuestion['hint'] }}
                            </div>
                        @endif
                </div>

                <div class="content-section" id="testcases-section" style="display: none;">
                    <div class="section-header">
                        <h2 class="section-title">Test Cases</h2>
                    </div>
                    @if($currentQuestion && isset($currentQuestion['input']) && is_array($currentQuestion['input']) && count($currentQuestion['input']) > 0)
                        @php
                            $expectedOutputs = $currentQuestion['expected_output'];
                            if (is_string($expectedOutputs)) {
                                $expectedOutputs = json_decode($expectedOutputs, true);
                            }
                        @endphp
                        @foreach($currentQuestion['input'] as $index => $input)
                            <div class="test-case-box">
                                <div class="test-case-header">
                                    <h3 class="test-case-title">Test Case {{ $index + 1 }}</h3>
                                </div>
                                <div class="test-case-content">
                                    <div class="test-case-item">
                                        <strong>Input</strong>
                                        <pre class="code-block">{{ is_array($input) ? json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $input }}</pre>
                                    </div>
                                    @if(isset($expectedOutputs[$index]))
                                        <div class="test-case-item">
                                            <strong>Expected Output</strong>
                                            <pre class="code-block">{{ is_array($expectedOutputs[$index]) ? json_encode($expectedOutputs[$index], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $expectedOutputs[$index] }}</pre>
                                        </div>
                                    @elseif(!is_array($expectedOutputs) && $expectedOutputs)
                                        <div class="test-case-item">
                                            <strong>Expected Output</strong>
                                            <pre class="code-block">{{ $expectedOutputs }}</pre>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="problem-box">
                            <p class="problem-text">No test cases available for this question.</p>
                        </div>
                    @endif
                </div>

                <div class="content-section" id="solution-section" style="display: none;">
                    <div class="section-header">
                        <h2 class="section-title">Expected Answer/Solution</h2>
                    </div>
                    <div class="solution-box">
                        @if($currentQuestion['question_type'] === 'MCQ_Single')
                            <div class="answer-display">
                                <strong>Correct Answer:</strong> {{ $currentQuestion['solution'] }}
                            </div>
                        @else
                            <pre class="code-block solution-code">{{ $currentQuestion['solution'] }}</pre>
                        @endif
                    </div>
                </div>
            @else
                <div class="empty-content">
                    <h2 class="empty-content-title">No Questions Available</h2>
                    <p class="empty-content-text">There are no pending questions that match your qualifications at the moment.</p>
                    @if(empty($qualifiedLanguages))
                        <button class="btn-primary" onclick="window.location.href='{{ route('reviewer.competency.choose') }}'">
                            Take Competency Test
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Grade Question Modal -->
    <div class="modal-overlay" id="gradeModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-header-content">
                    <div>
                        <h2 class="modal-title">Grade Question</h2>
                        <p class="modal-subtitle">Evaluate quality across criteria</p>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <!-- Overall Grade Display -->
                <div class="overall-grade-box">
                    <div class="overall-grade-label">OVERALL GRADE</div>
                    <div class="overall-grade-value">
                        <span id="overallGradePercent">0%</span>
                        <span class="pass-indicator" id="passIndicator" style="display: none;">
                            ✓ Passes (≥70%)
                        </span>
                    </div>
                </div>

                <!-- Grading Criteria -->
                <div class="criteria-list">
                    <!-- Question Quality -->
                    <div class="criteria-item">
                        <div class="criteria-header">
                            <div>
                                <div class="criteria-name">Question Quality</div>
                                <div class="criteria-description">Relevance & learning value</div>
                            </div>
                            <div class="criteria-value" id="qualityValue">0%</div>
                        </div>
                        <input type="range" class="criteria-slider" id="qualitySlider" min="0" max="100" value="0" step="5">
                    </div>

                    <!-- Clarity & Description -->
                    <div class="criteria-item">
                        <div class="criteria-header">
                            <div>
                                <div class="criteria-name">Clarity & Description</div>
                                <div class="criteria-description">Problem statement quality</div>
                            </div>
                            <div class="criteria-value" id="clarityValue">0%</div>
                        </div>
                        <input type="range" class="criteria-slider" id="claritySlider" min="0" max="100" value="0" step="5">
                    </div>

                    <!-- Difficulty Level -->
                    <div class="criteria-item">
                        <div class="criteria-header">
                            <div>
                                <div class="criteria-name">Difficulty Level</div>
                                <div class="criteria-description">Appropriate for category</div>
                            </div>
                            <div class="criteria-value" id="difficultyValue">0%</div>
                        </div>
                        <input type="range" class="criteria-slider" id="difficultySlider" min="0" max="100" value="0" step="5">
                    </div>

                    <!-- Test Cases Quality -->
                    <div class="criteria-item">
                        <div class="criteria-header">
                            <div>
                                <div class="criteria-name">Test Cases Quality</div>
                                <div class="criteria-description">Comprehensive & explained</div>
                            </div>
                            <div class="criteria-value" id="testcasesValue">0%</div>
                        </div>
                        <input type="range" class="criteria-slider" id="testcasesSlider" min="0" max="100" value="0" step="5">
                    </div>
                </div>

                <!-- Feedback -->
                <div class="feedback-section">
                    <label class="feedback-label">Feedback (Optional)</label>
                    <textarea class="feedback-textarea" id="feedbackText" placeholder="Additional comments or suggestions..." rows="4"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="closeGradeModal()">
                    <span class="btn-icon">✕</span>
                    Cancel
                </button>
                <button class="btn-modal-submit" id="submitGradeBtn" onclick="submitGrade()">
                    <span class="btn-icon">✓</span>
                    Submit Grade
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Question Modal -->
    <div class="modal-overlay" id="editModal" style="display: none;">
        <div class="modal-container" style="max-width: 900px;">
            <div class="modal-header">
                <div class="modal-header-content">
                    <div class="modal-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="modal-title">Edit Question</h2>
                        <p class="modal-subtitle">Make necessary corrections before approval</p>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <!-- Question Content -->
                <div class="edit-section">
                    <label class="edit-label">Question Content</label>
                    <textarea class="edit-textarea" id="editContent" placeholder="Enter the question content..." rows="8"></textarea>
                </div>

                <!-- Hint -->
                <div class="edit-section">
                    <label class="edit-label">Hint (Optional)</label>
                    <textarea class="edit-textarea" id="editHint" placeholder="Enter a hint for learners..." rows="2"></textarea>
                </div>

                <!-- Test Cases (for coding questions) -->
                <div class="edit-section" id="testCasesSection" style="display: none;">
                    <label class="edit-label">Test Cases (JSON Format)</label>
                    <textarea class="edit-textarea" id="editTestCases" placeholder='[{"input": "...", "expected_output": "...", "explanation": "..."}]' rows="6"></textarea>
                    <small style="color: #666; font-size: 12px;">Format: Array of objects with input, expected_output, and explanation fields</small>
                </div>

                <!-- Solution -->
                <div class="edit-section">
                    <label class="edit-label">Solution/Expected Answer</label>
                    <textarea class="edit-textarea" id="editSolution" placeholder="Enter the solution or correct answer..." rows="6"></textarea>
                </div>

                <!-- Options (for MCQ) -->
                <div class="edit-section" id="optionsSection" style="display: none;">
                    <label class="edit-label">Options (JSON Format)</label>
                    <textarea class="edit-textarea" id="editOptions" placeholder='{"A": "Option A", "B": "Option B", "C": "Option C", "D": "Option D"}' rows="4"></textarea>
                    <small style="color: #666; font-size: 12px;">Format: Object with keys A, B, C, D and their corresponding text</small>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="closeEditModal()">
                    <span class="btn-icon">✕</span>
                    Cancel
                </button>
                <button class="btn-modal-submit" id="saveEditBtn" onclick="saveEdit()">
                    <span class="btn-icon">💾</span>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal-overlay" id="successModal" style="display: none;">
        <div class="success-modal-container">
            <div class="success-icon-circle">
                <svg class="success-checkmark" width="48" height="48" viewBox="0 0 48 48" fill="none">
                    <path d="M8 24L18 34L40 12" stroke="#4CAF50" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <h2 class="success-title" id="successTitle">Question Approved</h2>
            <p class="success-message" id="successMessage">
                "<span id="successQuestionTitle"></span>" has been approved<br>
                with a score of <span class="success-score" id="successScore">75%</span>
            </p>
            
            <button class="btn-continue" onclick="closeSuccessModal()">
                Continue
            </button>
        </div>
    </div>

    <script>
        window.reviewConfig = {
            csrfToken: "{{ csrf_token() }}",
            routes: {
                getQuestion: "/reviewer/review/question", // Base URL
                submitGrade: "{{ route('reviewer.review.grade') }}", // Ensure this route exists
                editQuestion: "{{ route('reviewer.review.edit') }}"  // Ensure this route exists
            }
        };
    </script>

</body>
</html>
