<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Bookings</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Bookings</h1>
                    <div class="flex gap-4">
                        <span class="text-gray-600 dark:text-gray-400">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-800">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    @if($bookings->count() > 0)
                        <div class="space-y-4">
                            @foreach($bookings as $booking)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ $booking->service->name }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $booking->organization->name }}
                                            </p>
                                            <div class="mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                                <p><strong>Date:</strong> {{ $booking->booking_date->format('l, F d, Y') }}</p>
                                                <p><strong>Time:</strong> {{ $booking->start_time }} - {{ $booking->end_time }}</p>
                                                @if($booking->staff)
                                                    <p><strong>Staff:</strong> {{ $booking->staff->name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-4 flex flex-col items-end gap-2">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                            <div class="flex gap-2">
                                                <a href="{{ route('my-bookings.show', $booking) }}" 
                                                   class="text-sm text-blue-600 hover:text-blue-800">View</a>
                                                @if(in_array($booking->status, ['pending', 'confirmed']))
                                                    <form method="POST" action="{{ route('my-bookings.cancel', $booking) }}" 
                                                          onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                                        @csrf
                                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Cancel</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $bookings->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No bookings yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by booking a service.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</body>
</html>
