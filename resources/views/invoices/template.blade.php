<div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 print:shadow-none" id="invoice">
    <!-- Invoice Header -->
    <div class="border-b-2 border-gray-300 dark:border-gray-600 pb-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">INVOICE</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">{{ config('app.name') }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600 dark:text-gray-400">Invoice Number</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Issue Date</p>
                <p class="text-gray-900 dark:text-white">{{ $invoice->issued_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Bill To / Bill From -->
    <div class="grid grid-cols-2 gap-8 mb-8">
        <!-- Bill From -->
        <div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">From</h3>
            @if($invoice->isBookingInvoice())
                <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->booking->organization->name }}</p>
                <p class="text-gray-600 dark:text-gray-400">{{ $invoice->booking->organization->email }}</p>
                @if($invoice->booking->organization->phone)
                    <p class="text-gray-600 dark:text-gray-400">{{ $invoice->booking->organization->phone }}</p>
                @endif
            @else
                <p class="font-semibold text-gray-900 dark:text-white">{{ config('app.name') }}</p>
                <p class="text-gray-600 dark:text-gray-400">Subscription Services</p>
            @endif
        </div>

        <!-- Bill To -->
        <div>
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Bill To</h3>
            @if($invoice->isBookingInvoice())
                <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->booking->customer_name }}</p>
                <p class="text-gray-600 dark:text-gray-400">{{ $invoice->booking->customer_email }}</p>
                @if($invoice->booking->customer_phone)
                    <p class="text-gray-600 dark:text-gray-400">{{ $invoice->booking->customer_phone }}</p>
                @endif
            @else
                <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->subscriptionPayment->organization->name }}</p>
                <p class="text-gray-600 dark:text-gray-400">{{ $invoice->subscriptionPayment->organization->email }}</p>
                @if($invoice->subscriptionPayment->organization->phone)
                    <p class="text-gray-600 dark:text-gray-400">{{ $invoice->subscriptionPayment->organization->phone }}</p>
                @endif
            @endif
        </div>
    </div>

    <!-- Invoice Items -->
    <div class="mb-8">
        <table class="w-full">
            <thead>
                <tr class="border-b-2 border-gray-300 dark:border-gray-600">
                    <th class="text-left py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">Description</th>
                    <th class="text-right py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">Quantity</th>
                    <th class="text-right py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">Unit Price</th>
                    <th class="text-right py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="py-4 text-gray-900 dark:text-white">
                        @if($invoice->isBookingInvoice())
                            <div class="font-semibold">{{ $invoice->booking->service->name }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Booking Date: {{ $invoice->booking->booking_date->format('M d, Y') }}
                                at {{ $invoice->booking->start_time }}
                            </div>
                        @else
                            <div class="font-semibold">{{ $invoice->subscriptionPayment->subscriptionPlan->name }} Subscription</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Duration: {{ $invoice->subscriptionPayment->duration_months }} month(s)
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Period: {{ $invoice->subscriptionPayment->start_date->format('M d, Y') }} - {{ $invoice->subscriptionPayment->end_date->format('M d, Y') }}
                            </div>
                        @endif
                    </td>
                    <td class="py-4 text-right text-gray-900 dark:text-white">1</td>
                    <td class="py-4 text-right text-gray-900 dark:text-white">NPR {{ number_format($invoice->subtotal, 2) }}</td>
                    <td class="py-4 text-right text-gray-900 dark:text-white">NPR {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="flex justify-end mb-8">
        <div class="w-64">
            <div class="flex justify-between py-2 text-gray-700 dark:text-gray-300">
                <span>Subtotal:</span>
                <span>NPR {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->tax > 0)
                <div class="flex justify-between py-2 text-gray-700 dark:text-gray-300">
                    <span>Tax:</span>
                    <span>NPR {{ number_format($invoice->tax, 2) }}</span>
                </div>
            @endif
            @if($invoice->discount > 0)
                <div class="flex justify-between py-2 text-gray-700 dark:text-gray-300">
                    <span>Discount:</span>
                    <span>- NPR {{ number_format($invoice->discount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between py-3 border-t-2 border-gray-300 dark:border-gray-600 text-lg font-bold text-gray-900 dark:text-white">
                <span>Total:</span>
                <span>NPR {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Payment Information -->
    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Information</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Payment Method</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->payment_method_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Paid By</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->paid_by ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Payment Status</p>
                <div class="mt-1">
                    @if($invoice->isPaid())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            PAID
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            UNPAID
                        </span>
                    @endif
                </div>
            </div>
            @if($invoice->paid_at)
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Payment Date</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->paid_at->format('M d, Y') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="border-t border-gray-300 dark:border-gray-600 pt-6 text-center text-sm text-gray-600 dark:text-gray-400">
        <p>Thank you for your business!</p>
        <p class="mt-2">This is a computer-generated invoice and does not require a signature.</p>
    </div>
</div>

<style>
    @media print {
        @page {
            size: A4;
            margin: 10mm 12mm;
        }
        
        /* Hide everything except invoice */
        header, nav, .no-print {
            display: none !important;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: white;
        }
        
        #invoice {
            box-shadow: none !important;
            border-radius: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Reduce spacing in print */
        #invoice .p-6, #invoice .p-8 {
            padding: 8px !important;
        }
        
        #invoice .mb-4, #invoice .mb-6, #invoice .mb-8 {
            margin-bottom: 8px !important;
        }
        
        #invoice .py-12 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
        
        /* Force light colors for print */
        * {
            color-adjust: exact !important;
            -webkit-print-color-adjust: exact !important;
        }
    }
</style>
