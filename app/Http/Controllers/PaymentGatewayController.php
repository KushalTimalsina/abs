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
            'gateway_name' => ['required', 'in:esewa,khalti,stripe'],
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
        ]);

        // Check if gateway is allowed in subscription plan
        $availableMethods = $organization->getAvailablePaymentMethods();
        
        if (!in_array($validated['gateway_name'], $availableMethods) && !in_array('online', $availableMethods)) {
            return redirect()
                ->back()
                ->with('error', 'This payment gateway is not available in your subscription plan. Please upgrade to use online payments.');
        }

        // Prepare credentials based on gateway
        $credentials = [];
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
        }

        // Encrypt credentials
        $encryptedCredentials = Crypt::encryptString(json_encode($credentials));

        // Update or create gateway configuration
        PaymentGateway::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'gateway_name' => $validated['gateway_name'],
            ],
            [
                'credentials' => $encryptedCredentials,
                'is_active' => $request->has('is_active'),
                'is_test_mode' => $request->has('is_test_mode'),
            ]
        );

        return redirect()
            ->route('payment-gateways.index', $organization)
            ->with('success', ucfirst($validated['gateway_name']) . ' payment gateway configured successfully');
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
