# Multi-Language Code Execution Environment Setup Guide

## Overview

This guide provides a complete setup for a **Docker-based code execution environment** that supports **Java, Python, C++, JavaScript, PHP, and SQL**. The environment behaves exactly like Eclipse/VSCode/PhpStorm/MySQL Workbench, ensuring students can write and run code without platform-specific changes.

## Features

✅ **6 Languages Supported**: Java, Python, C++, JavaScript, PHP, SQL  
✅ **IDE-like Behavior**: Code runs exactly as in Eclipse/VSCode/PhpStorm/MySQL Workbench  
✅ **Standard Input/Output**: Multi-line input, space-separated values, array inputs work naturally  
✅ **Security**: Sandboxed execution with CPU, memory, and timeout limits  
✅ **Large Dataset Support**: Handles up to 10^6 elements  
✅ **SQL Support**: In-memory SQLite database for safe testing  
✅ **Monaco Editor Integration**: Full frontend support  

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Laravel Backend                         │
│  ┌───────────────────────────────────────────────────────┐  │
│  │         CodeExecutionService.php                      │  │
│  │  - Validates code and input                          │  │
│  │  - Creates temporary files                           │  │
│  │  - Runs Docker container                             │  │
│  │  - Returns output/errors                             │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Docker Container                          │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Ubuntu 22.04 with:                                   │  │
│  │  - OpenJDK 11 (Java)                                  │  │
│  │  - Python 3.10 (recursion limit: 100,000)            │  │
│  │  - GCC/G++ 11 (C/C++)                                │  │
│  │  - Node.js 18 (JavaScript)                           │  │
│  │  - PHP 8.1 CLI                                       │  │
│  │  - SQLite3 (SQL)                                     │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌───────────────────┐  ┌──────────────────────────────┐    │
│  │    run.sh         │  │    sql-runner.sh             │    │
│  │  - Detects lang   │  │  - Executes SQL queries      │    │
│  │  - Compiles code  │  │  - Returns table results     │    │
│  │  - Runs with      │  │  - Supports CRUD operations  │    │
│  │    stdin input    │  └──────────────────────────────┘    │
│  └───────────────────┘                                       │
└─────────────────────────────────────────────────────────────┘
```

---

## Installation Steps

### 1. Docker Setup

The Docker image is already built. To verify:

```bash
docker images | grep code-sandbox
```

You should see:
```
code-sandbox    latest    366b97eecbeb    Just now    1.2GB
```

### 2. Backend Configuration

The `CodeExecutionService` is already configured at:
```
app/Services/CodeExecutionService.php
```

### 3. Test the Environment

Run tests for each language:

#### **Java Test**
```bash
docker run --rm -i \
  --cpus="1" \
  --memory="512m" \
  --network=none \
  code-sandbox \
  /bin/bash /code/run.sh java <<'EOF'
public class Main {
    public static void main(String[] args) {
        java.util.Scanner sc = new java.util.Scanner(System.in);
        String name = sc.nextLine();
        int age = sc.nextInt();
        System.out.println("Hello " + name + ", age: " + age);
    }
}
---STDIN---
John Doe
25
EOF
```

Expected output: `Hello John Doe, age: 25`

#### **Python Test**
```bash
docker run --rm -i \
  --cpus="1" \
  --memory="512m" \
  --network=none \
  code-sandbox \
  /bin/bash /code/run.sh python <<'EOF'
name = input()
age = int(input())
print(f"Hello {name}, age: {age}")
---STDIN---
Jane Smith
30
EOF
```

Expected output: `Hello Jane Smith, age: 30`

#### **C++ Test**
```bash
docker run --rm -i \
  --cpus="1" \
  --memory="512m" \
  --network=none \
  code-sandbox \
  /bin/bash /code/run.sh cpp <<'EOF'
