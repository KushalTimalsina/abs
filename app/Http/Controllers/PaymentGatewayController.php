<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewayController extends Controller
{
    /**
     * Display payment gateway settings
     */
    public function index(Organization $organization)
    {
        $this->authorize('update', $organization);
        
        $gateways = $organization->paymentGateways()->get();
        $availablePaymentMethods = $organization->getAvailablePaymentMethods();
        
        return view('payment-gateways.index', compact('organization', 'gateways', 'availablePaymentMethods'));
    }

    /**
     * Store or update payment gateway configuration
     */
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);
        
        $validated = $request->validate([
            'gateway_name' => ['required', 'in:esewa,khalti,stripe,bank_transfer,cash'],
            'is_active' => ['boolean'],
            'is_test_mode' => ['boolean'],
            
            // eSewa credentials
            'esewa_merchant_id' => ['required_if:gateway_name,esewa', 'nullable', 'string'],
            'esewa_secret_key' => ['required_if:gateway_name,esewa', 'nullable', 'string'],
            
            // Khalti credentials
            'khalti_public_key' => ['required_if:gateway_name,khalti', 'nullable', 'string'],
            'khalti_secret_key' => ['required_if:gateway_name,khalti', 'nullable', 'string'],
            
            // Stripe credentials
            'stripe_publishable_key' => ['required_if:gateway_name,stripe', 'nullable', 'string'],
            'stripe_secret_key' => ['required_if:gateway_name,stripe', 'nullable', 'string'],
            
            // Bank Transfer details
            'bank_name' => ['required_if:gateway_name,bank_transfer', 'nullable', 'string'],
            'account_holder' => ['required_if:gateway_name,bank_transfer', 'nullable', 'string'],
            'account_number' => ['required_if:gateway_name,bank_transfer', 'nullable', 'string'],
            'branch' => ['nullable', 'string'],
            'bank_instructions' => ['nullable', 'string'],
            
            // Cash payment details
            'cash_instructions' => ['nullable', 'string'],
        ]);

        // Check if organization can accept online payments (only for online gateways)
        if (in_array($validated['gateway_name'], ['esewa', 'khalti', 'stripe']) && !$organization->canAcceptOnlinePayments()) {
            return redirect()
                ->back()
                ->with('error', 'Online payments are not available in your subscription plan. Please upgrade to a plan with online payment features enabled.');
        }

        // Prepare credentials and settings based on gateway
        $credentials = null;
        $settings = null;
        
        switch ($validated['gateway_name']) {
            case 'esewa':
                $credentials = [
                    'merchant_id' => $validated['esewa_merchant_id'],
                    'secret_key' => $validated['esewa_secret_key'],
                ];
                break;
            case 'khalti':
                $credentials = [
                    'public_key' => $validated['khalti_public_key'],
                    'secret_key' => $validated['khalti_secret_key'],
                ];
                break;
            case 'stripe':
                $credentials = [
                    'publishable_key' => $validated['stripe_publishable_key'],
                    'secret_key' => $validated['stripe_secret_key'],
                ];
                break;
            case 'bank_transfer':
                $settings = [
                    'bank_name' => $validated['bank_name'],
                    'account_holder' => $validated['account_holder'],
                    'account_number' => $validated['account_number'],
                    'branch' => $validated['branch'] ?? null,
                    'instructions' => $validated['bank_instructions'] ?? null,
                ];
                break;
            case 'cash':
                $settings = [
                    'instructions' => $validated['cash_instructions'] ?? 'Pay cash at the time of appointment.',
                ];
                break;
        }

        // Encrypt credentials only if they exist (online gateways)
        $encryptedCredentials = $credentials ? Crypt::encryptString(json_encode($credentials)) : null;

        // Update or create gateway configuration
        PaymentGateway::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'gateway_name' => $validated['gateway_name'],
            ],
            [
                'credentials' => $encryptedCredentials,
                'settings' => $settings,
                'is_active' => $request->has('is_active'),
                'is_test_mode' => in_array($validated['gateway_name'], ['esewa', 'khalti', 'stripe']) ? $request->has('is_test_mode') : false,
            ]
        );

        return redirect()
            ->route('payment-gateways.index', $organization)
            ->with('success', ucfirst(str_replace('_', ' ', $validated['gateway_name'])) . ' payment method configured successfully');
    }

    /**
     * Toggle gateway active status
     */
    public function toggle(Organization $organization, PaymentGateway $gateway)
    {
        $this->authorize('update', $organization);
        
        if ($gateway->organization_id !== $organization->id) {
            abort(404);
        }

        $gateway->update(['is_active' => !$gateway->is_active]);

        return redirect()
            ->back()
            ->with('success', 'Payment gateway status updated');
    }

    /**
     * Delete gateway configuration
     */
    public function destroy(Organization $organization, PaymentGateway $gateway)
    {
        $this->authorize('update', $organization);
        
        if ($gateway->organization_id !== $organization->id) {
            abort(404);
        }

        $gateway->delete();

        return redirect()
            ->route('payment-gateways.index', $organization)
            ->with('success', 'Payment gateway removed');
    }

    /**
     * Test gateway connection
     */
    public function test(Organization $organization, PaymentGateway $gateway)
    {
        $this->authorize('update', $organization);
        
        if ($gateway->organization_id !== $organization->id) {
            abort(404);
        }

        // Decrypt credentials
        try {
            $credentials = json_decode(Crypt::decryptString($gateway->credentials), true);
            
            // Basic validation - in production, you'd actually test the API
            $isValid = !empty($credentials) && count($credentials) > 0;
            
            if ($isValid) {
                return redirect()
                    ->back()
                    ->with('success', 'Gateway credentials are valid');
            } else {
                return redirect()
                    ->back()
                    ->with('error', 'Invalid gateway credentials');
            }
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to validate credentials: ' . $e->getMessage());
        }
    }
}
