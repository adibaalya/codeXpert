/**
 * public/js/coding-editor.js
 */

let editor;
let hasRunCode = false; 

// Language mapping for Monaco
const languageMap = { 
    'Java': 'java', 
    'Python': 'python', 
    'JavaScript': 'javascript', 
    'C++': 'cpp', 
    'C#': 'csharp', 
    'PHP': 'php', 
    'Ruby': 'ruby', 
    'Go': 'go', 
    'C': 'c' 
};

// Initialize Monaco Editor
require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});

require(['vs/editor/editor.main'], function() {
    // Access config from the Blade file
    const config = window.codingConfig;
    const monacoLanguage = languageMap[config.language] || 'plaintext';
    
    // Use the template passed from Laravel
    const initialCode = config.initialCode || '// Write your solution here...';
    
    editor = monaco.editor.create(document.getElementById('monacoEditor'), {
        value: initialCode,
        language: monacoLanguage,
        theme: 'vs-dark',
        automaticLayout: true,
        fontSize: 14,
        minimap: { enabled: true },
        scrollBeyondLastLine: false,
        lineNumbers: 'on',
        roundedSelection: false,
        readOnly: false,
        cursorStyle: 'line',
        wordWrap: 'on',
        tabSize: 4,
        insertSpaces: true,
        detectIndentation: true,
        formatOnPaste: true,
        formatOnType: true,
        autoIndent: 'full',
        trimAutoWhitespace: true,
        bracketPairColorization: { enabled: true },
        guides: { indentation: true, bracketPairs: true },
        suggest: { snippetsPreventQuickSuggestions: false }
    });

    // Add keyboard shortcut for formatting
    editor.addAction({
        id: 'format-document',
        label: 'Format Document',
        keybindings: [
            monaco.KeyMod.CtrlCmd | monaco.KeyMod.Shift | monaco.KeyCode.KeyF
        ],
        run: function(ed) {
            ed.getAction('editor.action.formatDocument').run();
        }
    });
});

// UI Logic: Tab Switching
function switchTab(tab) {
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
}

// Logic: Submit Code
function submitCode() {
    const code = editor.getValue();
    if (!code || code.trim() === '' || code.trim() === '// Write your solution here...') { 
        alert('Please write your solution before submitting'); 
        return; 
    }
    
    if (!hasRunCode) {
        alert('⚠️ Please run your code at least once before submitting to verify it works correctly.');
        return;
    }
    
    document.getElementById('codeSolution').value = code;
    const submitBtn = document.getElementById('submitBtn');
    const testingModal = document.getElementById('testingModal');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Submitting...</span>';
    testingModal.style.display = 'flex';
    document.getElementById('codeForm').submit();
}

// Logic: Run Code
function runCode() {
    const code = editor.getValue();
    const config = window.codingConfig;

    if (!code || code.trim() === '' || code.trim() === '// Write your solution here...') { 
        alert('Please write your solution before running'); 
        return; 
    }
    const runBtn = document.getElementById('runBtn');
    const outputSection = document.getElementById('outputSection');
    const outputContent = document.getElementById('outputContent');
    runBtn.disabled = true;
    runBtn.innerHTML = 'Running...';
    outputSection.style.display = 'block';
    outputContent.textContent = 'Running your code against all test cases...\n\nPlease wait...';
    
    fetch(config.routes.run, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
        },
        body: JSON.stringify({ 
            code: code, 
            language: config.language, 
            question_id: config.questionId 
        })
    })
    .then(response => response.text())
    .then(text => {
        let data;
        try { data = JSON.parse(text); } catch (e) { outputContent.textContent = '❌ Server returned invalid JSON:\n\n' + text; return; }
        if (data.success) {
            hasRunCode = true;
            
            const submitBtn = document.getElementById('submitBtn');
            const validationText = document.getElementById('validationText');
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            validationText.innerHTML = '✓ Ready to submit.';
            validationText.style.color = '#10B981';
            
            const statusColor = data.overallStatus === 'Accepted' ? '#10B981' : '#EF4444';
            const statusIcon = data.overallStatus === 'Accepted' ? '✓' : '✗';
            let output = `<div style="background: #252526; padding: 12px 20px; border-bottom: 1px solid #3e3e3e; display: flex; align-items: center; justify-content: space-between;">`;
            output += `<span style="color: ${statusColor}; font-weight: 700; font-size: 14px;">${statusIcon} ${data.overallStatus}</span>`;
            output += `<span style="color: #888; font-size: 13px;">${data.passedTests}/${data.totalTests} test cases passed</span>`;
            output += `</div>`;
            
            output += `<div style="background: #1e1e1e; padding: 15px 20px; border-bottom: 1px solid #3e3e3e;"><div style="display: flex; gap: 12px; flex-wrap: wrap;">`;
            if (data.testResults && Array.isArray(data.testResults)) {
                data.testResults.forEach((testResult, index) => {
                    const isFirst = index === 0;
                    const tabColor = testResult.passed ? '#10B981' : '#EF4444';
                    const tabIcon = testResult.passed ? '✓' : '✗';
                    output += `<button onclick="showTestCase(${index})" id="tab-${index}" style="background: ${isFirst ? '#2d2d2d' : 'transparent'}; border: none; color: ${tabColor}; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.2s;"><span style="font-size: 12px;">${tabIcon}</span> Case ${testResult.test_number}</button>`;
                });
            }
            output += `</div></div>`;
            
            output += `<div style="background: #1e1e1e; padding: 20px; color: #d4d4d4; font-family: 'Courier New', monospace; font-size: 13px; max-height: 300px; overflow-y: auto;">`;
            if (data.testResults && Array.isArray(data.testResults)) {
                data.testResults.forEach((testResult, index) => {
                    const isFirst = index === 0;
                    const outputColor = testResult.passed ? '#10B981' : '#EF4444';
                    output += `<div id="case-${index}" style="display: ${isFirst ? 'block' : 'none'};">`;
                    output += `<div style="margin-bottom: 20px;"><div style="color: #888; font-size: 12px; margin-bottom: 8px; font-weight: 600;">Input</div><div style="background: #252526; padding: 12px 15px; border-radius: 6px; border-left: 3px solid #4C6EF5; white-space: pre-wrap;">${escapeHtml(testResult.input)}</div></div>`;
                    output += `<div style="margin-bottom: 20px;"><div style="color: #888; font-size: 12px; margin-bottom: 8px; font-weight: 600;">Your Output</div><div style="background: #252526; padding: 12px 15px; border-radius: 6px; border-left: 3px solid ${outputColor};">${escapeHtml(testResult.output)}</div></div>`;
                    if (testResult.expected !== undefined && testResult.expected !== null && testResult.expected !== '') {
                        output += `<div style="margin-bottom: 20px;"><div style="color: #888; font-size: 12px; margin-bottom: 8px; font-weight: 600;">Expected Output</div><div style="background: #252526; padding: 12px 15px; border-radius: 6px; border-left: 3px solid #10B981;">${escapeHtml(testResult.expected)}</div></div>`;
                    }
                    output += `</div>`;
                });
            }
            output += `</div>`;
            outputContent.innerHTML = output;
        } else {
            outputContent.innerHTML = '<div style="background: #252526; padding: 12px 20px; border-bottom: 1px solid #3e3e3e;"><span style="color: #EF4444; font-weight: 700; font-size: 14px;">❌ EXECUTION ERROR</span></div><div style="padding: 20px; color: #EF4444; font-family: \'Courier New\', monospace; font-size: 13px; white-space: pre-wrap;">' + escapeHtml(data.output || 'Unknown error occurred') + '</div>';
        }
    })
    .catch(error => { outputContent.textContent = '❌ ERROR: Failed to execute code\n\n' + error.message; })
    .finally(() => { runBtn.disabled = false; runBtn.innerHTML = 'Run Code'; });
}

