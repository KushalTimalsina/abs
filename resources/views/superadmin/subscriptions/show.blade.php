<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Payment Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Organization Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Organization Details</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->subscription->organization->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->subscription->organization->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->subscription->organization->phone ?? 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Payment Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Details</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">NPR {{ number_format($payment->amount, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Method</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ ucfirst($payment->payment_method) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Transaction ID</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->transaction_id ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $payment->status === 'verified' ? 'bg-green-100 text-green-800' : 
                                               ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Date</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Subscription Details -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Details</h3>
                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->subscription->plan->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->subscription->start_date->format('M d, Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->subscription->end_date->format('M d, Y') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Payment Proof -->
                        @if($payment->payment_proof)
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Proof</h3>
                                <img src="{{ Storage::url($payment->payment_proof) }}" alt="Payment Proof" class="max-w-md rounded-lg shadow-md">
                            </div>
                        @endif

                        <!-- Notes -->
                        @if($payment->notes)
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Notes</h3>
                                <p class="text-sm text-gray-900 dark:text-white">{{ $payment->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    @if($payment->status === 'pending')
                        <div class="mt-6 flex items-center justify-end space-x-4">
                            <form action="{{ route('superadmin.subscriptions.reject', $payment) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this payment?');">
                                @csrf
                                <x-danger-button>
                                    {{ __('Reject Payment') }}
                                </x-danger-button>
                            </form>
                            <form action="{{ route('superadmin.subscriptions.verify', $payment) }}" method="POST" onsubmit="return confirm('Are you sure you want to verify this payment?');">
                                @csrf
                                <x-primary-button>
                                    {{ __('Verify Payment') }}
                                </x-primary-button>
                            </form>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('superadmin.subscriptions.payments') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            ← Back to Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
