<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerBookingController extends Controller
{
    /**
     * Display customer's bookings
     */
    public function index()
    {
        $customer = Auth::user();
        
        if (!$customer) {
            return redirect()->route('login')
                ->with('error', 'Please login to view your bookings.');
        }

        $bookings = Booking::where('customer_id', $customer->id)
            ->with(['organization', 'service', 'slot', 'invoice'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('customer.bookings.index', compact('bookings'));
    }

    /**
     * Show booking details
     */
    public function show(Booking $booking)
    {
        $customer = Auth::user();
        
        if (!$customer || $booking->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to this booking.');
        }

        $booking->load(['organization', 'service', 'slot', 'staff']);

        return view('customer.bookings.show', compact('booking'));
    }

    /**
     * Cancel a booking
     */
    public function cancel(Booking $booking)
    {
        $customer = Auth::user();
        
        if (!$customer || $booking->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to this booking.');
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()->back()
                ->with('error', 'This booking cannot be cancelled.');
        }

        $booking->update(['status' => 'cancelled']);

        // Free up the slot
        if ($booking->slot) {
            $booking->slot->update(['status' => 'available']);
        }

        return redirect()->route('my-bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }
}
