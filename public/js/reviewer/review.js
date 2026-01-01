/**
 * public/js/reviewer/review.js
 * FIXED: Uses Event Delegation to ensure buttons always work, 
 * even after dynamic loading.
 */

document.addEventListener('DOMContentLoaded', function() {
    const config = window.reviewConfig;
    let currentQuestionId = null;

    // =========================================================
    // 1. GLOBAL EVENT DELEGATION (The Fix)
    // =========================================================
    // This listens for clicks anywhere on the page and routes them 
    // to the correct function. This fixes the "unclickable" issue.
    
    document.body.addEventListener('click', function(e) {
        // Find the closest button element (in case user clicks the icon span inside)
        const btn = e.target.closest('button');
        if (!btn) return;

        // Route to functions based on class
        if (btn.classList.contains('btn-approve')) {
            openGradeModal();
        } else if (btn.classList.contains('btn-edit')) {
            toggleEditMode();
        } else if (btn.classList.contains('btn-save')) {
            saveInlineEdit();
        } else if (btn.classList.contains('btn-cancel')) {
            cancelEditMode();
        } else if (btn.classList.contains('btn-modal-cancel')) {
            // Handle both modal cancel buttons
            closeGradeModal();
            closeEditModal();
        } else if (btn.id === 'submitGradeBtn') {
            submitGrade();
        }
    });

    // =========================================================
    // 2. Initialization & Navigation
    // =========================================================

    // Check URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const paramId = urlParams.get('question_id');
    
    if (paramId) {
        const questionCard = document.querySelector(`.question-card[data-question-id="${paramId}"]`);
        if (questionCard) {
            document.querySelectorAll('.question-card').forEach(c => c.classList.remove('active'));
            questionCard.classList.add('active');
            questionCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        loadQuestionDetails(paramId);
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Sidebar Navigation
    document.querySelectorAll('.question-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.question-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const id = this.getAttribute('data-question-id');
            loadQuestionDetails(id);
        });
    });

    // Tab Switching
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('tab')) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            
            const tabName = e.target.getAttribute('data-tab');
            const section = document.getElementById(tabName + '-section');
            if(section) section.style.display = 'block';
        }
    });

    // Modal Sliders
    ['quality', 'clarity', 'difficulty', 'testcases'].forEach(type => {
        const slider = document.getElementById(type + 'Slider');
        if(slider) {
            slider.addEventListener('input', function(e) {
                document.getElementById(type + 'Value').textContent = e.target.value + '%';
                calculateOverallGrade();
            });
        }
    });

    // Close Modals on Outside Click
    window.onclick = function(event) {
        const gradeModal = document.getElementById('gradeModal');
        const editModal = document.getElementById('editModal');
        if (event.target === gradeModal) closeGradeModal();
        if (event.target === editModal) closeEditModal();
    };

    // =========================================================
    // 3. Logic Functions
    // =========================================================

    function ensureQuestionId() {
        if (!currentQuestionId) {
            const activeCard = document.querySelector('.question-card.active');
            if (activeCard) {
                currentQuestionId = activeCard.getAttribute('data-question-id');
            }
        }
        return currentQuestionId;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    // Load Question
    function loadQuestionDetails(questionId) {
        currentQuestionId = questionId;
        const contentArea = document.querySelector('.content-area');
        
        contentArea.innerHTML = `
            <div class="empty-content">
                <div class="empty-content-icon">⏳</div>
                <h2 class="empty-content-title">Loading...</h2>
            </div>`;
        
        fetch(`${config.routes.getQuestion}/${questionId}`)
            .then(res => {
                if (!res.ok) throw new Error('Question not found');
                return res.json();
            })
            .then(data => updateContentArea(data))
            .catch(err => {
                console.error(err);
                contentArea.innerHTML = `
                    <div class="empty-content">
                        <div class="empty-content-icon">❌</div>
                        <h2 class="empty-content-title">Error</h2>
                        <p class="empty-content-text">Could not load question.</p>
                    </div>`;
            });
    }

    // Render Question HTML (Removed onclicks)
    function updateContentArea(question) {
        const contentArea = document.querySelector('.content-area');
        
        const formatConstraints = (text) => {
            if (!text) return '';
            return escapeHtml(text).replace(/\n/g, '<br>').replace(/-\s*(Input parameters:|Output:|Rules:|Edge cases:)/gi, '<strong>$1</strong>');
        };

        let testCasesHTML = '';
        if (question.test_cases && question.test_cases.length > 0) {
            question.test_cases.forEach((tc, i) => {
                const input = typeof tc.input === 'object' ? JSON.stringify(tc.input, null, 2) : tc.input;
                const output = typeof tc.expected_output === 'object' ? JSON.stringify(tc.expected_output, null, 2) : tc.expected_output;
                testCasesHTML += `
                    <div class="test-case-box">
                        <div class="test-case-header"><h3 class="test-case-title">Test Case ${i + 1}</h3></div>
                        <div class="test-case-content">
                            ${tc.input !== undefined ? `<div class="test-case-item"><strong>Input</strong><pre class="code-block">${escapeHtml(input)}</pre></div>` : ''}
                            ${tc.expected_output !== undefined ? `<div class="test-case-item"><strong>Expected Output</strong><pre class="code-block">${escapeHtml(output)}</pre></div>` : ''}
                        </div>
                    </div>`;
            });
        } else {
            testCasesHTML = '<div class="problem-box"><p class="problem-text">No test cases available.</p></div>';
        }

        let solutionHTML = question.question_type === 'MCQ_Single' 
            ? `<div class="answer-display"><strong>Correct Answer:</strong> ${escapeHtml(question.solution)}</div>`
            : `<pre class="code-block solution-code">${escapeHtml(question.solution || 'No solution provided')}</pre>`;

        // NOTICE: No onclick="" attributes here anymore. The global listener handles it.
        contentArea.innerHTML = `
            <div class="question-header">
                <div class="question-header-left">
                    <h1 class="question-title">${escapeHtml(question.title)}</h1>
                    <div class="question-badges">
                        <span class="difficulty-badge ${question.difficulty.toLowerCase()}">${escapeHtml(question.difficulty)}</span>
                        <span class="category-badge">${escapeHtml(question.category)}</span>
                        <span class="language-badge">${escapeHtml(question.language)}</span>
                        <span class="topic-badge">${escapeHtml(question.chapter)}</span>
                        <span class="submitted-text">Submitted ${question.time_ago}</span>
                    </div>
                </div>
                <div class="question-actions">
                    <button class="btn-edit" id="editBtn"><span class="btn-icon">✎</span> Edit</button>
                    <button class="btn-save" id="saveBtn" style="display: none;"><span class="btn-icon">💾</span> Save</button>
                    <button class="btn-cancel" id="cancelBtn" style="display: none;"><span class="btn-icon">✕</span> Cancel</button>
                    <button class="btn-approve"><span class="btn-icon">✓</span> Approve</button>
                </div>
            </div>

            <div class="tabs">
                <button class="tab active" data-tab="problem">Problem</button>
                <button class="tab" data-tab="testcases">Test Cases (${question.test_cases ? question.test_cases.length : 0})</button>
                <button class="tab" data-tab="solution">Solution</button>
            </div>

            <div class="content-section" id="problem-section">
                <div class="section-header"><h2 class="section-title">Description</h2></div>
                <div class="problem-box"><p class="problem-text">${escapeHtml(question.description)}</p></div>
                <div class="section-header"><h2 class="section-title">Problem Statement</h2></div>
                <div class="problem-box"><p class="problem-text">${escapeHtml(question.problem_statement)}</p></div>
                <div class="section-header"><h2 class="section-title">Constraints</h2></div>
                <div class="problem-box"><p class="problem-text">${formatConstraints(question.constraints)}</p></div>
                ${question.hint ? `<div class="hint-box"><strong>💡 Hint:</strong> ${escapeHtml(question.hint)}</div>` : ''}
            </div>

            <div class="content-section" id="testcases-section" style="display: none;">
                <div class="section-header"><h2 class="section-title">Test Cases</h2></div>
                ${testCasesHTML}
            </div>

            <div class="content-section" id="solution-section" style="display: none;">
                <div class="section-header"><h2 class="section-title">Expected Answer/Solution</h2></div>
                <div class="solution-box">${solutionHTML}</div>
            </div>
        `;
    }

    // =========================================================
    // 4. Action Functions
    // =========================================================

    function openGradeModal() {
        ensureQuestionId();
        if (!currentQuestionId) return alert('Please select a question first.');
        
        ['quality', 'clarity', 'difficulty', 'testcases'].forEach(id => {
            const slider = document.getElementById(id+'Slider');
            if (slider) {
                slider.value = 0;
                document.getElementById(id+'Value').textContent = '0%';
            }
        });
        document.getElementById('feedbackText').value = '';
        calculateOverallGrade();
        document.getElementById('gradeModal').style.display = 'flex';
    }

    function closeGradeModal() {
        document.getElementById('gradeModal').style.display = 'none';
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function calculateOverallGrade() {
        const quality = parseInt(document.getElementById('qualitySlider').value) || 0;
        const clarity = parseInt(document.getElementById('claritySlider').value) || 0;
        const difficulty = parseInt(document.getElementById('difficultySlider').value) || 0;
        const testcases = parseInt(document.getElementById('testcasesSlider').value) || 0;

        const average = Math.round((quality + clarity + difficulty + testcases) / 4);
        document.getElementById('overallGradePercent').textContent = average + '%';
        
        const passIndicator = document.getElementById('passIndicator');
        passIndicator.style.display = average >= 70 ? 'inline-flex' : 'none';
        return average;
    }

    function submitGrade() {
        ensureQuestionId();
        const overallGrade = calculateOverallGrade();
        const submitBtn = document.getElementById('submitGradeBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="btn-icon">⏳</span> Submitting...';

        const gradeData = {
            question_id: currentQuestionId,
            quality_score: parseInt(document.getElementById('qualitySlider').value),
            clarity_score: parseInt(document.getElementById('claritySlider').value),
            difficulty_score: parseInt(document.getElementById('difficultySlider').value),
            testcases_score: parseInt(document.getElementById('testcasesSlider').value),
            overall_grade: overallGrade,
            feedback: document.getElementById('feedbackText').value,
            approved: overallGrade >= 70
        };

        sendData(config.routes.submitGrade, gradeData, submitBtn, '✓ Submit Grade', (data) => {
            closeGradeModal();
            showSuccessModal(overallGrade, data);
        });
    }

    function showSuccessModal(grade, data) {
        const isApproved = grade >= 70;
        const questionTitle = document.querySelector('.question-title').textContent;
        
        document.getElementById('successTitle').textContent = isApproved ? 'Question Approved' : 'Question Rejected';
        document.getElementById('successQuestionTitle').textContent = questionTitle;
        document.getElementById('successScore').textContent = grade + '%';
        
        const message = document.getElementById('successMessage');
        message.innerHTML = `"<span id="successQuestionTitle">${questionTitle}</span>" has been ${isApproved ? 'approved' : 'rejected'}<br>with a score of <span class="success-score">${grade}%</span>`;
        
        // Change icon color for rejection
        const checkmark = document.querySelector('.success-checkmark path');
        const iconCircle = document.querySelector('.success-icon-circle');
        if (!isApproved) {
            checkmark.setAttribute('stroke', '#DC2626');
            iconCircle.style.background = 'linear-gradient(135deg, #FEE2E2 0%, #FCA5A5 100%)';
        } else {
            checkmark.setAttribute('stroke', '#4CAF50');
            iconCircle.style.background = 'linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%)';
        }
        
        document.getElementById('successModal').style.display = 'flex';
    }

    function closeSuccessModal() {
        document.getElementById('successModal').style.display = 'none';
        window.location.reload();
    }

    // Make closeSuccessModal global
    window.closeSuccessModal = closeSuccessModal;

    function toggleEditMode() {
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const approveBtn = document.querySelector('.btn-approve'); 

        const isEditing = editBtn.style.display !== 'none';
        
        editBtn.style.display = isEditing ? 'none' : 'inline-flex';
        saveBtn.style.display = isEditing ? 'inline-flex' : 'none';
        cancelBtn.style.display = isEditing ? 'inline-flex' : 'none';
        if(approveBtn) approveBtn.style.display = isEditing ? 'none' : 'inline-flex';

        const elementsToEdit = document.querySelectorAll('.problem-text, .hint-box, .code-block, .answer-display');
        elementsToEdit.forEach(el => {
            el.contentEditable = isEditing ? 'true' : 'false';
            isEditing ? el.classList.add('editable') : el.classList.remove('editable');
        });
    }

    function saveInlineEdit() {
        ensureQuestionId();
        if (!currentQuestionId) return alert('No question selected.');

        const contentArea = document.querySelector('.content-area');
        const problemBoxes = contentArea.querySelectorAll('#problem-section .problem-box');
        const getHTML = (box) => box ? box.querySelector('.problem-text').innerHTML : '';
        const hintBox = contentArea.querySelector('.hint-box');

        const test_cases = [];
        contentArea.querySelectorAll('#testcases-section .test-case-box').forEach(box => {
            const blocks = box.querySelectorAll('.code-block');
            if (blocks.length >= 1) {
                test_cases.push({
                    input: blocks[0].textContent.trim(),
                    expected_output: blocks[1] ? blocks[1].textContent.trim() : ''
                });
            }
        });

        const solutionBox = contentArea.querySelector('#solution-section .solution-box .code-block, #solution-section .answer-display');
        let solutionText = '';
        if (solutionBox) solutionText = solutionBox.innerText.replace('Correct Answer:', '').trim();

        const saveData = {
            question_id: currentQuestionId,
            description: getHTML(problemBoxes[0]),
            problem_statement: getHTML(problemBoxes[1]),
            constraints: getHTML(problemBoxes[2]),
            hint: hintBox ? hintBox.innerHTML.replace('💡 Hint:', '').trim() : '',
            test_cases: test_cases.length > 0 ? test_cases : null,
            solution: solutionText
        };

        const saveBtn = document.getElementById('saveBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="btn-icon">⏳</span> Saving...';

        sendData(config.routes.editQuestion, saveData, saveBtn, '💾 Save', () => {
            showEditSuccessModal();
        });
    }

    function showEditSuccessModal() {
        const questionTitle = document.querySelector('.question-title').textContent;
        
        document.getElementById('successTitle').textContent = 'Changes Saved Successfully';
        document.getElementById('successQuestionTitle').textContent = questionTitle;
        
        const message = document.getElementById('successMessage');
        message.innerHTML = `Your edits to "<span id="successQuestionTitle">${questionTitle}</span>" have been saved successfully`;
        
        // Change to blue theme for edit success
        const checkmark = document.querySelector('.success-checkmark path');
        const iconCircle = document.querySelector('.success-icon-circle');
        checkmark.setAttribute('stroke', '#3B82F6');
        iconCircle.style.background = 'linear-gradient(135deg, #DBEAFE 0%, #93C5FD 100%)';
        
        // Hide the score element for edit
        const scoreElement = document.getElementById('successScore');
        if (scoreElement) {
            scoreElement.parentElement.style.display = 'none';
        }
        
        document.getElementById('successModal').style.display = 'flex';
    }

    function cancelEditMode() {
        window.location.reload();
    }

    // Helper to send data
    function sendData(url, data, btnElement, originalBtnText, successCallback) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                successCallback(data);
            } else {
                throw new Error(data.message || 'Action failed');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error: ' + err.message);
            btnElement.disabled = false;
            btnElement.innerHTML = `<span class="btn-icon">${originalBtnText.charAt(0)}</span> ${originalBtnText.substring(2)}`;
        });
    }
});