<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            My Bookings
        </h2>
    </x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Bookings</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_bookings'] }}</p>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['upcoming_bookings'] }}</p>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg dark:bg-purple-900">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['completed_bookings'] }}</p>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Spent</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">Rs {{ number_format($stats['total_spent'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Bookings -->
    @if($upcomingBookings->count() > 0)
    <div class="mb-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Bookings</h3>
        </div>
        <div class="p-4">
            <div class="space-y-4">
                @foreach($upcomingBookings as $booking)
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ $booking->organization->name }}</h4>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                <strong>Service:</strong> {{ $booking->service?->name ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                <strong>Date:</strong> {{ $booking->booking_date->format('l, F d, Y') }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                <strong>Time:</strong> {{ $booking->start_time->format('h:i A') }} - {{ $booking->end_time->format('h:i A') }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>Staff:</strong> {{ $booking->staff->name }}
                            </p>
                        </div>
                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('organization.bookings.show', [$booking->organization, $booking]) }}" class="px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                View Details
                            </a>
                            @if($booking->booking_date->diffInDays(now()) >= 1)
                            <a href="{{ route('reschedule.create', [$booking->organization, $booking]) }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400">
                                Reschedule
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Past Bookings -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Booking History</h3>
        </div>
        <div class="p-4">
            @forelse($pastBookings as $booking)
            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $booking->organization->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $booking->service?->name ?? 'N/A' }} • {{ $booking->booking_date->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $booking->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                    <a href="{{ route('organization.bookings.show', [$booking->organization, $booking]) }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        View
                    </a>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-500 dark:text-gray-400 py-8">No booking history</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
