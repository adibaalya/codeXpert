<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="{{ asset('js/navBar.js') }}"></script>
    <script src="{{ asset('js/reviewer/history.js') }}"></script>
    @include('layouts.reviewer.historyCSS')
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
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.review') }}'">Review</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.generate') }}'">Generate</button>
                <button class="nav-item active-reviewer">History</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ $reviewer->username ?? 'Dr. Sarah Wilson' }}</div>
                    <div class="user-role">Reviewer</div>
                </div>
                <div class="user-avatar-reviewer" onclick="toggleUserMenu(event)">
                    @if($reviewer->profile_photo)
                        <img src="{{ asset('storage/' . $reviewer->profile_photo) }}" alt="{{ $reviewer->username }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr($reviewer->username ?? 'DS', 0, 1)) }}{{ strtoupper(substr($reviewer->username ?? 'DS', 1, 1) ?? '') }}
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
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Language</label>
                    <select class="filter-select" id="topicFilter">
                        <option value="">All Language</option>
                        <option value="Python">Python</option>
                        <option value="Java">Java</option>
                        <option value="JavaScript">JavaScript</option>
                        <option value="C">C</option>
                        <option value="C++">C++</option>
                        <option value="PHP">PHP</option>
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
                                <span class="badge {{ strtolower(str_replace(' ', '-', $question->language ?? 'Java')) }}">
                                    {{ $question->language ?? 'Java' }}
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
                <h2 class="modal-title-main" id="modalTitle">Question Title</h2>
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
        window.historyConfig = {
            questions: {!! json_encode($questions->map(function($q) {
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
            })->keyBy('id')) !!}
        };
    </script>
</body>
</html>
