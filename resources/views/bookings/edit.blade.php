<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Booking') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('organization.bookings.update', [$organization, $booking]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="customer_name" :value="__('Customer Name')" />
                            <x-text-input id="customer_name" class="block mt-1 w-full" type="text" name="customer_name" :value="old('customer_name', $booking->customer_name)" required autofocus />
                            <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="customer_email" :value="__('Customer Email')" />
                            <x-text-input id="customer_email" class="block mt-1 w-full" type="email" name="customer_email" :value="old('customer_email', $booking->customer_email)" required />
                            <x-input-error :messages="$errors->get('customer_email')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="customer_phone" :value="__('Customer Phone')" />
                            <x-text-input id="customer_phone" class="block mt-1 w-full" type="text" name="customer_phone" :value="old('customer_phone', $booking->customer_phone)" required />
                            <x-input-error :messages="$errors->get('customer_phone')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="notes" :value="__('Notes (Optional)')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes', $booking->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="status" :value="__('Booking Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="pending" {{ old('status', $booking->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ old('status', $booking->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="completed" {{ old('status', $booking->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $booking->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="no_show" {{ old('status', $booking->status) === 'no_show' ? 'selected' : '' }}>No Show</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Booking Information</h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                        <p><strong>Service:</strong> {{ $booking->service->name }}</p>
                                        <p><strong>Date & Time:</strong> {{ $booking->slot->start_time->format('M d, Y h:i A') }}</p>
                                        <p><strong>Duration:</strong> {{ $booking->service->duration }} minutes</p>
                                        <p><strong>Price:</strong> NPR {{ number_format($booking->total_price, 2) }}</p>
                                        @if($booking->slot->staff)
                                            <p><strong>Staff:</strong> {{ $booking->slot->staff->name }}</p>
                                        @endif
                                    </div>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                        <p class="italic">To change the service or time slot, please reschedule the booking instead.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('organization.bookings.show', [$organization, $booking]) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Booking') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
