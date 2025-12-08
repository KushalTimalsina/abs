<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingRescheduled;
use App\Notifications\PaymentReceived;
use App\Notifications\StaffAssigned;
use Illuminate\Http\Request;

class NotificationTestController extends Controller
{
    /**
     * Test notification system
     */
    public function testNotifications()
    {
        // Get a sample booking with all relationships
        $booking = Booking::with(['customer', 'staff', 'service', 'organization', 'payment'])
            ->whereNotNull('customer_id')
            ->first();

        if (!$booking) {
            return response()->json([
                'error' => 'No bookings found. Please create a booking first.',
            ], 404);
        }

        $results = [];

        // Test 1: Booking Confirmed (Customer)
        try {
            $booking->customer->notify(new BookingConfirmed($booking, 'customer'));
            $results[] = '✓ BookingConfirmed notification sent to customer';
        } catch (\Exception $e) {
            $results[] = '✗ BookingConfirmed failed: ' . $e->getMessage();
        }

        // Test 2: Booking Confirmed (Staff)
        if ($booking->staff) {
            try {
                $booking->staff->notify(new BookingConfirmed($booking, 'staff'));
                $results[] = '✓ BookingConfirmed notification sent to staff';
            } catch (\Exception $e) {
                $results[] = '✗ BookingConfirmed (staff) failed: ' . $e->getMessage();
            }
        }

        // Test 3: Staff Assigned
        if ($booking->staff) {
            try {
                $booking->staff->notify(new StaffAssigned($booking));
                $results[] = '✓ StaffAssigned notification sent';
            } catch (\Exception $e) {
                $results[] = '✗ StaffAssigned failed: ' . $e->getMessage();
            }
        }

        // Test 4: Booking Cancelled
        try {
            $booking->customer->notify(new BookingCancelled($booking, 'Test cancellation', 'customer'));
            $results[] = '✓ BookingCancelled notification sent';
        } catch (\Exception $e) {
            $results[] = '✗ BookingCancelled failed: ' . $e->getMessage();
        }

        // Test 5: Booking Rescheduled
        try {
            $oldDate = $booking->booking_date->format('Y-m-d');
            $oldTime = $booking->start_time;
            $booking->customer->notify(new BookingRescheduled($booking, $oldDate, $oldTime, 'customer'));
            $results[] = '✓ BookingRescheduled notification sent';
        } catch (\Exception $e) {
            $results[] = '✗ BookingRescheduled failed: ' . $e->getMessage();
        }

        // Test 6: Payment Received
        if ($booking->payment) {
            try {
                $booking->customer->notify(new PaymentReceived($booking->payment));
                $results[] = '✓ PaymentReceived notification sent';
            } catch (\Exception $e) {
                $results[] = '✗ PaymentReceived failed: ' . $e->getMessage();
            }
        }

        // Get database notifications
        $dbNotifications = $booking->customer->notifications()->latest()->take(5)->get();

        return response()->json([
            'message' => 'Notification tests completed',
            'results' => $results,
            'booking_tested' => [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'customer' => $booking->customer->name,
                'staff' => $booking->staff?->name,
            ],
            'database_notifications_count' => $dbNotifications->count(),
            'latest_notifications' => $dbNotifications->map(function ($n) {
                return [
                    'type' => $n->data['type'] ?? 'unknown',
                    'message' => $n->data['message'] ?? '',
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            }),
            'instructions' => [
                'Check email logs at: storage/logs/laravel.log',
                'View database notifications in the notifications table',
                'For production, update MAIL_MAILER in .env to smtp',
            ],
        ]);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
