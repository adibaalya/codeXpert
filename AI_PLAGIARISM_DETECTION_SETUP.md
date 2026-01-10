# AI Plagiarism Detection Setup Guide

## Overview
CodeXpert now includes an AI-powered plagiarism detection system that analyzes student code submissions to detect patterns typical of AI-generated code (like ChatGPT, GitHub Copilot, etc.).

## Features
- **Automatic Analysis**: Every code submission is automatically analyzed for AI authorship patterns
- **Probability Score**: Returns a 0-100% probability that the code is AI-generated
- **Detection Indicators**: Lists specific patterns found (comments, naming, structure, etc.)
- **Risk Levels**: Minimal, Low, Medium, High based on probability score
- **Database Storage**: Plagiarism scores are stored in the `attempts` table for tracking

## Setup Instructions

### Step 1: Get a Free Gemini API Key

1. Visit [Google AI Studio](https://aistudio.google.com/app/apikey)
2. Sign in with your Google account
3. Click "Get API Key" or "Create API Key"
4. Copy your API key

**Note**: Google Gemini API has a generous free tier (60 requests per minute, 1,500 requests per day).

### Step 2: Add API Key to Environment

1. Open your `.env` file (copy from `.env.example` if needed):
   ```bash
   cp .env.example .env
   ```

2. Add your Gemini API key:
   ```env
   GEMINI_API_KEY=your_api_key_here
   ```

3. Save the file

### Step 3: Test the Implementation

The plagiarism detection is now automatically integrated. Test it by:

1. Start your Laravel server:
   ```bash
   php artisan serve
   ```

2. Login as a learner and submit code for a practice question

3. After submission, check the result page - you'll see:
   - AI Generation Probability score (0-100%)
   - Risk level badge (Minimal/Low/Medium/High)
   - Detection indicators
   - Analysis confidence level

## How It Works

### Analysis Criteria

The AI analyzes code for these patterns:

1. **Comment Patterns**
   - Overly perfect or comprehensive comments
   - Consistent docstring formatting
   - Comments explaining obvious code

2. **Variable Naming**
   - Extremely descriptive names (`resultArray`, `temporaryCounter`)
   - Consistent naming conventions (unusual for beginners)
   - Lack of abbreviations

3. **Code Structure**
   - Perfect indentation and spacing
   - Optimal algorithm choices
   - Comprehensive error handling
   - Advanced features beyond typical student level

4. **Human Idiosyncrasies**
   - Lack of typos or inconsistencies
   - No debugging artifacts (print statements, commented code)
   - No trial-and-error patterns

5. **Style Consistency**
   - Perfectly consistent code style
   - Mathematical precision in algorithms

### Risk Levels

| Score Range | Risk Level | Color | Meaning |
|-------------|-----------|-------|---------|
| 0-20% | Minimal | Green | Clear signs of human authorship |
| 21-40% | Low | Yellow | Likely human with good practices |
| 41-60% | Medium | Orange | Ambiguous - could be either |
| 61-80% | High | Red | Likely AI with some human touches |
| 81-100% | High | Red | Strong indicators of AI generation |

## Database Schema

The plagiarism score is stored in the `attempts` table:

```php
'plagiarismScore' => float (0-100) // AI generation probability
```

You can query plagiarism data:

```php
// Get all high-risk submissions
$highRiskAttempts = DB::table('attempts')
    ->where('plagiarismScore', '>=', 60)
    ->get();

// Get learner's average plagiarism score
$avgScore = DB::table('attempts')
    ->where('learner_ID', $learnerId)
    ->avg('plagiarismScore');
```

## Viewing Results

### For Learners
After submitting code, learners will see:
- A plagiarism detection card on the result page
- Color-coded risk level
- Probability percentage with progress bar
- List of detected indicators
- Confidence level of the analysis

### For Reviewers/Admins
You can create a dashboard to:
- Monitor plagiarism scores across all learners
- Flag suspicious submissions for review
- Track patterns over time
- Generate reports

Example query for reviewer dashboard:
```php
// Get recent high-risk submissions
$suspiciousAttempts = DB::table('attempts')
    ->join('learners', 'attempts.learner_ID', '=', 'learners.learner_ID')
    ->join('questions', 'attempts.question_ID', '=', 'questions.question_ID')
    ->where('attempts.plagiarismScore', '>=', 70)
    ->where('attempts.dateAttempted', '>=', now()->subDays(7))
    ->select(
        'learners.username',
        'questions.title',
        'attempts.plagiarismScore',
        'attempts.dateAttempted'
    )
    ->orderBy('attempts.plagiarismScore', 'desc')
    ->get();
```

## Service Architecture

### AIPlagiarismDetectionService

Located at: `app/Services/AIPlagiarismDetectionService.php`

**Main Methods:**

```php
// Analyze code and return results
public function analyzeCode(string $code, string $language): array

// Get risk level from probability
public function getRiskLevel(int $probability): string

// Get color code for risk level
public function getRiskColor(string $riskLevel): string
```

**Response Format:**
```php
[
    'ai_probability' => 75,
    'reason' => 'Code shows consistent naming and perfect formatting',
    'indicators' => [
        'Highly descriptive variable names',
        'Perfect indentation',
        'Comprehensive comments'
    ],
    'confidence' => 'high'
]
```

## Customization

### Adjusting Risk Thresholds

Edit `AIPlagiarismDetectionService.php`:

```php
public function getRiskLevel(int $probability): string
{
    if ($probability >= 80) {  // Change these values
        return 'high';
    } elseif ($probability >= 60) {
        return 'medium';
    } elseif ($probability >= 40) {
        return 'low';
    } else {
        return 'minimal';
    }
}
```

### Customizing the Prompt

Edit the `buildForensicPrompt()` method to adjust analysis criteria or scoring guidelines.

## Troubleshooting

### API Key Not Working
- Verify the key is correctly added to `.env`
- Clear config cache: `php artisan config:clear`
- Check API quota at [Google AI Studio](https://aistudio.google.com/)

### Fallback Mode
If the API is unavailable, the system returns:
```php
[
    'ai_probability' => 0,
    'reason' => 'Plagiarism detection service unavailable',
    'indicators' => [],
    'confidence' => 'low'
]
```

### Rate Limits
Free tier limits:
- 60 requests per minute
- 1,500 requests per day

If exceeded, the system falls back gracefully without breaking submissions.

## Important Notes

⚠️ **Disclaimer**: 
- This is a detection tool, not a definitive proof of plagiarism
- High scores indicate patterns typical of AI code, not necessarily cheating
- Some students naturally write clean, well-structured code
- Use as one factor in academic integrity assessments
- Consider context: problem difficulty, student history, etc.

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review API responses in logs (search for "Plagiarism analysis")
3. Test API key at Google AI Studio

## Future Enhancements

Potential improvements:
- Historical comparison (compare with student's previous submissions)
- Code similarity detection (compare with other students)
- Pattern learning (track common patterns per question)
- Reviewer alert system (notify when high-risk detected)
- Detailed analytics dashboard
