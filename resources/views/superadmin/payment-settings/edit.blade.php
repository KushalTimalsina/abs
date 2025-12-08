<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configure') }} {{ ucfirst(str_replace('_', ' ', $setting->gateway)) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('superadmin.payment-settings.update', $setting->gateway) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Active Status -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable this payment gateway</span>
                            </label>
                        </div>

                        @if(in_array($setting->gateway, ['esewa', 'khalti']))
                            <!-- QR Code Upload -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">QR Code</label>
                                
                                @if($setting->qr_code_path)
                                    <div class="mb-4">
                                        <img src="{{ asset('storage/' . $setting->qr_code_path) }}" alt="QR Code" class="w-64 h-64 object-contain border rounded-lg">
                                        <button type="button" onclick="if(confirm('Delete QR code?')) document.getElementById('delete-qr-form').submit();" class="mt-2 text-sm text-red-600 hover:text-red-800">
                                            Delete QR Code
                                        </button>
                                    </div>
                                @endif

                                <input type="file" name="qr_code" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-900 focus:outline-none">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload a QR code image (Max 2MB)</p>
                                @error('qr_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Merchant Details -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Merchant ID</label>
                                <input type="text" name="account_details[merchant_id]" value="{{ $setting->account_details['merchant_id'] ?? '' }}" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Merchant Name</label>
                                <input type="text" name="account_details[merchant_name]" value="{{ $setting->account_details['merchant_name'] ?? '' }}" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            </div>

                        @elseif($setting->gateway === 'bank_transfer')
                            <!-- Bank Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bank Name</label>
                                    <input type="text" name="account_details[bank_name]" value="{{ $setting->account_details['bank_name'] ?? '' }}" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Account Number</label>
                                    <input type="text" name="account_details[account_number]" value="{{ $setting->account_details['account_number'] ?? '' }}" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Account Name</label>
                                    <input type="text" name="account_details[account_name]" value="{{ $setting->account_details['account_name'] ?? '' }}" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Branch</label>
                                    <input type="text" name="account_details[branch]" value="{{ $setting->account_details['branch'] ?? '' }}" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SWIFT Code (Optional)</label>
                                    <input type="text" name="account_details[swift_code]" value="{{ $setting->account_details['swift_code'] ?? '' }}" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                </div>
                            </div>

                        @elseif($setting->gateway === 'stripe')
                            <!-- Stripe Info -->
                            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">Stripe Configuration</h4>
                                <p class="text-sm text-blue-800 dark:text-blue-400 mb-2">
                                    Stripe API keys are configured in your <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">.env</code> file:
                                </p>
                                <ul class="text-sm text-blue-800 dark:text-blue-400 space-y-1 list-disc list-inside">
                                    <li><code>STRIPE_KEY</code> - Your publishable key</li>
                                    <li><code>STRIPE_SECRET</code> - Your secret key</li>
                                    <li><code>STRIPE_WEBHOOK_SECRET</code> - Webhook signing secret</li>
                                </ul>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Currency</label>
                                <select name="account_details[currency]" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="usd" {{ ($setting->account_details['currency'] ?? 'usd') === 'usd' ? 'selected' : '' }}>USD</option>
                                    <option value="npr" {{ ($setting->account_details['currency'] ?? 'usd') === 'npr' ? 'selected' : '' }}>NPR</option>
                                </select>
                            </div>
                        @endif

                        <!-- Payment Instructions -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Instructions</label>
                            <textarea name="instructions" rows="4" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ $setting->instructions }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Instructions shown to users when making payment</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between">
                            <a href="{{ route('superadmin.payment-settings.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                ← Back to Payment Settings
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete QR Form (hidden) -->
    @if($setting->qr_code_path)
        <form id="delete-qr-form" action="{{ route('superadmin.payment-settings.delete-qr', $setting->gateway) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
</x-app-layout>
