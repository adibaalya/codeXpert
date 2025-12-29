<?php

namespace App\Services;

class CodeTemplateService
{
    /**
     * Generate the starter code wrapper with pre-made space for typing.
     * @param string $language
     * @return string
     */
    public function generateTemplate($language)
    {
        $lang = strtolower($language);

        switch ($lang) {
            case 'java':
            case 'c++':
            case 'cpp':
            case 'c#':
            case 'csharp':
                // Opens the curly braces and puts cursor on a new indented line
                return "class Solution {\n    \n}";

            case 'php':
                // PHP often looks cleaner with an extra gap
                return "class Solution {\n    \n}";

            case 'python':
                // Adds the indentation automatically after the colon
                return "class Solution:\n    ";

            case 'c':
                return "/**\n * Note: The returned array must be malloced, assume caller calls free().\n */\n";

            case 'javascript':
                return "/**\n * Write your solution here\n */\n";

            default:
                return "// Write your solution here...\n";
        }
    }
}