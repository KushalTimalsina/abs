@component('mail::message')
# Appointment Reminder

Hello **{{ $notifiable->name }}**,

This is a friendly reminder about your upcoming appointment **tomorrow**.

## Appointment Details

@component('mail::panel')
**Booking Number:** {{ $booking->booking_number }}  
**Service:** {{ $booking->service->name }}  
**Date:** {{ $booking->booking_date->format('l, F j, Y') }}  
**Time:** {{ $booking->start_time }} - {{ $booking->end_time }}  
@if($booking->staff)
**Staff:** {{ $booking->staff->name }}  
@endif
@endcomponent

@if($booking->organization)
**Organization:** {{ $booking->organization->name }}  
@if($booking->organization->address)
**Location:** {{ $booking->organization->address }}  
@endif
@if($booking->organization->phone)
**Contact:** {{ $booking->organization->phone }}  
@endif
@endif

@component('mail::button', ['url' => url('/bookings/' . $booking->id)])
View Booking Details
@endcomponent

### Need to Make Changes?

@component('mail::table')
| Action | Link |
|:-------|:-----|
| Reschedule | [Change Date/Time]({{ url('/bookings/' . $booking->id . '/reschedule') }}) |
| Cancel | [Cancel Booking]({{ url('/bookings/' . $booking->id . '/cancel') }}) |
@endcomponent

**Please note:** Cancellations or reschedules must be made at least 24 hours in advance.

We look forward to seeing you tomorrow!

Thank you,  
{{ $booking->organization->name ?? config('app.name') }}
@endcomponent
