@component('mail::message')
# Booking Confirmed

@if($recipientType === 'customer')
Hello **{{ $notifiable->name }}**,

Great news! Your booking has been confirmed.
@else
Hello **{{ $notifiable->name }}**,

You have been assigned to a new booking.
@endif

## Booking Details

@component('mail::panel')
**Booking Number:** {{ $booking->booking_number }}  
**Service:** {{ $booking->service->name }}  
**Date:** {{ $booking->booking_date->format('l, F j, Y') }}  
**Time:** {{ $booking->start_time }} - {{ $booking->end_time }}  
@if($booking->staff)
**Staff:** {{ $booking->staff->name }}  
@endif
@if($recipientType === 'staff' && $booking->customer)
**Customer:** {{ $booking->customer->name }}  
**Customer Email:** {{ $booking->customer->email }}  
@if($booking->customer->phone)
**Customer Phone:** {{ $booking->customer->phone }}  
@endif
@endif
@endcomponent

@if($booking->organization)
**Organization:** {{ $booking->organization->name }}  
@if($booking->organization->address)
**Location:** {{ $booking->organization->address }}  
@endif
@endif

@if($booking->customer_notes && $recipientType === 'staff')
**Customer Notes:**  
{{ $booking->customer_notes }}
@endif

@if($recipientType === 'customer')
@component('mail::button', ['url' => url('/bookings/' . $booking->id)])
View Booking Details
@endcomponent

Need to make changes? You can reschedule or cancel your booking from your dashboard.
@else
@component('mail::button', ['url' => url('/dashboard/bookings/' . $booking->id)])
View Booking Details
@endcomponent
@endif

Thank you,  
{{ $booking->organization->name ?? config('app.name') }}
@endcomponent
