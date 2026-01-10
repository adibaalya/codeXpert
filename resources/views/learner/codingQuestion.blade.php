<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $question->title }} - CodeXpert</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.codingCSS')
    @include('layouts.navCSS')
</head>

<body class="code-test-body">
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

    <div class="code-test-container">
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

                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px;">
                            <span style="font-size: 14px; color: #666; font-weight: 600;">Rate this question:</span>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" onclick="rateQuestion('good')" id="goodBtn" class="rating-btn {{ $userRating === 'good' ? 'rating-btn-active-good' : '' }}" style="background: {{ $userRating === 'good' ? 'linear-gradient(135deg, #10B981 0%, #34D399 100%)' : '#f5f5f5' }}; color: {{ $userRating === 'good' ? 'white' : '#666' }}; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease;">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                                    <span id="goodCount">{{ $question->good_ratings ?? 0 }}</span>
                                </button>
                                <button type="button" onclick="rateQuestion('bad')" id="badBtn" class="rating-btn {{ $userRating === 'bad' ? 'rating-btn-active-bad' : '' }}" style="background: {{ $userRating === 'bad' ? 'linear-gradient(135deg, #EF4444 0%, #F87171 100%)' : '#f5f5f5' }}; color: {{ $userRating === 'bad' ? 'white' : '#666' }}; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease;">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="transform: rotate(180deg);"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                                    <span id="badCount">{{ $question->bad_ratings ?? 0 }}</span>
                                </button>
                            </div>
                        </div>

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

                <div style="flex-shrink: 0; padding: 20px 30px; background: white; border-top: 1px solid #f0f0f0; border-radius: 0 0 20px 20px;">
                    <div style="display: flex; gap: 15px;">
                        <button type="button" onclick="window.location.href='{{ route('learner.practice') }}'" style="background: white; color: #666; border: 2px solid #e0e0e0; padding: 12px 20px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; flex: 1; justify-content: center;" onmouseover="this.style.borderColor='#b0b0b0'; this.style.color='#333'" onmouseout="this.style.borderColor='#e0e0e0'; this.style.color='#666'">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                            Back to Practice
                        </button>
                        <button type="button" onclick="getNextQuestion()" style="background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%); color: white; border: none; padding: 12px 20px; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease; flex: 1; justify-content: center; box-shadow: 0 4px 15px rgba(118, 75, 162, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(118, 75, 162, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(118, 75, 162, 0.3)'">
                            Skip Question
                            <svg 
                                width="20" 
                                height="20" 
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24" 
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    stroke-width="2" 
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="code-test-right-panel">
            <form action="{{ route('learner.coding.submit') }}" method="POST" id="codeForm">
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
                            <button type="button" class="submit-test-btn" id="submitBtn" onclick="submitCode()" disabled>
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
        @php
            $functionParams = $question->function_parameters;
            if (is_string($functionParams)) {
                $functionParams = json_decode($functionParams, true) ?? [];
            } elseif (!is_array($functionParams)) {
                $functionParams = [];
            }
            
            $initialCode = app('App\Services\CodeTemplateService')->generateTemplate(
                $question->language ?? 'Python',
                $question->function_name ?? null,
                $functionParams,
                $question->return_type ?? 'bool'
            );
        @endphp
        
        window.codingConfig = {
            questionId: "{{ $question->question_ID }}",
            language: "{{ $question->language ?? 'Python' }}",
            level: "{{ $question->level ?? '' }}",
            chapter: "{{ $question->chapter ?? '' }}",
            initialCode: @json($initialCode),
            routes: {
                run: "{{ route('learner.coding.run') }}",
                rate: "{{ route('learner.coding.rate') }}",
                random: "{{ route('learner.coding.random') }}"
            }
        };
    </script>

    <script src="{{ asset('js/learner/coding-editor.js') }}"></script>
    
    <script>
        // Hide modal if page loaded with an error (redirect back scenario)
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('testingModal');
            if (modal) {
                modal.style.display = 'none';
            }
            
            // Reset submit button state if there was an error
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Submit Solution <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            }
            
            @if(session('error'))
                // Show error in output section
                const outputSection = document.getElementById('outputSection');
                const outputContent = document.getElementById('outputContent');
                if (outputSection && outputContent) {
                    outputSection.style.display = 'block';
                    outputContent.innerHTML = '<div style="background: #252526; padding: 12px 20px; border-bottom: 1px solid #3e3e3e;"><span style="color: #EF4444; font-weight: 700; font-size: 14px;">❌ SUBMISSION ERROR</span></div><div style="padding: 20px; color: #EF4444; font-family: \'Courier New\', monospace; font-size: 13px; white-space: pre-wrap;">{{ session('error') }}</div>';
                }
            @endif
        });
    </script>
</body>
</html>