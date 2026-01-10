<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodeXpert - Customization Path</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="{{ asset('js/navBar.js') }}"></script>
    @include('layouts.learner.customizationPathCSS')
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
                <span>Your Languages</span>
            </div>

            <!-- Languages Grid -->
            <div class="languages-grid">
                @foreach($addedLanguages as $proficiency)
                    <div class="language-card" data-language="{{ $proficiency->language }}">
                        <div class="language-icon" style="background: linear-gradient(135deg, {{ $availableLanguages[$proficiency->language] ?? '#6B7280' }}, {{ $availableLanguages[$proficiency->language] ?? '#6B7280' }});">
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
                            <div class="language-icon" style="background: linear-gradient(135deg, {{ $color }}, {{ $color }});" class="w-12 h-12 flex items-center justify-center rounded-full text-2xl font-mono mx-auto mb-2">
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
                <button 
                    class="btn btn-primary px-4 py-2 text-white rounded-lg transition disabled:opacity-50" 
                    id="addLanguageBtn" 
                    onclick="addLanguage()" 
                    disabled
                    style="background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%); border: none;">
                    Add Language
                </button>
            </div>
        </div>
    </div>
    <script>
        window.customizationConfig = {
            routes: {
                store: "{{ route('learner.customization.store') }}",
                destroy: "{{ route('learner.customization.destroy') }}"
            }
        };
    </script>

    <script src="{{ asset('js/learner/customization.js') }}"></script>
</body>
</html>

