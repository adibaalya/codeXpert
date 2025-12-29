<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Exercise - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.practiceCSS')
    @include('layouts.navCSS')
</head>
<body>
    <!-- Header - Same as Dashboard -->
    <div class="header">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert.png') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item" onclick="window.location.href='{{ route('learner.dashboard') }}'">Dashboard</button>
                <button class="nav-item active" onclick="window.location.href='{{ route('learner.practice') }}'">Practice</button>
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

    <!-- Main Practice Content -->
    <div class="practice-container">
        <!-- Header -->
        <div class="practice-header">
            <h1 class="practice-title">Choose Your Exercise</h1>
            <p class="practice-subtitle">Select a programming language and difficulty level to begin</p>
        </div>

        <!-- Programming Language Selection -->
        <div class="selection-card">
            <div class="card-header">
                <div class="card-header-text">
                    <h3>Programming Language</h3>
                    <p>Choose your preferred language</p>
                </div>
            </div>
            <div class="language-grid">
                @forelse($languages as $language)
                    @php
                        $languageIcons = [
                            'Python' => ['icon' => '</>', 'bg' => '#4C6EF5', 'light' => '#E3F2FD'],
                            'JavaScript' => ['icon' => '</>', 'bg' => '#A855F7', 'light' => '#FFF9C4'],
                            'Java' => ['icon' => '</>', 'bg' => '#F97316', 'light' => '#FFEBEE'],
                            'C++' => ['icon' => '</>', 'bg' => '#EC4899', 'light' => '#E1F5FE'],
                            'C#' => ['icon' => '</>', 'bg' => '#8B5CF6', 'light' => '#ECEFF1'],
                            'Ruby' => ['icon' => '</>', 'bg' => '#CC342D', 'light' => '#FFEBEE'],
                            'PHP' => ['icon' => '</>', 'bg' => '#6366F1', 'light' => '#EDE7F6'],
                            'C' => ['icon' => '</>', 'bg' => '#EF4444', 'light' => '#E0F7FA'],
                        ];
                        
                        $iconInfo = $languageIcons[$language] ?? ['icon' => '</>', 'bg' => '#4C6EF5', 'light' => '#E3F2FD'];
                    @endphp
                    
                    <div class="language-option" data-language="{{ $language }}" onclick="selectLanguage(this)">
                        <div class="language-icon" style="background-color: {{ $iconInfo['bg'] }}; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 20px;">
                            {{ $iconInfo['icon'] }}
                        </div>
                        <div class="language-name">{{ $language }}</div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: #64748B;">
                        No languages available yet. Please check back later.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Difficulty Level Selection -->
        <div class="selection-card">
            <div class="card-header">
                <div class="card-header-text">
                    <h3>Difficulty Level</h3>
                    <p>Select your challenge level</p>
                </div>
            </div>
            <div class="difficulty-grid">
                <div class="difficulty-option" data-difficulty="Beginner" onclick="selectDifficulty(this)">
                    <div class="difficulty-header">
                        <div>
                            <div class="difficulty-title">Beginner</div>
                            <div class="difficulty-subtitle">Build your foundation</div>
                        </div>
                    </div>
                </div>
                
                <div class="difficulty-option" data-difficulty="Intermediate" onclick="selectDifficulty(this)">
                    <div class="difficulty-header">
                        <div>
                            <div class="difficulty-title">Intermediate</div>
                            <div class="difficulty-subtitle">Expand your knowledge</div>
                        </div>
                    </div>
                </div>
                
                <div class="difficulty-option" data-difficulty="Advanced" onclick="selectDifficulty(this)">
                    <div class="difficulty-header">
                        <div>
                            <div class="difficulty-title">Advanced</div>
                            <div class="difficulty-subtitle">Master advanced concepts</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Topics/Skills Selection (Dynamic based on language and level) -->
        <div class="selection-card" id="topicsCard" style="display: none;">
            <div class="card-header">
                <div class="card-header-text">
                    <h3>Topics</h3>
                    <p>Choose a specific topic to practice</p>
                </div>
            </div>
            <div class="skills-grid" id="topicsGrid">
                <!-- Topics will be loaded dynamically -->
            </div>
            <div id="topicsLoading" style="display: none; text-align: center; padding: 20px; color: #64748B;">
                <div style="display: inline-block; width: 20px; height: 20px; border: 3px solid #E2E8F0; border-top-color: #FF6B35; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 10px;">Loading topics...</p>
            </div>
            <div id="topicsEmpty" style="display: none; text-align: center; padding: 20px; color: #64748B;">
                No topics available for this combination. Please try another difficulty level.
            </div>
        </div>

        <!-- Action Section -->
        <div class="action-section">
            <div class="ready-section">
                <div>
                    <p class="ready-text">Ready to start:</p>
                    <div class="ready-tags" id="selectedTags">
                        <span class="ready-tag">Language: <strong id="selectedLanguage">-</strong></span>
                        <span class="ready-tag">Level: <strong id="selectedDifficulty">-</strong></span>
                        <span class="ready-tag">Skill: <strong id="selectedSkill">-</strong></span>
                    </div>
                </div>
            </div>
            <button class="start-button" id="startButton" onclick="startPractice()" disabled>
                <span>Start Practice</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        let selectedLanguage = null;
        let selectedDifficulty = null;
        let selectedSkill = null;

        function selectLanguage(element) {
            // Remove previous selection
            document.querySelectorAll('.language-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selection to clicked element
            element.classList.add('selected');
            selectedLanguage = element.getAttribute('data-language');
            document.getElementById('selectedLanguage').textContent = selectedLanguage;
            
            // Load topics if both language and difficulty are selected
            if (selectedLanguage && selectedDifficulty) {
                loadTopics();
            }
            
            checkAllSelected();
        }

        function selectDifficulty(element) {
            // Remove previous selection
            document.querySelectorAll('.difficulty-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selection to clicked element
            element.classList.add('selected');
            selectedDifficulty = element.getAttribute('data-difficulty');
            document.getElementById('selectedDifficulty').textContent = selectedDifficulty;
            
            // Load topics if both language and difficulty are selected
            if (selectedLanguage && selectedDifficulty) {
                loadTopics();
            }
            
            checkAllSelected();
        }

        function selectSkill(element) {
            // Remove previous selection
            document.querySelectorAll('.skill-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selection to clicked element
            element.classList.add('selected');
            selectedSkill = element.getAttribute('data-skill');
            document.getElementById('selectedSkill').textContent = selectedSkill;
            
            checkAllSelected();
        }

        async function loadTopics() {
            const topicsCard = document.getElementById('topicsCard');
            const topicsGrid = document.getElementById('topicsGrid');
            const topicsLoading = document.getElementById('topicsLoading');
            const topicsEmpty = document.getElementById('topicsEmpty');
            
            // Show topics card
            topicsCard.style.display = 'block';
            
            // Show loading state
            topicsGrid.innerHTML = '';
            topicsLoading.style.display = 'block';
            topicsEmpty.style.display = 'none';
            
            // Reset selected skill
            selectedSkill = null;
            document.getElementById('selectedSkill').textContent = '-';
            
            try {
                // Fetch topics from server
                const response = await fetch(`/learner/practice/topics?language=${encodeURIComponent(selectedLanguage)}&level=${encodeURIComponent(selectedDifficulty)}`);
                const data = await response.json();
                
                // Hide loading
                topicsLoading.style.display = 'none';
                
                if (data.topics && data.topics.length > 0) {
                    // Display topics
                    topicsGrid.innerHTML = data.topics.map(topic => 
                        `<div class="skill-option" data-skill="${topic}" onclick="selectSkill(this)">${topic}</div>`
                    ).join('');
                    topicsEmpty.style.display = 'none';
                } else {
                    // No topics available
                    topicsEmpty.style.display = 'block';
                }
            } catch (error) {
                console.error('Error loading topics:', error);
                topicsLoading.style.display = 'none';
                topicsEmpty.style.display = 'block';
            }
            
            checkAllSelected();
        }

        function checkAllSelected() {
            const startButton = document.getElementById('startButton');
            // Only require language and difficulty, topic is optional
            if (selectedLanguage && selectedDifficulty) {
                startButton.disabled = false;
            } else {
                startButton.disabled = true;
            }
        }

        function startPractice() {
            if (selectedLanguage && selectedDifficulty) {
                // Build URL with optional topic parameter
                let url = `/learner/coding/random?language=${encodeURIComponent(selectedLanguage)}&level=${encodeURIComponent(selectedDifficulty)}`;
                
                // Add topic if selected
                if (selectedSkill) {
                    url += `&topic=${encodeURIComponent(selectedSkill)}`;
                }
                
                // Redirect to random coding question
                window.location.href = url;
            }
        }

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
    </script>
</body>
</html>
