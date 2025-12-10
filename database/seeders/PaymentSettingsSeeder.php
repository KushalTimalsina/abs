<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use Illuminate\Database\Seeder;

class PaymentSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'gateway' => 'esewa',
                'is_active' => false,
                'account_details' => [
                    'merchant_id' => '',
                    'merchant_name' => '',
                ],
                'instructions' => 'Scan the QR code using your eSewa app and complete the payment. Upload the screenshot as proof.',
            ],
            [
                'gateway' => 'khalti',
                'is_active' => false,
                'account_details' => [
                    'merchant_id' => '',
                    'merchant_name' => '',
                ],
                'instructions' => 'Scan the QR code using your Khalti app and complete the payment. Upload the screenshot as proof.',
            ],
            [
                'gateway' => 'bank_transfer',
                'is_active' => false,
                'account_details' => [
                    'bank_name' => '',
                    'account_number' => '',
                    'account_name' => '',
                    'branch' => '',
                    'swift_code' => '',
                ],
                'instructions' => 'Transfer the amount to the bank account details shown above. Upload the bank receipt as proof.',
            ],
            [
                'gateway' => 'stripe',
                'is_active' => true,
                'account_details' => [
                    'currency' => 'usd',
                    'description' => 'Pay securely with your credit/debit card',
                ],
                'instructions' => 'Click the "Pay with Stripe" button to complete your payment securely using your credit or debit card.',
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentSetting::updateOrCreate(
                ['gateway' => $gateway['gateway']],
                $gateway
            );
        }
    }
}
