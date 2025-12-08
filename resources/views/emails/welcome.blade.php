<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
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
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to {{ config('app.name') }}!</h1>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }}!</h2>
        
        <p>Thank you for joining {{ config('app.name') }}. We're excited to have you on board!</p>
        
        @if($user->user_type === 'admin')
            <p><strong>Your Business Account is Ready!</strong></p>
            <p>You can now:</p>
            <ul>
                <li>Add your services and set pricing</li>
                <li>Manage your team members</li>
                <li>Configure your booking availability</li>
                <li>Start accepting appointments</li>
            </ul>
            
            <a href="{{ route('dashboard') }}" class="button">Go to Dashboard</a>
        @else
            <p><strong>Your Customer Account is Ready!</strong></p>
            <p>You can now:</p>
            <ul>
                <li>Browse available services</li>
                <li>Book appointments with your favorite businesses</li>
                <li>Manage your bookings</li>
                <li>Track your appointment history</li>
            </ul>
            
            <a href="{{ route('dashboard') }}" class="button">Start Booking</a>
        @endif
        
        <p>If you have any questions, feel free to reach out to our support team.</p>
        
        <p>Best regards,<br>
        The {{ config('app.name') }} Team</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This email was sent to {{ $user->email }}</p>
    </div>
</body>
</html>
