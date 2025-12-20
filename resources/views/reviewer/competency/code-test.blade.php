<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodeXpert - Coding Challenge</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.app')
    @include('layouts.competencyCSS')
    @include('layouts.navCSS')
</head>
<body class="code-test-body">

    <!-- Test Header -->
    <div class="test-header-wrapper">
        <div class="test-header">
            <div class="test-title">{{ session('test_language') }} Competency Test</div>
            <div class="test-subtitle">Verify your programming expertise</div>
            <span class="question-header-badge">PART 2: Coding Challenge</span>
        </div>
    </div>

    <!-- Coding Challenge Container -->
    <div class="code-test-container">
        <!-- Loading Modal for Test Execution -->
        <div id="testingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
            <div style="background: white; padding: 40px; border-radius: 12px; text-align: center; max-width: 500px;">
                <svg style="animation: spin 1s linear infinite; margin: 0 auto 20px;" width="48" height="48" fill="none" stroke="#4C6EF5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <h3 style="margin: 0 0 10px; font-size: 20px; color: #1a1a1a;">Evaluating Your Code</h3>
                <p style="margin: 0; color: #666; font-size: 14px;">Running all test cases in secure sandbox...</p>
                <p style="margin: 10px 0 0; color: #999; font-size: 12px;">This may take a few moments</p>
            </div>
        </div>

        <!-- Left Panel: Problem Description -->
        <div class="code-test-left-panel">
            <div class="problem-card">
                <!-- Tab Navigation -->
                <div class="problem-tabs">
                    <button class="problem-tab active" onclick="switchTab('problem')">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        Problem
                    </button>
                    <button class="problem-tab" onclick="switchTab('testcases')">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        Test Cases
                    </button>
                </div>

                <!-- Problem Tab Content -->
                <div id="problemTab" class="tab-content active">
                    <div class="problem-badge">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z"/>
                        </svg>
                        CODING CHALLENGE {{ $currentQuestion ?? 1 }} of {{ $totalQuestions ?? 3 }}
                    </div>

                    <h2 class="problem-title">{{ $question->title ?? 'Coding Challenge' }}</h2>

                    <div class="problem-section">
                        <h3 class="section-title">Problem</h3>
                        <p class="problem-description">{!! nl2br(e($question->description ?? 'Solve the coding problem below.')) !!}</p>
                    </div>

                    @if($question->constraints)
                    <div class="problem-section">
                        <h3 class="section-title">Constraints</h3>
                        <ul class="constraints-list">
                            @php
                                $constraints = is_string($question->constraints) ? json_decode($question->constraints, true) : $question->constraints;
                            @endphp
                            
                            @if(is_array($constraints))
                                @foreach($constraints as $constraint)
                                    <li>{!! nl2br(e($constraint)) !!}</li>
                                @endforeach
                            @else
                                <li>{!! nl2br(e($question->constraints)) !!}</li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    @if($question->hint)
                    <div class="problem-section">
                        <button type="button" class="hints-toggle-btn" id="hintsToggle" onclick="toggleHints()">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" class="hints-icon">
                                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                            </svg>
                            <span id="hintsButtonText">Show Hints</span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="chevron-icon" id="chevronIcon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div class="hints-box" id="hintsBox" style="display: none; margin-top: 12px;">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="float: left; margin-right: 12px;">
                                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                            </svg>
                            <div class="hints-box-content">
                                @php
                                    $hintText = $question->hint;
                                    // Check if hint contains numbered list format
                                    if (preg_match_all('/(\d+)\.\s*(.+?)(?=\d+\.|$)/s', $hintText, $matches, PREG_SET_ORDER)) {
                                        // It's a numbered list
                                        echo '<ol>';
                                        foreach ($matches as $match) {
                                            echo '<li>' . trim($match[2]) . '</li>';
                                        }
                                        echo '</ol>';
                                    } else {
                                        // Plain text
                                        echo '<p>' . nl2br(e($hintText)) . '</p>';
                                    }
                                @endphp
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Test Cases Tab Content -->
                <div id="testcasesTab" class="tab-content" style="display: none;">
                    <div class="problem-badge">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        TEST CASES
                    </div>

                    @php
                        $testInputs = is_string($question->input) ? json_decode($question->input, true) : $question->input;
                        $testOutputs = is_string($question->expected_output) ? json_decode($question->expected_output, true) : $question->expected_output;
                        $testCount = is_array($testInputs) ? count($testInputs) : 0;
                        
                        // Function to clean display text by removing markdown code blocks
                        function cleanDisplayText($text) {
                            if (is_array($text)) {
                                if (isset($text['input'])) {
                                    $text['input'] = cleanDisplayText($text['input']);
                                }
                                if (isset($text['output'])) {
                                    $text['output'] = cleanDisplayText($text['output']);
                                }
                                return $text;
                            }
                            if (is_string($text)) {
                                // Remove markdown code blocks
                                $text = preg_replace('/```[\w]*\n?/', '', $text);
                                $text = preg_replace('/```/', '', $text);
                                return trim($text);
                            }
                            return $text;
                        }
                    @endphp

                    @if($testCount > 0)
                        @for($i = 0; $i < $testCount; $i++)
                            @php
                                $inputDisplay = cleanDisplayText($testInputs[$i]);
                                $outputDisplay = isset($testOutputs[$i]) ? cleanDisplayText($testOutputs[$i]) : null;
                            @endphp
                            <div class="test-case-item">
                                <div class="test-case-header">
                                    <span class="test-case-title">Test Case {{ $i + 1 }}</span>
                                    @if($i == 0)
                                        <span class="test-case-badge">Example</span>
                                    @endif
                                </div>
                                
                                <div class="test-case-section">
                                    <div class="test-case-label">Input:</div>
                                    <pre class="test-case-code">{{ is_array($inputDisplay) ? json_encode($inputDisplay, JSON_PRETTY_PRINT) : $inputDisplay }}</pre>
                                </div>

                                @if($outputDisplay)
                                <div class="test-case-section">
                                    <div class="test-case-label">Expected Output:</div>
                                    <pre class="test-case-code">{{ is_array($outputDisplay) ? json_encode($outputDisplay, JSON_PRETTY_PRINT) : $outputDisplay }}</pre>
                                </div>
                                @endif
                            </div>
                        @endfor
                    @else
                        <div class="problem-section">
                            <p class="problem-description">Test cases will be used to evaluate your solution. Make sure your code handles all edge cases correctly.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Panel: Code Editor -->
        <div class="code-test-right-panel">
            <form action="{{ route('reviewer.competency.code.submit') }}" method="POST" id="codeForm">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->question_ID }}">
                <input type="hidden" name="solution" id="codeSolution">
                
                <div class="editor-card">
                    <div class="editor-header">
                        <div class="editor-tabs">
                            <div class="editor-tab active">
                                <div class="tab-dot red"></div>
                                <div class="tab-dot yellow"></div>
                                <div class="tab-dot green"></div>
                                <span class="tab-filename" id="tabFilename">solution.{{ $fileExtension ?? 'txt' }}</span>
                            </div>
                            <div class="language-badge">{{ $language ?? 'Java' }}</div>
                        </div>
                    </div>

                    <div class="editor-wrapper">
                        <div id="monacoEditor" class="monaco-editor-container"></div>
                    </div>

                    <!-- Output Section -->
                    <div id="outputSection" class="output-section" style="display: none;">
                        <div class="output-header">
                            <div class="output-title">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                                Output
                            </div>
                            <button type="button" class="close-output-btn" onclick="closeOutput()">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <pre id="outputContent" class="output-content"></pre>
                    </div>

                    <div class="editor-footer">
                        <div class="validation-message">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Your code will be evaluated for correctness and originality
                        </div>
                        
                        <div class="editor-actions">
                            <button type="button" class="run-code-btn" id="runBtn" onclick="runCode()">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                </svg>
                                Run Code
                            </button>
                            
                            <button type="button" class="submit-test-btn" id="submitBtn" onclick="submitCode()">
                                @if(($currentQuestion ?? 1) < ($totalQuestions ?? 3))
                                    Next Question
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                @else
                                    Submit Test
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Monaco Editor CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
    
    <script>
        let editor;
        const language = "{{ $language ?? 'Java' }}";

        // Language mappings for Monaco Editor
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

        // Get previous solution if exists
        const previousSolution = @json(session('code_solutions.' . $question->question_ID, ''));

        // Initialize Monaco Editor
        require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
        
        require(['vs/editor/editor.main'], function() {
            const monacoLanguage = languageMap[language] || 'plaintext';
            const initialCode = previousSolution || '// Write your solution here...\n\n';
            
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
                // Enhanced formatting options
                tabSize: 4,
                insertSpaces: true,
                detectIndentation: true,
                formatOnPaste: true,
                formatOnType: true,
                autoIndent: 'full',
                trimAutoWhitespace: true,
                bracketPairColorization: {
                    enabled: true
                },
                guides: {
                    indentation: true,
                    bracketPairs: true
                },
                suggest: {
                    snippetsPreventQuickSuggestions: false
                }
            });

            // Add keyboard shortcut for formatting (Shift+Alt+F or Cmd+K Cmd+F)
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

            // Format code on initial load if it exists
            if (previousSolution) {
                setTimeout(() => {
                    editor.getAction('editor.action.formatDocument').run();
                }, 500);
            }
        });

        // Submit code
        function submitCode() {
            const code = editor.getValue();
            
            if (!code || code.trim() === '' || code.trim() === '// Write your solution here...') {
                alert('Please write your solution before submitting');
                return;
            }

            // Set the solution in the hidden input
            document.getElementById('codeSolution').value = code;

            const submitBtn = document.getElementById('submitBtn');
            const testingModal = document.getElementById('testingModal');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Submitting...</span> <svg style="animation: spin 1s linear infinite;" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';

            // Show the loading modal
            testingModal.style.display = 'flex';

            // Submit the form
            document.getElementById('codeForm').submit();
        }

        // Run code
        function runCode() {
            console.log('=== runCode function called ===');
            
            const code = editor.getValue();
            console.log('Code length:', code ? code.length : 0);
            
            if (!code || code.trim() === '' || code.trim() === '// Write your solution here...') {
                alert('Please write your solution before running');
                return;
            }

            const runBtn = document.getElementById('runBtn');
            const outputSection = document.getElementById('outputSection');
            const outputContent = document.getElementById('outputContent');
            
            console.log('Elements found:', {
                runBtn: !!runBtn,
                outputSection: !!outputSection,
                outputContent: !!outputContent
            });
            
            // Disable run button and show loading state
            runBtn.disabled = true;
            runBtn.innerHTML = `
                <svg style="animation: spin 1s linear infinite;" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Running...
            `;
            
            // Show output section with loading message
            outputSection.style.display = 'block';
            outputContent.textContent = 'Running your code against all test cases...\n\nPlease wait...';
            
            console.log('Making fetch request to:', '{{ route("reviewer.competency.code.run") }}');
            console.log('Question ID:', '{{ $question->question_ID }}');

            // Make AJAX request to run code
            fetch('{{ route("reviewer.competency.code.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    solution: code,
                    question_id: '{{ $question->question_ID }}'
                })
            })
            .then(response => {
                console.log('✓ Fetch successful! Response status:', response.status);
                console.log('Response OK:', response.ok);
                console.log('Response type:', response.type);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text();
            })
            .then(text => {
                console.log('✓ Response text received, length:', text.length);
                console.log('First 200 chars:', text.substring(0, 200));
                
                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(text);
                    console.log('✓ Parsed JSON successfully:', data);
                } catch (e) {
                    console.error('✗ Failed to parse JSON:', e);
                    outputContent.textContent = '❌ Server returned invalid JSON:\n\n' + text;
                    return;
                }
                
                if (data.success) {
                    console.log('✓ Execution successful!');
                    console.log('Test results:', data.testResults);
                    
                    // Build LeetCode-style tabbed output interface
                    let output = '';
                    
                    // Header
                    output += `<div style="background: #252526; padding: 12px 20px; border-bottom: 1px solid #3e3e3e; display: flex; align-items: center; gap: 10px;">`;
                    output += `<span style="color: #10B981; font-weight: 700; font-size: 14px;">✓ Accepted</span>`;
                    output += `<span style="color: #666; font-size: 12px;">Runtime: 0 ms</span>`;
                    output += `</div>`;
                    
                    // Test case tabs
                    output += `<div style="background: #1e1e1e; padding: 15px 20px; border-bottom: 1px solid #3e3e3e;">`;
                    output += `<div style="display: flex; gap: 12px; flex-wrap: wrap;">`;
                    
                    if (data.testResults && Array.isArray(data.testResults)) {
                        data.testResults.forEach((testResult, index) => {
                            const isFirst = index === 0;
                            output += `<button onclick="showTestCase(${index})" id="tab-${index}" style="
                                background: ${isFirst ? '#2d2d2d' : 'transparent'}; 
                                border: none; 
                                color: #10B981; 
                                padding: 8px 16px; 
                                border-radius: 6px; 
                                font-size: 13px; 
                                font-weight: 600; 
                                cursor: pointer; 
                                display: flex; 
                                align-items: center; 
                                gap: 6px;
                                transition: background 0.2s;
                                font-family: 'Inter', sans-serif;">
                                <span style="font-size: 12px;">✓</span> Case ${testResult.test_number}
                            </button>`;
                        });
                    }
                    
                    output += `</div></div>`;
                    
                    // Test case content area
                    output += `<div style="background: #1e1e1e; padding: 20px; color: #d4d4d4; font-family: 'Courier New', monospace; font-size: 13px; max-height: 300px; overflow-y: auto;">`;
                    
                    if (data.testResults && Array.isArray(data.testResults)) {
                        data.testResults.forEach((testResult, index) => {
                            const isFirst = index === 0;
                            output += `<div id="case-${index}" style="display: ${isFirst ? 'block' : 'none'};">`;
                            
                            // Input section
                            output += `<div style="margin-bottom: 20px;">`;
                            output += `<div style="color: #888; font-size: 12px; margin-bottom: 8px; font-weight: 600;">Input</div>`;
                            output += `<div style="background: #252526; padding: 12px 15px; border-radius: 6px; border-left: 3px solid #4C6EF5; white-space: pre-wrap; word-wrap: break-word;">${escapeHtml(testResult.input)}</div>`;
                            output += `</div>`;
                            
                            // Output section
                            output += `<div style="margin-bottom: 20px;">`;
                            output += `<div style="color: #888; font-size: 12px; margin-bottom: 8px; font-weight: 600;">Output</div>`;
                            output += `<div style="background: #252526; padding: 12px 15px; border-radius: 6px; border-left: 3px solid #10B981;">${escapeHtml(testResult.output)}</div>`;
                            output += `</div>`;
                            
                            output += `</div>`;
                        });
                    }
                    
                    output += `</div>`;
                    
                    outputContent.innerHTML = output;
                } else {
                    // Show error
                    console.log('✗ Execution failed:', data.output);
                    let errorOutput = '<div style="background: #252526; padding: 12px 20px; border-bottom: 1px solid #3e3e3e; display: flex; align-items: center; gap: 10px;">';
                    errorOutput += '<span style="color: #EF4444; font-weight: 700; font-size: 14px;">❌ EXECUTION ERROR</span>';
                    errorOutput += '</div>';
                    errorOutput += '<div style="padding: 20px; color: #EF4444; font-family: \'Courier New\', monospace; font-size: 13px; white-space: pre-wrap;">';
                    errorOutput += escapeHtml(data.output || 'Unknown error occurred');
                    errorOutput += '</div>';
                    outputContent.innerHTML = errorOutput;
                }
            })
            .catch(error => {
                console.error('✗✗✗ FETCH ERROR:', error);
                console.error('Error stack:', error.stack);
                outputContent.textContent = '❌ ERROR: Failed to execute code\n\n' + 
                    'Error: ' + error.message + '\n\n' +
                    'Check browser console (F12) for details.\n\n' +
                    'Common causes:\n' +
                    '- Not logged in (session expired)\n' +
                    '- CSRF token invalid\n' +
                    '- Server error (check Laravel logs)\n' +
                    '- Network connectivity issue';
            })
            .finally(() => {
                console.log('=== Request complete, re-enabling button ===');
                
                // Re-enable run button
                runBtn.disabled = false;
                runBtn.innerHTML = `
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                    </svg>
                    Run Code
                `;
            });
        }

        // Close output section
        function closeOutput() {
            const outputSection = document.getElementById('outputSection');
            outputSection.style.display = 'none';
        }

        // User menu toggle
        function toggleUserMenu(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown && dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
        });

        // Add spin animation
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
        document.head.appendChild(style);

        // Prevent page navigation warnings
        window.addEventListener('beforeunload', function(e) {
            const code = editor.getValue();
            if (code && code.trim() !== '' && code.trim() !== '// Write your solution here...') {
                // Only warn if there's actual code written
                return;
            }
        });

        // Toggle hints section
        function toggleHints() {
            const hintsBox = document.getElementById('hintsBox');
            const hintsButtonText = document.getElementById('hintsButtonText');
            const chevronIcon = document.getElementById('chevronIcon');
            
            if (hintsBox.style.display === 'none') {
                hintsBox.style.display = 'block';
                hintsButtonText.textContent = 'Hide Hints';
                chevronIcon.style.transform = 'rotate(180deg)';
            } else {
                hintsBox.style.display = 'none';
                hintsButtonText.textContent = 'Show Hints';
                chevronIcon.style.transform = 'rotate(0deg)';
            }
        }

        // Switch tabs in the left panel
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

        // Escape HTML for safe rendering
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Show specific test case - prevent form submission
        function showTestCase(index) {
            // Prevent default button behavior
            event.preventDefault();
            event.stopPropagation();
            
            const testCases = document.querySelectorAll('[id^="case-"]');
            const tabs = document.querySelectorAll('[id^="tab-"]');
            
            testCases.forEach((testCase, i) => {
                testCase.style.display = i === index ? 'block' : 'none';
            });
            
            tabs.forEach((tab, i) => {
                tab.style.background = i === index ? '#2d2d2d' : 'transparent';
            });
            
            return false; // Extra safety to prevent form submission
        }
    </script>

</body>
</html>
