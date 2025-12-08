<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $reason;
    protected $recipientType;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, string $reason = null, string $recipientType = 'customer')
    {
        $this->booking = $booking;
        $this->reason = $reason;
        $this->recipientType = $recipientType;
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
        $booking = $this->booking->load(['service', 'organization', 'customer', 'staff']);
        
        return (new MailMessage)
            ->subject('Booking Cancelled - ' . $booking->booking_number)
            ->markdown('emails.booking-cancelled', [
                'booking' => $booking,
                'reason' => $this->reason,
                'recipientType' => $this->recipientType,
                'notifiable' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_cancelled',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'service_name' => $this->booking->service->name,
            'booking_date' => $this->booking->booking_date->format('Y-m-d'),
            'start_time' => $this->booking->start_time,
            'reason' => $this->reason,
            'recipient_type' => $this->recipientType,
            'message' => "Booking {$this->booking->booking_number} has been cancelled.",
        ];
    }
}
