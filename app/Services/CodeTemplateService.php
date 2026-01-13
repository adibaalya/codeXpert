<?php

namespace App\Services;

class CodeTemplateService
{
    /**
     * Generate template for code editor
     * 
     * @param string $language The programming language
     * @param string|null $functionName The function name (defaults to "solve")
     * @param array $parameters Array of parameters ['name' => 'type']
     * @param string $returnType Return type (defaults to 'bool')
     * @return string The generated template code
     */
    public function generateTemplate($language, $functionName = null, $parameters = [], $returnType = 'bool')
    {
        // Use "solve" as default function name if not provided
        $functionName = !empty($functionName) ? $functionName : 'solve';
        
        $lang = strtolower($language);

        switch ($lang) {
            case 'php':
                return $this->generatePHPTemplate($functionName, $parameters, $returnType);

            case 'java':
                return $this->generateJavaTemplate($functionName, $parameters, $returnType);
                
            case 'c#':
            case 'csharp':
                return $this->generateCSharpTemplate($functionName, $parameters, $returnType);

            case 'c++':
            case 'cpp':
                return $this->generateCppTemplate($functionName, $parameters, $returnType);

            case 'python':
                return $this->generatePythonTemplate($functionName, $parameters, $returnType);

            case 'javascript':
                return $this->generateJavaScriptTemplate($functionName, $parameters, $returnType);

            case 'c':
                return $this->generateCTemplate($functionName, $parameters, $returnType);

            default:
                return "// Write your code here";
        }
    }
    
    public function generatePHPTemplate($functionName, $parameters, $returnType)
    {
        $functionName = !empty($functionName) ? $functionName : 'maxConcurrentEvents';
        
        // If parameters are provided (e.g., ['events' => 'array']), use them
        if (!empty($parameters)) {
            $params = $this->formatPHPParameters($parameters);
        } else {
            $params = "\$events"; // Default fallback
        }
        
        return "<?php\n\nclass Solution {\n    /**\n     * @param array \$events\n     * @return int\n     */\n    function {$functionName}({$params}): int {\n        // Write your code here\n    }\n}";
    }
    
    /**
     * Generate Java template
     */
    private function generateJavaTemplate($functionName, $parameters, $returnType)
    {
        $javaReturnType = $this->mapToJavaType($returnType);
        $params = $this->formatJavaParameters($parameters);
        return "class Solution {\n    public {$javaReturnType} {$functionName}({$params}) {\n        // Write your code here\n    }\n}";
    }
    
    /**
     * Generate C# template
     */
    private function generateCSharpTemplate($functionName, $parameters, $returnType)
    {
        $csharpReturnType = $this->mapToCSharpType($returnType);
        $params = $this->formatCSharpParameters($parameters);
        return "class Solution {\n    public {$csharpReturnType} {$functionName}({$params}) {\n        // Write your code here\n    }\n}";
    }
    
    /**
     * Generate C++ template
     */
    private function generateCppTemplate($functionName, $parameters, $returnType)
    {
        $cppReturnType = $this->mapToCppType($returnType);
        $params = $this->formatCppParameters($parameters);
        return "class Solution {\npublic:\n    {$cppReturnType} {$functionName}({$params}) {\n        // Write your code here\n    }\n};";
    }
    
    /**
     * Generate Python template (no pass keyword, 4 spaces indentation)
     */
    private function generatePythonTemplate($functionName, $parameters, $returnType)
    {
        // No class, no 'self', no indentation needed
        return "def {$functionName}( ):\n    # Write your code here\n    return None";
    }
    
    /**
     * Generate JavaScript template
     */
    private function generateJavaScriptTemplate($functionName, $parameters, $returnType)
    {
        $params = $this->formatJavaScriptParameters($parameters);
        
        // We use the 'function' keyword for valid syntax
        // We add '...' (rest operator) before the params to collect 
        // the spread arguments from CodeExecutionService into a single array
        return "function {$functionName}(...{$params}) {\n    // Write your code here\n}";
    }
    
    /**
     * Generate C template (standalone function)
     */
    private function generateCTemplate($functionName, $parameters, $returnType)
    {
        $cReturnType = $this->mapToCType($returnType);
        $params = $this->formatCParameters($parameters);
        return "{$cReturnType} {$functionName}({$params}) {\n    // Write your code here\n}";
    }
    
