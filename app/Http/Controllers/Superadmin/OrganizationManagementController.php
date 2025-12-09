<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use Illuminate\Http\Request;

class OrganizationManagementController extends Controller
{
    /**
     * Display all organizations
     */
    public function index(Request $request)
    {
        $query = Organization::with(['subscription.plan', 'users']);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('status', 'active');
            } elseif ($request->status === 'suspended') {
                $query->where('status', 'suspended');
            }
        }

        // Search
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = $request->input('per_page', 20);
        $organizations = $query->paginate($perPage)->withQueryString();

        return view('superadmin.organizations.index', compact('organizations'));
    }

    /**
     * Show organization details
     */
    public function show(Organization $organization)
    {
        $organization->load([
            'subscription.plan',
            'users',
            'services',
            'bookings.customer',
            'payments'
        ]);

        $stats = [
            'total_bookings' => $organization->bookings()->count(),
            'completed_bookings' => $organization->bookings()->where('status', 'completed')->count(),
            'total_revenue' => $organization->payments()->where('status', 'completed')->sum('amount') / 100,
            'team_members' => $organization->users()->count(),
            'active_services' => $organization->services()->where('is_active', true)->count(),
        ];

        return view('superadmin.organizations.show', compact('organization', 'stats'));
    }

    /**
     * Suspend organization
     */
    public function suspend(Organization $organization)
    {
        $organization->update(['status' => 'suspended']);
        
        $organization->subscription()->update([
            'is_active' => false,
        ]);

        return redirect()->back()
            ->with('success', 'Organization suspended successfully');
    }

    /**
     * Activate organization
     */
    public function activate(Organization $organization)
    {
        $organization->update(['status' => 'active']);
        
        $organization->subscription()->update([
            'is_active' => true,
        ]);

        return redirect()->back()
            ->with('success', 'Organization activated successfully');
    }

    /**
     * Delete organization
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('superadmin.organizations.index')
            ->with('success', 'Organization deleted successfully');
    }
}
