<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CodeXpert - Customization Path</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @include('layouts.customizationPathCSS')
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
                <button class="nav-item active" onclick="window.location.href='{{ route('learner.dashboard') }}'">Dashboard</button>
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

    <div class="customization-container">
        <h1 class="title-text">Set Up Your Learning Path</h1>
        <p class="subtitle">Choose languages and set your skill level for each</p>

        @if($addedLanguages->count() === 0)
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">&lt;/&gt;</div>
                <p>No languages added yet. Click below to add your first language!</p>
            </div>

            <!-- Add Button -->
            <div class="add-language-section">
                <button class="add-language-btn" onclick="openModal()">
                    <div class="add-icon">+</div>
                    <div class="language-btn-text">
                        <div class="text-lng">Add Another Language</div>
                        <div class="text-sm">{{ count($availableLanguages) }} languages available</div>
                    </div>
                </button>
            </div>
        @else
            <!-- Section Title with Icon -->
            <div class="section-title">
                <svg class="section-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Your Languages</span>
            </div>

            <!-- Languages Grid -->
            <div class="languages-grid">
                @foreach($addedLanguages as $proficiency)
                    <div class="language-card" data-language="{{ $proficiency->language }}">
                        <div class="language-icon {{ $availableLanguages[$proficiency->language] ?? 'blue' }}">
                            &lt;/&gt;
                        </div>
                        <div class="language-info">
                            <div class="language-name">{{ $proficiency->language }}</div>
                            <span class="language-level">{{ $proficiency->level }}</span>
                        </div>
                        <button class="remove-btn" onclick="removeLanguage('{{ $proficiency->language }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>

            <!-- Add Button -->
            <div class="add-language-section">
                <button class="add-language-btn" onclick="openModal()">
                    <div class="add-icon">+</div>
                    <div class="language-btn-text">
                        <div class="text-lng">Add Another Language</div>
                        <div class="text-sm">{{ count($availableLanguages) }} languages available</div>
                    </div>
                </button>
            </div>

            <!-- Start Learning Button -->
            <div class="start-learning-section">
                <form action="{{ route('learner.customization.complete') }}" method="POST">
                    @csrf
                    <button type="submit" class="start-btn">
                        <span>Start Learning</span>
                    </button>
                </form>
            </div>
        @endif
    </div>

    <button class="help-btn">?</button>

    <!-- Modal -->
    <div class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center transition-opacity duration-300 opacity-0 pointer-events-none z-50" id="languageModal">
        <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 transform transition-transform duration-300 scale-95">
            <div class="modal-header flex justify-between items-center border-b pb-3 mb-4">
                <div class="modal-title flex items-center text-xl font-semibold text-gray-800">
                    <span class="add-icon text-indigo-600 bg-indigo-50 w-10 h-10 flex items-center justify-center rounded-full font-bold text-2xl mr-3">+</span>
                    <span>Select Language & Level</span>
                </div>
                <button class="close-btn text-gray-400 text-2xl hover:text-gray-600 transition" onclick="closeModal()">×</button>
            </div>

            <div class="mb-6">
                <h3>Choose a language:</h3>
                <div class="language-grid grid grid-cols-3 gap-4">
                    @foreach($availableLanguages as $language => $color)
                        <div class="language-option p-4 border rounded-lg text-center cursor-pointer hover:border-indigo-500 transition duration-200" data-language="{{ $language }}" onclick="selectLanguage('{{ $language }}')">
                            <div class="language-icon {{ $color }} w-12 h-12 flex items-center justify-center rounded-full text-2xl font-mono mx-auto mb-2">
                                &lt;/&gt;
                            </div>
                            <div class="text-sm font-medium">{{ $language }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="levelSelection" class="mb-6" style="display: none;">
                <h3 class="text-lg font-medium mb-3">Select your skill level for <span id="selectedLanguageName" class="font-bold text-indigo-600"></span>:</h3>
                <div class="level-grid grid grid-cols-3 gap-4">
                    <div class="level-option beginner p-4 border rounded-lg text-center cursor-pointer hover:border-indigo-500 transition duration-200" onclick="selectLevel('Beginner', event)">
                        <div class="level-name font-medium">Beginner</div>
                    </div>
                    <div class="level-option intermediate p-4 border rounded-lg text-center cursor-pointer hover:border-indigo-500 transition duration-200" onclick="selectLevel('Intermediate', event)">
                        <div class="level-name font-medium">Intermediate</div>
                    </div>
                    <div class="level-option advanced p-4 border rounded-lg text-center cursor-pointer hover:border-indigo-500 transition duration-200" onclick="selectLevel('Advanced', event)">
                        <div class="level-name font-medium">Advanced</div>
                    </div>
                </div>
            </div>

            <div class="modal-actions flex justify-end space-x-3 pt-4 border-t">
                <button class="btn btn-cancel px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition" onclick="closeModal()">Cancel</button>
                <button class="btn btn-primary px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50" id="addLanguageBtn" onclick="addLanguage()" disabled>
                    Add Language
                </button>
            </div>
        </div>
    </div>
    <script>
        @verbatim
        let selectedLanguage = null;
        let selectedLevel = null;

        // Helper to toggle modal classes
        function toggleModal(show) {
            const modal = document.getElementById('languageModal');
            if (show) {
                modal.classList.add('active');
                modal.classList.remove('opacity-0', 'pointer-events-none');
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.remove('scale-95');
                }
            } else {
                modal.classList.remove('active');
                modal.classList.add('opacity-0', 'pointer-events-none');
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.add('scale-95');
                }
            }
        }

        function openModal() {
            toggleModal(true);
            selectedLanguage = null;
            selectedLevel = null;
            document.getElementById('levelSelection').style.display = 'none';
            document.getElementById('addLanguageBtn').disabled = true;
            
            // Remove all selections
            document.querySelectorAll('.language-option').forEach(el => el.classList.remove('selected', 'border-indigo-500', 'ring-2', 'ring-indigo-500'));
            document.querySelectorAll('.level-option').forEach(el => el.classList.remove('selected', 'border-indigo-500', 'ring-2', 'ring-indigo-500'));
            
            // Get all added languages
            const addedLanguages = Array.from(document.querySelectorAll('.language-card')).map(card => card.dataset.language);
            
            // Hide already selected languages from modal
            document.querySelectorAll('.language-option').forEach(option => {
                const language = option.dataset.language;
                if (addedLanguages.includes(language)) {
                    option.style.display = 'none';
                } else {
                    option.style.display = 'block';
                }
            });
        }

        function closeModal() {
            toggleModal(false);
        }

        function selectLanguage(language) {
            // Check if already added
            const existingCard = document.querySelector(`.language-card[data-language="${language}"]`);
            if (existingCard) {
                console.error('This language has already been added!'); 
                return;
            }

            selectedLanguage = language;
            document.getElementById('selectedLanguageName').textContent = language;
            document.getElementById('levelSelection').style.display = 'block';
            
            // Update UI
            document.querySelectorAll('.language-option').forEach(el => el.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-500'));
            const selectedEl = document.querySelector(`.language-option[data-language="${language}"]`);
            if (selectedEl) {
                selectedEl.classList.add('border-indigo-500', 'ring-2', 'ring-indigo-500');
            }

            // Reset level selection visually
            document.querySelectorAll('.level-option').forEach(el => el.classList.remove('selected', 'border-indigo-500', 'ring-2', 'ring-indigo-500'));
            selectedLevel = null;
            document.getElementById('addLanguageBtn').disabled = true;
        }

        function selectLevel(level, event) {
            selectedLevel = level;
            document.getElementById('addLanguageBtn').disabled = false;
            
            // Update UI
            document.querySelectorAll('.level-option').forEach(el => el.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-500'));
            event.currentTarget.classList.add('border-indigo-500', 'ring-2', 'ring-indigo-500');
        }
        @endverbatim

        async function addLanguage() {
            @verbatim
            if (!selectedLanguage || !selectedLevel) {
                console.error('Please select both a language and a level');
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            @endverbatim

                const response = await fetch('{{ route("learner.customization.store") }}', {
            @verbatim
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken 
                    },
                    body: JSON.stringify({
                        language: selectedLanguage,
                        level: selectedLevel
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    console.error(data.message || 'Error adding language');
                }
            } catch (error) {
                console.error('Error:', error);
                console.error('An error occurred. Please try again.');
            }
            @endverbatim
        }

        async function removeLanguage(language) {
            @verbatim
            if (!confirm(`Remove ${language} from your learning path?`)) { 
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            @endverbatim

                const response = await fetch('{{ route("learner.customization.destroy") }}', {
            @verbatim
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        language: language
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Successfully deleted, reload the page
                    window.location.reload();
                } else {
                    alert(data.message || 'Error removing language');
                    console.error('Error response:', data);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
            @endverbatim
        }

        @verbatim
        // Initialize event listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Close modal when clicking outside
            const languageModal = document.getElementById('languageModal');
            if (languageModal) {
                languageModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });
            }

            // Handle ESC key to close modal
            document.addEventListener('keydown', function(e) {
                const modal = document.getElementById('languageModal');
                if (e.key === "Escape" && modal && modal.classList.contains('active')) {
                    closeModal();
                }
            });
        });
        @endverbatim
    </script>
</body>
</html>

