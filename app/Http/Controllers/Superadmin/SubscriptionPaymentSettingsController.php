<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriptionPaymentSettingsController extends Controller
{
    /**
     * Display payment settings
     */
    public function index()
    {
        $paymentSettings = PaymentSetting::all();
        
        // Get all available gateways
        $availableGateways = ['esewa', 'khalti', 'stripe', 'bank_transfer'];
        
        return view('superadmin.payment-settings.index', compact('paymentSettings', 'availableGateways'));
    }

    /**
     * Store or update payment gateway configuration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'gateway' => ['required', 'in:esewa,khalti,stripe,bank_transfer'],
            'is_active' => ['boolean'],
            
            // QR Code for eSewa/Khalti
            'qr_code' => ['nullable', 'image', 'max:2048'],
            
            // Account details (flexible JSON)
            'merchant_id' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            
            // Bank transfer details
            'bank_name' => ['required_if:gateway,bank_transfer', 'nullable', 'string'],
            'account_number' => ['required_if:gateway,bank_transfer', 'nullable', 'string'],
            'account_name' => ['required_if:gateway,bank_transfer', 'nullable', 'string'],
            'branch' => ['nullable', 'string'],
            
            // Instructions
            'instructions' => ['nullable', 'string'],
        ]);

        // Handle QR code upload
        $qrCodePath = null;
        if ($request->hasFile('qr_code')) {
            $qrCodePath = $request->file('qr_code')->store('qr-codes', 'public');
        }

        // Prepare account details based on gateway
        $accountDetails = [];
        switch ($validated['gateway']) {
            case 'esewa':
            case 'khalti':
                $accountDetails = [
                    'merchant_id' => $validated['merchant_id'] ?? null,
                    'description' => $validated['description'] ?? null,
                ];
                break;
            case 'bank_transfer':
                $accountDetails = [
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'account_name' => $validated['account_name'],
                    'branch' => $validated['branch'] ?? null,
                    'description' => $validated['description'] ?? null,
                ];
                break;
            case 'stripe':
                $accountDetails = [
                    'description' => $validated['description'] ?? 'Pay with credit/debit card',
                ];
                break;
        }

        // Update or create payment setting
        $paymentSetting = PaymentSetting::updateOrCreate(
            ['gateway' => $validated['gateway']],
            [
                'is_active' => $request->has('is_active'),
                'qr_code_path' => $qrCodePath ?? PaymentSetting::where('gateway', $validated['gateway'])->value('qr_code_path'),
                'account_details' => $accountDetails,
                'instructions' => $validated['instructions'] ?? null,
            ]
        );

        return redirect()
            ->route('superadmin.payment-settings.index')
            ->with('success', ucfirst(str_replace('_', ' ', $validated['gateway'])) . ' payment method configured successfully');
    }

    /**
     * Toggle payment gateway active status
     */
    public function toggle(PaymentSetting $paymentSetting)
    {
        $paymentSetting->update(['is_active' => !$paymentSetting->is_active]);

        return redirect()
            ->back()
            ->with('success', 'Payment method status updated');
    }

    /**
     * Delete payment gateway configuration
     */
    public function destroy(PaymentSetting $paymentSetting)
    {
        // Delete QR code if exists
        if ($paymentSetting->qr_code_path) {
            Storage::disk('public')->delete($paymentSetting->qr_code_path);
        }

        $paymentSetting->delete();

        return redirect()
            ->route('superadmin.payment-settings.index')
            ->with('success', 'Payment method removed');
    }
}
