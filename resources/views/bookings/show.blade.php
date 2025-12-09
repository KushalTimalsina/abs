<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Booking #{{ $booking->id }} - {{ $organization->name }}
            </h2>
            <a href="{{ route('organization.bookings.index', $organization) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Back to Bookings
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Booking Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Booking Information</h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Booking ID</p>
                                    <p class="font-medium text-gray-900 dark:text-white">#{{ $booking->id }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($booking->status === 'confirmed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($booking->status === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $booking->booking_date->format('l, F d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Time</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $booking->slot->start_time ?? 'N/A' }} - {{ $booking->slot->end_time ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Created</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $booking->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Payment Status</p>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($booking->payment_status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                        @endif">
                                        {{ ucfirst($booking->payment_status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer Information</h3>
                            
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $booking->customer_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $booking->customer_email }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $booking->customer_phone ?? 'N/A' }}</p>
                                </div>
                                @if($booking->notes)
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Notes</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $booking->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Service Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Booking Details</h3>
                            
                            @if($booking->service)
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Service Name</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $booking->service->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $booking->service->description ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Duration</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $booking->service->duration }} minutes</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Price</p>
                                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">NPR {{ number_format($booking->service->price, 2) }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-3">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            This booking was created through the widget
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Booking Type</p>
                                        <p class="font-medium text-gray-900 dark:text-white">Widget Booking</p>
                                    </div>
                                    @if($booking->slot)
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Time Slot</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $booking->slot->start_time->format('h:i A') }} - {{ $booking->slot->end_time->format('h:i A') }}</p>
                                    </div>
                                    @endif
                                    @if($booking->staff)
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Assigned Staff</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $booking->staff->name }}</p>
                                    </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>
                            
                            <div class="space-y-3">
                                @if($booking->status === 'pending')
                                    <form action="{{ route('organization.bookings.confirm', [$organization, $booking]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold">
                                            Confirm Booking
                                        </button>
                                    </form>
                                @endif

                                @if($booking->status === 'confirmed')
                                    <form action="{{ route('organization.bookings.complete', [$organization, $booking]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold">
                                            Mark as Completed
                                        </button>
                                    </form>
                                @endif

                                @if($booking->status !== 'cancelled' && $booking->status !== 'completed')
                                    <form action="{{ route('organization.bookings.cancel', [$organization, $booking]) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-semibold">
                                            Cancel Booking
                                        </button>
                                    </form>
                                @endif

                                @if($booking->payment_status !== 'paid')
                                    <a href="{{ route('organization.payments.show', [$organization, $booking]) }}" class="block w-full text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold">
                                        Process Payment
                                    </a>
                                @endif

                                @if($booking->payment && $booking->invoice)
                                    <a href="{{ route('invoices.show', $booking->invoice) }}" class="block w-full text-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md font-semibold">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        View Invoice
                                    </a>
                                @elseif($booking->payment)
                                    <form action="{{ route('invoices.generate.booking', $booking) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md font-semibold">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Generate Invoice
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('organization.bookings.edit', [$organization, $booking]) }}" class="block w-full text-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md font-semibold">
                                    Edit Booking
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Booking Created</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $booking->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>

                                @if($booking->status !== 'pending')
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">Status: {{ ucfirst($booking->status) }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $booking->updated_at->format('M d, Y h:i A') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
