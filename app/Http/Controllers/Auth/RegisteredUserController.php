<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SubscriptionPlan;
use App\Models\WidgetSettings;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return view('auth.register', compact('plans'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'in:admin,customer'],
            'phone' => ['nullable', 'string', 'max:20'],
            
            // Organization fields (required for admin)
            'organization_name' => ['required_if:user_type,admin', 'string', 'max:255'],
            'subscription_plan_id' => ['required_if:user_type,admin', 'exists:subscription_plans,id'],
        ]);

        DB::beginTransaction();
        
        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => $request->user_type,
                'phone' => $request->phone,
            ]);

            // If registering as admin, create organization and subscription
            if ($request->user_type === 'admin') {
                $organization = Organization::create([
                    'name' => $request->organization_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'status' => 'active',
                ]);

                // Attach user as admin
                $organization->users()->attach($user->id, [
                    'role' => 'admin',
                    'status' => 'active',
                    'joined_at' => now(),
                    'permissions' => json_encode([]), // Admin has all permissions
                ]);

                // Create subscription
                $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);
                OrganizationSubscription::create([
                    'organization_id' => $organization->id,
                    'subscription_plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(), // 1 month subscription
                    'status' => 'active',
                    'auto_renew' => true,
                ]);

                // Create default widget settings
                WidgetSettings::create([
                    'organization_id' => $organization->id,
                    'primary_color' => '#3B82F6',
                    'secondary_color' => '#1E40AF',
                    'font_family' => 'Inter, sans-serif',
                    'show_logo' => true,
                ]);

                // Store organization in session
                session(['current_organization_id' => $organization->id]);
            }

            event(new Registered($user));

            Auth::login($user);

            DB::commit();

            // Redirect based on user type
            if ($user->user_type === 'admin') {
                return redirect()->route('organization.setup')
                    ->with('success', 'Welcome! Let\'s complete your organization setup.');
            }

            return redirect(RouteServiceProvider::HOME);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
