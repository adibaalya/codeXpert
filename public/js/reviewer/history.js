/**
 * public/js/reviewer/history.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Configuration & Data
    // We expect window.historyConfig to be set in the Blade file
    const questionsData = window.historyConfig ? window.historyConfig.questions : {};

    // 2. DOM Elements
    const elements = {
        modalOverlay: document.getElementById('modalOverlay'),
        modalTitle: document.getElementById('modalTitle'),
        modalContent: document.getElementById('modalContentFormatted'),
        searchInput: document.getElementById('searchInput'),
        difficultyFilter: document.getElementById('difficultyFilter'),
        topicFilter: document.getElementById('topicFilter'),
        showingCount: document.querySelector('.showing-count'),
        closeBtn: document.querySelector('.close-btn')
    };

    // 3. Modal Logic
    
    // Open Modal
    window.openModal = function(questionId) {
        const question = questionsData[questionId];
        if (!question) return;

        // Set Title
        elements.modalTitle.textContent = question.title || 'Untitled Question';

        // Format Content
        elements.modalContent.innerHTML = formatQuestionContent(question);

        // Show Modal
        elements.modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    // Close Modal Function
    window.closeModal = function() {
        elements.modalOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    };

    // Close on Overlay Click
    window.closeModalOnOverlay = function(event) {
        if (event.target === elements.modalOverlay) {
            closeModal();
        }
    };

    // Close on Escape Key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // Close Button Event Listener (in case onclick is removed from HTML)
    if (elements.closeBtn) {
        elements.closeBtn.addEventListener('click', closeModal);
    }

    // 4. Content Formatting Helpers

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
        
        // Show Constraints (Optional addition)
        if (question.constraints) {
             html += `
                <div class="content-section-modal">
                    <h3 class="section-heading">Constraints</h3>
                    <div class="section-text">${formatSectionContent(question.constraints)}</div>
                </div>
            `;
        }

        return html;
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

    // 5. Filter Logic

    function filterQuestions() {
        const searchTerm = elements.searchInput.value.toLowerCase();
        const difficulty = elements.difficultyFilter.value.toLowerCase();
        const language = elements.languageFilter.value.toLowerCase();

        const cards = document.querySelectorAll('.question-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const title = card.querySelector('.question-title').textContent.toLowerCase();
            const description = card.querySelector('.question-description').textContent.toLowerCase();
            // Assuming the badges are the first and second children with class .badge
            const badges = card.querySelectorAll('.badge');
            const cardDifficulty = badges[0] ? badges[0].textContent.trim().toLowerCase() : '';
            const cardLanguage = badges[1] ? badges[1].textContent.trim().toLowerCase() : '';
            const matchesSearch = !searchTerm || title.includes(searchTerm) || description.includes(searchTerm);
            const matchesDifficulty = !difficulty || cardDifficulty.includes(difficulty);
            const matchesLanguage = !language || cardLanguage === language;

            if (matchesSearch && matchesDifficulty && matchesLanguage) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Update showing count
        const total = cards.length;
        if(elements.showingCount) {
             elements.showingCount.innerHTML = `Showing <strong>${visibleCount}</strong> of <strong>${total}</strong> questions`;
        }
    }

    // Attach Event Listeners for Filters
    if(elements.searchInput) elements.searchInput.addEventListener('input', filterQuestions);
    if(elements.difficultyFilter) elements.difficultyFilter.addEventListener('change', filterQuestions);
    if(elements.topicFilter) elements.topicFilter.addEventListener('change', filterQuestions);
});