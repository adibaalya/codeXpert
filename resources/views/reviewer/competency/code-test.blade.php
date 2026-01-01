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
    <script src="{{ asset('js/reviewer/competency.js') }}"></script>
    @include('layouts.app')
    @include('layouts.competencyCSS')
</head>
<body class="code-test-body-reviewer">

    <!-- Loading Modal for Testing -->
    <div id="testingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: white; padding: 40px 50px; border-radius: 16px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 500px;">
            <div style="margin-bottom: 24px;">
                <svg style="animation: spin 1s linear infinite;" width="60" height="60" fill="none" stroke="#4C6EF5" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <h3 style="font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 12px;">Testing Your Solution</h3>
            <p style="color: #666; font-size: 14px; line-height: 1.6;">
                Running your code against all test cases...<br>
                <span style="font-weight: 600; color: #4C6EF5;">This may take a few moments</span>
            </p>
            <div style="margin-top: 20px; padding: 12px; background: #F3F4F6; border-radius: 8px;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px; color: #666; font-size: 13px;">
                    <div class="dot-pulse"></div>
                    <span>Compiling and executing...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Header -->
    <div class="test-header-wrapper" style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 20px;">
        
        <!-- Display any error messages -->
        @if(session('error'))
            <div style="position: fixed; top: 20px; right: 20px; background: #FEE2E2; border: 2px solid #EF4444; color: #DC2626; padding: 16px 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; max-width: 400px;">
                <div style="display: flex; align-items: start; gap: 12px;">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <div style="font-weight: 700; margin-bottom: 4px;">Error</div>
                        <div>{{ session('error') }}</div>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="test-header-left">
            <div class="test-title">
                {{ session('test_language') }} Competency Test
            </div>
            <div class="test-subtitle" style="color: #666; font-size: 14px; margin-top: 4px;">
                Part 2: Coding Challenges
            </div>
        </div>

        <div class="timer-card" >
            <div class="timer-label">
                Time Remaining
            </div>
            
            <div class="timer-value-wrapper" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="rgb(92, 33, 195)" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                
                <div class="timer-display" id="timer" style="font-size: 20px; font-weight: 700; color: #111827; font-feature-settings: 'tnum';">
                    45:00
                </div>
            </div>
        </div>

    </div>

    <!-- Coding Challenge Container -->
    <div class="code-test-container-reviewer">
        <div class="code-test-left-panel">
            
            <div class="problem-card" style="padding: 0 !important; display: flex; flex-direction: column; height: calc(100vh - 60px); min-height: 700px; overflow: hidden; position: relative;">
                
                <div class="problem-tabs" style="padding: 20px 30px 0 30px; flex-shrink: 0;">
                    <button class="problem-tab active" onclick="switchTab('problem')">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                        Problem
                    </button>
                    <button class="problem-tab" onclick="switchTab('testcases')">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                        Test Cases
                    </button>
                </div>

                <div style="flex: 1; overflow-y: auto; overflow-x: hidden; padding: 30px;">
                    
                    <div id="problemTab" style="display: block;">
                        <div class="problem-badge">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z"/></svg>
                            CODING CHALLENGE
                        </div>

                        <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
                            @if($question->chapter)
                            <span style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">{{ $question->chapter }}</span>
                            @endif
                            @if($question->level)
                            <span style="background: {{ $question->level === 'Easy' ? 'linear-gradient(135deg, #10B981 0%, #34D399 100%)' : ($question->level === 'Medium' ? 'linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%)' : 'linear-gradient(135deg, #EF4444 0%, #F87171 100%)') }}; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">{{ $question->level }}</span>
                            @endif
                            @if($question->language)
                            <span style="background: linear-gradient(135deg, #FF6B6B 0%, #FFB366 100%); color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">{{ $question->language }}</span>
                            @endif
                        </div>

                        <h2 class="problem-title">{{ $question->title ?? 'Coding Challenge' }}</h2>

                        <div class="problem-section">
                            <h3 class="section-title">Description</h3>
                            <p class="problem-description" style="margin-bottom: 16px; line-height: 1.6;">{!! nl2br(e($question->description ?? 'Solve the coding problem below.')) !!}</p>
                            <div style="margin-top: 16px;">
                                <p class="problem-description" style="font-weight: 600; margin-bottom: 8px;">Problem Statement:</p>
                                <p class="problem-description">{!! nl2br(e($question->problem_statement ?? 'No problem statement available.')) !!}</p>
                            </div>
                        </div>

                        @if($question->constraints)
                        <div class="problem-section">
                            <h3 class="section-title">Constraints</h3>
                            <ul class="constraints-list">
                                @php $constraints = is_string($question->constraints) ? json_decode($question->constraints, true) : $question->constraints; @endphp
                                @if(is_array($constraints))
                                    @foreach($constraints as $constraint) <li>{!! nl2br(e($constraint)) !!}</li> @endforeach
                                @else
                                    <li>{!! nl2br(e($question->constraints)) !!}</li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        @if($question->hint)
                        <div class="problem-section">
                            <button type="button" class="hints-toggle-btn" id="hintsToggle" onclick="toggleHints()">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" class="hints-icon"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/></svg>
                                <span id="hintsButtonText">Show Hints</span>
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="chevron-icon" id="chevronIcon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="hints-box" id="hintsBox" style="display: none; margin-top: 12px;">
                                <div class="hints-box-content">
                                    @php
                                        $hintText = $question->hint;
                                        if (preg_match_all('/(\d+)\.\s*(.+?)(?=\d+\.|$)/s', $hintText, $matches, PREG_SET_ORDER)) {
                                            echo '<ol>'; foreach ($matches as $match) { echo '<li>' . trim($match[2]) . '</li>'; } echo '</ol>';
                                        } else {
                                            echo '<p>' . nl2br(e($hintText)) . '</p>';
                                        }
                                    @endphp
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div id="testcasesTab" style="display: none;">
                        <div class="problem-badge">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                            TEST CASES
                        </div>

                        @php
                            $testInputs = is_string($question->input) ? json_decode($question->input, true) : $question->input;
                            $testOutputs = is_string($question->expected_output) ? json_decode($question->expected_output, true) : $question->expected_output;
                            $testCount = is_array($testInputs) ? count($testInputs) : 0;
                            
                            $cleanDisplayText = function($text) use (&$cleanDisplayText) {
                                if (is_array($text)) {
                                    $cleaned = []; foreach ($text as $key => $value) { $cleaned[$key] = $cleanDisplayText($value); } return $cleaned;
                                }
                                if (is_string($text)) { return trim(str_replace('```', '', preg_replace('/```[\w]*\n?/', '', $text))); }
                                return $text;
                            };
                        @endphp

                        @if($testCount > 0)
                            @for($i = 0; $i < $testCount; $i++)
                                @php
                                    $inputDisplay = $cleanDisplayText($testInputs[$i]);
                                    $outputDisplay = isset($testOutputs[$i]) ? $cleanDisplayText($testOutputs[$i]) : null;
                                @endphp
                                <div class="test-case-item">
                                    <div class="test-case-header">
                                        <span class="test-case-title">Test Case {{ $i + 1 }}</span>
                                        @if($i == 0) <span class="test-case-badge">Example</span> @endif
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
        </div>

        <div class="code-test-right-panel">
            <form action="{{ route('reviewer.competency.code.submit') }}" method="POST" id="codeForm">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->question_ID }}">
                <input type="hidden" name="solution" id="codeSolution">
                <input type="hidden" name="language" id="languageInput" value="{{ $question->language ?? 'Python' }}">
                
                <div class="editor-card">
                    <div class="editor-header">
                        <div class="editor-tabs">
                            <div class="editor-tab active">
                                <div class="tab-dot red"></div><div class="tab-dot yellow"></div><div class="tab-dot green"></div>
                                <span class="tab-filename" id="tabFilename">solution.{{ strtolower($question->language ?? 'py') }}</span>
                            </div>
                            <div class="language-badge">{{ $question->language ?? 'Python' }}</div>
                        </div>
                    </div>
                    <div class="editor-wrapper">
                        <div id="monacoEditor" class="monaco-editor-container"></div>
                    </div>
                    <div id="outputSection" class="output-section" style="display: none;">
                        <div class="output-header">
                            <div class="output-title"><svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg> Output</div>
                            <button type="button" class="close-output-btn" onclick="closeOutput()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <pre id="outputContent" class="output-content"></pre>
                    </div>
                    <div class="editor-footer">
                        <div class="validation-message">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg> 
                            <span id="validationText">⚠️ Run your code first, then submit when ready</span>
                        </div>
                        <div class="editor-actions">
                            <button type="button" class="run-code-btn" id="runBtn" onclick="runCode()">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                </svg> 
                                Run Code
                            </button>
                            <button type="button" class="submit-test-btn" id="submitBtn" onclick="submitCode()" disabled style="opacity: 0.5; cursor: not-allowed;">
                                Submit Solution 
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
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
        window.competencyCodeConfig = {
            questionId: "{{ $question->question_ID }}",
            language: "{{ $question->language ?? 'Java' }}",
            remainingSeconds: {{ $remainingSeconds ?? 2700 }},
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            routes: {
                run: "{{ route('reviewer.competency.code.run') }}"
            }
        };

        // 2. Initialize Monaco (Must stay in Blade due to require.config scope)
        let editor; 
        const previousSolution = @json(session('code_solutions.' . $question->question_ID . '.solution', ''));
        const languageMap = { 'Java': 'java', 'Python': 'python', 'JavaScript': 'javascript', 'C++': 'cpp', 'C#': 'csharp', 'PHP': 'php', 'C': 'c' };

        require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
        
        require(['vs/editor/editor.main'], function() {
            const monacoLanguage = languageMap["{{ $question->language ?? 'Java' }}"] || 'plaintext';
            // Access template service directly via Blade
            const codeTemplate = @json(app('App\Services\CodeTemplateService')->generateTemplate($question->language ?? 'Python'));
            
            // Assign to window so external JS can access it
            window.editor = monaco.editor.create(document.getElementById('monacoEditor'), {
                value: previousSolution || codeTemplate,
                language: monacoLanguage,
                theme: 'vs-dark',
                automaticLayout: true,
                fontSize: 14,
                minimap: { enabled: true },
                scrollBeyondLastLine: false,
                tabSize: 4
            });

            // Format document shortcut
            window.editor.addAction({
                id: 'format-document',
                label: 'Format Document',
                keybindings: [monaco.KeyMod.CtrlCmd | monaco.KeyMod.Shift | monaco.KeyCode.KeyF],
                run: function(ed) { ed.getAction('editor.action.formatDocument').run(); }
            });
        });

        // Add spin animation dynamically
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    </script>

</body>
</html>
