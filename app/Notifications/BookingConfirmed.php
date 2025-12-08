<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $recipientType; // 'customer' or 'staff'

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, string $recipientType = 'customer')
    {
        $this->booking = $booking;
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
            ->subject('Booking Confirmed - ' . $booking->booking_number)
            ->markdown('emails.booking-confirmed', [
                'booking' => $booking,
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
            'type' => 'booking_confirmed',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'service_name' => $this->booking->service->name,
            'booking_date' => $this->booking->booking_date->format('Y-m-d'),
            'start_time' => $this->booking->start_time,
            'recipient_type' => $this->recipientType,
            'message' => $this->recipientType === 'customer' 
                ? "Your booking {$this->booking->booking_number} has been confirmed."
                : "You have been assigned to booking {$this->booking->booking_number}.",
        ];
    }
}
