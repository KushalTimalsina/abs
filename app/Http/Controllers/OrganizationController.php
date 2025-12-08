<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    /**
     * Show organization setup wizard (for new organizations)
     */
    public function setup()
    {
        $user = Auth::user();
        
        // Get user's organization
        $organization = $user->organizations()->wherePivot('role', 'admin')->first();
        
        if (!$organization) {
            return redirect()->route('dashboard')->with('error', 'No organization found');
        }

        return view('organization.setup', compact('organization'));
    }

    /**
     * Show organization profile
     */
    public function show(Organization $organization)
    {
        $this->authorize('view', $organization);
        
        $subscription = $organization->subscription;
        $teamMembers = $organization->users()->wherePivot('status', 'active')->get();
        $services = $organization->services()->where('is_active', true)->get();
        
        return view('organization.show', compact('organization', 'subscription', 'teamMembers', 'services'));
    }

    /**
     * Show edit form
     */
    public function edit(Organization $organization)
    {
        $this->authorize('update', $organization);
        
        return view('organization.edit', compact('organization'));
    }

    /**
     * Update organization
     */
    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }
            
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $organization->update($validated);

        return redirect()
            ->route('organization.show', $organization)
            ->with('success', 'Organization updated successfully');
    }

    /**
     * Complete initial setup
     */
    public function completeSetup(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);
        
        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $organization->update($validated);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Organization setup completed! You can now add services and team members.');
    }

    /**
     * Switch current organization context
     */
    public function switch(Organization $organization)
    {
        $user = Auth::user();
        
        // Verify user is member of this organization
        $isMember = $organization->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();
            
        if (!$isMember) {
            abort(403, 'You are not a member of this organization');
        }

        session(['current_organization_id' => $organization->id]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Switched to ' . $organization->name);
    }
}