#include <iostream>
#include <string>
using namespace std;
int main() {
    string name;
    int age;
    getline(cin, name);
    cin >> age;
    cout << "Hello " << name << ", age: " << age << endl;
    return 0;
}
---STDIN---
Bob Johnson
35
EOF
```

Expected output: `Hello Bob Johnson, age: 35`

#### **JavaScript Test**
```bash
docker run --rm -i \
  --cpus="1" \
  --memory="512m" \
  --network=none \
  code-sandbox \
  /bin/bash /code/run.sh javascript <<'EOF'
const readline = require('readline');
const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});
const lines = [];
rl.on('line', (line) => lines.push(line));
rl.on('close', () => {
    const name = lines[0];
    const age = parseInt(lines[1]);
    console.log(`Hello ${name}, age: ${age}`);
});
---STDIN---
Alice Brown
28
EOF
```

Expected output: `Hello Alice Brown, age: 28`

#### **PHP Test**
```bash
docker run --rm -i \
  --cpus="1" \
  --memory="512m" \
  --network=none \
  code-sandbox \
  /bin/bash /code/run.sh php <<'EOF'
<?php
$name = trim(fgets(STDIN));
$age = intval(trim(fgets(STDIN)));
echo "Hello $name, age: $age\n";
?>
---STDIN---
Charlie Davis
40
EOF
```

Expected output: `Hello Charlie Davis, age: 40`

#### **SQL Test**
```bash
docker run --rm -i \
  --cpus="1" \
  --memory="512m" \
  --network=none \
  code-sandbox \
  /bin/bash /code/sql-runner.sh <<'EOF'
CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, age INTEGER);
INSERT INTO users (name, age) VALUES ('Alice', 25);
INSERT INTO users (name, age) VALUES ('Bob', 30);
SELECT * FROM users WHERE age > 20;
EOF
```

Expected output:
```
1|Alice|25
2|Bob|30
```

---

## Language-Specific Features

### **Java**
- ✅ Standard `Scanner` with multi-line input
- ✅ `System.out.println` works like Eclipse console
- ✅ Supports arrays, collections, streams
- ✅ Stack size: 4MB (sufficient for deep recursion)
- ✅ Heap size: 256MB

**Example - Array Input:**
```java
import java.util.*;
public class Main {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        int n = sc.nextInt();
        int[] arr = new int[n];
        for(int i = 0; i < n; i++) {
            arr[i] = sc.nextInt();
        }
        System.out.println(Arrays.toString(arr));
    }
}
// Input: 5 \n 1 2 3 4 5
// Output: [1, 2, 3, 4, 5]
```

### **Python**
- ✅ Standard `input()` and `print()`
- ✅ Recursion limit: 100,000 (set automatically)
- ✅ Multi-line input works naturally
- ✅ Lists, tuples, dicts supported

**Example - List Input:**
```python
n = int(input())
arr = list(map(int, input().split()))
print(f"Sum: {sum(arr)}")
# Input: 5 \n 1 2 3 4 5
# Output: Sum: 15
```

### **C++**
- ✅ Standard `cin`, `cout`, `cerr`
- ✅ STL: vectors, strings, maps, sets
- ✅ Multi-line input with `getline()`
- ✅ C++11/14/17 features supported

**Example - Vector Input:**
```cpp
#include <iostream>
#include <vector>
using namespace std;
int main() {
    int n;
    cin >> n;
    vector<int> arr(n);
    for(int i = 0; i < n; i++) cin >> arr[i];
    int sum = 0;
    for(int x : arr) sum += x;
    cout << "Sum: " << sum << endl;
    return 0;
}
// Input: 5 \n 1 2 3 4 5
// Output: Sum: 15
```

### **JavaScript (Node.js)**
- ✅ Standard `console.log()`
- ✅ `readline` interface for input
- ✅ Async/await supported
- ✅ ES6+ features

**Example - Array Input:**
```javascript
const readline = require('readline');
const rl = readline.createInterface({
    input: process.stdin
});
const lines = [];
rl.on('line', (line) => lines.push(line));
rl.on('close', () => {
    const n = parseInt(lines[0]);
    const arr = lines[1].split(' ').map(Number);
    console.log(`Sum: ${arr.reduce((a, b) => a + b, 0)}`);
});
// Input: 5 \n 1 2 3 4 5
// Output: Sum: 15
```

### **PHP**
- ✅ Standard CLI PHP
- ✅ `fgets(STDIN)` for input
- ✅ `echo` and `print` work like terminal
- ✅ All PHP 8.1 features

**Example - Array Input:**
```php
<?php
$n = intval(trim(fgets(STDIN)));
$arr = array_map('intval', explode(' ', trim(fgets(STDIN))));
echo "Sum: " . array_sum($arr) . "\n";
?>
// Input: 5 \n 1 2 3 4 5
// Output: Sum: 15
```

### **SQL**
- ✅ SQLite3 in-memory database
- ✅ CREATE, INSERT, SELECT, UPDATE, DELETE
- ✅ Multi-statement support (separated by `;`)
- ✅ Table output like MySQL Workbench

**Example - Multi-table Operations:**
```sql
CREATE TABLE products (id INTEGER PRIMARY KEY, name TEXT, price REAL);
INSERT INTO products VALUES (1, 'Laptop', 999.99);
INSERT INTO products VALUES (2, 'Mouse', 29.99);
SELECT * FROM products WHERE price < 1000;
-- Output:
-- 1|Laptop|999.99
-- 2|Mouse|29.99
```

---

## Monaco Editor Integration

### Frontend Setup

Install Monaco Editor:
```bash
npm install monaco-editor
```

### Sample Vue.js Component

```vue
<template>
  <div class="code-editor">
    <div class="editor-header">
      <select v-model="language" @change="changeLanguage">
        <option value="java">Java</option>
        <option value="python">Python</option>
        <option value="cpp">C++</option>
        <option value="javascript">JavaScript</option>
        <option value="php">PHP</option>
        <option value="sql">SQL</option>
      </select>
      <button @click="runCode" :disabled="running">
        {{ running ? 'Running...' : 'Run Code' }}
      </button>
    </div>
    
    <div ref="editorContainer" class="editor-container"></div>
    
    <div class="input-section">
      <label>Standard Input:</label>
      <textarea v-model="stdin" placeholder="Enter input here..."></textarea>
    </div>
    
    <div class="output-section">
      <h3>Output:</h3>
      <pre>{{ output }}</pre>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
  </div>
