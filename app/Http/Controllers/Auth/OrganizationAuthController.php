<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRegistrationRequest;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganizationAuthController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        return view('auth.register-organization', compact('plans'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(OrganizationRegistrationRequest $request): RedirectResponse
    {
        // 1. Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'admin',
            'phone' => $request->phone,
        ]);

        // 2. Create Organization
        $organization = Organization::create([
            'name' => $request->organization_name,
            'slug' => Str::slug($request->organization_name), // Slug is auto-generated in boot but good to be explicit/safe or let model handle it.
            // 'email' => $request->email, // Optional: use admin email as org email initially
        ]);

        // 3. Link User to Organization as Admin
        $organization->users()->attach($user->id, [
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
            'permissions' => json_encode(['all']), // Admin gets all permissions
        ]);

        // 4. Create Subscription
        $plan = SubscriptionPlan::where('slug', $request->plan_slug)->firstOrFail();
        
        OrganizationSubscription::create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            // For now, assuming infinite trial or manual payment activation. 
            // In real flow, might redirect to payment. For MVP/Task, active immediately or trial.
            // Let's set it as 'active' for 'Cash' plans (Basic) or if free.
            // If strictly following logic, 'Basic' is 50rs. 
            // Let's assume for this "Registration" flow we activate it for now or set logic.
            // Requirement says "Subscribe to a plan".
            // Let's default to active with 30 days expiry for Demo purposes.
            'ends_at' => now()->addDays(30), 
            'status' => 'active',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard'); // Redirect to dashboard
    }
}