    /**
     * Map generic type to C++ type
     */
    private function mapToCppType($type)
    {
        $typeMap = [
            'bool' => 'bool',
            'boolean' => 'bool',
            'int' => 'int',
            'integer' => 'int',
            'float' => 'double',
            'double' => 'double',
            'string' => 'string',
            'array' => 'vector<int>',
            'int[]' => 'vector<int>',
            'string[]' => 'vector<string>',
        ];
        
        return $typeMap[strtolower($type)] ?? 'int';
    }
    
    /**
     * Map generic type to Java type
     */
    private function mapToJavaType($type)
    {
        $typeMap = [
            'bool' => 'boolean',
            'boolean' => 'boolean',
            'int' => 'int',
            'integer' => 'int',
            'float' => 'double',
            'double' => 'double',
            'string' => 'String',
            'array' => 'int[]',
            'int[]' => 'int[]',
            'string[]' => 'String[]',
        ];
        
        return $typeMap[strtolower($type)] ?? 'int';
    }
    
    /**
     * Map generic type to C type
     */
    private function mapToCType($type)
    {
        $typeMap = [
            'bool' => 'bool',
            'boolean' => 'bool',
            'int' => 'int',
            'integer' => 'int',
            'float' => 'double',
            'double' => 'double',
            'string' => 'char*',
            'array' => 'int*',
            'int[]' => 'int*',
        ];
        
        return $typeMap[strtolower($type)] ?? 'int';
    }
    
    /**
     * Map generic type to C# type
     */
    private function mapToCSharpType($type)
    {
        $typeMap = [
            'bool' => 'bool',
            'boolean' => 'bool',
            'int' => 'int',
            'integer' => 'int',
            'float' => 'double',
            'double' => 'double',
            'string' => 'string',
            'array' => 'int[]',
            'int[]' => 'int[]',
            'string[]' => 'string[]',
        ];
        
        return $typeMap[strtolower($type)] ?? 'int';
    }
    
    /**
     * Format parameters for C++
     */
    private function formatCppParameters($parameters)
    {
        if (empty($parameters)) return '';
        
        $formatted = [];
        foreach ($parameters as $name => $type) {
            $cppType = $this->mapToCppType($type);
            // For vectors, pass by reference
            if (strpos($cppType, 'vector') !== false) {
                $formatted[] = "{$cppType}& {$name}";
            } else {
                $formatted[] = "{$cppType} {$name}";
            }
        }
        return implode(', ', $formatted);
    }
    
    /**
     * Format parameters for Java
     */
    private function formatJavaParameters($parameters)
    {
        if (empty($parameters)) return '';
        
        $formatted = [];
        foreach ($parameters as $name => $type) {
            $javaType = $this->mapToJavaType($type);
            $formatted[] = "{$javaType} {$name}";
        }
        return implode(', ', $formatted);
    }
    
    /**
     * Format parameters for Python
     */
    private function formatPythonParameters($parameters)
    {
        if (empty($parameters)) return '';
        
        $names = array_keys($parameters);
        return ', ' . implode(', ', $names);
    }
    
    /**
     * Format parameters for JavaScript
     */
    private function formatJavaScriptParameters($parameters)
    {
        if (empty($parameters)) return '';
        
        $names = array_keys($parameters);
        return implode(', ', $names);
    }
    
    /**
     * Format parameters for C - CodeTemplateService.php
     */
    private function formatCParameters($parameters)
    {
        if (empty($parameters)) return '';
        
        $formatted = [];
        foreach ($parameters as $name => $type) {
            $cType = $this->mapToCType($type);
            
            // If it's a pointer or array, force a pair: (data, size)
            if ($cType === 'int*' || $cType === 'char*' || strpos($type, '[]') !== false) {
                $formatted[] = "{$cType} {$name}";
                $formatted[] = "int {$name}Size"; 
            } else {
                $formatted[] = "{$cType} {$name}";
            }
        }
        return implode(', ', $formatted);
    }
    
    /**
     * Format parameters for PHP
     */
    private function formatPHPParameters($parameters)
    {
        if (empty($parameters)) return '';
        
        $formatted = [];
        foreach ($parameters as $name => $type) {
            $formatted[] = "\${$name}";
        }
        return implode(', ', $formatted);
    }
    
    /**
     * Format parameters for C#
     */
    private function formatCSharpParameters($parameters)
    {
        if (empty($parameters)) return '';
        
        $formatted = [];
        foreach ($parameters as $name => $type) {
            $csharpType = $this->mapToCSharpType($type);
            $formatted[] = "{$csharpType} {$name}";
        }
        return implode(', ', $formatted);
    }
}