function closeOutput() { document.getElementById('outputSection').style.display = 'none'; }
function escapeHtml(unsafe) { return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;"); }

function showTestCase(index) {
    event.preventDefault(); event.stopPropagation();
    const testCases = document.querySelectorAll('[id^="case-"]');
    const tabs = document.querySelectorAll('[id^="tab-"]');
    testCases.forEach((testCase, i) => { testCase.style.display = i === index ? 'block' : 'none'; });
    tabs.forEach((tab, i) => { tab.style.background = i === index ? '#2d2d2d' : 'transparent'; });
    return false;
}

function toggleHints() {
    const hintsBox = document.getElementById('hintsBox');
    if (hintsBox.style.display === 'none') {
        hintsBox.style.display = 'block'; document.getElementById('hintsButtonText').textContent = 'Hide Hints'; document.getElementById('chevronIcon').style.transform = 'rotate(180deg)';
    } else {
        hintsBox.style.display = 'none'; document.getElementById('hintsButtonText').textContent = 'Show Hints'; document.getElementById('chevronIcon').style.transform = 'rotate(0deg)';
    }
}

// Logic: Rate Question
function rateQuestion(rating) {
    const config = window.codingConfig;
    const goodBtn = document.getElementById('goodBtn'); 
    const badBtn = document.getElementById('badBtn');
    goodBtn.disabled = true; badBtn.disabled = true;
    
    fetch(config.routes.rate, {
        method: 'POST', 
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
        },
        body: JSON.stringify({ question_id: config.questionId, rating: rating })
    }).then(r => r.json()).then(d => {
        if (d.success) {
            if (rating === 'good') { goodBtn.style.background = 'linear-gradient(135deg, #10B981 0%, #34D399 100%)'; goodBtn.style.color = 'white'; badBtn.style.background = '#f5f5f5'; badBtn.style.color = '#666'; } 
            else { badBtn.style.background = 'linear-gradient(135deg, #EF4444 0%, #F87171 100%)'; badBtn.style.color = 'white'; goodBtn.style.background = '#f5f5f5'; goodBtn.style.color = '#666'; }
            document.getElementById('goodCount').textContent = d.good_ratings; document.getElementById('badCount').textContent = d.bad_ratings;
        }
    }).finally(() => { goodBtn.disabled = false; badBtn.disabled = false; });
}

// Logic: Skip/Next Question
function getNextQuestion() {
    const config = window.codingConfig;
    const skipBtn = event.target.closest('button');
    skipBtn.disabled = true;
    skipBtn.innerHTML = '<svg style="animation: spin 1s linear infinite;" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Loading...';
    
    let url = config.routes.random;
    const params = new URLSearchParams();
    
    // Add current question ID to exclude it
    params.append('exclude', config.questionId);
    
    if (config.language) params.append('language', config.language);
    if (config.level) params.append('level', config.level);
    if (config.chapter) params.append('topic', config.chapter);
    
    window.location.href = url + '?' + params.toString();
}

// Add animation styles dynamically
const style = document.createElement('style');
style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);