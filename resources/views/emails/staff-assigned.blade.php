@component('mail::message')
# New Booking Assignment

Hello **{{ $notifiable->name }}**,

You have been assigned to a new booking.

## Booking Details

@component('mail::panel')
**Booking Number:** {{ $booking->booking_number }}  
**Service:** {{ $booking->service->name }}  
**Date:** {{ $booking->booking_date->format('l, F j, Y') }}  
**Time:** {{ $booking->start_time }} - {{ $booking->end_time }}
@endcomponent

## Customer Information

@component('mail::panel')
**Name:** {{ $booking->customer->name }}  
**Email:** {{ $booking->customer->email }}  
@if($booking->customer->phone)
**Phone:** {{ $booking->customer->phone }}  
@endif
@endcomponent

@if($booking->customer_notes)
### Customer Notes
{{ $booking->customer_notes }}
@endif

@if($booking->organization)
**Organization:** {{ $booking->organization->name }}  
@if($booking->organization->address)
**Location:** {{ $booking->organization->address }}  
@endif
@endif

@component('mail::button', ['url' => url('/dashboard/bookings/' . $booking->id)])
View Booking Details
@endcomponent

Please review the booking details and prepare accordingly. If you have any questions or concerns, please contact your organization administrator.

Thank you,  
{{ $booking->organization->name ?? config('app.name') }}
@endcomponent
