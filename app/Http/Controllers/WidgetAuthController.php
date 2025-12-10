<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WidgetAuthController extends Controller
{
    /**
     * Register a new customer
     */
    public function register(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'customer',
        ]);

        // Create API token
        $token = $user->createToken('widget-auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Login customer
     */
    public function login(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $validated['email'])
                    ->where('user_type', 'customer')
                    ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Create API token
        $token = $user->createToken('widget-auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout customer
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Redirect to Google for authentication (Widget context)
     */
    public function redirectToGoogle(Organization $organization)
    {
        // Store organization slug in session to return to correct widget
        session(['widget_organization' => $organization->slug]);
        session(['from_widget' => true]);
        
        // Use main callback URL (already registered in Google)
        return \Laravel\Socialite\Facades\Socialite::driver('google')
            ->redirect();
    }

    /**
     * Handle Google callback for widget
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')
                ->redirectUrl(route('widget.auth.google.callback'))
                ->user();
            
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
                // Create new customer user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                    'user_type' => 'customer',
                ]);
            }
            
            // Log the user in
            Auth::login($user, true);
            
            // Get organization slug from session
            $orgSlug = session('widget_organization', 'default');
            session()->forget('widget_organization');
            
            // Redirect back to widget
            return redirect("/widget/{$orgSlug}");
            
        } catch (\Exception $e) {
            \Log::error('Widget Google OAuth Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            $orgSlug = session('widget_organization', 'default');
            session()->forget('widget_organization');
            
            return redirect("/widget/{$orgSlug}")->with('error', 'Failed to authenticate with Google: ' . $e->getMessage());
        }
    }
}
