<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display role-specific dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Redirect based on user type
        switch ($user->user_type) {
            case 'superadmin':
                return redirect()->route('superadmin.dashboard');
            case 'admin':
            case 'team_member':
            case 'frontdesk':
                return $this->organizationDashboard();
            case 'customer':
                return $this->customerDashboard();
            default:
                abort(403, 'Invalid user type');
        }
    }

    /**
     * Organization dashboard (Admin, Team, Frontdesk)
     */
    protected function organizationDashboard()
    {
        $user = Auth::user();
        $organizationId = session('current_organization_id');
        
        if (!$organizationId) {
            // Get first organization user belongs to
            $organization = $user->organizations()->first();
            if ($organization) {
                session(['current_organization_id' => $organization->id]);
                $organizationId = $organization->id;
            } else {
                return redirect()->route('organization.setup')
                    ->with('error', 'Please complete your organization setup');
            }
        }
        
        $organization = Organization::findOrFail($organizationId);
        
        // Check authorization
        if (!$user->organizations()->where('organization_id', $organization->id)->exists()) {
            abort(403, 'You do not have access to this organization');
        }

        // Get user's role in organization
        $userRole = $user->organizations()
            ->where('organization_id', $organization->id)
            ->first()->pivot->role;

        // Get dashboard data based on role
        $data = $this->getDashboardData($organization, $user, $userRole);
        
        return view('dashboards.organization', compact('organization', 'user', 'userRole', 'data'));
    }

    /**
     * Customer dashboard
     */
    protected function customerDashboard()
    {
        $user = Auth::user();
        
        // Get customer's bookings
        $upcomingBookings = $user->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', today())
            ->with(['organization', 'service', 'staff'])
            ->orderBy('booking_date')
            ->limit(5)
            ->get();
        
        $pastBookings = $user->bookings()
            ->whereIn('status', ['completed', 'cancelled'])
            ->with(['organization', 'service', 'staff'])
            ->orderBy('booking_date', 'desc')
            ->limit(10)
            ->get();
        
        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'upcoming_bookings' => $user->bookings()
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('booking_date', '>=', today())
                ->count(),
            'completed_bookings' => $user->bookings()
                ->where('status', 'completed')
                ->count(),
            'total_spent' => $user->bookings()
                ->whereHas('payment', function($q) {
                    $q->where('status', 'completed');
                })
                ->with('payment')
                ->get()
                ->sum(function($booking) {
                    return $booking->payment ? $booking->payment->amount / 100 : 0;
                }),
        ];
        
        return view('dashboards.customer', compact('upcomingBookings', 'pastBookings', 'stats'));
    }

    /**
     * Get dashboard data based on role
     */
    protected function getDashboardData(Organization $organization, $user, $role)
    {
        $data = [];
        
        // Common metrics for all roles
        $data['today_bookings'] = $organization->bookings()
            ->whereDate('booking_date', today())
            ->count();
        
        $data['upcoming_bookings'] = $organization->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', today())
            ->count();

        if ($role === 'admin') {
            // Admin-specific metrics
            $data['total_revenue'] = $organization->bookings()
                ->whereHas('payment', function($q) {
                    $q->where('status', 'completed');
                })
                ->with('payment')
                ->get()
                ->sum(function($booking) {
                    return $booking->payment ? $booking->payment->amount / 100 : 0;
                });
            
            $data['this_month_revenue'] = $organization->bookings()
                ->whereHas('payment', function($q) {
                    $q->where('status', 'completed')
                      ->whereMonth('created_at', now()->month);
                })
                ->with('payment')
                ->get()
                ->sum(function($booking) {
                    return $booking->payment ? $booking->payment->amount / 100 : 0;
                });
            
            $data['total_customers'] = $organization->bookings()
                ->distinct('customer_id')
                ->count('customer_id');
            
            $data['team_members'] = $organization->users()
                ->wherePivot('status', 'active')
                ->count();
            
            $data['active_services'] = $organization->services()->count();
            
            // Recent bookings
            $data['recent_bookings'] = $organization->bookings()
                ->with(['service', 'staff'])
                ->latest()
                ->limit(10)
                ->get();
            
            // Revenue chart data (last 7 days)
            $data['revenue_chart'] = $this->getRevenueChartData($organization);
            
        } elseif ($role === 'team_member') {
            // Team member-specific metrics
            $data['my_bookings_today'] = $organization->bookings()
                ->where('staff_id', $user->id)
                ->whereDate('booking_date', today())
                ->count();
            
            $data['my_upcoming_bookings'] = $organization->bookings()
                ->where('staff_id', $user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('booking_date', '>=', today())
                ->with(['service'])
                ->orderBy('start_time')
                ->limit(10)
                ->get();
            
        } elseif ($role === 'frontdesk') {
            // Frontdesk-specific metrics
            $data['pending_bookings'] = $organization->bookings()
                ->where('status', 'pending')
                ->count();
            
            $data['today_schedule'] = $organization->bookings()
                ->whereDate('booking_date', today())
                ->with(['service', 'staff'])
                ->orderBy('start_time')
                ->get();
        }
        
        return $data;
    }

    /**
     * Get revenue chart data for last 7 days
     */
    protected function getRevenueChartData(Organization $organization)
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = $organization->bookings()
                ->whereHas('payment', function($q) use ($date) {
                    $q->where('status', 'completed')
                      ->whereDate('created_at', $date);
                })
                ->with('payment')
                ->get()
                ->sum(function($booking) {
                    return $booking->payment ? $booking->payment->amount / 100 : 0;
                });
            
            $data[] = [
                'date' => $date->format('M d'),
                'revenue' => $revenue,
            ];
        }
        
        return $data;
    }

    /**
     * Switch organization
     */
    public function switchOrganization(Request $request, Organization $organization)
    {
        $user = Auth::user();
        
        // Verify user has access to this organization
        if (!$user->organizations()->where('organization_id', $organization->id)->exists()) {
            abort(403, 'You do not have access to this organization');
        }
        
        session(['current_organization_id' => $organization->id]);
        
        return redirect()->route('dashboard')
            ->with('success', "Switched to {$organization->name}");
    }
}
