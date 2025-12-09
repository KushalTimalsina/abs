<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPayment;
use App\Models\WidgetSettings;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Mail\SubscriptionConfirmation;
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

            // If registering as admin, create organization and pending subscription payment
            if ($request->user_type === 'admin') {
                $organization = Organization::create([
                    'name' => $request->organization_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'status' => 'inactive', // Inactive until payment is verified
                ]);

                // Attach user as admin
                $organization->users()->attach($user->id, [
                    'role' => 'admin',
                    'status' => 'active',
                    'joined_at' => now(),
                    'permissions' => json_encode([]), // Admin has all permissions
                ]);

                // Create pending subscription payment instead of active subscription
                $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);
                $subscriptionPayment = SubscriptionPayment::create([
                    'organization_id' => $organization->id,
                    'subscription_plan_id' => $plan->id,
                    'amount' => $plan->price,
                    'payment_method' => 'pending', // Will be updated when payment is made
                    'status' => 'pending',
                    'duration_months' => ceil($plan->duration_days / 30),
                    'start_date' => now(),
                    'end_date' => now()->addDays($plan->duration_days ?? 30),
                ]);

                // Create default widget settings
                WidgetSettings::create([
                    'organization_id' => $organization->id,
                    'primary_color' => '#3B82F6',
                    'secondary_color' => '#1E40AF',
                    'font_family' => 'Inter, sans-serif',
                    'show_logo' => true,
                ]);

                // Store payment ID temporarily (will be set in session after login)
                $pendingPaymentId = $subscriptionPayment->id;
                $currentOrgId = $organization->id;
            }

            event(new Registered($user));
            
            // Send welcome email only (subscription confirmation sent after payment verification)
            try {
                Mail::to($user->email)->queue(new WelcomeEmail($user));
            } catch (\Exception $e) {
                // Log the error but don't fail registration
                \Log::error('Failed to send welcome email: ' . $e->getMessage());
            }

            Auth::login($user);

            // Set session AFTER login to prevent session regeneration from clearing it
            if ($user->user_type === 'admin' && isset($pendingPaymentId)) {
                session([
                    'current_organization_id' => $currentOrgId,
                    'pending_payment_id' => $pendingPaymentId,
                ]);
            }

            DB::commit();

            // Redirect based on user type
            if ($user->user_type === 'admin') {
                return redirect()->route('subscription.payment.show')
                    ->with('success', 'Please complete your subscription payment to activate your account.');
            }

            return redirect(RouteServiceProvider::HOME);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
