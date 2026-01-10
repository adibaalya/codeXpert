/**
 * public/js/customization.js
 */

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

async function addLanguage() {
    if (!selectedLanguage || !selectedLevel) {
        console.error('Please select both a language and a level');
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        // Use the route from the global config
        const response = await fetch(window.customizationConfig.routes.store, {
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
}

async function removeLanguage(language) {
    if (!confirm(`Remove ${language} from your learning path?`)) { 
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        // Use the route from the global config
        const response = await fetch(window.customizationConfig.routes.destroy, {
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
            window.location.reload();
        } else {
            alert(data.message || 'Error removing language');
            console.error('Error response:', data);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

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