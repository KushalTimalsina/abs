<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .invoice {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header-flex {
            display: table;
            width: 100%;
        }
        .header-left, .header-right {
            display: table-cell;
            width: 50%;
        }
        .header-right {
            text-align: right;
        }
        h1 {
            font-size: 32px;
            margin: 0 0 10px 0;
        }
        .section-title {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .grid-2 {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .grid-col {
            display: table-cell;
            width: 50%;
            padding-right: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        thead th {
            border-bottom: 2px solid #333;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }
        thead th:last-child,
        tbody td:last-child {
            text-align: right;
        }
        tbody td {
            padding: 15px 10px;
            border-bottom: 1px solid #ddd;
        }
        .totals {
            width: 300px;
            margin-left: auto;
            margin-bottom: 30px;
        }
        .totals-row {
            display: table;
            width: 100%;
            padding: 8px 0;
        }
        .totals-label, .totals-value {
            display: table-cell;
        }
        .totals-value {
            text-align: right;
        }
        .totals-total {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 16px;
            padding-top: 10px;
        }
        .payment-info {
            background: #f5f5f5;
            padding: 20px;
            margin-bottom: 30px;
        }
        .payment-info h3 {
            margin-top: 0;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-unpaid {
            background: #f8d7da;
            color: #721c24;
        }
        .footer {
            border-top: 1px solid #ddd;
            padding-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="invoice">
        <!-- Header -->
        <div class="header">
            <div class="header-flex">
                <div class="header-left">
                    <h1>INVOICE</h1>
                    <p>{{ config('app.name') }}</p>
                </div>
                <div class="header-right">
                    <p class="section-title">Invoice Number</p>
                    <p style="font-size: 18px; font-weight: bold;">{{ $invoice->invoice_number }}</p>
                    <p class="section-title" style="margin-top: 10px;">Issue Date</p>
                    <p>{{ $invoice->issued_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Bill To / From -->
        <div class="grid-2">
            <div class="grid-col">
                <p class="section-title">From</p>
                @if($invoice->isBookingInvoice())
                    <p style="font-weight: bold;">{{ $invoice->booking->organization->name }}</p>
                    <p>{{ $invoice->booking->organization->email }}</p>
                    @if($invoice->booking->organization->phone)
                        <p>{{ $invoice->booking->organization->phone }}</p>
                    @endif
                @else
                    <p style="font-weight: bold;">{{ config('app.name') }}</p>
                    <p>Subscription Services</p>
                @endif
            </div>
            <div class="grid-col">
                <p class="section-title">Bill To</p>
                @if($invoice->isBookingInvoice())
                    <p style="font-weight: bold;">{{ $invoice->booking->customer->name }}</p>
                    <p>{{ $invoice->booking->customer->email }}</p>
                    @if($invoice->booking->customer->phone)
                        <p>{{ $invoice->booking->customer->phone }}</p>
                    @endif
                @else
                    <p style="font-weight: bold;">{{ $invoice->subscriptionPayment->organization->name }}</p>
                    <p>{{ $invoice->subscriptionPayment->organization->email }}</p>
                    @if($invoice->subscriptionPayment->organization->phone)
                        <p>{{ $invoice->subscriptionPayment->organization->phone }}</p>
                    @endif
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Quantity</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @if($invoice->isBookingInvoice())
                            <strong>{{ $invoice->booking->service->name }}</strong><br>
                            <span style="font-size: 12px; color: #666;">
                                Booking Date: {{ $invoice->booking->booking_date->format('M d, Y') }}
                                at {{ $invoice->booking->start_time }}
                            </span>
                        @else
                            <strong>{{ $invoice->subscriptionPayment->subscriptionPlan->name }} Subscription</strong><br>
                            <span style="font-size: 12px; color: #666;">
                                Duration: {{ $invoice->subscriptionPayment->duration_months }} month(s)<br>
                                Period: {{ $invoice->subscriptionPayment->start_date->format('M d, Y') }} - {{ $invoice->subscriptionPayment->end_date->format('M d, Y') }}
                            </span>
                        @endif
                    </td>
                    <td style="text-align: right;">1</td>
                    <td style="text-align: right;">NPR {{ number_format($invoice->subtotal, 2) }}</td>
                    <td style="text-align: right;">NPR {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-row">
                <div class="totals-label">Subtotal:</div>
                <div class="totals-value">NPR {{ number_format($invoice->subtotal, 2) }}</div>
            </div>
            @if($invoice->tax > 0)
                <div class="totals-row">
                    <div class="totals-label">Tax:</div>
                    <div class="totals-value">NPR {{ number_format($invoice->tax, 2) }}</div>
                </div>
            @endif
            @if($invoice->discount > 0)
                <div class="totals-row">
                    <div class="totals-label">Discount:</div>
                    <div class="totals-value">- NPR {{ number_format($invoice->discount, 2) }}</div>
                </div>
            @endif
            <div class="totals-row totals-total">
                <div class="totals-label">Total:</div>
                <div class="totals-value">NPR {{ number_format($invoice->total, 2) }}</div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="payment-info">
            <h3>Payment Information</h3>
            <div class="grid-2">
                <div class="grid-col">
                    <p class="section-title">Payment Method</p>
                    <p><strong>{{ $invoice->payment_method_name }}</strong></p>
                    <p class="section-title" style="margin-top: 15px;">Paid By</p>
                    <p><strong>{{ $invoice->paid_by ?? 'N/A' }}</strong></p>
                </div>
                <div class="grid-col">
                    <p class="section-title">Payment Status</p>
                    <p>
                        <span class="status-badge {{ $invoice->isPaid() ? 'status-paid' : 'status-unpaid' }}">
                            {{ $invoice->isPaid() ? 'PAID' : 'UNPAID' }}
                        </span>
                    </p>
                    @if($invoice->paid_at)
                        <p class="section-title" style="margin-top: 15px;">Payment Date</p>
                        <p><strong>{{ $invoice->paid_at->format('M d, Y') }}</strong></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
</body>
</html>
