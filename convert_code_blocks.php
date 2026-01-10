<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$questions = App\Models\Question::where('questionCategory', 'competencyTest')->get();
$count = 0;

foreach ($questions as $question) {
    $content = $question->content;
    
    // Convert markdown code blocks (```language code ```) to HTML (<pre><code>code</code></pre>)
    $pattern = '/```[\w+]*\s*(.*?)\s*```/s';
    $replacement = '<pre><code>$1</code></pre>';
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if ($content !== $newContent) {
        $question->content = $newContent;
        $question->save();
        $count++;
        echo "Updated question ID: {$question->question_ID}\n";
    }
}

echo "\nTotal questions updated: $count\n";
