@props(['question'])

<div class="test-cases-content">
    <div class="test-cases-header">
        <h3>Test Cases</h3>
        <p class="test-cases-subtitle">Your code will be tested against these cases</p>
    </div>

    @if($question->input && is_array($question->input) && count($question->input) > 0)
        <div class="test-cases-list">
            @foreach($question->input as $index => $testInput)
                @php
                    $expectedOutput = $question->expected_output[$index] ?? 'N/A';
                @endphp
                
                <div class="test-case-item">
                    <div class="test-case-header">
                        <div class="test-case-number">Test Case {{ $index + 1 }}</div>
                        @if($index === 0)
                            <span class="sample-badge">Sample</span>
                        @endif
                    </div>
                    
                    <div class="test-case-body">
                        <div class="test-case-section">
                            <strong>Input:</strong>
                            <pre class="test-case-code">{{ is_array($testInput) ? json_encode($testInput, JSON_PRETTY_PRINT) : $testInput }}</pre>
                        </div>
                        
                        <div class="test-case-section">
                            <strong>Expected Output:</strong>
                            <pre class="test-case-code">{{ is_array($expectedOutput) ? json_encode($expectedOutput, JSON_PRETTY_PRINT) : $expectedOutput }}</pre>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-test-cases">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>No test cases available for this question</p>
        </div>
    @endif
</div>

<style>
.test-cases-content {
    padding: 24px;
    height: 100%;
    overflow-y: auto;
    background: #ffffff;
}

.test-cases-header {
    margin-bottom: 24px;
}

.test-cases-header h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1E293B;
    margin: 0 0 8px 0;
}

.test-cases-subtitle {
    font-size: 14px;
    color: #64748B;
    margin: 0;
}

.test-cases-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.test-case-item {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s;
}

.test-case-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-color: #CBD5E1;
}

.test-case-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #E2E8F0;
    border-bottom: 1px solid #CBD5E1;
}

.test-case-number {
    font-weight: 600;
    color: #1E293B;
    font-size: 14px;
}

.sample-badge {
    background: #FF6B35;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.test-case-body {
    padding: 16px;
}

.test-case-section {
    margin-bottom: 12px;
}

.test-case-section:last-child {
    margin-bottom: 0;
}

.test-case-section strong {
    display: block;
    margin-bottom: 6px;
    color: #475569;
    font-size: 13px;
}

.test-case-code {
    background: #1E293B;
    color: #E2E8F0;
    padding: 12px;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    overflow-x: auto;
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.no-test-cases {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
    color: #94A3B8;
}

.no-test-cases svg {
    margin-bottom: 16px;
}

.no-test-cases p {
    font-size: 14px;
    margin: 0;
}
</style>
