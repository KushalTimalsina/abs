<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Service;
use App\Models\Slot;
use App\Models\Booking;
use App\Models\WidgetAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class WidgetApiController extends Controller
{
    /**
     * Get organization services for widget
     */
    public function getServices(Organization $organization)
    {
        // Track widget view
        $this->trackAnalytics($organization, 'view');
        
        $services = $organization->services()
            ->where('is_active', true)
            ->select('id', 'name', 'description', 'duration', 'price')
            ->get();
        
        return response()->json([
            'success' => true,
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'description' => $organization->description,
            ],
            'services' => $services,
        ]);
    }

    /**
     * Get available slots for a service
     */
    public function getAvailableSlots(Request $request, Organization $organization, Service $service)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = Carbon::parse($request->date);
        
        $slots = Slot::where('organization_id', $organization->id)
            ->where('service_id', $service->id)
            ->where('slot_date', $date)
            ->where('is_available', true)
            ->where('is_blocked', false)
            ->whereDoesntHave('booking', function($q) {
                $q->whereIn('status', ['pending', 'confirmed']);
            })
            ->with('staff:id,name')
            ->orderBy('start_time')
            ->get()
            ->map(function($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time->format('H:i'),
                    'end_time' => $slot->end_time->format('H:i'),
                    'staff' => $slot->staff->name,
                ];
            });

        return response()->json([
            'success' => true,
            'date' => $date->format('Y-m-d'),
            'slots' => $slots,
        ]);
    }

    /**
     * Create booking from widget
     */
    public function createBooking(Request $request, Organization $organization)
    {
        $validator = Validator::make($request->all(), [
            'slot_id' => 'required|exists:slots,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $slot = Slot::with('service')->findOrFail($request->slot_id);

        // Verify slot belongs to organization
        if ($slot->organization_id !== $organization->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid slot',
            ], 400);
        }

        // Check if slot is still available
        if (!$slot->is_available || $slot->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'This slot is no longer available',
            ], 400);
        }

        // Check for existing booking
        if ($slot->booking()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This slot has already been booked',
            ], 400);
        }

        // Create booking
        $booking = Booking::create([
            'organization_id' => $organization->id,
            'service_id' => $slot->service_id,
            'slot_id' => $slot->id,
            'staff_id' => $slot->staff_id,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'booking_date' => $slot->slot_date,
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'status' => 'pending',
            'payment_status' => 'pending',
            'notes' => $request->notes,
            'booking_number' => 'BK-' . strtoupper(uniqid()),
        ]);

        // Mark slot as unavailable
        $slot->update(['is_available' => false]);

        // Track booking conversion
        $this->trackAnalytics($organization, 'booking');

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking' => [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'service' => $slot->service->name,
                'date' => $booking->booking_date->format('l, F d, Y'),
                'time' => $booking->start_time->format('h:i A'),
                'status' => $booking->status,
            ],
        ]);
    }

    /**
     * Track widget analytics
     */
    protected function trackAnalytics(Organization $organization, string $eventType)
    {
        WidgetAnalytics::create([
            'organization_id' => $organization->id,
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
        ]);
    }

    /**
     * Get widget analytics (for organization dashboard)
     */
    public function getAnalytics(Organization $organization)
    {
        $this->authorize('view', $organization);

        $stats = [
            'total_views' => WidgetAnalytics::where('organization_id', $organization->id)
                ->where('event_type', 'view')
                ->count(),
            'total_bookings' => WidgetAnalytics::where('organization_id', $organization->id)
                ->where('event_type', 'booking')
                ->count(),
            'views_today' => WidgetAnalytics::where('organization_id', $organization->id)
                ->where('event_type', 'view')
                ->whereDate('created_at', today())
                ->count(),
            'bookings_today' => WidgetAnalytics::where('organization_id', $organization->id)
                ->where('event_type', 'booking')
                ->whereDate('created_at', today())
                ->count(),
        ];

        // Calculate conversion rate
        $stats['conversion_rate'] = $stats['total_views'] > 0 
            ? round(($stats['total_bookings'] / $stats['total_views']) * 100, 2) 
            : 0;

        // Get daily stats for last 7 days
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $views = WidgetAnalytics::where('organization_id', $organization->id)
                ->where('event_type', 'view')
                ->whereDate('created_at', $date)
                ->count();
            $bookings = WidgetAnalytics::where('organization_id', $organization->id)
                ->where('event_type', 'booking')
                ->whereDate('created_at', $date)
                ->count();

            $dailyStats[] = [
                'date' => $date->format('M d'),
                'views' => $views,
                'bookings' => $bookings,
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'daily_stats' => $dailyStats,
        ]);
    }
}
