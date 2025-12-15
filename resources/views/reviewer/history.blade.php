<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.historyCSS')
    @include('layouts.navCSS')
</head>
<body>
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
                <button class="nav-item active">History</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ $reviewer->username ?? 'Dr. Sarah Wilson' }}</div>
                    <div class="user-role">Reviewer</div>
                </div>
                <div class="user-avatar" onclick="toggleUserMenu(event)">
                    {{ strtoupper(substr($reviewer->username ?? 'DS', 0, 1)) }}{{ strtoupper(substr($reviewer->username ?? 'DS', 1, 1) ?? '') }}
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header">
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

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Approved Questions</h1>
            <p class="page-subtitle">Browse and review previously approved questions</p>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-header">
                <h2 class="filters-title">Filters</h2>
                <span class="showing-count">Showing <strong>{{ $questions->count() }}</strong> of <strong>{{ $totalCount ?? $questions->count() }}</strong> questions</span>
            </div>
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">🔍 Search questions...</label>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search by title or description...">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Difficulty</label>
                    <select class="filter-select" id="difficultyFilter">
                        <option value="">All Difficulties</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Topic</label>
                    <select class="filter-select" id="topicFilter">
                        <option value="">All Topics</option>
                        <option value="algorithms">Algorithms</option>
                        <option value="data-structures">Data Structures</option>
                        <option value="basics">Basics</option>
                        <option value="trees">Trees</option>
                        <option value="graphs">Graphs</option>
                        <option value="dynamic-programming">Dynamic Programming</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div class="questions-container" id="questionsContainer">
            @forelse($questions as $question)
                <div class="question-card" onclick="openModal({{ $question->id }})">
                    <div class="question-header">
                        <div class="question-left">
                            <div class="question-badges">
                                <span class="badge {{ strtolower($question->difficulty ?? 'intermediate') }}">
                                    {{ ucfirst($question->difficulty ?? 'Intermediate') }}
                                </span>
                                <span class="badge {{ strtolower(str_replace(' ', '-', $question->topic ?? 'algorithms')) }}">
                                    {{ $question->topic ?? 'Algorithms' }}
                                </span>
                            </div>
                            <h3 class="question-title">{{ $question->title ?? 'Untitled' }}</h3>
                            <p class="question-description">
                                {{ Str::limit($question->description, 150) }}
                            </p>
                            <div class="question-meta">
                                <span class="meta-item">
                                    📅 {{ $question->approved_at ? \Carbon\Carbon::parse($question->approved_at)->format('M d, Y') : 'Oct 23, 2025' }}
                                </span>
                                <span class="meta-item">
                                    👤 Approved by {{ $question->approver->username ?? 'Dibooo' }}
                                </span>
                            </div>
                        </div>
                        <div class="question-right">
                            <span class="status-badge approved">✓ Approved</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <h3 class="empty-title">No Questions Found</h3>
                    <p class="empty-description">There are no approved questions yet. Start reviewing to see them here!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModalOnOverlay(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 class="modal-title-main">Description</h2>
                <button class="close-btn" onclick="closeModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="problem-content-box">
                    <div id="modalContentFormatted"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store questions data for modal
        const questionsData = {!! json_encode($questions->map(function($q) {
            return [
                'id' => $q->id,
                'title' => $q->title,
                'description' => $q->description,
                'problem_statement' => $q->problem_statement,
                'constraints' => $q->constraints,
                'input_format' => $q->input_format,
                'output_format' => $q->output_format,
                'hint' => $q->hint,
                'difficulty' => $q->difficulty ?? $q->level ?? 'Intermediate',
                'language' => $q->language ?? 'Python',
                'chapter' => $q->chapter,
            ];
        })->keyBy('id')) !!};

        // Modal functions
        function openModal(questionId) {
            const question = questionsData[questionId];
            if (!question) return;

            // Format and display the content
            const contentDiv = document.getElementById('modalContentFormatted');
            contentDiv.innerHTML = formatQuestionContent(question);

            document.getElementById('modalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function formatQuestionContent(question) {
            let html = '';

            // Show Description
            if (question.description) {
                html += `
                    <div class="content-section-modal">
                        <h3 class="section-heading">Description</h3>
                        <div class="section-text">${formatSectionContent(question.description)}</div>
                    </div>
                `;
            }

            // Show Problem Statement
            if (question.problem_statement) {
                html += `
                    <div class="content-section-modal">
                        <h3 class="section-heading">Problem Statement</h3>
                        <div class="section-text">${formatSectionContent(question.problem_statement)}</div>
                    </div>
                `;
            }

            return html;
        }

        function parseContentSections(content) {
            // ...existing code...
        }

        function formatSectionContent(text) {
            if (!text) return '';
            
            // Escape HTML first
            let formatted = escapeHtml(text);
            
            // Format bullet points (lines starting with - or •)
            formatted = formatted.replace(/^[-•]\s+(.+)$/gm, '<div class="bullet-item">• $1</div>');
            
            // Format numbered lists
            formatted = formatted.replace(/^(\d+)\.\s+(.+)$/gm, '<div class="bullet-item">$1. $2</div>');
            
            // Convert remaining line breaks to <br> (but not within bullet items)
            formatted = formatted.split('\n').map(line => {
                if (line.includes('<div class="bullet-item">')) {
                    return line;
                }
                return line ? line + '<br>' : '<br>';
            }).join('');
            
            return formatted;
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function closeModalOnOverlay(event) {
            if (event.target === document.getElementById('modalOverlay')) {
                closeModal();
            }
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Filter functionality
        const searchInput = document.getElementById('searchInput');
        const difficultyFilter = document.getElementById('difficultyFilter');
        const topicFilter = document.getElementById('topicFilter');

        function filterQuestions() {
            const searchTerm = searchInput.value.toLowerCase();
            const difficulty = difficultyFilter.value.toLowerCase();
            const topic = topicFilter.value.toLowerCase();

            const cards = document.querySelectorAll('.question-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const title = card.querySelector('.question-title').textContent.toLowerCase();
                const description = card.querySelector('.question-description').textContent.toLowerCase();
                const cardDifficulty = card.querySelector('.badge:first-child').textContent.toLowerCase();
                const cardTopic = card.querySelector('.badge:nth-child(2)').textContent.toLowerCase();

                const matchesSearch = !searchTerm || title.includes(searchTerm) || description.includes(searchTerm);
                const matchesDifficulty = !difficulty || cardDifficulty.includes(difficulty);
                const matchesTopic = !topic || cardTopic.includes(topic);

                if (matchesSearch && matchesDifficulty && matchesTopic) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Update showing count
            const showingCount = document.querySelector('.showing-count');
            const total = cards.length;
            showingCount.innerHTML = `Showing <strong>${visibleCount}</strong> of <strong>${total}</strong> questions`;
        }

        searchInput.addEventListener('input', filterQuestions);
        difficultyFilter.addEventListener('change', filterQuestions);
        topicFilter.addEventListener('change', filterQuestions);

        // Toggle user dropdown menu
        function toggleUserMenu(event) {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
            event.stopPropagation();
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>
