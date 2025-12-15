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
    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.dashboard') }}'">Dashboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.review') }}'">Review</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.generate') }}'">Generate</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.history') }}'">History</button>
                <button class="nav-item" onclick="window.location.href='{{ route('reviewer.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ Auth::guard('reviewer')->user()->username }}</div>
                    <div class="user-role">Reviewer</div>
                </div>
                <div class="user-avatar" onclick="toggleUserMenu(event)">
                    {{ strtoupper(substr(Auth::guard('reviewer')->user()->username, 0, 1)) }}{{ strtoupper(substr(Auth::guard('reviewer')->user()->username, 1, 1) ?? '') }}
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header">
                        <div class="user-dropdown-avatar">
                            {{ strtoupper(substr(Auth::guard('reviewer')->user()->username, 0, 2)) }}
                        </div>
                        <div>
                            <div class="user-dropdown-name">{{ Auth::guard('reviewer')->user()->username }}</div>
                            <div class="user-dropdown-email">{{ Auth::guard('reviewer')->user()->email }}</div>
                        </div>
                    </div>
                    
                    @php
                        $competencyResult = \App\Models\CompetencyTestResult::where('reviewer_ID', Auth::guard('reviewer')->user()->reviewer_ID)
                            ->where('passed', true)
                            ->latest()
                            ->first();
                    @endphp
                    
                    @if($competencyResult)
                    <div class="verified-badge-dropdown">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Verified Reviewer</span>
                    </div>
                    @endif
                    
                    <a href="{{ route('reviewer.competency.choose') }}" class="user-dropdown-item">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Take Competency Test</span>
                    </a>
                    
                    <div class="user-dropdown-divider"></div>
                    
                    <form method="POST" action="{{ route('reviewer.logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="user-dropdown-item logout">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                                <li>{{ $constraint }}</li>
                            @endforeach
                        @else
                            <li>{{ $question->constraints }}</li>
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
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                        </svg>
                        <span>{{ $question->hint }}</span>
                    </div>
                </div>
                @endif
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
            const code = editor.getValue();
            
            if (!code || code.trim() === '' || code.trim() === '// Write your solution here...') {
                alert('Please write your solution before running');
                return;
            }

            const runBtn = document.getElementById('runBtn');
            const outputSection = document.getElementById('outputSection');
            const outputContent = document.getElementById('outputContent');
            
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
            outputContent.textContent = 'Running your code in Docker container...\n\nPlease wait...';

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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    outputContent.textContent = data.output;
                } else {
                    outputContent.textContent = 'Error: ' + data.output;
                }
            })
            .catch(error => {
                outputContent.textContent = 'Error: Failed to execute code. Please try again.\n\n' + error.message;
            })
            .finally(() => {
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
    </script>

</body>
</html>
