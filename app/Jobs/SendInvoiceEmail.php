<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invoice;
    public $customerEmail;
    public $customerName;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice, string $customerEmail, string $customerName)
    {
        $this->invoice = $invoice;
        $this->customerEmail = $customerEmail;
        $this->customerName = $customerName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load relationships for PDF generation
        if ($this->invoice->isBookingInvoice()) {
            $this->invoice->load(['booking.service', 'booking.organization', 'payment']);
        } else {
            $this->invoice->load(['subscriptionPayment.subscriptionPlan', 'subscriptionPayment.organization']);
        }

        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $this->invoice]);

        // Send email with PDF attachment
        Mail::send('emails.invoice', [
            'invoice' => $this->invoice,
            'customerName' => $this->customerName
        ], function($message) use ($pdf) {
            $message->to($this->customerEmail)
                ->subject('Invoice ' . $this->invoice->invoice_number . ' from ' . config('app.name'))
                ->attachData($pdf->output(), $this->invoice->invoice_number . '.pdf');
        });
    }
}
