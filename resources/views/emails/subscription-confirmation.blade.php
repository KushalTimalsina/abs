<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Confirmation</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .subscription-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
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
            font-weight: 600;
            color: #6b7280;
        }
        .value {
            color: #111827;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #10b981;
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
        <h1>✓ Subscription Confirmed!</h1>
    </div>
    
    <div class="content">
        <h2>Hello {{ $organization->name }}!</h2>
        
        <p>Your subscription has been successfully activated. Thank you for choosing {{ config('app.name') }}!</p>
        
        <div class="subscription-details">
            <h3 style="margin-top: 0;">Subscription Details</h3>
            
            <div class="detail-row">
                <span class="label">Plan:</span>
                <span class="value">{{ $subscription->plan->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Price:</span>
                <span class="value">NPR {{ number_format($subscription->plan->price, 2) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Duration:</span>
                <span class="value">{{ $subscription->plan->duration_days }} days</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Start Date:</span>
                <span class="value">{{ $subscription->start_date->format('F d, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">End Date:</span>
                <span class="value">{{ $subscription->end_date->format('F d, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value" style="color: #10b981; font-weight: 600;">Active</span>
            </div>
        </div>
        
        <p><strong>What's included in your plan:</strong></p>
        <ul>
            @if($subscription->plan->max_team_members)
                <li>Up to {{ $subscription->plan->max_team_members }} team members</li>
            @else
                <li>Unlimited team members</li>
            @endif
            
            @if($subscription->plan->max_services)
                <li>Up to {{ $subscription->plan->max_services }} services</li>
            @else
                <li>Unlimited services</li>
            @endif
            
            @if($subscription->plan->slot_scheduling_days)
                <li>{{ $subscription->plan->slot_scheduling_days }} days advance scheduling</li>
            @endif
        </ul>
        
        <a href="{{ route('dashboard') }}" class="button">Go to Dashboard</a>
        
        <p>If you have any questions about your subscription, please don't hesitate to contact us.</p>
        
        <p>Best regards,<br>
        The {{ config('app.name') }} Team</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This email was sent to {{ $organization->email ?? $organization->users->first()->email }}</p>
    </div>
</body>
</html>
