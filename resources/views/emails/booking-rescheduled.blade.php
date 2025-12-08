@component('mail::message')
# Booking Rescheduled

@if($recipientType === 'customer')
Hello **{{ $notifiable->name }}**,

Your booking has been rescheduled to a new date and time.
@else
Hello **{{ $notifiable->name }}**,

A booking you are assigned to has been rescheduled.
@endif

## Updated Booking Details

@component('mail::panel')
**Booking Number:** {{ $booking->booking_number }}  
**Service:** {{ $booking->service->name }}
@endcomponent

### Previous Schedule
~~**Date:** {{ \Carbon\Carbon::parse($oldDate)->format('l, F j, Y') }}~~  
~~**Time:** {{ $oldTime }}~~

### New Schedule
**Date:** {{ $booking->booking_date->format('l, F j, Y') }}  
**Time:** {{ $booking->start_time }} - {{ $booking->end_time }}

@if($booking->staff && $recipientType === 'customer')
**Staff:** {{ $booking->staff->name }}  
@endif

@if($recipientType === 'staff' && $booking->customer)
**Customer:** {{ $booking->customer->name }}  
**Customer Email:** {{ $booking->customer->email }}  
@if($booking->customer->phone)
**Customer Phone:** {{ $booking->customer->phone }}  
@endif
@endif

@if($booking->organization)
**Organization:** {{ $booking->organization->name }}  
@if($booking->organization->address)
**Location:** {{ $booking->organization->address }}  
@endif
@endif

@component('mail::button', ['url' => url('/bookings/' . $booking->id)])
View Updated Booking
@endcomponent

@if($recipientType === 'customer')
Please make note of your new appointment time. We look forward to seeing you!
@endif

Thank you,  
{{ $booking->organization->name ?? config('app.name') }}
@endcomponent
