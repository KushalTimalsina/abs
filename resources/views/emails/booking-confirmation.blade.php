<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .booking-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #6b7280;
        }
        .value {
            color: #111827;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Booking Confirmed!</h1>
        <p>Your appointment has been successfully booked</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $booking->customer_name }},</p>
        
        <p>Thank you for your booking! We're excited to serve you.</p>
        
        <div class="booking-details">
            <h2 style="margin-top: 0; color: #667eea;">Booking Details</h2>
            
            <div class="detail-row">
                <span class="label">Booking Number:</span>
                <span class="value">{{ $booking->booking_number }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Service:</span>
                <span class="value">{{ $booking->service->name ?? 'N/A' }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Date:</span>
                <span class="value">{{ $booking->booking_date->format('l, F d, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Time:</span>
                <span class="value">{{ $booking->start_time->format('h:i A') }} - {{ $booking->end_time->format('h:i A') }}</span>
            </div>
            
            @if($booking->staff)
            <div class="detail-row">
                <span class="label">Staff Member:</span>
                <span class="value">{{ $booking->staff->name }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value" style="color: #10b981; font-weight: bold;">{{ ucfirst($booking->status) }}</span>
            </div>
        </div>
        
        @if($booking->customer_notes)
        <div style="background: #fef3c7; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <strong>Your Notes:</strong>
            <p style="margin: 5px 0 0 0;">{{ $booking->customer_notes }}</p>
        </div>
        @endif
        
        <div style="text-align: center;">
            <a href="{{ route('customer.bookings') }}" class="button">View My Bookings</a>
        </div>
        
        <div style="background: #e0e7ff; padding: 15px; border-radius: 6px; margin-top: 20px;">
            <strong>📅 Add to Calendar</strong>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Don't forget to add this appointment to your calendar!</p>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb;">
            <h3 style="color: #667eea;">Need to Make Changes?</h3>
            <p>If you need to reschedule or cancel your appointment, please contact us as soon as possible.</p>
        </div>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
