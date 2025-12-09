<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Show create notification form
     */
    public function create()
    {
        $organizations = Organization::where('status', 'active')->get();
        $plans = SubscriptionPlan::all();
        
        return view('superadmin.notifications.create', compact('organizations', 'plans'));
    }

    /**
     * Send notification to organizations
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'in:info,success,warning,error'],
            'recipient_type' => ['required', 'in:all,specific,plan'],
            'organizations' => ['required_if:recipient_type,specific', 'array'],
            'organizations.*' => ['exists:organizations,id'],
            'plans' => ['required_if:recipient_type,plan', 'array'],
            'plans.*' => ['exists:subscription_plans,id'],
        ]);
        
        // Get recipients based on type
        $recipients = collect();
        
        if ($validated['recipient_type'] === 'all') {
            // Get all organization admins
            $recipients = User::whereHas('organizations', function($query) {
                $query->where('status', 'active')
                      ->wherePivot('role', 'admin')
                      ->wherePivot('status', 'active');
            })->get();
            
        } elseif ($validated['recipient_type'] === 'specific') {
            // Get admins of specific organizations
            $recipients = User::whereHas('organizations', function($query) use ($validated) {
                $query->whereIn('organizations.id', $validated['organizations'])
                      ->where('organizations.status', 'active')
                      ->wherePivot('role', 'admin')
                      ->wherePivot('status', 'active');
            })->get();
            
        } elseif ($validated['recipient_type'] === 'plan') {
            // Get admins of organizations with specific plans
            $recipients = User::whereHas('organizations.subscription', function($query) use ($validated) {
                $query->whereIn('subscription_plan_id', $validated['plans'])
                      ->where('is_active', true);
            })->whereHas('organizations', function($query) {
                $query->wherePivot('role', 'admin')
                      ->wherePivot('status', 'active');
            })->get();
        }
        
        // Remove duplicates
        $recipients = $recipients->unique('id');
        
        // Send notification to each recipient
        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\CustomNotification(
                $validated['title'],
                $validated['message'],
                $validated['type']
            ));
        }
        
        return redirect()->route('superadmin.dashboard')
            ->with('success', 'Notification sent to ' . $recipients->count() . ' organization admin(s)');
    }
}
