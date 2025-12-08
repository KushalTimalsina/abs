<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SuperadminDashboardController extends Controller
{
    /**
     * Display superadmin dashboard
     */
    public function index()
    {
        // Get statistics
        $totalOrganizations = Organization::count();
        $activeOrganizations = Organization::whereHas('subscription', function($q) {
            $q->where('is_active', true)
              ->where('end_date', '>=', now());
        })->count();
        
        // Get total revenue from verified subscription payments
        $totalRevenue = SubscriptionPayment::where('status', 'verified')->sum('amount');
        
        // Get pending payments count
        $pendingPayments = SubscriptionPayment::where('status', 'pending')->count();
        
        // Get recent organizations
        $recentOrganizations = Organization::with('subscription.plan')
            ->latest()
            ->limit(5)
            ->get();
        
        // Get recent payments
        $recentPayments = SubscriptionPayment::with(['organization', 'subscriptionPlan'])
            ->latest()
            ->limit(5)
            ->get();
        
        return view('superadmin.dashboard', compact(
            'totalOrganizations',
            'activeOrganizations',
            'totalRevenue',
            'pendingPayments',
            'recentOrganizations',
            'recentPayments'
        ));
    }

    /**
     * Get revenue chart data
     */
    protected function getRevenueChartData()
    {
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = Payment::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('amount') / 100;
            
            $data[] = [
                'date' => $date->format('M d'),
                'revenue' => $revenue,
            ];
        }
        
        return $data;
    }

    /**
     * Get organization growth data
     */
    protected function getOrganizationGrowthData()
    {
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Organization::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('M d'),
                'count' => $count,
            ];
        }
        
        return $data;
    }
}
