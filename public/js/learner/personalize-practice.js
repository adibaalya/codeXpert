/**
 * public/js/personalise-practice.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // State Variables
    let state = {
        language: null,
        difficulty: null,
        skill: null
    };

    // DOM Elements
    const elements = {
        languageOptions: document.querySelectorAll('.language-option'),
        difficultyOptions: document.querySelectorAll('.difficulty-option'),
        topicsCard: document.getElementById('topicsCard'),
        topicsGrid: document.getElementById('topicsGrid'),
        topicsLoading: document.getElementById('topicsLoading'),
        topicsEmpty: document.getElementById('topicsEmpty'),
        startButton: document.getElementById('startButton'),
        display: {
            language: document.getElementById('selectedLanguage'),
            difficulty: document.getElementById('selectedDifficulty'),
            skill: document.getElementById('selectedSkill')
        }
    };

    // 1. Language Selection
    elements.languageOptions.forEach(option => {
        option.addEventListener('click', function() {
            // UI Update
            elements.languageOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            // State Update
            state.language = this.dataset.language;
            elements.display.language.textContent = state.language;

            handleSelectionChange();
        });
    });

    // 2. Difficulty Selection
    elements.difficultyOptions.forEach(option => {
        option.addEventListener('click', function() {
            // UI Update
            elements.difficultyOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            // State Update
            state.difficulty = this.dataset.difficulty;
            elements.display.difficulty.textContent = state.difficulty;

            handleSelectionChange();
        });
    });

    // 3. Dynamic Topic Selection (Event Delegation)
    // We attach the listener to the parent because children are created dynamically
    elements.topicsGrid.addEventListener('click', function(e) {
        if (e.target.classList.contains('skill-option')) {
            const selectedOption = e.target;

            // UI Update
            const allSkills = elements.topicsGrid.querySelectorAll('.skill-option');
            allSkills.forEach(opt => opt.classList.remove('selected'));
            selectedOption.classList.add('selected');

            // State Update
            state.skill = selectedOption.dataset.skill;
            elements.display.skill.textContent = state.skill;

            checkAllSelected();
        }
    });

    // 4. Start Button Action
    elements.startButton.addEventListener('click', function() {
        if (state.language && state.difficulty) {
            const config = window.practiceConfig;
            
            // Construct URL
            const params = new URLSearchParams({
                language: state.language,
                level: state.difficulty
            });

            if (state.skill) {
                params.append('topic', state.skill);
            }

            // Redirect
            window.location.href = `${config.routes.start}?${params.toString()}`;
        }
    });

    // Helper: Handle logic when Language or Difficulty changes
    function handleSelectionChange() {
        if (state.language && state.difficulty) {
            loadTopics();
        }
        checkAllSelected();
    }

    // Helper: Load Topics from Server
    async function loadTopics() {
        // Reset UI
        elements.topicsCard.style.display = 'block';
        elements.topicsGrid.innerHTML = '';
        elements.topicsLoading.style.display = 'block';
        elements.topicsEmpty.style.display = 'none';
        
        // Reset Skill State
        state.skill = null;
        elements.display.skill.textContent = '-';

        try {
            const config = window.practiceConfig;
            const url = `${config.routes.topics}?language=${encodeURIComponent(state.language)}&level=${encodeURIComponent(state.difficulty)}`;
            
            const response = await fetch(url);
            const data = await response.json();

            elements.topicsLoading.style.display = 'none';

            if (data.topics && data.topics.length > 0) {
                // Render Topics
                elements.topicsGrid.innerHTML = data.topics.map(topic => 
                    `<div class="skill-option" data-skill="${topic}">${topic}</div>`
                ).join('');
                elements.topicsEmpty.style.display = 'none';
            } else {
                elements.topicsEmpty.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading topics:', error);
            elements.topicsLoading.style.display = 'none';
            elements.topicsEmpty.style.display = 'block';
        }
    }

    // Helper: Enable/Disable Start Button
    function checkAllSelected() {
        if (state.language && state.difficulty) {
            elements.startButton.disabled = false;
        } else {
            elements.startButton.disabled = true;
        }
    }
});