@props(['question'])

<div class="problem-content">
    <div class="problem-header">
        <h2 class="problem-title">{{ $question->title }}</h2>
        <div class="problem-meta">
            <span class="difficulty-badge difficulty-{{ strtolower($question->level) }}">
                {{ $question->level }}
            </span>
            <span class="language-badge">
                {{ $question->language }}
            </span>
        </div>
    </div>

    <div class="problem-section">
        <h3 class="section-title">Problem Statement</h3>
        <div class="section-content">
            {!! nl2br(e($question->problem_statement ?? $question->description)) !!}
        </div>
    </div>

    @if($question->constraints)
    <div class="problem-section">
        <h3 class="section-title">Constraints</h3>
        <div class="section-content constraints">
            {!! nl2br(e($question->constraints)) !!}
        </div>
    </div>
    @endif

    @if($question->input && is_array($question->input) && count($question->input) > 0)
    <div class="problem-section">
        <h3 class="section-title">Example</h3>
        <div class="example-block">
            @php
                $firstTestCase = $question->input[0] ?? null;
                $firstExpectedOutput = $question->expected_output[0] ?? null;
            @endphp
            
            @if($firstTestCase)
            <div class="example-item">
                <strong>Input:</strong>
                <pre class="example-code">{{ is_array($firstTestCase) ? json_encode($firstTestCase, JSON_PRETTY_PRINT) : $firstTestCase }}</pre>
            </div>
            @endif
            
            @if($firstExpectedOutput)
            <div class="example-item">
                <strong>Expected Output:</strong>
                <pre class="example-code">{{ is_array($firstExpectedOutput) ? json_encode($firstExpectedOutput, JSON_PRETTY_PRINT) : $firstExpectedOutput }}</pre>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if($question->hint)
    <div class="problem-section">
        <h3 class="section-title">💡 Hint</h3>
        <div class="section-content hint">
            {!! nl2br(e($question->hint)) !!}
        </div>
    </div>
    @endif
</div>

<style>
.problem-content {
    padding: 24px;
    height: 100%;
    overflow-y: auto;
    background: #ffffff;
}

.problem-header {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #E2E8F0;
}

.problem-title {
    font-size: 24px;
    font-weight: 700;
    color: #1E293B;
    margin: 0 0 12px 0;
}

.problem-meta {
    display: flex;
    gap: 12px;
    align-items: center;
}

.difficulty-badge, .language-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}

.difficulty-beginner {
    background: #DCFCE7;
    color: #166534;
}

.difficulty-intermediate {
    background: #FEF3C7;
    color: #92400E;
}

.difficulty-advanced {
    background: #FEE2E2;
    color: #991B1B;
}

.language-badge {
    background: #E0E7FF;
    color: #3730A3;
}

.problem-section {
    margin-bottom: 24px;
}

.section-title {
    font-size: 16px;
    font-weight: 700;
    color: #1E293B;
    margin: 0 0 12px 0;
}

.section-content {
    font-size: 14px;
    line-height: 1.7;
    color: #475569;
}

.section-content.constraints {
    background: #F8FAFC;
    padding: 16px;
    border-radius: 8px;
    border-left: 4px solid #FF6B35;
}

.section-content.hint {
    background: #FFF7ED;
    padding: 16px;
    border-radius: 8px;
    border-left: 4px solid #F59E0B;
}

.example-block {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    padding: 16px;
}

.example-item {
    margin-bottom: 12px;
}

.example-item:last-child {
    margin-bottom: 0;
}

.example-item strong {
    display: block;
    margin-bottom: 6px;
    color: #1E293B;
    font-size: 13px;
}

.example-code {
    background: #1E293B;
    color: #E2E8F0;
    padding: 12px;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    overflow-x: auto;
    margin: 0;
}
</style>
