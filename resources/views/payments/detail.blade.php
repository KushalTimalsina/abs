<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Payment Details') }}
            </h2>
            <a href="{{ route('organization.payments.index', $organization) }}" 
               class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                ← Back to Payments
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Payment Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction ID</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->transaction_id ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">NPR {{ number_format($payment->amount / 100, 2) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($payment->payment_method === 'esewa') bg-green-100 text-green-800
                                    @elseif($payment->payment_method === 'khalti') bg-purple-100 text-purple-800
                                    @elseif($payment->payment_method === 'stripe') bg-blue-100 text-blue-800
                                    @elseif($payment->payment_method === 'bank_transfer') bg-blue-100 text-blue-800
                                    @elseif($payment->payment_method === 'cash') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $payment->payment_method === 'bank_transfer' ? 'Bank Transfer' : ucfirst($payment->payment_method ?? 'N/A') }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($payment->status === 'completed') bg-green-100 text-green-800
                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->created_at->format('M d, Y h:i A') }}</p>
                        </div>

                        @if($payment->verified_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Verified At</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->verified_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Proof -->
            @if($payment->payment_proof)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Proof</h3>
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $payment->payment_proof) }}" 
                             alt="Payment Proof" 
                             class="max-w-md rounded-lg shadow-lg">
                    </div>
                    <div class="mt-4">
                        <a href="{{ asset('storage/' . $payment->payment_proof) }}" 
                           target="_blank"
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            View Full Size →
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Booking Details -->
            @if($payment->booking)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Booking Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Booking Number</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->booking->booking_number }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->booking->customer_name }}</p>
                            <p class="text-xs text-gray-500">{{ $payment->booking->customer_email }}</p>
                        </div>

                        @if($payment->booking->service)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->booking->service->name }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Booking Date</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->booking->booking_date->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('organization.bookings.show', [$organization, $payment->booking]) }}" 
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            View Full Booking Details →
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Verify Payment (for pending payments) -->
            @if($payment->status === 'pending')
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Verify Payment</h3>
                    
                    <form action="{{ route('organization.payments.verify', [$organization, $payment]) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Verification Status
                            </label>
                            <select name="status" required class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select Status</option>
                                <option value="completed">Approve - Mark as Completed</option>
                                <option value="failed">Reject - Mark as Failed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Admin Notes (Optional)
                            </label>
                            <textarea name="admin_notes" rows="3" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Add any notes about this verification..."></textarea>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Submit Verification
                            </button>
                            <a href="{{ route('organization.payments.index', $organization) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
