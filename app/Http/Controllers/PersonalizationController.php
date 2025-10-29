<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProficiency; 
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PersonalizationController extends Controller
{
    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'selections' => 'required|array',
            'selections.*.language' => 'required|string|max:50',
            'selections.*.level' => 'required|string|in:Beginner,Intermediate,Advanced',
        ]);

        $user = Auth::user();
        
        // 1. Delete all existing proficiency records for the user
        //DB::table('user_proficiency')
          //  ->where('user_id', $user->id)
            //->delete();
        
        // 2. Prepare data for insertion
        $proficiencyData = array_map(function ($selection) use ($user) {
            return [
                'user_id' => $user->id,
                'language' => $selection['language'],
                'level' => $selection['level'],
                'xp_points' => 0
            ];
        }, $validated['selections']);

        // 3. Insert all proficiency records at once
        UserProficiency::insert($proficiencyData);

        return response()->json([
            'message' => 'User proficiency saved successfully.', 
            'languages_count' => count($proficiencyData)
        ]);
    }
}
