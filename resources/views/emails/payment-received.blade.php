@component('mail::message')
# Payment Received

Hello **{{ $notifiable->name }}**,

Thank you! Your payment has been successfully processed.

## Payment Details

@component('mail::panel')
**Transaction ID:** {{ $payment->transaction_id }}  
**Amount:** {{ $payment->currency }} {{ number_format($payment->amount, 2) }}  
**Payment Method:** {{ ucfirst($payment->payment_method) }}  
**Date:** {{ $payment->created_at->format('l, F j, Y \a\t g:i A') }}  
**Status:** {{ ucfirst($payment->status) }}
@endcomponent

## Booking Information

@component('mail::panel')
**Booking Number:** {{ $payment->booking->booking_number }}  
**Service:** {{ $payment->booking->service->name }}  
**Date:** {{ $payment->booking->booking_date->format('l, F j, Y') }}  
**Time:** {{ $payment->booking->start_time }} - {{ $payment->booking->end_time }}
@endcomponent

@if($payment->booking->organization)
**Organization:** {{ $payment->booking->organization->name }}
@endif

@if($payment->booking->invoice)
@component('mail::button', ['url' => url('/invoices/' . $payment->booking->invoice->id . '/download')])
Download Invoice
@endcomponent
@endif

@component('mail::button', ['url' => url('/bookings/' . $payment->booking->id)])
View Booking Details
@endcomponent

A receipt has been sent to your email address. Please keep this for your records.

Thank you for your payment!

Best regards,  
{{ $payment->booking->organization->name ?? config('app.name') }}
@endcomponent
