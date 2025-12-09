<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Confirmed - {{ $organization->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8 text-center">
                <!-- Success Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                    <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Booking Confirmed!</h1>
                <p class="text-gray-600 dark:text-gray-400 mb-8">Your booking has been successfully created.</p>

                <!-- Booking Details -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 text-left mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Booking Details</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Booking Number:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $booking->booking_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Service:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $booking->service->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Date:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($booking->booking_date)->format('l, F d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Time:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $booking->start_time }} - {{ $booking->end_time }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Price:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">NPR {{ number_format($booking->service->price, 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Status:</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6 text-left">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">What's Next?</h3>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                        <li>You will receive a confirmation email at {{ $booking->customer_email }}</li>
                        <li>Please arrive 5-10 minutes before your appointment</li>
                        <li>If you need to reschedule or cancel, please contact us</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('public.booking.index', $organization->slug) }}"
                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        Book Another Service
                    </a>
                    @auth
                        <a href="{{ route('my-bookings.index') }}"
                           class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                            View My Bookings
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</body>
</html>
