<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Learner;
use Illuminate\Http\Request;

class CodingQuestionController extends Controller
{
    /**
     * Display the coding question page with a specific question
     */
    public function show(Request $request, $questionId)
    {
        // Get the authenticated learner
        $learner = auth()->guard('learner')->user();
        
        // Fetch the question from database
        $question = Question::where('question_ID', $questionId)
            ->where('questionType', 'Code_Solution')
            ->where('status', 'Approved')
            ->firstOrFail();
        
        // Check if learner has already rated this question
        $userRating = \App\Models\QuestionRating::where('learner_ID', $learner->learner_ID)
            ->where('question_ID', $questionId)
            ->first();
        
        return view('learner.codingQuestion', [
            'learner' => $learner,
            'question' => $question,
            'userRating' => $userRating ? $userRating->rating : null
        ]);
    }
    
    /**
     * Get a random question based on filters (for practice mode)
     */
    public function random(Request $request)
    {
        $language = $request->query('language');
        $level = $request->query('level');
        $topic = $request->query('topic');
        
        $query = Question::where('questionType', 'Code_Solution')
            ->where('status', 'Approved')
            ->where('questionCategory', 'learnerPractice');
        
        if ($language) {
            $query->where('language', $language);
        }
        
        if ($level) {
            $query->where('level', $level);
        }
        
        if ($topic) {
            $query->where('chapter', $topic);
        }
        
        // Get a random question
        $question = $query->inRandomOrder()->first();
        
        if (!$question) {
            // Log for debugging
            \Log::info('No question found', [
                'language' => $language,
                'level' => $level,
                'topic' => $topic,
                'query_count' => Question::where('questionType', 'Code_Solution')
                    ->where('status', 'Approved')
                    ->where('questionCategory', 'learnerPractice')
                    ->count()
            ]);
            
            return redirect()->route('learner.practice')
                ->with('error', 'No questions found matching your criteria. Please try different filters.');
        }
        
        return redirect()->route('learner.coding.show', ['questionId' => $question->question_ID]);
    }
    
    /**
     * Get suggested questions based on learner's proficiency
     */
    public function suggested(Request $request)
    {
        $learner = Learner::where('learner_ID', session('learner_id'))->firstOrFail();
        
        // Get learner's weakest topics from proficiency
        $weakTopics = $learner->proficiencies()
            ->where('score', '<', 60)
            ->pluck('topic')
            ->toArray();
        
        $query = Question::where('questionType', 'Code_Solution')
            ->where('status', 'Approved');
        
        if (!empty($weakTopics)) {
            $query->whereIn('questionCategory', $weakTopics);
        }
        
        $question = $query->inRandomOrder()->first();
        
        if (!$question) {
            // Fallback to any random question
            $question = Question::where('questionType', 'Code_Solution')
                ->where('status', 'Approved')
                ->inRandomOrder()
                ->first();
        }
        
        if (!$question) {
            return redirect()->route('learner.dashboard')
                ->with('error', 'No questions available at the moment.');
        }
        
        return redirect()->route('learner.coding.show', ['questionId' => $question->question_ID]);
    }
}
