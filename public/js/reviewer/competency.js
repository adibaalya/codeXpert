/**
 * public/js/reviewer/competency.js
 * Handles logic for both:
 * 1. Competency Selection Page (Language Choice)
 * 2. Competency Test Page (MCQ & Timer)
 */

document.addEventListener('DOMContentLoaded', function() {

    // =========================================================
    // SECTION 1: COMPETENCY SELECTION PAGE (Language Choice)
    // =========================================================
    
    const languageCards = document.querySelectorAll('.language-card');

    if (languageCards.length > 0) {
        languageCards.forEach(card => {
            const hoverBg = card.getAttribute('data-hover-bg');
            const defaultBg = card.getAttribute('data-default-bg');
            const isSufficient = card.getAttribute('data-is-sufficient') === 'true';

            // Hover effects
            card.addEventListener('mouseenter', function() {
                if (!this.classList.contains('selected') && isSufficient) {
                    this.style.background = hoverBg;
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('selected') && isSufficient) {
                    this.style.background = defaultBg;
                }
            });
            
            // Click effect - make it stay in hover state
            card.addEventListener('click', function() {
                if (isSufficient) {
                    // Remove selected class and reset background for all cards
                    languageCards.forEach(c => {
                        c.classList.remove('selected');
                        c.style.borderColor = 'transparent';
                        c.style.background = c.getAttribute('data-default-bg');
                    });
                    
                    // Add selected class and apply hover background to clicked card
                    this.classList.add('selected');
                    this.style.background = hoverBg;

                    // Trigger the hidden radio input click
                    const radioId = this.querySelector('input[type="radio"]').id;
                    if(radioId) {
                        document.getElementById(radioId).click();
                    }
                }
            });
        });
    }

    // =========================================================
    // SECTION 2: COMPETENCY TEST PAGE (MCQ, Timer, Security)
    // =========================================================

    const mcqForm = document.getElementById('mcqForm');

    if (mcqForm) {
        // --- 2.1 Security: Prevent Copy/Paste ---
        const questionCard = document.querySelector('.question-card');
        
        if (questionCard) {
            // Prevent right-click context menu
            questionCard.addEventListener('contextmenu', e => e.preventDefault());
            
            // Prevent copy/cut shortcuts
            questionCard.addEventListener('copy', e => e.preventDefault());
            questionCard.addEventListener('cut', e => e.preventDefault());
            
            // Prevent drag selection
            questionCard.addEventListener('dragstart', e => e.preventDefault());
            
            // Prevent specific keyboard shortcuts (Ctrl+C, Ctrl+A, etc.)
            questionCard.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && 
                    ['c', 'x', 'a'].includes(e.key.toLowerCase())) {
                    e.preventDefault();
                }
            });
        }

        // --- 2.2 MCQ Option Selection ---
        const optionsList = document.querySelector('.options-list');
        const submitBtn = document.getElementById('submitBtn');
        const selectedAnswerInput = document.getElementById('selectedAnswer');

        if (optionsList) {
            optionsList.addEventListener('click', function(e) {
                const optionItem = e.target.closest('.option-item');
                if (!optionItem) return;
                
                // Get the option letter
                const answer = optionItem.getAttribute('data-option-value');
                
                // UI Update: Deselect others, select current
                document.querySelectorAll('.option-item').forEach(opt => opt.classList.remove('selected'));
                optionItem.classList.add('selected');
                
                // Update hidden input
                if (selectedAnswerInput) selectedAnswerInput.value = answer;
                
                // Enable submit button
                if (submitBtn) submitBtn.disabled = false;
            });
        }

        // --- 2.3 Navigation Helpers ---
        // Attached to window for onclick="..." attributes in Blade
        window.goToPrevious = function() {
            document.getElementById('previousForm').submit();
        };

        // --- 2.4 Timer Logic ---
        // Get remaining seconds from global config (set in Blade)
        let timeLeft = window.testConfig ? window.testConfig.remainingSeconds : 2700;
        const timerDisplay = document.getElementById('timer');

        function updateTimer() {
            if (!timerDisplay) return;

            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            timerDisplay.textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                mcqForm.submit(); // Auto-submit when time is up
            } else {
                timeLeft--;
            }
        }
        
        // Start Timer
        updateTimer();
        setInterval(updateTimer, 1000);
    }

    // =========================================================
    // SECTION 3: COMPETENCY CODING CHALLENGE (Editor, Timer, Actions)
    // =========================================================

    const codeTestBody = document.querySelector('.code-test-body-reviewer');

    if (codeTestBody) {
        const config = window.competencyCodeConfig; // Config passed from Blade

        // --- 3.1 Timer Logic (Specific to Code Test) ---
        let timeLeft = config ? config.remainingSeconds : 2700;
        const timerDisplay = document.getElementById('timer');

        function updateCodeTimer() {
            if (!timerDisplay) return;

            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            timerDisplay.textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                alert('Time is up! Your code will be submitted automatically.');
                document.getElementById('codeForm').submit();
            } else {
                timeLeft--;
            }
        }

        if (timerDisplay) {
            updateCodeTimer();
            setInterval(updateCodeTimer, 1000);
        }

        // --- 3.2 UI Helpers (Tabs, Output, Hints) ---
        
        window.switchTab = function(tab) {
            const problemTab = document.getElementById('problemTab');
            const testcasesTab = document.getElementById('testcasesTab');
            const problemButton = document.querySelector('.problem-tab:nth-child(1)');
            const testcasesButton = document.querySelector('.problem-tab:nth-child(2)');

            if (tab === 'problem') {
                problemTab.style.display = 'block';
                testcasesTab.style.display = 'none';
                problemButton.classList.add('active');
                testcasesButton.classList.remove('active');
            } else if (tab === 'testcases') {
                problemTab.style.display = 'none';
                testcasesTab.style.display = 'block';
                problemButton.classList.remove('active');
                testcasesButton.classList.add('active');
            }
        };

        window.toggleHints = function() {
            const hintsBox = document.getElementById('hintsBox');
            if (hintsBox.style.display === 'none') {
                hintsBox.style.display = 'block';
                document.getElementById('hintsButtonText').textContent = 'Hide Hints';
                document.getElementById('chevronIcon').style.transform = 'rotate(180deg)';
            } else {
                hintsBox.style.display = 'none';
                document.getElementById('hintsButtonText').textContent = 'Show Hints';
                document.getElementById('chevronIcon').style.transform = 'rotate(0deg)';
            }
        };

        window.closeOutput = function() {
            document.getElementById('outputSection').style.display = 'none';
        };

        window.showTestCase = function(index) {
            const testCases = document.querySelectorAll('[id^="case-"]');
            const tabs = document.querySelectorAll('[id^="tab-"]');
            
            testCases.forEach((testCase, i) => {
                testCase.style.display = i === index ? 'block' : 'none';
            });
            
            tabs.forEach((tab, i) => {
                tab.style.background = i === index ? '#2d2d2d' : 'transparent';
            });
        };

        // --- 3.3 Core Actions (Submit & Run) ---

        window.submitCode = function() {
            // Note: window.editor is defined in the Blade file
            const code = window.editor ? window.editor.getValue() : '';
            
            if (!code || code.trim() === '' || code.trim() === '// Write your solution here...') {
                alert('Please write your solution before submitting');
                return;
            }

            document.getElementById('codeSolution').value = code;
            const submitBtn = document.getElementById('submitBtn');
            const testingModal = document.getElementById('testingModal');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Submitting...</span>';
            testingModal.style.display = 'flex';
            document.getElementById('codeForm').submit();
        };

        window.runCode = function() {
            const code = window.editor ? window.editor.getValue() : '';
            
            if (!code || code.trim() === '' || code.trim() === '// Write your solution here...') {
                alert('Please write your solution before running');
                return;
            }

            const runBtn = document.getElementById('runBtn');
            const outputSection = document.getElementById('outputSection');
            const outputContent = document.getElementById('outputContent');
            
            // UI Loading State
            runBtn.disabled = true;
            runBtn.innerHTML = 'Running...';
            outputSection.style.display = 'block';
            outputContent.textContent = 'Running your code against all test cases...\n\nPlease wait...';
            
            fetch(config.routes.run, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify({
                    solution: code,
                    question_id: config.questionId
                })
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.text();
            })
            .then(text => {
                let data;
                try { data = JSON.parse(text); } 
                catch (e) { outputContent.textContent = '❌ Server Error:\n' + text; return; }
                
                if (data.success) {
                    // Re-enable buttons
                    document.getElementById('submitBtn').disabled = false;
                    document.getElementById('submitBtn').style.opacity = '1';
                    document.getElementById('submitBtn').style.cursor = 'pointer';
                    
                    const validationText = document.getElementById('validationText');
                    if (data.allPassed) {
                        validationText.innerHTML = '✓ All tests passed! Ready to submit.';
                        validationText.style.color = '#10B981';
                    } else {
                        validationText.innerHTML = `⚠️ ${data.passedTests}/${data.totalTests} tests passed.`;
                        validationText.style.color = '#F59E0B';
                    }
                    
                    // Generate Result HTML (Simplified for brevity, similar to original)
                    let output = generateOutputHtml(data);
                    outputContent.innerHTML = output;
                } else {
                    outputContent.innerHTML = `<div style="padding:15px; color:#EF4444;">❌ Execution Error: ${escapeHtml(data.output || 'Unknown')}</div>`;
                }
            })
            .catch(error => {
                outputContent.textContent = '❌ Connection Error: ' + error.message;
            })
            .finally(() => {
                runBtn.disabled = false;
                runBtn.innerHTML = `Run Code`; // You can put the SVG back here if needed
            });
        };

        // Helper to generate the specific Output HTML structure
        function generateOutputHtml(data) {
            const statusColor = data.allPassed ? '#10B981' : '#EF4444';
            let html = `<div style="background:#252526; padding:12px; border-bottom:1px solid #3e3e3e; display:flex; gap:10px;">
                <span style="color:${statusColor}; font-weight:700;">${data.allPassed ? '✓ Accepted' : '✗ Failed'}</span>
                <span style="color:#888;">${data.passedTests}/${data.totalTests} passed</span>
            </div><div style="background:#1e1e1e; padding:15px;">`;
            
            // Tabs
            html += `<div style="display:flex; gap:10px; margin-bottom:15px;">`;
            data.testResults.forEach((res, i) => {
                const color = res.passed ? '#10B981' : '#EF4444';
                html += `<button onclick="showTestCase(${i});" style="color:${color}; background:${i===0?'#2d2d2d':'transparent'}; border:none; padding:5px 10px; cursor:pointer;">Case ${res.test_number}</button>`;
            });
            html += `</div>`;

            // Content
            data.testResults.forEach((res, i) => {
                html += `<div id="case-${i}" style="display:${i===0?'block':'none'}; color:#d4d4d4; font-family:monospace;">
                    <div style="margin-bottom:10px;"><div style="color:#888;">Input</div><div style="background:#252526; padding:10px;">${escapeHtml(res.input)}</div></div>
                    <div style="margin-bottom:10px;"><div style="color:#888;">Your Output</div><div style="background:#252526; padding:10px;">${escapeHtml(res.output)}</div></div>
                    ${res.expected ? `<div><div style="color:#888;">Expected</div><div style="background:#252526; padding:10px;">${escapeHtml(res.expected)}</div></div>` : ''}
                </div>`;
            });
            html += `</div>`;
            return html;
        }

        function escapeHtml(unsafe) {
            return unsafe ? unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';
        }
    }
});

