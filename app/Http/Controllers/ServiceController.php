<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * Display a listing of services
     */
    public function index(Organization $organization)
    {
        $this->authorize('view', $organization);
        
        $services = $organization->services()->latest()->paginate(15);
        
        return view('services.index', compact('organization', 'services'));
    }

    /**
     * Show the form for creating a new service
     */
    public function create(Organization $organization)
    {
        $this->authorize('manageServices', $organization);
        
        return view('services.create', compact('organization'));
    }

    /**
     * Store a newly created service
     */
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('manageServices', $organization);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration' => ['required', 'integer', 'min:5', 'max:480'], // 5 min to 8 hours
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['organization_id'] = $organization->id;
        $validated['is_active'] = $request->has('is_active');

        $service = Service::create($validated);

        return redirect()
            ->route('services.index', $organization)
            ->with('success', 'Service created successfully');
    }

    /**
     * Display the specified service
     */
    public function show(Organization $organization, Service $service)
    {
        $this->authorize('view', $organization);
        
        if ($service->organization_id !== $organization->id) {
            abort(404);
        }
        
        return view('services.show', compact('organization', 'service'));
    }

    /**
     * Show the form for editing the specified service
     */
    public function edit(Organization $organization, Service $service)
    {
        $this->authorize('manageServices', $organization);
        
        if ($service->organization_id !== $organization->id) {
            abort(404);
        }
        
        return view('services.edit', compact('organization', 'service'));
    }

    /**
     * Update the specified service
     */
    public function update(Request $request, Organization $organization, Service $service)
    {
        $this->authorize('manageServices', $organization);
        
        if ($service->organization_id !== $organization->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $service->update($validated);

        return redirect()
            ->route('services.index', $organization)
            ->with('success', 'Service updated successfully');
    }

    /**
     * Remove the specified service
     */
    public function destroy(Organization $organization, Service $service)
    {
        $this->authorize('manageServices', $organization);
        
        if ($service->organization_id !== $organization->id) {
            abort(404);
        }
        
        // Check if service has active bookings
        $hasActiveBookings = $service->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
            
        if ($hasActiveBookings) {
            return redirect()
                ->route('services.index', $organization)
                ->with('error', 'Cannot delete service with active bookings. Please cancel or complete them first.');
        }
        
        $service->delete();

        return redirect()
            ->route('services.index', $organization)
            ->with('success', 'Service deleted successfully');
    }

    /**
     * Toggle service active status
     */
    public function toggleStatus(Organization $organization, Service $service)
    {
        $this->authorize('manageServices', $organization);
        
        if ($service->organization_id !== $organization->id) {
            abort(404);
        }
        
        $service->update(['is_active' => !$service->is_active]);

        return redirect()
            ->back()
            ->with('success', 'Service status updated');
    }
}
