<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Service;
use App\Models\Slot;
use App\Models\Booking;
use App\Models\User;
use App\Models\WidgetAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
    public function getAvailableSlots(Request $request, Organization $organization, $serviceId)
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

        // Manually fetch and validate the service belongs to the organization
        $service = Service::where('id', $serviceId)
            ->where('organization_id', $organization->id)
            ->first();
            
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        $date = Carbon::parse($request->date);
        $now = Carbon::now();
        
        // Get slots for the organization, service, and date
        $slots = Slot::where('organization_id', $organization->id)
            ->where('service_id', $service->id)
            ->whereDate('date', $date)
            ->where('status', 'available')
            ->whereDoesntHave('booking', function($q) {
                $q->whereIn('status', ['pending', 'confirmed']);
            })
            ->with('staff:id,name')
            ->orderBy('start_time')
            ->get()
            ->map(function($slot) use ($now, $date) {
                // Check if this slot is in the past
                // Format start_time properly since it's already a Carbon instance
                $slotDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $slot->start_time->format('H:i:s'));
                $isPast = $slotDateTime->lt($now);
                
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time->format('H:i'),
                    'end_time' => $slot->end_time->format('H:i'),
                    'staff' => $slot->staff ? $slot->staff->name : 'Any Staff',
                    'available' => !$isPast, // Mark past slots as unavailable
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
            'service_id' => 'required|exists:services,id',
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

        $slot = Slot::with('shift')->findOrFail($request->slot_id);

        // Verify slot belongs to organization
        if ($slot->organization_id !== $organization->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid slot',
            ], 400);
        }

        // Check if slot is still available
        if ($slot->status !== 'available') {
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

        // Get or create customer user
        $customer = User::firstOrCreate(
            ['email' => $request->customer_email],
            [
                'name' => $request->customer_name,
                'phone' => $request->customer_phone,
                'password' => bcrypt(Str::random(16)),
                'user_type' => 'customer',
            ]
        );

        try {
            // Get the booking date
            $bookingDate = $slot->date instanceof \Carbon\Carbon ? $slot->date : Carbon::parse($slot->date);
            
            // Create full datetime by combining date with time
            // Extract time components from slot times
            $startTime = $slot->start_time instanceof \Carbon\Carbon 
                ? $slot->start_time->format('H:i:s') 
                : $slot->start_time;
            $endTime = $slot->end_time instanceof \Carbon\Carbon 
                ? $slot->end_time->format('H:i:s') 
                : $slot->end_time;
            
            // Create full datetime objects
            $startDateTime = Carbon::parse($bookingDate->format('Y-m-d') . ' ' . $startTime);
            $endDateTime = Carbon::parse($bookingDate->format('Y-m-d') . ' ' . $endTime);
            
            // Create booking
            $booking = Booking::create([
                'organization_id' => $organization->id,
                'service_id' => $request->service_id,
                'slot_id' => $slot->id,
                'customer_id' => $customer->id,
                'staff_id' => $slot->assigned_staff_id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'booking_date' => $bookingDate,
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'customer_notes' => $request->notes,
                'booking_number' => 'BK-' . strtoupper(uniqid()),
            ]);

            // Note: Don't change slot status to 'booked' here - keep it as 'available'
            // The slot is linked to the booking via the booking relationship
            // $slot->update(['status' => 'booked']);

            // Track booking conversion
            $this->trackAnalytics($organization, 'booking');
            
            // Send booking confirmation email (queued)
            try {
                \Mail::to($booking->customer_email)
                    ->queue(new \App\Mail\BookingConfirmation($booking));
            } catch (\Exception $e) {
                \Log::error('Failed to queue booking confirmation email', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the booking if email fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'service' => $organization->name . ' Booking',
                    'date' => $booking->booking_date->format('l, F d, Y'),
                    'time' => $booking->start_time->format('h:i A') . ' - ' . $booking->end_time->format('h:i A'),
                    'status' => $booking->status,
                ],
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Widget booking creation failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'organization_id' => $organization->id,
                'service_id' => $request->service_id,
                'slot_id' => $request->slot_id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating your booking. Please try again or contact support.',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile()),
                ] : null,
            ], 500);
        }
    }

    /**
     * Track widget analytics
     */
    protected function trackAnalytics(Organization $organization, string $eventType)
    {
        // Get or create widget settings for the organization
        $widgetSettings = $organization->widgetSettings;
        
        if (!$widgetSettings) {
            // Skip analytics if no widget settings exist
            return;
        }
        
        WidgetAnalytics::updateOrCreate(
            [
                'widget_settings_id' => $widgetSettings->id,
                'organization_id' => $organization->id,
                'date' => now()->toDateString(),
            ],
            [
                'event_type' => $eventType,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('referer'),
            ]
        );
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

    /**
     * Initiate payment for widget booking
     */
    public function initiatePayment(Request $request, Organization $organization, Booking $booking)
    {
        // Verify booking belongs to organization
        if ($booking->organization_id !== $organization->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid booking',
            ], 403);
        }

        // Check if already paid
        if ($booking->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already been paid',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'gateway' => 'required|in:esewa,khalti,stripe,cash',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // For cash payment, just mark as pending payment
            if ($request->gateway === 'cash') {
                return response()->json([
                    'success' => true,
                    'payment_type' => 'cash',
                    'message' => 'Please pay cash at the venue',
                ]);
            }

            // Get payment gateway settings
            $gateway = $organization->paymentGateways()
                ->where('gateway_name', $request->gateway)
                ->where('is_active', true)
                ->first();

            if (!$gateway) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not available',
                ], 400);
            }

            // Calculate amount (use service price or default)
            $amount = $booking->service?->price ?? 1000; // Default amount if no service

            // Create payment record
            $payment = \App\Models\Payment::create([
                'organization_id' => $organization->id,
                'booking_id' => $booking->id,
                'amount' => $amount,
                'payment_method' => $request->gateway,
                'status' => 'pending',
                'currency' => 'NPR',
            ]);

            // Generate payment URL
            $successUrl = route('api.widget.payment.success', [$organization, $booking, $payment]);
            $failureUrl = route('api.widget.payment.failure', [$organization, $booking, $payment]);

            return response()->json([
                'success' => true,
                'payment_type' => 'redirect',
                'payment_url' => $successUrl . '?test=true', // Test URL for now
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle payment success callback
     */
    public function paymentSuccess(Organization $organization, Booking $booking, \App\Models\Payment $payment)
    {
        try {
            // Update payment status
            $payment->update([
                'status' => 'completed',
                'transaction_id' => request('transaction_id', 'TEST-' . time()),
            ]);

            // Update booking status
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            // Redirect to widget with success message
            return redirect()->route('widget.show', $organization->slug)
                ->with('payment_success', 'Payment successful! Your booking is confirmed.');

        } catch (\Exception $e) {
            return redirect()->route('widget.show', $organization->slug)
                ->with('payment_error', 'Payment verification failed');
        }
    }

    /**
     * Handle payment failure callback
     */
    public function paymentFailure(Organization $organization, Booking $booking, \App\Models\Payment $payment)
    {
        // Update payment status
        $payment->update(['status' => 'failed']);

        // Redirect to widget with error message
        return redirect()->route('widget.show', $organization->slug)
            ->with('payment_error', 'Payment was cancelled or failed. Please try again.');
    }

    /**
     * Submit bank transfer payment proof
     */
    public function submitBankTransfer(Request $request, Organization $organization, Booking $booking)
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'string', 'max:255'],
            'proof_image' => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        try {
            // Store the payment proof image
            $proofPath = $request->file('proof_image')->store('payment-proofs', 'public');

            // Create or update payment record
            $payment = \App\Models\Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'organization_id' => $organization->id,
                    'amount' => $booking->service->price,
                    'payment_method' => 'bank_transfer',
                    'transaction_id' => $validated['transaction_id'],
                    'status' => 'pending',
                    'payment_proof' => $proofPath,
                ]
            );

            // Update booking
            $booking->update([
                'payment_status' => 'unpaid',
                'payment_method' => 'bank_transfer',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment proof submitted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit: ' . $e->getMessage(),
            ], 500);
        }
    }
}
