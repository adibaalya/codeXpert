<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Learner;
use App\Models\Reviewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $request->validate([ 
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:learner,reviewer',
        ]);

        $learnerExists = Learner::where('email', $request->email)->exists();
        $reviewerExists = Reviewer::where('email', $request->email)->exists();

        if ($learnerExists || $reviewerExists) {
            throw ValidationException::withMessages([
                'email' => __('The email has already been taken.'),
            ]);
        }

        if ($request->role === 'learner') {
            $user = Learner::create([
                'username' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'registration_date' => now(),
                'totalPoint' => 0,
                'streak' => 0,
            ]);

            // Log the learner in
            Auth::guard('learner')->login($user);
            
            $request->session()->regenerate();

            // Redirect to customization path for first-time setup
            return redirect()->route('learner.customization');
        } else {
            $user = Reviewer::create([
                'username' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'registrationDate' => now(),
                'isQualified' => false,
            ]);

            // Log the reviewer in
            Auth::guard('reviewer')->login($user);
            
            $request->session()->regenerate();

            // Redirect to competency test for new reviewers
            return redirect()->route('reviewer.competency.choose');
        }
    }
}
