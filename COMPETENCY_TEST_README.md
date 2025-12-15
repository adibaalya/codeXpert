# Competency Test System for Reviewers

## Overview
When reviewers first login, they are directed to a competency test to verify their expertise before they can review questions. The test evaluates their programming knowledge in their chosen language.

## Flow

### 1. Language Selection (`/reviewer/competency/choose`)
- Reviewer selects their preferred language: Python, Java, JavaScript, or C++
- Shows test requirements and passing criteria
- Redirects to MCQ test after selection

### 2. Multiple Choice Questions (`/reviewer/competency/mcq`)
- 10 randomly selected MCQ questions
- Questions are filtered by:
  - `questionCategory = "competencyTest"`
  - `questionType = "MCQ_Single"`
  - Selected language
  - Mixed levels: beginner, intermediate, advanced
- Each question worth 10 points
- Progress bar shows completion status
- Timer counts down from 45 minutes

### 3. Code Challenges (`/reviewer/competency/code`)
- 5 randomly selected coding questions
- Questions are filtered by:
  - `questionCategory = "competencyTest"`
  - `questionType = "Code_Solution"`
  - Selected language
- Integrated with **Monaco Editor** (VS Code editor in browser)
- Shows:
  - Problem description
  - Test cases
  - Hints (optional)
- Features:
  - Syntax highlighting
  - Code completion
  - Run tests (simulated for now)

### 4. Results (`/reviewer/competency/result/{id}`)
- Displays final score and achievement level
- Scoring criteria:
  - **≥90%**: Can review ALL levels (Beginner, Intermediate, Advanced)
  - **≥75%**: Can review Beginner and Intermediate
  - **≥50%**: Can review Beginner only
  - **<50%**: Test failed, must retake
- Shows:
  - Total correctness score
  - Plagiarism detection score (placeholder)
  - Level achieved
- Updates `reviewers.isQualified = true` if passed

## Database Schema

### `questions` table
```php
- question_ID (primary key)
- content (text) - Question description
- answersData (text) - Correct answer
- questionType (MCQ_Single, Code_Solution)
- questionCategory (competencyTest, practice, etc.)
- language (Python, Java, JavaScript, C++)
- level (beginner, intermediate, advanced)
- options (JSON array) - For MCQ questions
- testCases (JSON array) - For code questions
- hint (text) - Optional hint
```

### `competency_test_results` table
```php
- id (primary key)
- reviewer_ID (foreign key)
- language (selected language)
- mcq_score (score from MCQ section)
- code_score (score from code section)
- total_score (final percentage)
- plagiarism_score (plagiarism check result)
- level_achieved (beginner, intermediate, all)
- passed (boolean)
- mcq_answers (JSON) - All MCQ answers
- code_solutions (JSON) - All code submissions
- completed_at (timestamp)
```

## Routes

```php
// Reviewer Competency Test Routes
GET  /reviewer/competency/choose       - Language selection page
POST /reviewer/competency/start        - Start test with selected language
GET  /reviewer/competency/mcq          - Show MCQ question
POST /reviewer/competency/mcq          - Submit MCQ answer
GET  /reviewer/competency/code         - Show code challenge
POST /reviewer/competency/code         - Submit code solution
GET  /reviewer/competency/submit       - Process final submission
GET  /reviewer/competency/result/{id}  - Show test results
```

## Controller Methods

### `CompetencyTestController`
- `chooseLanguage()` - Display language selection
- `startTest()` - Initialize test session with selected language
- `showMCQ()` - Display current MCQ question
- `submitMCQ()` - Save MCQ answer and advance
- `showCode()` - Display current code challenge
- `submitCode()` - Save code solution and advance
- `submitTest()` - Calculate scores and save results
- `showResult()` - Display final test results

## Session Management
The test uses Laravel sessions to track progress:
- `test_language` - Selected programming language
- `test_started_at` - Test start time
- `mcq_questions` - Array of MCQ question IDs
- `current_mcq_index` - Current MCQ question index
- `mcq_answers` - Submitted MCQ answers
- `code_questions` - Array of code question IDs
- `current_code_index` - Current code question index
- `code_solutions` - Submitted code solutions

## Monaco Editor Integration
The code editor uses Monaco Editor (same as VS Code):
- CDN: `https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0`
- Language support: Python, Java, JavaScript, C++
- Features: Syntax highlighting, auto-completion, dark theme
- Code submission via hidden form input

## Sample Questions Included
The system includes 14 sample questions across all languages:
- **Python**: 3 MCQ + 2 Code questions
- **Java**: 2 MCQ + 1 Code question
- **JavaScript**: 2 MCQ + 1 Code question
- **C++**: 2 MCQ + 1 Code question

## Adding More Questions
To add questions, insert into `questions` table:
```php
Question::create([
    'content' => 'Your question here',
    'answersData' => 'Correct answer',
    'questionType' => 'MCQ_Single', // or 'Code_Solution'
    'questionCategory' => 'competencyTest',
    'language' => 'Python',
    'level' => 'beginner',
    'status' => 'Approved',
    'options' => ['Option A', 'Option B', 'Option C', 'Option D'], // For MCQ
    'testCases' => [...], // For Code questions
    'hint' => 'Optional hint'
]);
```

## Future Enhancements
1. **Code Execution**: Integrate with a code execution engine to actually run test cases
2. **Plagiarism Detection**: Implement actual plagiarism checking algorithm
3. **Time Tracking**: Enforce 45-minute time limit
4. **Question Pool**: Expand question database for each language
5. **Retake Logic**: Allow reviewers to retake test after cooldown period
6. **Analytics**: Track performance metrics and difficulty levels
7. **Review History**: Show past test attempts and improvements

## Testing the System
1. Register as a reviewer
2. Login with reviewer credentials
3. You'll be automatically redirected to `/reviewer/competency/choose`
4. Select a language and complete the test
5. View your results and qualification level

## Note
The system currently stores the `answersData` field as text. For more complex scenarios (e.g., multiple correct answers), consider using JSON format for `answersData`.
