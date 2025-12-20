<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\UserProficiency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomizationPathController extends Controller
{
    /**
     * Show the customization path page
     */
    public function show()
    {
        $learner = Auth::guard('learner')->user();
        
        // Get already added languages
        $addedLanguages = UserProficiency::where('learner_ID', $learner->learner_ID)
            ->get();

        // Available languages
        $availableLanguages = [
            'Java' => 'orange',
            'JavaScript' => 'yellow',
            'Python' => 'blue',
            'C' => 'blue',
            'C++' => 'purple',
            'SQL' => 'teal',
        ];

        return view('learner.customizationPath', compact('learner', 'addedLanguages', 'availableLanguages'));
    }

    /**
     * Store a new language proficiency
     */
    public function store(Request $request)
    {
        $learner = Auth::guard('learner')->user();

        $request->validate([
            'language' => 'required|string|max:30',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
        ]);

        // Check if language already exists for this learner
        $exists = UserProficiency::where('learner_ID', $learner->learner_ID)
            ->where('language', $request->language)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Language already added'
            ], 400);
        }

        // Create new proficiency
        UserProficiency::create([
            'learner_ID' => $learner->learner_ID,
            'language' => $request->language,
            'level' => $request->level,
            'XP' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language added successfully'
        ]);
    }

    /**
     * Complete the customization and redirect to dashboard
     */
    public function complete()
    {
        $learner = Auth::guard('learner')->user();
        
        // Check if at least one language is added
        $hasLanguages = UserProficiency::where('learner_ID', $learner->learner_ID)->exists();
        
        if (!$hasLanguages) {
            return redirect()->route('learner.customization')
                ->with('error', 'Please add at least one language before continuing.');
        }

        return redirect()->route('learner.dashboard');
    }

    /**
     * Remove a language proficiency
     */
    public function destroy(Request $request)
    {
        $learner = Auth::guard('learner')->user();

        $request->validate([
            'language' => 'required|string|max:30',
        ]);

        $deleted = UserProficiency::where('learner_ID', $learner->learner_ID)
            ->where('language', $request->language)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Language removed successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Language not found or already removed'
        ], 404);
    }
}
