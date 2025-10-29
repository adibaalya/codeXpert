<?php

// app/Http/Controllers/Auth/SocialLoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class SocialLoginController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirectToProvider(string $provider)
    {
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

        // Determine the column name for the social ID (e.g., 'google_id')
        $socialIdColumn = $provider . '_id';

        // 1. Check if a user exists with this social ID
        $user = User::where($socialIdColumn, $socialUser->getId())->first();

        if ($user) {
            // User found, log them in
            Auth::login($user);
            return redirect('/dashboard');
        } else {
            // 2. Check if a user exists with this email (e.g., they registered with email before)
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // User found by email, link the social ID and log them in
                $user->update([$socialIdColumn => $socialUser->getId()]);
                Auth::login($user);
                return redirect('/dashboard');
            } else {
                // 3. New user, create the account

                // Get the base username part from the email
                $emailUsername = explode('@', $socialUser->getEmail())[0];
                
                $newUser = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    
                    // Add the missing username field
                    'username' => $socialUser->getNickname() ?? $emailUsername, 
                    
                    'email' => $socialUser->getEmail(),
                    $socialIdColumn => $socialUser->getId(),
                ]);

                Auth::login($newUser);
                return redirect()->route('personalization.show');
            }
        }
    }
}
