<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Service;
use App\Models\Slot;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PublicBookingController extends Controller
{
    /**
     * Show organization's public booking page
     */
    public function index($slug)
    {
        $organization = Organization::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $services = $organization->services()
            ->where('is_active', true)
            ->get();

        return view('public.booking.index', compact('organization', 'services'));
    }

    /**
     * Show service details and slot selection
     */
    public function showService($slug, Service $service)
    {
        $organization = Organization::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        if ($service->organization_id !== $organization->id) {
            abort(404);
        }

        return view('public.booking.service', compact('organization', 'service'));
    }

    /**
     * Get available slots for a service on a specific date
     */
    public function getAvailableSlots(Request $request, $slug, Service $service)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $organization = Organization::where('slug', $slug)->firstOrFail();

        if ($service->organization_id !== $organization->id) {
            abort(404);
        }

        $date = Carbon::parse($request->date);

        $slots = Slot::where('service_id', $service->id)
            ->whereDate('date', $date)
            ->where('status', 'available')
            ->with('staff:id,name')
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'staff_name' => $slot->staff->name ?? 'Any Staff',
                    'available' => $slot->status === 'available',
                ];
            });

        return response()->json($slots);
    }

    /**
     * Show booking form
     */
    public function showBookingForm($slug, Service $service, Slot $slot)
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if ($service->organization_id !== $organization->id || $slot->service_id !== $service->id) {
            abort(404);
        }

        if ($slot->status !== 'available') {
            return redirect()->route('public.booking.service', [$slug, $service])
                ->with('error', 'This time slot is no longer available.');
        }

        return view('public.booking.form', compact('organization', 'service', 'slot'));
    }

    /**
     * Create a new booking
     */
    public function store(Request $request, $slug)
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'slot_id' => 'required|exists:slots,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Get or create customer user
            $customer = User::firstOrCreate(
                ['email' => $validated['customer_email']],
                [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'password' => bcrypt(Str::random(16)), // Random password
                    'user_type' => 'customer',
                ]
            );

            // Get slot and verify availability
            $slot = Slot::findOrFail($validated['slot_id']);
            
            if ($slot->status !== 'available') {
                throw new \Exception('This time slot is no longer available.');
            }

            // Create booking
            $booking = Booking::create([
                'organization_id' => $organization->id,
                'service_id' => $validated['service_id'],
                'slot_id' => $slot->id,
                'customer_id' => $customer->id,
                'staff_id' => $slot->staff_id,
                'booking_date' => $slot->date,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'customer_notes' => $validated['notes'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            // Update slot status
            $slot->update(['status' => 'booked']);

            DB::commit();

            // Send confirmation email (optional)
            // Mail::to($customer->email)->send(new BookingConfirmation($booking));

            return redirect()->route('public.booking.confirmation', [$slug, $booking])
                ->with('success', 'Booking created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    /**
     * Show booking confirmation
     */
    public function confirmation($slug, Booking $booking)
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        $booking->load(['service', 'slot']);

        return view('public.booking.confirmation', compact('organization', 'booking'));
    }
}
