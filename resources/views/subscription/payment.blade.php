<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Complete Your Subscription Payment
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Subscription Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Details</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Organization</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $payment->organization->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Plan</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $payment->subscriptionPlan->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Amount</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">NPR {{ number_format($payment->amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Duration</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $payment->subscriptionPlan->duration_days }} days</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payment Methods -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Choose Payment Method</h3>
                        
                        @if($paymentGateways->count() > 0)
                            <div class="space-y-4">
                                @foreach($paymentGateways as $gateway)
                                    <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 transition-colors cursor-pointer payment-method-card" data-gateway="{{ $gateway->gateway }}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center flex-1">
                                                @if($gateway->gateway === 'esewa')
                                                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold mr-3">eS</div>
                                                @elseif($gateway->gateway === 'khalti')
                                                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center text-white font-bold mr-3">K</div>
                                                @elseif($gateway->gateway === 'bank_transfer')
                                                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold mr-3">
                                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold mr-3">S</div>
                                                @endif
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $gateway->gateway)) }}</h4>
                                                        @if(in_array($gateway->gateway, ['esewa', 'khalti', 'stripe']))
                                                            <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 rounded-full">Online</span>
                                                        @else
                                                            <span class="px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 rounded-full">Offline</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        @if(in_array($gateway->gateway, ['esewa', 'khalti', 'stripe']))
                                                            Instant activation after payment
                                                        @else
                                                            Manual verification required
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <input type="radio" name="payment_method_select" value="{{ $gateway->gateway }}" class="w-5 h-5 text-blue-600">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <p class="text-yellow-800 dark:text-yellow-200">No payment methods are currently available. Please contact support.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Details (Dynamic based on selected method) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Details</h3>
                        
                        <div id="payment-details-container">
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">Select a payment method to see details</p>
                        </div>

                        @foreach($paymentGateways as $gateway)
                            <div id="details-{{ $gateway->gateway }}" class="payment-details hidden">
                                @if(in_array($gateway->gateway, ['esewa', 'khalti']))
                                    @if($gateway->qr_code_path)
                                        <div class="text-center mb-4">
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Scan QR Code to Pay</p>
                                            <img src="{{ asset('storage/' . $gateway->qr_code_path) }}" alt="QR Code" class="w-64 h-64 mx-auto border rounded-lg">
                                        </div>
                                    @endif
                                    @if($gateway->account_details)
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Merchant ID</p>
                                            <p class="font-mono text-lg font-semibold text-gray-900 dark:text-white">{{ $gateway->account_details['merchant_id'] ?? 'N/A' }}</p>
                                        </div>
                                    @endif
                                @elseif($gateway->gateway === 'bank_transfer')
                                    @if($gateway->account_details)
                                        <div class="space-y-3">
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <p class="text-xs text-gray-600 dark:text-gray-400">Bank Name</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $gateway->account_details['bank_name'] ?? 'N/A' }}</p>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <p class="text-xs text-gray-600 dark:text-gray-400">Account Number</p>
                                                <p class="font-mono font-semibold text-gray-900 dark:text-white">{{ $gateway->account_details['account_number'] ?? 'N/A' }}</p>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <p class="text-xs text-gray-600 dark:text-gray-400">Account Name</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $gateway->account_details['account_name'] ?? 'N/A' }}</p>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <p class="text-xs text-gray-600 dark:text-gray-400">Branch</p>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $gateway->account_details['branch'] ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @elseif($gateway->gateway === 'stripe')
                                    <div class="text-center">
                                        <p class="text-gray-600 dark:text-gray-400 mb-4">Pay securely with your credit or debit card</p>
                                        <form action="{{ route('stripe.checkout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold inline-flex items-center">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                                </svg>
                                                Pay with Stripe
                                            </button>
                                        </form>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Secure payment powered by Stripe</p>
                                    </div>
                                @endif

                                @if($gateway->instructions)
                                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                        <p class="text-sm text-blue-800 dark:text-blue-300">{{ $gateway->instructions }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Payment Proof Upload Form -->
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Submit Payment Proof</h3>
                    
                    <form action="{{ route('subscription.payment.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="payment_method" id="selected_payment_method">

                        <!-- Transaction ID -->
                        <div class="mb-4">
                            <x-input-label for="transaction_id" value="Transaction ID (Optional)" />
                            <x-text-input id="transaction_id" type="text" name="transaction_id" :value="old('transaction_id')" class="block mt-1 w-full" placeholder="Enter transaction ID if applicable" />
                            <x-input-error :messages="$errors->get('transaction_id')" class="mt-2" />
                        </div>

                        <!-- Payment Proof -->
                        <div class="mb-4">
                            <x-input-label for="payment_proof" value="Payment Proof (Screenshot/Receipt)" />
                            <input id="payment_proof" type="file" name="payment_proof" accept="image/*" class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-900 focus:outline-none">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload a screenshot or photo of your payment receipt (Max 2MB)</p>
                            <x-input-error :messages="$errors->get('payment_proof')" class="mt-2" />
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <x-input-label for="admin_notes" value="Additional Notes (Optional)" />
                            <textarea id="admin_notes" name="admin_notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Any additional information about your payment">{{ old('admin_notes') }}</textarea>
                            <x-input-error :messages="$errors->get('admin_notes')" class="mt-2" />
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between">
                            <a href="{{ route('subscription.payment.skip') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                Skip for now
                            </a>
                            <x-primary-button>
                                Submit Payment
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle payment method selection
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.addEventListener('click', function() {
                const gateway = this.dataset.gateway;
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Update hidden field
                document.getElementById('selected_payment_method').value = gateway;
                
                // Hide all payment details
                document.querySelectorAll('.payment-details').forEach(detail => {
                    detail.classList.add('hidden');
                });
                
                // Show selected payment details
                document.getElementById('details-' + gateway).classList.remove('hidden');
                
                // Update card styles
                document.querySelectorAll('.payment-method-card').forEach(c => {
                    c.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
                    c.classList.add('border-gray-200', 'dark:border-gray-700');
                });
                this.classList.remove('border-gray-200', 'dark:border-gray-700');
                this.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            });
        });
    </script>
</x-app-layout>
