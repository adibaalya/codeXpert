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
    @include('layouts.app')

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
            if (!window.confirm(`Remove ${language} from your learning path?`)) { 
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
                        'X-CSRF-TOKEN': csrfToken 
                    },
                    body: JSON.stringify({
                        language: language
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    console.error(data.message || 'Error removing language');
                }
            } catch (error) {
                console.error('Error:', error);
                console.error('An error occurred. Please try again.');
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
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
            </div>
            <span class="logo-text">CodeXpert</span>
        </div>
    </div>

    <div class="p-4 md:p-8 max-w-4xl mx-auto">
        <h1 class="title-text">Set Up Your Learning Path</h1>
        <p class="subtitle">Choose languages and set your skill level for each</p>

        @if($addedLanguages->count() === 0)
            <!-- Show Add Button First when empty -->
            <button class="add-language-btn flex items-center w-full justify-center p-4 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 transition mb-6" onclick="openModal()">
                <div class="add-icon text-indigo-600 bg-white border border-indigo-600 w-8 h-8 flex items-center justify-center rounded-full font-bold text-2xl mr-4">+</div>
                <div class="language-btn-text">
                    <div class="text-lng">Add Another Language</div>
                    <div class="text-sm text-gray-500">{{ count($availableLanguages) }} languages available</div>
                </div>
            </button>

            <!-- Empty State Below -->
            <div class="languages-container bg-white shadow-lg rounded-xl p-6 mb-8">
                <div class="empty-state text-center p-10 border-2 border-dashed border-gray-300 rounded-lg text-gray-500">
                    <div class="empty-icon text-4xl mb-3 font-mono">&lt;/&gt;</div>
                    <p>No languages added yet. Click above to add your first language!</p>
                </div>
            </div>
        @else
            <!-- Show Languages First when they exist -->
            <div class="languages-container bg-white shadow-lg rounded-xl p-6 mb-8">
                <div class="section-title flex items-center mb-6 text-xl font-semibold text-indigo-700">
                    <span>Your Languages</span>
                </div>

                <div class="grid gap-4">
                @foreach($addedLanguages as $proficiency)
                    <div class="language-card flex items-center justify-between p-4 border rounded-lg hover:shadow-md transition duration-200" data-language="{{ $proficiency->language }}">
                        <div class="flex items-center">
                            <div class="language-icon {{ $availableLanguages[$proficiency->language] ?? 'bg-blue-100 text-blue-700' }} w-10 h-10 flex items-center justify-center rounded-full text-lg font-mono mr-4">
                                &lt;/&gt;
                            </div>
                            <div class="language-info">
                                <div class="language-name font-semibold text-gray-800">{{ $proficiency->language }}</div>
                                <span class="language-level">{{ $proficiency->level }}</span>
                            </div>

                        </div>
                        <button class="remove-btn text-gray-400 hover:text-red-600 transition" onclick="removeLanguage('{{ $proficiency->language }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                @endforeach
                </div>
            </div>

            <!-- Add Button Below when languages exist -->
            <button class="add-language-btn flex items-center w-full justify-center p-4 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 transition mb-6" onclick="openModal()">
                <div class="add-icon text-indigo-600 bg-white border border-indigo-600 w-8 h-8 flex items-center justify-center rounded-full font-bold text-2xl mr-4">+</div>
                <div>
                    <div class="text-lng">Add Another Language</div>
                    <div class="text-sm text-gray-500">{{ count($availableLanguages) }} languages available</div>
                </div>
            </button>

            <!-- Show Start Learning button only when languages exist -->
            <form action="{{ route('learner.customization.complete') }}" method="POST" class="mt-8">
                @csrf
                <button type="submit" class="start-btn w-full flex items-center justify-center py-3 px-4 bg-green-500 text-white font-bold text-lg rounded-xl hover:bg-green-600 transition">
                    <span>Start Learning</span>
                </button>
            </form>
        @endif
    </div>

    <button class="help-btn fixed bottom-4 right-4 bg-indigo-500 text-white w-12 h-12 rounded-full shadow-lg text-xl hover:bg-indigo-600 transition">?</button>

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
</body>
</html>

