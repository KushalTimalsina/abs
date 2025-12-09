<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display all subscription plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount('subscriptions')->paginate(15);

        return view('superadmin.plans.index', compact('plans'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('superadmin.plans.create');
    }

    /**
     * Store new plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_team_members' => 'nullable|integer|min:1',
            'max_services' => 'nullable|integer|min:1',
            'max_bookings_per_month' => 'nullable|integer|min:1',
            'slot_scheduling_days' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-generate slug from name
        $validated['slug'] = \Str::slug($validated['name']);
        
        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (SubscriptionPlan::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Set defaults for optional fields
        $validated['max_team_members'] = $validated['max_team_members'] ?? 999999;
        $validated['max_services'] = $validated['max_services'] ?? 999999;
        $validated['slot_scheduling_days'] = $validated['slot_scheduling_days'] ?? 365;
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['online_payment_enabled'] = $request->has('online_payment_enabled') ? true : false;

        SubscriptionPlan::create($validated);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Subscription plan created successfully');
    }

    /**
     * Show edit form
     */
    public function edit(SubscriptionPlan $plan)
    {
        return view('superadmin.plans.edit', compact('plan'));
    }

    /**
     * Update plan
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_team_members' => 'nullable|integer|min:1',
            'max_services' => 'nullable|integer|min:1',
            'max_bookings_per_month' => 'nullable|integer|min:1',
            'slot_scheduling_days' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'online_payment_enabled' => 'boolean',
        ]);

        $validated['features'] = json_encode($validated['features'] ?? []);
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['online_payment_enabled'] = $request->has('online_payment_enabled') ? true : false;

        $plan->update($validated);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Subscription plan updated successfully');
    }

    /**
     * Delete plan
     */
    public function destroy(SubscriptionPlan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete plan with active subscriptions');
        }

        $plan->delete();

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Subscription plan deleted successfully');
    }
}
