<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Payment Gateway Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($settings as $setting)
                            <div class="border-2 {{ $setting->is_active ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700' }} rounded-lg p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        @if($setting->gateway === 'esewa')
                                            <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold mr-3">
                                                eS
                                            </div>
                                        @elseif($setting->gateway === 'khalti')
                                            <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center text-white font-bold mr-3">
                                                K
                                            </div>
                                        @elseif($setting->gateway === 'bank_transfer')
                                            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold mr-3">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold mr-3">
                                                S
                                            </div>
                                        @endif
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $setting->gateway)) }}</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                @if($setting->is_active)
                                                    <span class="text-green-600 dark:text-green-400">● Active</span>
                                                @else
                                                    <span class="text-gray-400">● Inactive</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2 mb-4">
                                    @if($setting->qr_code_path)
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 inline mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            QR Code uploaded
                                        </p>
                                    @endif
                                    @if($setting->account_details)
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 inline mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Account details configured
                                        </p>
                                    @endif
                                </div>

                                <a href="{{ route('superadmin.payment-settings.edit', $setting->gateway) }}" 
                                   class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                                    Configure
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
