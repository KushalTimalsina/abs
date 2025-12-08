<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Superadmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SuperadminAuthController extends Controller
{
    /**
     * Show the superadmin login form
     */
    public function showLoginForm()
    {
        return view('auth.superadmin-login');
    }

    /**
     * Handle superadmin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $superadmin = Superadmin::where('email', $request->email)->first();

        if (!$superadmin || !Hash::check($request->password, $superadmin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$superadmin->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Login the superadmin
        Auth::guard('superadmin')->login($superadmin, $request->filled('remember'));

        // Update last login
        $superadmin->updateLastLogin();

        $request->session()->regenerate();

        return redirect()->intended(route('superadmin.dashboard'));
    }

    /**
     * Handle superadmin logout
     */
    public function logout(Request $request)
    {
        Auth::guard('superadmin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}
