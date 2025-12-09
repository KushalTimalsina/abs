<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete Booking - {{ $organization->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Complete Your Booking</h1>

                <!-- Booking Summary -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-2">Booking Summary</h2>
                    <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <p><strong>Service:</strong> {{ $service->name }}</p>
                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($slot->date)->format('l, F d, Y') }}</p>
                        <p><strong>Time:</strong> {{ $slot->start_time }} - {{ $slot->end_time }}</p>
                        <p><strong>Duration:</strong> {{ $service->duration }} minutes</p>
                        <p><strong>Price:</strong> NPR {{ number_format($service->price, 0) }}</p>
                    </div>
                </div>

                <!-- Booking Form -->
                <form method="POST" action="{{ route('public.booking.store', $organization->slug) }}">
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                    <input type="hidden" name="slot_id" value="{{ $slot->id }}">

                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="customer_name" id="customer_name" required
                                   value="{{ old('customer_name', auth()->user()->name ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('customer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="customer_email" id="customer_email" required
                                   value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('customer_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Phone Number
                            </label>
                            <input type="tel" name="customer_phone" id="customer_phone"
                                   value="{{ old('customer_phone', auth()->user()->phone ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('customer_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Additional Notes (Optional)
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex gap-4">
                        <a href="{{ route('public.booking.service', [$organization->slug, $service]) }}"
                           class="flex-1 text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            Back
                        </a>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                            Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
