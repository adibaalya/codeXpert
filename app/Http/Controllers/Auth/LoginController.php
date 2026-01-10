<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Learner;
use App\Models\Reviewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:learner,reviewer',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');
        $role = $request->role;

        // Attempt login based on role
        if ($role === 'learner') {
            $guard = 'learner';
        } else {
            $guard = 'reviewer';
        }

        if (Auth::guard($guard)->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Clear any intended URL from session to force our redirect
            $request->session()->forget('url.intended');
            
            // Redirect based on role and qualification status
            if ($role === 'reviewer') {
                $reviewer = Auth::guard('reviewer')->user();
                
                \Log::info('Reviewer Login', [
                    'reviewer_id' => $reviewer->reviewer_ID,
                    'isQualified' => $reviewer->isQualified,
                    'redirecting_to' => $reviewer->isQualified ? 'reviewer.dashboard' : 'reviewer.competency.choose'
                ]);
                
                if (!$reviewer->isQualified) {
                    return redirect()->route('reviewer.competency.choose');
                }
                // Redirect verified reviewers to reviewer dashboard
                return redirect()->route('reviewer.dashboard');
            }
            
            // Redirect learners to their customization or dashboard
            return redirect()->route('learner.customization');
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        // Logout from both guards
        Auth::guard('learner')->logout();
        Auth::guard('reviewer')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
