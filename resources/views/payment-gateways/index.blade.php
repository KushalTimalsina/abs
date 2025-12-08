<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Payment Gateways - {{ $organization->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(!in_array('online', $availablePaymentMethods) && !in_array('esewa', $availablePaymentMethods))
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-yellow-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">Online Payments Not Available</h3>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                            Your current subscription plan ({{ $organization->getCurrentPlan()->name }}) only supports cash payments. 
                            <a href="#" class="underline font-medium">Upgrade to Mid or Top plan</a> to accept online payments.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Configured Gateways -->
            @if($gateways->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Configured Payment Gateways</h3>
                    <div class="space-y-4">
                        @foreach($gateways as $gateway)
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                    <span class="text-lg font-bold text-gray-700 dark:text-gray-300">
                                        {{ strtoupper(substr($gateway->gateway_name, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($gateway->gateway_name) }}</h4>
                                    <div class="flex items-center space-x-3 mt-1">
                                        <span class="text-sm {{ $gateway->is_active ? 'text-green-600' : 'text-gray-500' }}">
                                            {{ $gateway->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        @if($gateway->is_test_mode)
                                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Test Mode</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <form method="POST" action="{{ route('payment-gateways.test', [$organization, $gateway]) }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        Test
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('payment-gateways.toggle', [$organization, $gateway]) }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400">
                                        {{ $gateway->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('payment-gateways.destroy', [$organization, $gateway]) }}" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Add New Gateway -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Add Payment Gateway</h3>
                    
                    <form method="POST" action="{{ route('payment-gateways.store', $organization) }}" class="space-y-6" x-data="{ gateway: 'esewa' }">
                        @csrf

                        <!-- Gateway Selection -->
                        <div>
                            <x-input-label value="Select Gateway" />
                            <div class="grid grid-cols-3 gap-4 mt-2">
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="gateway === 'esewa' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'">
                                    <input type="radio" name="gateway_name" value="esewa" x-model="gateway" class="sr-only">
                                    <div class="text-center w-full">
                                        <div class="font-semibold text-gray-900 dark:text-white">eSewa</div>
                                        <div class="text-xs text-gray-500">Nepal</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="gateway === 'khalti' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'">
                                    <input type="radio" name="gateway_name" value="khalti" x-model="gateway" class="sr-only">
                                    <div class="text-center w-full">
                                        <div class="font-semibold text-gray-900 dark:text-white">Khalti</div>
                                        <div class="text-xs text-gray-500">Nepal</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="gateway === 'stripe' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'">
                                    <input type="radio" name="gateway_name" value="stripe" x-model="gateway" class="sr-only">
                                    <div class="text-center w-full">
                                        <div class="font-semibold text-gray-900 dark:text-white">Stripe</div>
                                        <div class="text-xs text-gray-500">International</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- eSewa Credentials -->
                        <div x-show="gateway === 'esewa'" x-transition class="space-y-4">
                            <div>
                                <x-input-label for="esewa_merchant_id" value="eSewa Merchant ID" />
                                <x-text-input id="esewa_merchant_id" name="esewa_merchant_id" type="text" class="mt-1 block w-full" placeholder="EPAYTEST" />
                            </div>
                            <div>
                                <x-input-label for="esewa_secret_key" value="eSewa Secret Key" />
                                <x-text-input id="esewa_secret_key" name="esewa_secret_key" type="password" class="mt-1 block w-full" />
                            </div>
                        </div>

                        <!-- Khalti Credentials -->
                        <div x-show="gateway === 'khalti'" x-transition class="space-y-4">
                            <div>
                                <x-input-label for="khalti_public_key" value="Khalti Public Key" />
                                <x-text-input id="khalti_public_key" name="khalti_public_key" type="text" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <x-input-label for="khalti_secret_key" value="Khalti Secret Key" />
                                <x-text-input id="khalti_secret_key" name="khalti_secret_key" type="password" class="mt-1 block w-full" />
                            </div>
                        </div>

                        <!-- Stripe Credentials -->
                        <div x-show="gateway === 'stripe'" x-transition class="space-y-4">
                            <div>
                                <x-input-label for="stripe_publishable_key" value="Stripe Publishable Key" />
                                <x-text-input id="stripe_publishable_key" name="stripe_publishable_key" type="text" class="mt-1 block w-full" placeholder="pk_test_..." />
                            </div>
                            <div>
                                <x-input-label for="stripe_secret_key" value="Stripe Secret Key" />
                                <x-text-input id="stripe_secret_key" name="stripe_secret_key" type="password" class="mt-1 block w-full" placeholder="sk_test_..." />
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" checked>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activate immediately</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_test_mode" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" checked>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Test mode</span>
                            </label>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>
                                Add Payment Gateway
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">Getting API Credentials</h4>
                <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                    <li><strong>eSewa:</strong> Visit <a href="https://developer.esewa.com.np" target="_blank" class="underline">developer.esewa.com.np</a> to get merchant credentials</li>
                    <li><strong>Khalti:</strong> Sign up at <a href="https://khalti.com" target="_blank" class="underline">khalti.com</a> and get API keys from dashboard</li>
                    <li><strong>Stripe:</strong> Create account at <a href="https://stripe.com" target="_blank" class="underline">stripe.com</a> and find keys in Developers section</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