</template>

<script>
import * as monaco from 'monaco-editor';

export default {
  data() {
    return {
      editor: null,
      language: 'java',
      stdin: '',
      output: '',
      error: '',
      running: false,
      templates: {
        java: `public class Main {
    public static void main(String[] args) {
        System.out.println("Hello World!");
    }
}`,
        python: `print("Hello World!")`,
        cpp: `#include <iostream>
using namespace std;
int main() {
    cout << "Hello World!" << endl;
    return 0;
}`,
        javascript: `console.log("Hello World!");`,
        php: `<?php
echo "Hello World!\\n";
?>`,
        sql: `SELECT 'Hello World!' as message;`
      }
    };
  },
  mounted() {
    this.editor = monaco.editor.create(this.$refs.editorContainer, {
      value: this.templates[this.language],
      language: this.getMonacoLanguage(this.language),
      theme: 'vs-dark',
      automaticLayout: true,
      fontSize: 14,
      minimap: { enabled: false }
    });
  },
  methods: {
    getMonacoLanguage(lang) {
      const map = {
        java: 'java',
        python: 'python',
        cpp: 'cpp',
        javascript: 'javascript',
        php: 'php',
        sql: 'sql'
      };
      return map[lang] || 'plaintext';
    },
    changeLanguage() {
      this.editor.setValue(this.templates[this.language]);
      monaco.editor.setModelLanguage(
        this.editor.getModel(),
        this.getMonacoLanguage(this.language)
      );
    },
    async runCode() {
      this.running = true;
      this.output = '';
      this.error = '';
      
      try {
        const response = await fetch('/api/execute-code', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            language: this.language,
            code: this.editor.getValue(),
            stdin: this.stdin
          })
        });
        
        const result = await response.json();
        
        if (result.success) {
          this.output = result.output;
        } else {
          this.error = result.error;
        }
      } catch (err) {
        this.error = 'Network error: ' + err.message;
      } finally {
        this.running = false;
      }
    }
  },
  beforeUnmount() {
    if (this.editor) {
      this.editor.dispose();
    }
  }
};
</script>

