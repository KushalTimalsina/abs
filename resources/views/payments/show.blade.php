<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Complete Payment - {{ $organization->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Booking Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Booking Details</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Service:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $booking->service?->name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Date:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $booking->booking_date->format('M d, Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Time:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $booking->start_time->format('h:i A') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Duration:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $booking->service?->duration ?? 'N/A' }} min</span>
                        </div>
                        <div class="col-span-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Total Amount:</span>
                            <span class="ml-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Rs {{ number_format($booking->service?->price ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Select Payment Method</h3>
                    
                    <!-- Cash Payment Option -->
                    <div class="mb-6">
                        <form method="POST" action="{{ route('organization.payments.cash', [$organization, $booking]) }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-between p-4 border-2 border-green-500 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">Mark as Cash Received</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Customer paid cash at venue</div>
                                    </div>
                                </div>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </form>
                    </div>

                    @if($availableGateways->count() > 0)
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-6">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">Online Payment Options</h4>
                        <div class="space-y-3">
                            @foreach($availableGateways as $gateway)
                            <form method="POST" action="{{ route('organization.payments.initiate', [$organization, $booking]) }}">
                                @csrf
                                <input type="hidden" name="gateway" value="{{ $gateway->gateway_name }}">
                                <button type="submit" class="w-full flex items-center justify-between p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mr-4">
                                            <span class="text-lg font-bold text-gray-700 dark:text-gray-300">
                                                {{ strtoupper(substr($gateway->gateway_name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="text-left">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($gateway->gateway_name) }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">Pay with {{ ucfirst($gateway->gateway_name) }}</div>
                                        </div>
                                    </div>
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 text-center text-gray-500 dark:text-gray-400">
                        <p class="text-sm">No online payment gateways configured</p>
                        <p class="text-xs mt-1">Contact admin to set up payment gateways</p>
                    </div>
                    @endif

                    <div class="mt-6 text-center">
                        <a href="{{ route('organization.bookings.show', [$organization, $booking]) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                            ← Back to Booking
                        </a>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-semibold">Secure Payment</p>
                        <p class="mt-1">Your payment information is encrypted and secure. You will be redirected to the payment gateway to complete your transaction.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
