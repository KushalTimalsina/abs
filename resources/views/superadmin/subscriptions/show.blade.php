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

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
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
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->organization->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->organization->email ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->organization->phone ?? 'N/A' }}</dd>
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
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $payment->subscriptionPlan->name }}</dd>
                                </div>
                                @if($payment->start_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($payment->start_date)->format('M d, Y') }}</dd>
                                </div>
                                @endif
                                @if($payment->end_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($payment->end_date)->format('M d, Y') }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Payment Proof -->
                        @if($payment->payment_proof)
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Proof</h3>
                                <a href="{{ asset('storage/' . $payment->payment_proof) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $payment->payment_proof) }}" alt="Payment Proof" class="max-w-md rounded-lg shadow-md hover:shadow-xl transition-shadow">
                                </a>
                                <p class="text-xs text-gray-500 mt-2">Click to view full size</p>
                            </div>
                        @endif

                        <!-- Notes -->
                        @if($payment->notes)
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer Notes</h3>
                                <p class="text-sm text-gray-900 dark:text-white">{{ $payment->notes }}</p>
                            </div>
                        @endif

                        @if($payment->admin_notes)
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Admin Notes</h3>
                                <p class="text-sm text-gray-900 dark:text-white">{{ $payment->admin_notes }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    @if($payment->status === 'pending')
                        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Verify Payment</h3>
                            
                            <form action="{{ route('superadmin.subscriptions.verify', $payment) }}" method="POST" class="space-y-4">
                                @csrf
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required
                                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Duration (Months)</label>
                                        <select name="duration_months" required
                                                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="1">1 Month</option>
                                            <option value="3">3 Months</option>
                                            <option value="6">6 Months</option>
                                            <option value="12" selected>12 Months</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Admin Notes (Optional)</label>
                                    <textarea name="admin_notes" rows="3"
                                              class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                              placeholder="Add any notes about this verification..."></textarea>
                                </div>
                                
                                <div class="flex items-center justify-end space-x-4">
                                    <button type="button" onclick="if(confirm('Are you sure you want to reject this payment?')) { document.getElementById('rejectForm').submit(); }"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                        Reject Payment
                                    </button>
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                        Verify & Activate
                                    </button>
                                </div>
                            </form>
                            
                            <!-- Hidden Reject Form -->
                            <form id="rejectForm" action="{{ route('superadmin.subscriptions.reject', $payment) }}" method="POST" class="hidden">
                                @csrf
                                <input type="hidden" name="admin_notes" value="Payment rejected by admin">
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
