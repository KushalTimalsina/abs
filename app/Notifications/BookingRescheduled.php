<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRescheduled extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $oldDate;
    protected $oldTime;
    protected $recipientType;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, string $oldDate, string $oldTime, string $recipientType = 'customer')
    {
        $this->booking = $booking;
        $this->oldDate = $oldDate;
        $this->oldTime = $oldTime;
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
            ->subject('Booking Rescheduled - ' . $booking->booking_number)
            ->markdown('emails.booking-rescheduled', [
                'booking' => $booking,
                'oldDate' => $this->oldDate,
                'oldTime' => $this->oldTime,
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
            'type' => 'booking_rescheduled',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'service_name' => $this->booking->service->name,
            'old_date' => $this->oldDate,
            'old_time' => $this->oldTime,
            'new_date' => $this->booking->booking_date->format('Y-m-d'),
            'new_time' => $this->booking->start_time,
            'recipient_type' => $this->recipientType,
            'message' => "Booking {$this->booking->booking_number} has been rescheduled.",
        ];
    }
}
