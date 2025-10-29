@php
// --- PHP Data Definitions (From your original JS) ---
$languages = [
    ['name' => 'Python', 'gradient' => 'from-[#3776ab] to-[#4584b6]', 'darkBg' => 'bg-[#2d5a7b]'],
    ['name' => 'Java', 'gradient' => 'from-[#f89820] to-[#ea7c1f]', 'darkBg' => 'bg-[#c67a1a]'],
    ['name' => 'C++', 'gradient' => 'from-[#00599c] to-[#004d84]', 'darkBg' => 'bg-[#004570]'],
    ['name' => 'JavaScript', 'gradient' => 'from-[#f7df1e] to-[#e8d21a]', 'darkBg' => 'bg-[#c4ad18]'],
    ['name' => 'TypeScript', 'gradient' => 'from-[#3178c6] to-[#2a68b0]', 'darkBg' => 'bg-[#25579a]'],
    ['name' => 'Go', 'gradient' => 'from-[#00add8] to-[#00a4cc]', 'darkBg' => 'bg-[#0089ae]'],
    ['name' => 'Rust', 'gradient' => 'from-[#ce422b] to-[#b83822]', 'darkBg' => 'bg-[#9e2f1e]'],
    ['name' => 'Ruby', 'gradient' => 'from-[#cc342d] to-[#b82a24]', 'darkBg' => 'bg-[#9b231e]'],
];

$levels = [
    ['level' => 'Beginner', 'color' => 'from-green-600 to-green-500', 'icon' => 'BookOpen'],
    ['level' => 'Intermediate', 'color' => 'from-blue-600 to-blue-500', 'icon' => 'Zap'],
    ['level' => 'Advanced', 'color' => 'from-purple-600 to-purple-500', 'icon' => 'Rocket'],
];
@endphp

