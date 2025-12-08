<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Reschedule Booking') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Current Booking Details</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $booking->customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $booking->service->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Date & Time</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $booking->slot->start_time->format('M d, Y h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $booking->service->duration }} minutes</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Select New Date & Time</h3>
                    
                    <form action="{{ route('organization.reschedules.store', [$organization, $booking]) }}" method="POST">
                        @csrf

                        <div class="mb-6">
                            <x-input-label for="new_slot_id" :value="__('Available Time Slots')" />
                            <select id="new_slot_id" name="new_slot_id" required class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select a new time slot</option>
                                @foreach($availableSlots as $date => $slots)
                                    <optgroup label="{{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}">
                                        @foreach($slots as $slot)
                                            <option value="{{ $slot->id }}">
                                                {{ $slot->start_time->format('h:i A') }} - {{ $slot->end_time->format('h:i A') }}
                                                @if($slot->staff)
                                                    (with {{ $slot->staff->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('new_slot_id')" class="mt-2" />
                            @if(empty($availableSlots))
                                <p class="mt-2 text-sm text-yellow-600 dark:text-yellow-400">
                                    No available slots found for this service. Please try again later or contact support.
                                </p>
                            @endif
                        </div>

                        <div class="mb-6">
                            <x-input-label for="reason" :value="__('Reason for Rescheduling (Optional)')" />
                            <textarea id="reason" name="reason" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Let us know why you need to reschedule...">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Important Notice</h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>Your reschedule request will be sent for approval. You will be notified once it's reviewed.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('organization.bookings.show', [$organization, $booking]) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600">
                                Cancel
                            </a>
                            <x-primary-button :disabled="empty($availableSlots)">
                                {{ __('Submit Reschedule Request') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
