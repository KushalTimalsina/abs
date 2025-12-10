<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Services\BookingNumberService;

class OrganizationSettingsController extends Controller
{
    protected $bookingNumberService;

    public function __construct(BookingNumberService $bookingNumberService)
    {
        $this->bookingNumberService = $bookingNumberService;
    }

    /**
     * Show organization settings page
     */
    public function index(Organization $organization)
    {
        $this->authorize('view', $organization);

        // Get current settings
        $masterPrefix = $organization->getMasterPrefix();
        $showMasterPrefix = $organization->getShowMasterPrefix();
        $orgCode = $organization->getBookingMiniSlug();
        $centerPrefix = $organization->getCenterPrefix();
        $currentStart = $organization->getBookingNumberStart();
        $currentFormat = $organization->getBookingNumberFormat();

        // Generate sample booking numbers
        $samples = $organization->getSampleBookingNumbers(5);

        return view('organization.settings', compact(
            'organization',
            'masterPrefix',
            'showMasterPrefix',
            'orgCode',
            'centerPrefix',
            'currentStart',
            'currentFormat',
            'samples'
        ));
    }

    /**
     * Update booking number settings
     */
    public function updateBookingNumbers(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'booking_number_master_prefix' => ['required', 'string', 'max:5', 'regex:/^[A-Z]+$/'],
            'booking_number_show_master' => ['nullable', 'boolean'],
            'booking_number_center_prefix' => ['nullable', 'string', 'max:5', 'regex:/^[A-Z]*$/'],
            'booking_number_start' => ['required', 'integer', 'min:1', 'max:99999'],
            'booking_number_format' => ['required', 'in:dotted,compact'],
        ]);

        // Update settings
        $organization->setMasterPrefix($validated['booking_number_master_prefix']);
        $organization->setShowMasterPrefix($request->has('booking_number_show_master'));
        $organization->setCenterPrefix($validated['booking_number_center_prefix'] ?? '');
        $organization->setBookingNumberStart($validated['booking_number_start']);
        $organization->setBookingNumberFormat($validated['booking_number_format']);

        return redirect()
            ->route('organization.settings', $organization)
            ->with('success', 'Booking number settings updated successfully!');
    }

    /**
     * Preview booking numbers (AJAX)
     */
    public function previewBookingNumbers(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'master_prefix' => ['required', 'string', 'max:5'],
            'show_master' => ['nullable', 'boolean'],
            'center_prefix' => ['nullable', 'string', 'max:5'],
            'start' => ['required', 'integer', 'min:1', 'max:99999'],
            'format' => ['required', 'in:dotted,compact'],
        ]);

        $orgCode = $organization->getBookingMiniSlug();
        $samples = [];

        for ($i = 0; $i < 5; $i++) {
            $sequence = str_pad($validated['start'] + $i, 5, '0', STR_PAD_LEFT);
            
            $parts = [];
            
            // Add master prefix if visible
            if ($validated['show_master'] ?? true) {
                $parts[] = $validated['master_prefix'];
            }
            
            // Add organization code (always)
            $parts[] = $orgCode;
            
            // Add center prefix if provided
            if (!empty($validated['center_prefix'])) {
                $parts[] = $validated['center_prefix'];
            }
            
            // Add sequence number
            $parts[] = $sequence;
            
            // Join based on format
            if ($validated['format'] === 'compact') {
                $samples[] = implode('', $parts);
            } else {
                $samples[] = implode('.', $parts);
            }
        }

        return response()->json([
            'success' => true,
            'samples' => $samples,
        ]);
    }
}
