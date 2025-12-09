<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .invoice-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
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
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4F46E5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice {{ $invoice->invoice_number }}</h1>
            <p>{{ config('app.name') }}</p>
        </div>
        
        <div class="content">
            <p>Dear {{ $customerName }},</p>
            
            <p>Thank you for your business! Please find your invoice attached to this email.</p>
            
            <div class="invoice-details">
                <div class="detail-row">
                    <span class="label">Invoice Number:</span>
                    <span class="value">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Issue Date:</span>
                    <span class="value">{{ $invoice->issued_at->format('M d, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Amount:</span>
                    <span class="value">NPR {{ number_format($invoice->total, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Payment Status:</span>
                    <span class="value" style="color: {{ $invoice->isPaid() ? '#10b981' : '#ef4444' }}; font-weight: bold;">
                        {{ $invoice->isPaid() ? 'PAID' : 'UNPAID' }}
                    </span>
                </div>
                @if($invoice->payment_method)
                <div class="detail-row">
                    <span class="label">Payment Method:</span>
                    <span class="value">{{ $invoice->payment_method_name }}</span>
                </div>
                @endif
            </div>
            
            <p>The invoice PDF is attached to this email. If you have any questions about this invoice, please don't hesitate to contact us.</p>
            
            <p>Best regards,<br>
            {{ config('app.name') }}</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
