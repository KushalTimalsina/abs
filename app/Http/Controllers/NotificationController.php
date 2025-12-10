<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display notifications
     */
    public function index()
    {
        // Check if superadmin
        $superadmin = Auth::guard('superadmin')->user();
        $user = $superadmin ?? Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $notifications = $user->notifications()
            ->paginate(20);
        
        $unreadCount = $user->unreadNotifications()->count();
        
        // Get sent notifications for admins/superadmin
        $sentNotifications = null;
        if ($superadmin || ($user && isAdmin())) {
            $sentNotifications = \App\Models\CustomNotification::where(function($query) use ($superadmin, $user) {
                if ($superadmin) {
                    $query->where('sender_type', 'superadmin')
                          ->where('sender_id', $superadmin->id);
                } else {
                    $query->where('sender_type', 'organization')
                          ->where('sender_id', $user->organizations()->first()->id ?? 0);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'sent_page');
        }
        
        return view('notifications.index', compact('notifications', 'unreadCount', 'sentNotifications'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);
        
        $notification->markAsRead();
        
        return redirect()->back()
            ->with('success', 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return redirect()->back()
            ->with('success', 'All notifications marked as read');
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);
        
        $notification->delete();
        
        return redirect()->back()
            ->with('success', 'Notification deleted');
    }

    /**
     * Show create notification form (for admins and superadmin)
     */
    public function create()
    {
        // Check if superadmin
        $superadmin = Auth::guard('superadmin')->user();
        
        if ($superadmin) {
            // Show superadmin notification form
            $organizations = \App\Models\Organization::where('status', 'active')->get();
            $plans = \App\Models\SubscriptionPlan::all();
            return view('superadmin.notifications.create', compact('organizations', 'plans'));
        }
        
        // Regular user - organization admin
        $user = Auth::user();
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return redirect()->route('notifications.index')
                ->with('error', 'You must be part of an organization to send notifications.');
        }
        
        // Get team members
        $teamMembers = $organization->users()
            ->wherePivot('status', 'active')
            ->get();
        
        // Get customers (users who have made bookings with this organization)
        $customers = \App\Models\User::whereHas('bookings', function($query) use ($organization) {
            $query->where('organization_id', $organization->id);
        })
        ->where('user_type', 'customer')
        ->distinct()
        ->get();
        
        return view('notifications.create', compact('organization', 'teamMembers', 'customers'));
    }

    /**
     * Send notification to team members or organizations
     */
    public function store(Request $request)
    {
        // Check if superadmin
        $superadmin = Auth::guard('superadmin')->user();
        
        if ($superadmin) {
            // Superadmin sending to organizations
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
                $recipients = \App\Models\User::whereHas('organizations', function($query) {
                    $query->where('organizations.status', 'active')
                          ->where('organization_users.role', 'admin')
                          ->where('organization_users.status', 'active');
                })->get();
            } elseif ($validated['recipient_type'] === 'specific') {
                $recipients = \App\Models\User::whereHas('organizations', function($query) use ($validated) {
                    $query->whereIn('organizations.id', $validated['organizations'])
                          ->where('organizations.status', 'active')
                          ->where('organization_users.role', 'admin')
                          ->where('organization_users.status', 'active');
                })->get();
            } elseif ($validated['recipient_type'] === 'plan') {
                $recipients = \App\Models\User::whereHas('organizations.subscription', function($query) use ($validated) {
                    $query->whereIn('subscription_plan_id', $validated['plans'])
                          ->where('is_active', true);
                })->whereHas('organizations', function($query) {
                    $query->where('organizations.status', 'active')
                          ->where('organization_users.role', 'admin')
                          ->where('organization_users.status', 'active');
                })->get();
            }
            
            $recipients = $recipients->unique('id');
            
            // Send notification to each recipient
            foreach ($recipients as $recipient) {
                $recipient->notify(new \App\Notifications\CustomNotification(
                    $validated['title'],
                    $validated['message'],
                    $validated['type']
                ));
            }
            
            // Also send a copy to self so superadmin can see it in their notifications
            $superadmin->notify(new \App\Notifications\CustomNotification(
                $validated['title'],
                '[SENT] ' . $validated['message'],
                $validated['type']
            ));
            
            return redirect(url('/superadmin/notifications'))
                ->with('success', 'Notification sent to ' . $recipients->count() . ' organization admin(s)');
        }
        
        // Regular user - organization admin
        $user = Auth::user();
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return redirect()->route('notifications.index')
                ->with('error', 'You must be part of an organization to send notifications.');
        }
        
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'in:info,success,warning,error'],
            'recipient_type' => ['required', 'in:all_team,specific_team,all_customers,specific_customers'],
            'recipients' => ['required_if:recipient_type,specific_team,specific_customers', 'array'],
            'recipients.*' => ['exists:users,id'],
        ]);
        
        // Get recipients based on type
        if ($validated['recipient_type'] === 'all_team') {
            $recipients = $organization->users()
                ->wherePivot('status', 'active')
                ->get();
        } elseif ($validated['recipient_type'] === 'specific_team') {
            $recipients = $organization->users()
                ->whereIn('users.id', $validated['recipients'])
                ->wherePivot('status', 'active')
                ->get();
        } elseif ($validated['recipient_type'] === 'all_customers') {
            $recipients = \App\Models\User::whereHas('bookings', function($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            })
            ->where('user_type', 'customer')
            ->distinct()
            ->get();
        } else { // specific_customers
            $recipients = \App\Models\User::whereIn('id', $validated['recipients'])
                ->where('user_type', 'customer')
                ->get();
        }
        
        // Send notification to each recipient
        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\CustomNotification(
                $validated['title'],
                $validated['message'],
                $validated['type']
            ));
        }
        
        // Also send a copy to self so admin can see it in their notifications
        $user->notify(new \App\Notifications\CustomNotification(
            $validated['title'],
            '[SENT] ' . $validated['message'],
            $validated['type']
        ));
        
        $recipientType = str_contains($validated['recipient_type'], 'customer') ? 'customer(s)' : 'team member(s)';
        return redirect()->route('notifications.index')
            ->with('success', 'Notification sent to ' . $recipients->count() . ' ' . $recipientType);
    }
}
