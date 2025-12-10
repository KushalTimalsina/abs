<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // Update existing user's Google ID if not set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                    ]);
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)), // Random password for OAuth users
                    'user_type' => 'customer',
                ]);
            }
            
            // Log the user in
            Auth::login($user, true);
            
            // Check if this was from widget
            if (session('from_widget')) {
                $orgSlug = session('widget_organization', 'default');
                session()->forget(['widget_organization', 'from_widget']);
                return redirect("/widget/{$orgSlug}");
            }
            
            return redirect()->intended('dashboard');
            
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('Google OAuth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if from widget
            if (session('from_widget')) {
                $orgSlug = session('widget_organization', 'default');
                session()->forget(['widget_organization', 'from_widget']);
                return redirect("/widget/{$orgSlug}")->with('error', 'Failed to authenticate with Google');
            }
            
            return redirect()->route('login')->with('error', 'Failed to authenticate with Google. Please try again. Error: ' . $e->getMessage());
        }
    }
}
