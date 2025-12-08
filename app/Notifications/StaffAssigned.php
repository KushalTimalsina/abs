<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking->load(['service', 'organization', 'customer']);
        
        return (new MailMessage)
            ->subject('New Booking Assignment - ' . $booking->booking_number)
            ->markdown('emails.staff-assigned', [
                'booking' => $booking,
                'notifiable' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'staff_assigned',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'service_name' => $this->booking->service->name,
            'customer_name' => $this->booking->customer->name,
            'booking_date' => $this->booking->booking_date->format('Y-m-d'),
            'start_time' => $this->booking->start_time,
            'message' => "You have been assigned to booking {$this->booking->booking_number}.",
        ];
    }
}