<div 
    class="bg-black min-h-screen"
    x-data="{
        selections: @js($initialSelections ?? []), // [{ language: 'Python', level: 'Beginner' }]
        isAddingLanguage: false,
        selectedLanguage: null,
        selectedLevel: null,
        languages: @js($languages),
        levels: @js($levels),

        get availableLanguages() {
            return this.languages.filter(lang => 
                !this.selections.some(sel => sel.language === lang.name)
            );
        },
        startAddingLanguage() {
            if (this.availableLanguages.length === 0) {
                // You would use a JavaScript toast library here (like Sonner/SweetAlert2)
                console.error('All languages added!');
                return;
            }
            this.selectedLanguage = this.availableLanguages[0].name; // UX Improvement: Pre-select
            this.isAddingLanguage = true;
        },
        handleAddLanguage() {
            if (this.selectedLanguage && this.selectedLevel) {
                this.selections.push({ language: this.selectedLanguage, level: this.selectedLevel });
                this.selectedLanguage = null;
                this.selectedLevel = null;
                this.isAddingLanguage = false;
                // Success Toast/Notification here
                console.log('Language added!');
            }
        },
        handleRemoveLanguage(language) {
            this.selections = this.selections.filter(sel => sel.language !== language);
            // Info Toast/Notification here
            console.log(language + ' removed');
        },
        handleComplete() {
            if (this.selections.length === 0) {
                console.error('Please add at least one language');
                return;
            }
            // 1. Prepare data and token
            const data = { 
                selections: this.selections, 
                _token: '{{ csrf_token() }}' // Laravel CSRF Token
            };
            
            // 2. Submit data via fetch/Axios
            fetch('{{ route('personalization.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => {
                if (!response.ok) {
                    // Read the JSON response to get the error message
                    return response.json().then(error => {
                        throw new Error(error.message || 'API request failed with status: ' + response.status);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Success:', data);
                // Success Toast
                // 3. Redirect to dashboard on success
                window.location.href = '{{ url('/dashboard') }}';
            })
            .catch(error => {
                console.error('Error:', error);
                // Error Toast/Notification
            });
        }
    }"
>
    {{-- Navigation Bar (Unchanged) --}}
    <div class="h-[83px] w-full bg-gray-900 border-b border-[rgba(131,131,131,0.5)]">
        <div class="max-w-[1280px] mx-auto h-full flex items-center px-8">
            <div class="flex items-center gap-4">
                {{-- Logo Image here --}}
                <h1 class="bg-clip-text bg-gradient-to-r from-[#ff6f61] to-[#ffce7b] text-[25px]" style="WebkitTextFillColor: transparent;">
                    CodeXpert
                </h1>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-[1100px] mx-auto px-8 py-16">
        {{-- Header (Unchanged) --}}
        <div class="text-center mb-12">
            {{-- Logo Image here --}}
            <h2 class="bg-clip-text bg-gradient-to-r from-[#ff6f61] to-[#ffce7b] text-5xl mb-4" style="WebkitTextFillColor: transparent;">
                Set Up Your Learning Path
            </h2>
            <p class="text-gray-400 text-xl">
                Choose languages and set your skill level for each
            </p>
        </div>

        {{-- Selected Languages --}}
        <div x-show="selections.length > 0">
            <h3 class="text-white text-2xl mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-[#ff8a6b]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5l-2.6 1.5M7 5l2.6 1.5M19 12h3M2 12h3M17 19l-2.6-1.5M7 19l2.6-1.5"></path></svg>
                Your Languages
            </h3>
            <div class="grid grid-cols-2 gap-5 mb-8">
                <template x-for="selection in selections" :key="selection.language">
                    <div class="relative bg-gradient-to-br from-gray-900 to-gray-800 rounded-xl p-6 border-2 border-gray-700 shadow-lg hover:border-gray-600 transition-all group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                {{-- Language Icon --}}
                                <div :class="`w-16 h-16 rounded-lg bg-gradient-to-br ${languages.find(l => l.name === selection.language)?.gradient} flex items-center justify-center shadow-lg`">
                                    <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                </div>
                                
                                {{-- Language & Level Info --}}
                                <div x-data="{ levelInfo: levels.find(l => l.level === selection.level) }">
                                    <p class="text-white text-2xl mb-1" x-text="selection.language"></p>
                                    <div :class="`inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-gradient-to-r ${levelInfo.color}`">
                                        {{-- Level Icon (simplified) --}}
                                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path></svg>
                                        <span class="text-white text-sm" x-text="selection.level"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Remove Button --}}
                            <button
                                @click="handleRemoveLanguage(selection.language)"
                                class="w-8 h-8 rounded-full bg-red-900/30 border border-red-800 flex items-center justify-center hover:bg-red-900/50 transition-colors opacity-0 group-hover:opacity-100"
                            >
                                <svg class="w-4 h-4 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>


        {{-- Add Language Section --}}
        <div x-cloak x-show="!isAddingLanguage" class="flex justify-center mb-8">
            <button
                @click="startAddingLanguage"
                :disabled="availableLanguages.length === 0"
                :class="`bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-dashed rounded-xl p-8 transition-all duration-300 ${
                    availableLanguages.length === 0  ? 'border-gray-800 opacity-50 cursor-not-allowed' : 'border-gray-700 hover:border-[#ff6f61] hover:shadow-[0px_0px_25px_5px_rgba(255,111,97,0.2)] hover:scale-102'
                }`"
            >
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-r from-[#ff6f61] to-[#ffce7b] flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </div>
                    <div class="text-left">
                        <p class="text-white text-xl mb-1">Add Another Language</p>
                        <p class="text-gray-400 text-sm" x-text="availableLanguages.length === 0 ? 'All languages added!' : `${availableLanguages.length} languages available`"></p>
                    </div>
                </div>
            </button>
        </div>
        
        {{-- Language and Level Selection Modal (inlined) --}}
        <div x-cloak x-show="isAddingLanguage" class="bg-gradient-to-br from-gray-900 via-gray-900 to-gray-800 rounded-xl p-8 border-2 border-[#ff6f61] shadow-[0px_0px_25px_5px_rgba(255,111,97,0.3)] mb-8">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-white text-2xl flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-[#ff6f61] to-[#ffce7b] flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </div>
                    Select Language & Level
                </h3>
                <button
                    @click="isAddingLanguage = false; selectedLanguage = null; selectedLevel = null;"
                    class="text-gray-400 hover:text-white transition-colors"
                >
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            {{-- Language Selection --}}
            <div class="mb-8">
                <p class="text-gray-400 mb-4 text-lg">Choose a language:</p>
                <div class="grid grid-cols-4 gap-4">
                    <template x-for="lang in availableLanguages" :key="lang.name">
                        <button
                            @click="selectedLanguage = lang.name; selectedLevel = null;"
                            :class="`relative bg-gray-800 rounded-lg p-5 transition-all duration-300 border-2 ${
                                selectedLanguage === lang.name ? 'border-[#ff6f61] shadow-[0px_0px_20px_3px_rgba(255,111,97,0.3)] scale-105' : 'border-gray-700 hover:border-gray-600 hover:scale-102'
                            }`"
                        >
                            <div class="flex flex-col items-center">
                                <div :class="`w-12 h-12 rounded-lg bg-gradient-to-br ${lang.gradient} mb-3 flex items-center justify-center shadow-lg`">
                                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                </div>
                                <p class="text-white text-sm" x-text="lang.name"></p>
                            </div>
                            <div x-show="selectedLanguage === lang.name" class="absolute -top-2 -right-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-[#ff6f61] to-[#ffce7b] flex items-center justify-center shadow-lg">
                                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Level Selection --}}
            <div x-cloak x-show="selectedLanguage">
                <p class="text-gray-400 mb-4 text-lg" x-text="`Select your skill level for ${selectedLanguage}:`"></p>
                <div class="grid grid-cols-3 gap-5">
                    <template x-for="levelInfo in levels" :key="levelInfo.level">
                        <button
                            @click="selectedLevel = levelInfo.level"
                            :class="`relative bg-gradient-to-br ${levelInfo.color} rounded-xl p-8 transition-all duration-300 ${
                                selectedLevel === levelInfo.level ? 'shadow-[0px_0px_30px_8px_rgba(255,111,97,0.4)] scale-110' : 'opacity-90 hover:opacity-100 hover:scale-105'
                            }`"
                        >
                            <div class="text-center">
                                <div class="w-14 h-14 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-3 shadow-lg">
                                    {{-- Level Icon (simplified) --}}
                                    <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path></svg>
                                </div>
                                <p class="text-white text-xl" x-text="levelInfo.level"></p>
                            </div>
                            <div x-show="selectedLevel === levelInfo.level" class="absolute -top-2 -right-2">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-lg">
                                    <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div x-show="selectedLanguage && selectedLevel" class="flex justify-end gap-3 mt-8">
                <button
                    @click="isAddingLanguage = false; selectedLanguage = null; selectedLevel = null;"
                    class="bg-gray-800 border border-gray-700 text-white px-8 py-3 rounded-lg hover:bg-gray-750 transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="handleAddLanguage"
                    :disabled="!selectedLanguage || !selectedLevel"
                    :class="`bg-gradient-to-r from-[#ff6f61] to-[#ffce7b] text-white px-10 py-3 rounded-lg shadow-[0px_4px_4px_0px_rgba(0,0,0,0.25)] transition-all duration-300 hover:scale-105 hover:shadow-[0px_0px_25px_5px_rgba(255,111,97,0.5)] flex items-center gap-2 ${
                        !selectedLanguage || !selectedLevel ? 'opacity-50 cursor-not-allowed' : ''
                    }`"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Language
                </button>
            </div>
        </div>


        {{-- Complete Button --}}
        <div x-cloak x-show="selections.length > 0 && !isAddingLanguage" class="flex justify-center mt-12">
            <button
                @click="handleComplete"
                class="bg-gradient-to-r from-[#ff6f61] to-[#ffce7b] text-white px-16 py-5 rounded-xl shadow-[0px_4px_4px_0px_rgba(0,0,0,0.25)] text-2xl transition-all duration-300 hover:scale-105 hover:shadow-[0px_0px_30px_5px_rgba(255,111,97,0.5)] inline-flex items-center gap-3"
            >
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5l-2.6 1.5M7 5l2.6 1.5M19 12h3M2 12h3M17 19l-2.6-1.5M7 19l2.6-1.5"></path></svg>
                Start Learning
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0118 0v2a9 9 0 01-18 0z"></path><path d="M8 11v2M12 11v2M16 11v2"></path></svg>
            </button>
        </div>

        {{-- Empty State --}}
        <div x-cloak x-show="selections.length === 0 && !isAddingLanguage" class="text-center py-12">
            <div class="w-20 h-20 rounded-full bg-gray-900 border-2 border-gray-800 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
            </div>
            <p class="text-gray-500 text-lg">
                No languages added yet. Click above to add your first language!
            </p>
        </div>
    </div>
</div>