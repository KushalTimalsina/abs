<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            My Bookings
        </h2>
    </x-slot>

    <div class="py-12">
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($bookings->count() > 0)
                    <div class="space-y-4">
                        @foreach($bookings as $booking)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold">{{ $booking->service->name ?? 'Service' }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $booking->organization->name ?? 'Organization' }}</p>
                                    <div class="mt-2 space-y-1 text-sm">
                                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</p>
                                        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</p>
                                        <p><strong>Price:</strong> <span class="text-lg font-bold text-gray-900 dark:text-white">NPR {{ number_format($booking->total_amount ?? $booking->service->price ?? 0, 2) }}</span></p>
                                        <p><strong>Booking Status:</strong> 
                                            <span class="px-2 py-1 rounded text-xs {{ 
                                                $booking->status === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                                ($booking->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'))
                                            }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </p>
                                        <p><strong>Payment Status:</strong> 
                                            <span class="px-2 py-1 rounded text-xs {{ 
                                                $booking->payment_status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                ($booking->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                                ($booking->payment_status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'))
                                            }}">
                                                {{ ucfirst($booking->payment_status ?? 'pending') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right flex flex-col gap-2">
                                    @if($booking->payment_status === 'paid' && $booking->invoice)
                                    <a href="{{ route('invoices.show', $booking->invoice) }}" 
                                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        View Invoice
                                    </a>
                                    @endif
                                    @if(in_array($booking->status, ['pending', 'confirmed']))
                                    <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" onclick="return confirm('Are you sure you want to cancel this booking?')" 
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                                            Cancel Booking
                                        </button>
                                    </form>
                                    @endif
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No bookings</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You haven't made any bookings yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
