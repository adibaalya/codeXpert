<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Questions - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.reviewCSS')
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
                    {{ strtoupper(substr($reviewer->username, 0, 1)) }}{{ strtoupper(substr($reviewer->username, 1, 1) ?? '') }}
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header-reviewer">
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

    <script>
        // Check for question_id parameter in URL and load that question
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const questionId = urlParams.get('question_id');
            
            if (questionId) {
                // Find and activate the question card
                const questionCard = document.querySelector(`.question-card[data-question-id="${questionId}"]`);
                
                if (questionCard) {
                    // Remove active class from all cards
                    document.querySelectorAll('.question-card').forEach(c => c.classList.remove('active'));
                    
                    // Activate the target card
                    questionCard.classList.add('active');
                    
                    // Scroll the card into view
                    questionCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    
                    // Load the question details
                    loadQuestionDetails(questionId);
                } else {
                    // Question not in the list, but try to load it anyway
                    loadQuestionDetails(questionId);
                }
                
                // Clean up URL (optional - removes the query parameter)
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Hide all sections
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Show selected section
                const tabName = this.getAttribute('data-tab');
                document.getElementById(tabName + '-section').style.display = 'block';
            });
        });

        // Question card switching with AJAX loading
        document.querySelectorAll('.question-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.question-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                // Load question details via AJAX
                const questionId = this.getAttribute('data-question-id');
                loadQuestionDetails(questionId);
            });
        });

        // Function to load question details
        function loadQuestionDetails(questionId) {
            // Show loading state
            const contentArea = document.querySelector('.content-area');
            contentArea.innerHTML = '<div class="empty-content"><div class="empty-content-icon">⏳</div><h2 class="empty-content-title">Loading...</h2></div>';
            
            // Fetch question details
            fetch(`/reviewer/review/question/${questionId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Question not found');
                    }
                    return response.json();
                })
                .then(data => {
                    updateContentArea(data);
                })
                .catch(error => {
                    console.error('Error loading question:', error);
                    contentArea.innerHTML = '<div class="empty-content"><div class="empty-content-icon">❌</div><h2 class="empty-content-title">Error Loading Question</h2><p class="empty-content-text">Unable to load the question details. Please try again.</p></div>';
                });
        }

        // Function to update the content area with question data
        function updateContentArea(question) {
            const contentArea = document.querySelector('.content-area');
            
            // Helper function to format constraints
            function formatConstraints(text) {
                if (!text) return '';
                // Escape HTML first
                let escaped = escapeHtml(text);
                // Replace line breaks with <br>
                escaped = escaped.replace(/\n/g, '<br>');
                // Format the keywords to be bold and remove leading hyphens
                escaped = escaped.replace(/-\s*(Input parameters:|Output:|Rules:|Edge cases:)/gi, '<strong>$1</strong>');
                return escaped;
            }
            
            // Build test cases HTML
            let testCasesHTML = '';
            if (question.test_cases && question.test_cases.length > 0) {
                question.test_cases.forEach((testCase, index) => {
                    const inputDisplay = typeof testCase.input === 'object' ? 
                        JSON.stringify(testCase.input, null, 2) : testCase.input;
                    const outputDisplay = typeof testCase.expected_output === 'object' ? 
                        JSON.stringify(testCase.expected_output, null, 2) : testCase.expected_output;
                    
                    testCasesHTML += `
                        <div class="test-case-box">
                            <div class="test-case-header">
                                <h3 class="test-case-title">Test Case ${index + 1}</h3>
                            </div>
                            <div class="test-case-content">
                                ${testCase.input !== undefined && testCase.input !== null ? `
                                    <div class="test-case-item">
                                        <strong>Input</strong>
                                        <pre class="code-block">${escapeHtml(inputDisplay)}</pre>
                                    </div>
                                ` : ''}
                                ${testCase.expected_output !== undefined && testCase.expected_output !== null ? `
                                    <div class="test-case-item">
                                        <strong>Expected Output</strong>
                                        <pre class="code-block">${escapeHtml(outputDisplay)}</pre>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });
            } else {
                testCasesHTML = '<div class="problem-box"><p class="problem-text">No test cases available for this question.</p></div>';
            }

            // Build options HTML for MCQ
            let optionsHTML = '';
            if (question.question_type === 'MCQ_Single' && question.options) {
                optionsHTML = `
                    <div class="options-box">
                        <strong>Options:</strong>
                        <ul class="options-list">
                            ${Object.entries(question.options).map(([key, value]) => 
                                `<li>${escapeHtml(key)}. ${escapeHtml(value)}</li>`
                            ).join('')}
                        </ul>
                    </div>
                `;
            }

            // Build solution HTML
            let solutionHTML = '';
            if (question.question_type === 'MCQ_Single') {
                solutionHTML = `
                    <div class="answer-display">
                        <strong>Correct Answer:</strong> ${escapeHtml(question.solution)}
                    </div>
                `;
            } else {
                solutionHTML = `
                    <pre class="code-block solution-code">${escapeHtml(question.solution || 'No solution provided')}</pre>
                `;
            }

            // Update the entire content area
            contentArea.innerHTML = `
                <!-- Question Header -->
                <div class="question-header">
                    <div class="question-header-left">
                        <h1 class="question-title">${escapeHtml(question.title)}</h1>
                        <div class="question-badges">
                            <span class="difficulty-badge ${question.difficulty.toLowerCase()}">${escapeHtml(question.difficulty)}</span>
                            <span class="category-badge">${escapeHtml(question.category)}</span>
                            <span class="language-badge">${escapeHtml(question.language)}</span>
                            <span class="topic-badge">${escapeHtml(question.chapter)}</span>
                            <span class="submitted-text">Submitted ${question.time_ago}</span>
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
                        Test Cases (${question.test_cases ? question.test_cases.length : 0})
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
                        <p class="problem-text">${escapeHtml(question.description)}</p>
                    </div>

                    <div class="section-header">
                        <h2 class="section-title">Problem Statement</h2>
                    </div>
                    <div class="problem-box">
                        <p class="problem-text">${escapeHtml(question.problem_statement)}</p>
                    </div>

                    <div class="section-header">
                        <h2 class="section-title">Constraints</h2>
                    </div>
                    <div class="problem-box">
                        <p class="problem-text">${formatConstraints(question.constraints)}</p>
                    </div>
                    
                    ${question.hint ? `
                        <div class="hint-box">
                            <strong>💡 Hint:</strong> ${escapeHtml(question.hint)}
                        </div>
                    ` : ''}
                </div>

                <div class="content-section" id="testcases-section" style="display: none;">
                    <div class="section-header">
                        <h2 class="section-title">Test Cases</h2>
                    </div>
                    ${testCasesHTML}
                </div>

                <div class="content-section" id="solution-section" style="display: none;">
                    <div class="section-header">
                        <h2 class="section-title">Expected Answer/Solution</h2>
                    </div>
                    <div class="solution-box">
                        ${solutionHTML}
                    </div>
                </div>
            `;

            // Re-attach tab event listeners
            document.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    document.querySelectorAll('.content-section').forEach(section => {
                        section.style.display = 'none';
                    });
                    
                    const tabName = this.getAttribute('data-tab');
                    document.getElementById(tabName + '-section').style.display = 'block';
                });
            });

            // Re-attach button event listeners for dynamically loaded content
            const editBtn = document.querySelector('.btn-edit');
            const approveBtn = document.querySelector('.btn-approve');
            
            if (editBtn) {
                editBtn.addEventListener('click', openEditModal);
            }
            
            if (approveBtn) {
                approveBtn.addEventListener('click', openGradeModal);
            }
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, m => map[m]);
        }

        // Toggle user dropdown menu
        function toggleUserMenu(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
        });

        // Grading Modal Functions
        let currentQuestionId = null;

        // Open grade modal when clicking Approve button
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-approve')) {
                openGradeModal();
            }
        });

        function openGradeModal() {
            // Get current question ID from active card
            const activeCard = document.querySelector('.question-card.active');
            if (activeCard) {
                currentQuestionId = activeCard.getAttribute('data-question-id');
            }

            // Reset all sliders to 0
            document.getElementById('qualitySlider').value = 0;
            document.getElementById('claritySlider').value = 0;
            document.getElementById('difficultySlider').value = 0;
            document.getElementById('testcasesSlider').value = 0;
            document.getElementById('feedbackText').value = '';

            // Update displays
            updateSliderDisplay('quality', 0);
            updateSliderDisplay('clarity', 0);
            updateSliderDisplay('difficulty', 0);
            updateSliderDisplay('testcases', 0);
            calculateOverallGrade();

            // Show modal
            document.getElementById('gradeModal').style.display = 'flex';
        }

        function closeGradeModal() {
            document.getElementById('gradeModal').style.display = 'none';
            currentQuestionId = null;
        }

        // Update slider value display
        function updateSliderDisplay(criterion, value) {
            document.getElementById(criterion + 'Value').textContent = value + '%';
        }

        // Calculate overall grade
        function calculateOverallGrade() {
            const quality = parseInt(document.getElementById('qualitySlider').value);
            const clarity = parseInt(document.getElementById('claritySlider').value);
            const difficulty = parseInt(document.getElementById('difficultySlider').value);
            const testcases = parseInt(document.getElementById('testcasesSlider').value);

            const average = Math.round((quality + clarity + difficulty + testcases) / 4);
            
            document.getElementById('overallGradePercent').textContent = average + '%';
            
            const passIndicator = document.getElementById('passIndicator');
            if (average >= 70) {
                passIndicator.style.display = 'inline-flex';
            } else {
                passIndicator.style.display = 'none';
            }

            return average;
        }

        // Attach event listeners to sliders
        document.getElementById('qualitySlider').addEventListener('input', function(e) {
            updateSliderDisplay('quality', e.target.value);
            calculateOverallGrade();
        });

        document.getElementById('claritySlider').addEventListener('input', function(e) {
            updateSliderDisplay('clarity', e.target.value);
            calculateOverallGrade();
        });

        document.getElementById('difficultySlider').addEventListener('input', function(e) {
            updateSliderDisplay('difficulty', e.target.value);
            calculateOverallGrade();
        });

        document.getElementById('testcasesSlider').addEventListener('input', function(e) {
            updateSliderDisplay('testcases', e.target.value);
            calculateOverallGrade();
        });

        // Submit grade
        function submitGrade() {
            const overallGrade = calculateOverallGrade();
            const feedback = document.getElementById('feedbackText').value;

            const submitBtn = document.getElementById('submitGradeBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="btn-icon">⏳</span> Submitting...';

            const gradeData = {
                question_id: currentQuestionId,
                quality_score: parseInt(document.getElementById('qualitySlider').value),
                clarity_score: parseInt(document.getElementById('claritySlider').value),
                difficulty_score: parseInt(document.getElementById('difficultySlider').value),
                testcases_score: parseInt(document.getElementById('testcasesSlider').value),
                overall_grade: overallGrade,
                feedback: feedback,
                approved: overallGrade >= 70,
                _token: '{{ csrf_token() }}'
            };

            // Send grade data to backend
            fetch('/reviewer/review/grade', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(gradeData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeGradeModal();
                    
                    // Show success message
                    alert(overallGrade >= 70 ? 
                        '✓ Question approved successfully!' : 
                        '✕ Question rejected (grade below 70%)');
                    
                    // Reload page to refresh question list
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Failed to submit grade');
                }
            })
            .catch(error => {
                console.error('Error submitting grade:', error);
                alert('Error submitting grade: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="btn-icon">✓</span> Submit Grade';
            });
        }

        // Close modal when clicking outside
        document.getElementById('gradeModal').addEventListener('click', function(e) {
            if (e.target.id === 'gradeModal') {
                closeGradeModal();
            }
        });

        // Prevent closing when clicking inside modal
        document.querySelector('.modal-container').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Edit Modal Functions
        function openEditModal() {
            // Get current question ID from active card
            const activeCard = document.querySelector('.question-card.active');
            if (activeCard) {
                currentQuestionId = activeCard.getAttribute('data-question-id');
            }

            // Fetch question details
            fetch(`/reviewer/review/question/${currentQuestionId}`)
                .then(response => response.json())
                .then(data => {
                    // Populate modal fields with question data
                    document.getElementById('editContent').value = data.content || '';
                    document.getElementById('editHint').value = data.hint || '';
                    document.getElementById('editSolution').value = data.solution || '';

                    if (data.question_type === 'MCQ_Single') {
                        document.getElementById('optionsSection').style.display = 'block';
                        document.getElementById('editOptions').value = JSON.stringify(data.options || {}, null, 2);
                        document.getElementById('testCasesSection').style.display = 'none';
                    } else {
                        document.getElementById('optionsSection').style.display = 'none';
                        document.getElementById('testCasesSection').style.display = 'block';
                        document.getElementById('editTestCases').value = JSON.stringify(data.test_cases || [], null, 2);
                    }

                    // Show modal
                    document.getElementById('editModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error loading question for edit:', error);
                    alert('Error loading question for edit: ' + error.message);
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            currentQuestionId = null;
        }

        function saveEdit() {
            const editBtn = document.getElementById('saveEditBtn');
            editBtn.disabled = true;
            editBtn.innerHTML = '<span class="btn-icon">⏳</span> Saving...';

            // Parse JSON safely
            let options = null;
            let test_cases = null;

            try {
                const optionsValue = document.getElementById('editOptions').value.trim();
                if (optionsValue) {
                    options = JSON.parse(optionsValue);
                }
            } catch (e) {
                alert('Error: Invalid JSON format in Options field. Please check your syntax.');
                editBtn.disabled = false;
                editBtn.innerHTML = '<span class="btn-icon">💾</span> Save Changes';
                return;
            }

            try {
                const testCasesValue = document.getElementById('editTestCases').value.trim();
                if (testCasesValue) {
                    test_cases = JSON.parse(testCasesValue);
                }
            } catch (e) {
                alert('Error: Invalid JSON format in Test Cases field. Please check your syntax.');
                editBtn.disabled = false;
                editBtn.innerHTML = '<span class="btn-icon">💾</span> Save Changes';
                return;
            }

            const editData = {
                question_id: currentQuestionId,
                content: document.getElementById('editContent').value,
                hint: document.getElementById('editHint').value,
                solution: document.getElementById('editSolution').value,
                options: options,
                test_cases: test_cases,
                _token: '{{ csrf_token() }}'
            };

            // Send edit data to backend
            fetch('/reviewer/review/edit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(editData)
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server error: ${response.status} - ${text.substring(0, 100)}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeEditModal();
                    
                    // Show success message
                    alert('✓ Question edited successfully!');
                    
                    // Reload page to refresh question list
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error saving edits:', error);
                alert('Error saving edits: ' + error.message);
                editBtn.disabled = false;
                editBtn.innerHTML = '<span class="btn-icon">💾</span> Save Changes';
            });
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target.id === 'editModal') {
                closeEditModal();
            }
        });

        // Prevent closing when clicking inside modal
        document.querySelector('.modal-container').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Inline Edit Functions
        function toggleEditMode() {
            // Get current question ID from active card
            const activeCard = document.querySelector('.question-card.active');
            if (activeCard) {
                currentQuestionId = activeCard.getAttribute('data-question-id');
            }

            const editBtn = document.getElementById('editBtn');
            const saveBtn = document.getElementById('saveBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const approveBtn = document.getElementById('approveBtn');

            const contentArea = document.querySelector('.content-area');
            const problemBoxes = contentArea.querySelectorAll('#problem-section .problem-box');
            const hintBox = contentArea.querySelector('.hint-box');
            const testCaseBoxes = contentArea.querySelectorAll('#testcases-section .test-case-box');
            const solutionBox = contentArea.querySelector('#solution-section .solution-box');

            if (editBtn.style.display !== 'none') {
                // Enter edit mode
                editBtn.style.display = 'none';
                saveBtn.style.display = 'inline-flex';
                cancelBtn.style.display = 'inline-flex';
                approveBtn.style.display = 'none';

                // Make all problem-text elements editable
                problemBoxes.forEach(box => {
                    const problemText = box.querySelector('.problem-text');
                    if (problemText) {
                        problemText.contentEditable = 'true';
                        problemText.classList.add('editable');
                    }
                });

                // Make hint editable if exists
                if (hintBox) {
                    hintBox.contentEditable = 'true';
                    hintBox.classList.add('editable');
                }

                // Make test cases editable
                testCaseBoxes.forEach(box => {
                    const codeBlocks = box.querySelectorAll('.code-block');
                    codeBlocks.forEach(block => {
                        block.contentEditable = 'true';
                        block.classList.add('editable');
                    });
                });

                // Make solution editable
                if (solutionBox) {
                    const solutionContent = solutionBox.querySelector('.code-block, .answer-display');
                    if (solutionContent) {
                        solutionContent.contentEditable = 'true';
                        solutionContent.classList.add('editable');
                    }
                }
            } else {
                // Exit edit mode
                editBtn.style.display = 'inline-flex';
                saveBtn.style.display = 'none';
                cancelBtn.style.display = 'none';
                approveBtn.style.display = 'inline-flex';

                // Make all content non-editable
                problemBoxes.forEach(box => {
                    const problemText = box.querySelector('.problem-text');
                    if (problemText) {
                        problemText.contentEditable = 'false';
                        problemText.classList.remove('editable');
                    }
                });

                if (hintBox) {
                    hintBox.contentEditable = 'false';
                    hintBox.classList.remove('editable');
                }

                // Make test cases non-editable
                testCaseBoxes.forEach(box => {
                    const codeBlocks = box.querySelectorAll('.code-block');
                    codeBlocks.forEach(block => {
                        block.contentEditable = 'false';
                        block.classList.remove('editable');
                    });
                });

                // Make solution non-editable
                if (solutionBox) {
                    const solutionContent = solutionBox.querySelector('.code-block, .answer-display');
                    if (solutionContent) {
                        solutionContent.contentEditable = 'false';
                        solutionContent.classList.remove('editable');
                    }
                }
            }
        }

        function saveInlineEdit() {
            if (!currentQuestionId) {
                alert('Error: No question selected');
                return;
            }

            const contentArea = document.querySelector('.content-area');
            const problemBoxes = contentArea.querySelectorAll('#problem-section .problem-box');
            
            // Get the text content from each problem box
            const description = problemBoxes[0] ? problemBoxes[0].querySelector('.problem-text').innerHTML : '';
            const problemStatement = problemBoxes[1] ? problemBoxes[1].querySelector('.problem-text').innerHTML : '';
            const constraints = problemBoxes[2] ? problemBoxes[2].querySelector('.problem-text').innerHTML : '';
            const hintBox = contentArea.querySelector('.hint-box');
            const hint = hintBox ? hintBox.innerHTML : '';

            // Get test cases
            const testCaseBoxes = contentArea.querySelectorAll('#testcases-section .test-case-box');
            const test_cases = [];
            testCaseBoxes.forEach((box, index) => {
                const codeBlocks = box.querySelectorAll('.code-block');
                if (codeBlocks.length >= 2) {
                    test_cases.push({
                        input: codeBlocks[0].textContent.trim(),
                        expected_output: codeBlocks[1].textContent.trim()
                    });
                } else if (codeBlocks.length === 1) {
                    test_cases.push({
                        input: codeBlocks[0].textContent.trim(),
                        expected_output: ''
                    });
                }
            });

            // Get solution
            const solutionBox = contentArea.querySelector('#solution-section .solution-box');
            let solution = '';
            if (solutionBox) {
                const solutionContent = solutionBox.querySelector('.code-block, .answer-display');
                if (solutionContent) {
                    solution = solutionContent.textContent.trim();
                }
            }

            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="btn-icon">⏳</span> Saving...';

            const editData = {
                question_id: currentQuestionId,
                description: description,
                problem_statement: problemStatement,
                constraints: constraints,
                hint: hint,
                test_cases: test_cases.length > 0 ? test_cases : null,
                solution: solution,
                _token: '{{ csrf_token() }}'
            };

            // Send edit data to backend
            fetch('/reviewer/review/edit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(editData)
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server error: ${response.status} - ${text.substring(0, 100)}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('✓ Question edited successfully!');
                    
                    // Reload page to refresh with saved content
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Failed to save edits');
                }
            })
            .catch(error => {
                console.error('Error saving edits:', error);
                alert('Error saving edits: ' + error.message);
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<span class="btn-icon">💾</span> Save';
            });
        }

        function cancelEditMode() {
            // Reload the page to discard changes
            window.location.reload();
        }
    </script>
</body>
</html>
