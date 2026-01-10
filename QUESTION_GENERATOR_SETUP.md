# AI Question Generator Setup Guide

## Overview
This feature allows reviewers to generate coding questions using Gemini AI based on custom prompts and parameters.

## Setup Instructions

### 1. Get Gemini API Key
1. Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Sign in with your Google account
3. Click "Create API Key"
4. Copy the generated API key

### 2. Configure Environment
Add the following to your `.env` file:

```
GEMINI_API_KEY=your_gemini_api_key_here
```

### 3. Access the Feature
- Login as a reviewer
- Navigate to: `http://your-domain.com/reviewer/generate`
- Or add a link to your reviewer dashboard

### 4. Usage
1. Enter a prompt describing the question you want
2. Select language, difficulty, topic, and number of questions
3. Click "Generate Question"
4. The AI will generate a complete question with:
   - Problem statement
   - Constraints
   - Test cases
   - Solution code
   - Expected approach

## Features
- **AI-Powered Generation**: Uses Gemini AI to create unique questions
- **Customizable Parameters**: Control difficulty, topic, language, etc.
- **Complete Questions**: Includes tests, solutions, and explanations
- **Beautiful UI**: Matches the design from your mockups
- **Tab Navigation**: Problem, Tests, and Solution tabs
- **Save to Queue**: Option to save generated questions for review

## Example Prompt
"Create a question about implementing a binary search tree with insertion and deletion operations"

## Troubleshooting

### API Key Not Working
- Ensure you've copied the complete API key
- Check that there are no extra spaces in the .env file
- Restart your Laravel server after adding the key

### Generation Fails
- Check your internet connection
- Verify the API key is valid
- Check Laravel logs: `storage/logs/laravel.log`

## File Structure
- View: `resources/views/reviewer/generate.blade.php`
- Controller: `app/Http/Controllers/Reviewer/QuestionGeneratorController.php`
- Routes: `routes/web.php` (reviewer.generate routes)
