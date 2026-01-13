/**
 * public/js/reviewer/generate.js
 */

document.addEventListener('DOMContentLoaded', function() {
    let currentQuestionData = null;
    const config = window.generateConfig;

    // Helper: Get CSRF Token
    const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]').content;

    // Helper: Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ============================================================
    // Global Functions (Attached to window for HTML onclick support)
    // ============================================================

    window.switchTab = function(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        event.target.closest('.tab').classList.add('active');

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(tabName + 'Tab').classList.add('active');
    };

    window.regenerate = function() {
        document.getElementById('generatedContent').classList.remove('active');
        document.getElementById('emptyState').style.display = 'flex';
        currentQuestionData = null;
    };

    window.retryGeneration = function() {
        document.getElementById('errorState').style.display = 'none';
        document.getElementById('loadingState').style.display = 'block';
        document.getElementById('loadingText').textContent = 'Retrying...';
        const subtext = document.getElementById('loadingSubtext');
        subtext.style.display = 'block';
        subtext.textContent = 'Please wait while we retry generating your question.';
        
        // Trigger the form submit programmatically
        document.getElementById('generateForm').dispatchEvent(new Event('submit'));
    };

    window.saveToQueue = async function() {
        if (!currentQuestionData) {
            showModal('Error', 'Please generate a question first before saving.');
            return;
        }

        const saveBtn = event.target;
        const originalText = saveBtn.textContent;
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        try {
            const response = await fetch(config.routes.save, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(currentQuestionData)
            });

            const result = await response.json();

            if (result.success) {
                const questionTitle = currentQuestionData.title || 'Question';
                showModal(
                    'Question Saved', 
                    `"${questionTitle}" has been saved to the review queue and will be reviewed by an expert.`
                );
                window.regenerate();
            } else {
                showModal('Error', result.message || 'Failed to save question');
            }
        } catch (error) {
            console.error('Error:', error);
            showModal('Error', 'An error occurred while saving the question.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    };

    // Helper function to show modal
    function showModal(title, message) {
        const modal = document.getElementById('successModal');
        const modalTitle = document.getElementById('successTitle');
        const modalMessage = document.getElementById('successMessage');
        
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modal.style.display = 'flex';
    }

    // Global function to close modal
    window.closeModal = function() {
        const modal = document.getElementById('successModal');
        modal.style.display = 'none';
    };

    // ============================================================
    // LeetCode Button Handler
    // ============================================================
    const leetcodeBtn = document.getElementById('leetcodeBtn');
    if (leetcodeBtn) {
        leetcodeBtn.addEventListener('click', async function(e) {
            e.preventDefault();

            const language = document.getElementById('language').value;
            const difficulty = document.getElementById('difficulty').value;

            // Validate language field
            if (!language || language === '') {
                document.getElementById('emptyState').style.display = 'none';
                document.getElementById('generatedContent').classList.remove('active');
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('errorState').style.display = 'block';
                document.getElementById('errorTitle').textContent = 'Language Required';
                document.getElementById('errorMessage').textContent = 'Please select a language to fetch a LeetCode question.';
                document.getElementById('retryButton').style.display = 'none';
                return;
            }

            // Validate difficulty field
            if (!difficulty || difficulty === '') {
                document.getElementById('emptyState').style.display = 'none';
                document.getElementById('generatedContent').classList.remove('active');
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('errorState').style.display = 'block';
                document.getElementById('errorTitle').textContent = 'Difficulty Required';
                document.getElementById('errorMessage').textContent = 'Please select a difficulty level.';
                document.getElementById('retryButton').style.display = 'none';
                return;
            }

            // Map difficulty levels
            const difficultyMap = {
                'beginner': 'Easy',
                'intermediate': 'Medium',
                'advanced': 'Hard'
            };
            const leetcodeDifficulty = difficultyMap[difficulty] || 'Medium';

            // UI: Show loading state
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('generatedContent').classList.remove('active');
            document.getElementById('loadingState').style.display = 'block';
            document.getElementById('loadingText').textContent = 'Fetching LeetCode question...';
            const subtext = document.getElementById('loadingSubtext');
            subtext.style.display = 'block';
            subtext.textContent = 'Please wait while we fetch a random question from LeetCode.';
            document.getElementById('errorState').style.display = 'none';
            leetcodeBtn.disabled = true;
            document.getElementById('generateBtn').disabled = true;

            try {
                const response = await fetch('/reviewer/generate/leetcode', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        difficulty: leetcodeDifficulty,
                        language: language
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Hide loading, show generated content
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('generatedContent').classList.add('active');

                    // Store the complete question data for saving later
                    currentQuestionData = {
                        ...result.data,
                        language: language,
                        difficulty: difficulty
                    };

                    // Populate the content UI
                    document.getElementById('questionTitle').textContent = result.data.title;
                    document.getElementById('difficultyBadge').textContent = difficulty.charAt(0).toUpperCase() + difficulty.slice(1);
                    
                    const topicText = result.data.topic ? result.data.topic.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'Algorithm';
                    document.getElementById('topicBadge').textContent = topicText;
                    document.getElementById('languageBadge').textContent = language;
                    
                    document.getElementById('problemDescription').textContent = result.data.description;
                    document.getElementById('problemStatement').textContent = result.data.problemStatement;
                    document.getElementById('expectedApproach').textContent = result.data.expectedApproach;

                    // Populate constraints
                    const constraintsList = document.getElementById('constraintsList');
                    constraintsList.innerHTML = '';
                    if (result.data.constraints && Array.isArray(result.data.constraints)) {
                        result.data.constraints.forEach(constraint => {
                            const li = document.createElement('li');
                            li.textContent = constraint;
                            constraintsList.appendChild(li);
                        });
                    }

                    // Populate tests
                    const testsContent = document.getElementById('testsContent');
                    testsContent.innerHTML = '';
                    if (result.data.tests && Array.isArray(result.data.tests)) {
                        result.data.tests.forEach((test, index) => {
                            testsContent.innerHTML += `
                                <div style="margin-bottom: 20px;">
                                    <div class="section-title">Test ${index + 1}</div>
                                    <div class="problem-text"><strong>Input:</strong> ${test.input || 'See LeetCode'}</div>
                                    <div class="problem-text"><strong>Expected Output:</strong> ${test.output || 'See LeetCode'}</div>
                                    ${test.explanation ? `<div class="problem-text"><strong>Explanation:</strong> ${test.explanation}</div>` : ''}
                                </div>
                            `;
                        });
                        document.getElementById('testCount').textContent = result.data.tests.length || 0;
                    } else {
                        testsContent.innerHTML = '<div class="problem-text">No test cases available. Please refer to the LeetCode problem for examples.</div>';
                        document.getElementById('testCount').textContent = 0;
                    }

                    // Populate solution
                    document.getElementById('solutionContent').innerHTML = `<pre style="background: #f3f4f6; padding: 16px; border-radius: 8px; overflow-x: auto;"><code>${escapeHtml(result.data.solution)}</code></pre>`;
                
                    // Show LeetCode link if available
                    if (result.data.leetcode_url) {
                        document.getElementById('problemDescription').innerHTML += `<br><br><a href="${result.data.leetcode_url}" target="_blank" style="color: #FFA116; text-decoration: underline;">View on LeetCode</a>`;
                    }
                
                } else {
                    // Handle API Failure
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('errorState').style.display = 'block';
                    document.getElementById('errorTitle').textContent = 'Failed to Fetch LeetCode Question';
                    document.getElementById('errorMessage').textContent = result.message || 'Unable to fetch question from LeetCode. Please try again.';
                    document.getElementById('retryButton').style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('errorState').style.display = 'block';
                document.getElementById('errorTitle').textContent = 'An Error Occurred';
                document.getElementById('errorMessage').textContent = 'An error occurred while fetching the LeetCode question. Please try again.';
                document.getElementById('retryButton').style.display = 'block';
            } finally {
                leetcodeBtn.disabled = false;
                document.getElementById('generateBtn').disabled = false;
            }
        });
    }

    // ============================================================
    // Event Listeners
    // ============================================================

    const form = document.getElementById('generateForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Validate prompt field
            if (!data.prompt || data.prompt.trim() === '') {
                // Show error state with validation message
                document.getElementById('emptyState').style.display = 'none';
                document.getElementById('generatedContent').classList.remove('active');
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('errorState').style.display = 'block';
                document.getElementById('errorTitle').textContent = 'Prompt Required';
                document.getElementById('errorMessage').textContent = 'Please fill in the prompt field to generate a question.';
                document.getElementById('retryButton').style.display = 'none';
                return;
            }

            // Validate language field
            if (!data.language || data.language === '') {
                document.getElementById('emptyState').style.display = 'none';
                document.getElementById('generatedContent').classList.remove('active');
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('errorState').style.display = 'block';
                document.getElementById('errorTitle').textContent = 'Language Required';
                document.getElementById('errorMessage').textContent = 'Please select a language.';
                document.getElementById('retryButton').style.display = 'none';
                return;
            }

            // Validate difficulty field
            if (!data.difficulty || data.difficulty === '') {
                document.getElementById('emptyState').style.display = 'none';
                document.getElementById('generatedContent').classList.remove('active');
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('errorState').style.display = 'block';
                document.getElementById('errorTitle').textContent = 'Difficulty Required';
                document.getElementById('errorMessage').textContent = 'Please select a difficulty level.';
                document.getElementById('retryButton').style.display = 'none';
                return;
            }

            // UI: Show loading state
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('generatedContent').classList.remove('active');
            document.getElementById('loadingState').style.display = 'block';
            document.getElementById('errorState').style.display = 'none';
            document.getElementById('generateBtn').disabled = true;

            try {
                const response = await fetch(config.routes.generate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // Hide loading, show generated content
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('generatedContent').classList.add('active');

                    // Store the complete question data for saving later
                    currentQuestionData = {
                        ...result.data,
                        language: data.language,
                        difficulty: data.difficulty
                    };

                    // Populate the content UI
                    document.getElementById('questionTitle').textContent = result.data.title;
                    document.getElementById('difficultyBadge').textContent = data.difficulty.charAt(0).toUpperCase() + data.difficulty.slice(1);
                    
                    // Format Topic Badge
                    const topicText = result.data.topic ? result.data.topic.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'Algorithm';
                    document.getElementById('topicBadge').textContent = topicText;
                    
                    document.getElementById('languageBadge').textContent = data.language;
                    
                    document.getElementById('problemDescription').textContent = result.data.description;
                    document.getElementById('problemStatement').textContent = result.data.problemStatement;
                    document.getElementById('expectedApproach').textContent = result.data.expectedApproach;

                    // Populate constraints
                    const constraintsList = document.getElementById('constraintsList');
                    constraintsList.innerHTML = '';
                    if (result.data.constraints && Array.isArray(result.data.constraints)) {
                        result.data.constraints.forEach(constraint => {
                            const li = document.createElement('li');
                            li.textContent = constraint;
                            constraintsList.appendChild(li);
                        });
                    }

                    // Populate tests
                    const testsContent = document.getElementById('testsContent');
                    testsContent.innerHTML = '';
                    if (result.data.tests && Array.isArray(result.data.tests)) {
                        result.data.tests.forEach((test, index) => {
                            testsContent.innerHTML += `
                                <div style="margin-bottom: 20px;">
                                    <div class="section-title">Test ${index + 1}</div>
                                    <div class="problem-text"><strong>Input:</strong> ${test.input}</div>
                                    <div class="problem-text"><strong>Expected Output:</strong> ${test.output}</div>
                                    ${test.explanation ? `<div class="problem-text"><strong>Explanation:</strong> ${test.explanation}</div>` : ''}
                                </div>
                            `;
                        });
                        document.getElementById('testCount').textContent = result.data.tests.length;
                    }

                    // Populate solution
                    document.getElementById('solutionContent').innerHTML = `<pre style="background: #f3f4f6; padding: 16px; border-radius: 8px; overflow-x: auto;"><code>${escapeHtml(result.data.solution)}</code></pre>`;
                
                } else {
                    // Handle API Failure
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('errorState').style.display = 'block';
                    document.getElementById('errorTitle').textContent = 'Generation Failed';
                    document.getElementById('errorMessage').textContent = result.message;
                    
                    const retryBtn = document.getElementById('retryButton');
                    if (result.message && result.message.toLowerCase().includes('rate limit')) {
                        retryBtn.style.display = 'none';
                    } else {
                        retryBtn.style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('errorState').style.display = 'block';
                document.getElementById('errorTitle').textContent = 'An Error Occurred';
                document.getElementById('errorMessage').textContent = 'An error occurred while generating the question. Please try again.';
                document.getElementById('retryButton').style.display = 'block';
            } finally {
                document.getElementById('generateBtn').disabled = false;
            }
        });
    }
});