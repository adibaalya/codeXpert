<?php

// app/Http/Controllers/Auth/SocialLoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Learner;
use App\Models\Reviewer;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SocialLoginController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirectToProvider(string $provider)
    {
        // Store the selected role in session before redirecting to social provider
        $role = request()->query('role', 'learner');
        Session::put('social_login_role', $role);
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the provider.
     */
    public function handleProviderCallback(string $provider)
    {
        try {
            // Get user info from the social provider
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            // Handle error, e.g., if user cancels login
            return redirect('/login')->withErrors(['social_login' => 'Could not authenticate with ' . ucfirst($provider) . '.']);
        }

        // Get role from session (default to learner if not set)
        $role = Session::get('social_login_role', 'learner');
        
        // Determine the column name for the social ID (e.g., 'google_ID' or 'github_ID')
        $socialIdColumn = $provider . '_ID';
        
        // Get the base username part from the email
        $emailUsername = explode('@', $socialUser->getEmail())[0];
        $username = $socialUser->getNickname() ?? $emailUsername;

        if ($role === 'learner') {
            // Check if learner exists with this social ID
            $user = Learner::where($socialIdColumn, $socialUser->getId())->first();

            if ($user) {
                // User found, log them in
                Auth::guard('learner')->login($user);
                return redirect('/learner/dashboard');
            }

            // Check if learner exists with this email
            $user = Learner::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // User found by email, link the social ID and log them in
                $user->update([$socialIdColumn => $socialUser->getId()]);
                Auth::guard('learner')->login($user);
                return redirect('/dashboard');
            }

            // Create new learner
            $user = Learner::create([
                'username' => $username,
                'email' => $socialUser->getEmail(),
                $socialIdColumn => $socialUser->getId(),
                'password' => bcrypt(uniqid()), // Random password for social login users
                'registration_date' => now(),
                'totalPoint' => 0,
                'streak' => 0,
            ]);

            Auth::guard('learner')->login($user);
            // Redirect to customization path for first-time setup
            return redirect()->route('learner.customization');
            
        } else {
            // Handle reviewer
            // Check if reviewer exists with this social ID
            $user = Reviewer::where($socialIdColumn, $socialUser->getId())->first();

            if ($user) {
                // User found, log them in
                Auth::guard('reviewer')->login($user);
                
                // Check if reviewer needs to take competency test
                if (!$user->isQualified) {
                    return redirect()->route('reviewer.competency.choose');
                }
                
                return redirect('/reviewer/dashboard');
            }

            // Check if reviewer exists with this email
            $user = Reviewer::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // User found by email, link the social ID and log them in
                $user->update([$socialIdColumn => $socialUser->getId()]);
                Auth::guard('reviewer')->login($user);
                
                // Check if reviewer needs to take competency test
                if (!$user->isQualified) {
                    return redirect()->route('reviewer.competency.choose');
                }
                
                return redirect('/reviewer/dashboard');
            }

            // Create new reviewer
            $user = Reviewer::create([
                'username' => $username,
                'email' => $socialUser->getEmail(),
                $socialIdColumn => $socialUser->getId(),
                'password' => bcrypt(uniqid()), // Random password for social login users
                'registrationDate' => now(),
                'isQualified' => false,
            ]);

            Auth::guard('reviewer')->login($user);
            
            // New reviewers must take competency test
            return redirect()->route('reviewer.competency.choose');
        }
    }
}