<style scoped>
.code-editor {
  display: flex;
  flex-direction: column;
  height: 100vh;
  padding: 20px;
}
.editor-header {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
}
.editor-container {
  height: 400px;
  border: 1px solid #ddd;
  margin-bottom: 20px;
}
.input-section, .output-section {
  margin-bottom: 20px;
}
textarea {
  width: 100%;
  height: 100px;
  padding: 10px;
  font-family: monospace;
}
pre {
  background: #f5f5f5;
  padding: 15px;
  border-radius: 5px;
  font-family: monospace;
  white-space: pre-wrap;
}
.error {
  color: red;
  background: #ffe6e6;
  padding: 10px;
  border-radius: 5px;
  margin-top: 10px;
}
button {
  padding: 10px 20px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
button:disabled {
  background: #ccc;
  cursor: not-allowed;
}
</style>
```

### Laravel Route

Add to `routes/web.php` or `routes/api.php`:

```php
use App\Services\CodeExecutionService;

Route::post('/api/execute-code', function (Request $request) {
    $validated = $request->validate([
        'language' => 'required|in:java,python,cpp,javascript,php,sql',
        'code' => 'required|string|max:100000',
        'stdin' => 'nullable|string|max:1000000'
    ]);

    $service = app(CodeExecutionService::class);
    $result = $service->execute(
        $validated['language'],
        $validated['code'],
        $validated['stdin'] ?? ''
    );

    return response()->json($result);
});
```

---

## Security Considerations

### Docker Security Limits

The container runs with:
- **CPU Limit**: 1 core
- **Memory Limit**: 512MB
- **Network**: Disabled (`--network=none`)
- **Timeout**: 10 seconds
- **No privileged access**
- **Read-only root filesystem** (except /tmp)

### Additional Security

1. **Input Validation**: Code and stdin are validated before execution
2. **File Cleanup**: Temporary files are deleted after execution
3. **No File System Access**: Code cannot access host files
4. **No Internet Access**: Network is disabled
5. **Resource Limits**: CPU, memory, and time limits prevent abuse

---

## Troubleshooting

### Issue: "Docker command not found"
**Solution**: Install Docker Desktop or Docker Engine

### Issue: "Permission denied"
**Solution**: Add user to docker group:
```bash
sudo usermod -aG docker $USER
newgrp docker
```

### Issue: "Container timeout"
**Solution**: Increase timeout in CodeExecutionService.php (line 31)

### Issue: "Java heap space error"
**Solution**: Increase Java heap size in Dockerfile (line 15)

### Issue: "Python recursion error"
**Solution**: The recursion limit is already set to 100,000. Check your algorithm.

### Issue: "SQL query fails"
**Solution**: Ensure SQL syntax is correct. Only SQLite3 syntax is supported.

---

## Performance Optimization

### For Large Datasets

1. **Java**: Use BufferedReader instead of Scanner
```java
BufferedReader br = new BufferedReader(new InputStreamReader(System.in));
String line = br.readLine();
```

2. **Python**: Use `sys.stdin` for faster input
```python
import sys
lines = sys.stdin.read().splitlines()
```

3. **C++**: Use fast I/O
```cpp
ios_base::sync_with_stdio(false);
cin.tie(NULL);
```

---

## Maintenance

### Rebuild Docker Image

After modifying Dockerfile or scripts:
```bash
cd code-runner
docker build -t code-sandbox .
```

### Update PHP Service

After modifying CodeExecutionService.php:
```bash
# No rebuild needed, changes take effect immediately
```

### Clear Old Containers

```bash
docker container prune
```

### Clear Unused Images

```bash
docker image prune -a
```

---

## Support

For issues or questions:
1. Check logs: `docker logs <container-id>`
2. Test individual languages with manual commands
3. Verify file permissions in `/tmp/code-exec`

---

## License

This setup is part of the codeXpert project.
