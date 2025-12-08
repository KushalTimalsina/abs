@component('mail::message')
# Booking Cancelled

@if($recipientType === 'customer')
Hello **{{ $notifiable->name }}**,

Your booking has been cancelled.
@else
Hello **{{ $notifiable->name }}**,

A booking you were assigned to has been cancelled.
@endif

## Booking Details

@component('mail::panel')
**Booking Number:** {{ $booking->booking_number }}  
**Service:** {{ $booking->service->name }}  
**Date:** {{ $booking->booking_date->format('l, F j, Y') }}  
**Time:** {{ $booking->start_time }} - {{ $booking->end_time }}  
@if($booking->staff && $recipientType === 'customer')
**Staff:** {{ $booking->staff->name }}  
@endif
@if($recipientType === 'staff' && $booking->customer)
**Customer:** {{ $booking->customer->name }}  
@endif
@endcomponent

@if($reason)
**Cancellation Reason:**  
{{ $reason }}
@endif

@if($booking->payment && $booking->payment->status === 'completed')
@component('mail::panel')
### Refund Information
A refund will be processed to your original payment method within 5-7 business days.  
**Amount:** {{ $booking->payment->currency }} {{ $booking->payment->amount }}
@endcomponent
@endif

@if($recipientType === 'customer')
@component('mail::button', ['url' => url('/services')])
Book Another Appointment
@endcomponent

We're sorry to see this booking cancelled. We hope to serve you again soon!
@else
@component('mail::button', ['url' => url('/dashboard/bookings')])
View All Bookings
@endcomponent
@endif

Thank you,  
{{ $booking->organization->name ?? config('app.name') }}
@endcomponent
