<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications for bookings happening in the next 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for bookings that need reminders...');

        // Get bookings for tomorrow that are confirmed or pending
        $tomorrow = Carbon::tomorrow();
        $dayAfterTomorrow = Carbon::tomorrow()->addDay();

        $bookings = Booking::with(['customer', 'staff', 'service', 'organization'])
            ->whereBetween('booking_date', [$tomorrow, $dayAfterTomorrow])
            ->whereIn('status', ['confirmed', 'pending'])
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            // Send reminder to customer
            if ($booking->customer) {
                $booking->customer->notify(new BookingReminder($booking));
                $count++;
                $this->info("Sent reminder to {$booking->customer->email} for booking {$booking->booking_number}");
            }

            // Optionally send reminder to staff as well
            if ($booking->staff) {
                $booking->staff->notify(new BookingReminder($booking));
                $count++;
                $this->info("Sent reminder to staff {$booking->staff->email} for booking {$booking->booking_number}");
            }
        }

        $this->info("Sent {$count} reminder notifications for {$bookings->count()} bookings.");

        return Command::SUCCESS;
    }
}
