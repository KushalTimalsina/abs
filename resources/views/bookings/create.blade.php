<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Create New Booking - {{ $organization->name }}
            </h2>
            <a href="{{ route('organization.bookings.index', $organization) }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400">
                ← Back to Bookings
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('organization.bookings.store', $organization) }}" x-data="bookingForm()">
                        @csrf

                        <!-- Customer Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Customer Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="customer_name" value="Customer Name" />
                                    <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" :value="old('customer_name')" required />
                                    <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_email" value="Email" />
                                    <x-text-input id="customer_email" name="customer_email" type="email" class="mt-1 block w-full" :value="old('customer_email')" required />
                                    <x-input-error :messages="$errors->get('customer_email')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_phone" value="Phone Number" />
                                    <x-text-input id="customer_phone" name="customer_phone" type="tel" class="mt-1 block w-full" :value="old('customer_phone')" required />
                                    <x-input-error :messages="$errors->get('customer_phone')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Service Selection -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Select Service</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @forelse($services as $service)
                                <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="selectedService === {{ $service->id }} ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400'">
                                    <input type="radio" name="service_id" value="{{ $service->id }}" 
                                           x-model="selectedService" 
                                           @change="updateDuration({{ $service->duration }})"
                                           class="sr-only" required>
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $service->name }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $service->duration }} minutes • NPR {{ number_format($service->price / 100, 2) }}
                                        </div>
                                        @if($service->description)
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $service->description }}</div>
                                        @endif
                                    </div>
                                    <div class="ml-3" x-show="selectedService === {{ $service->id }}">
                                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </label>
                                @empty
                                <div class="col-span-2 text-center py-8 text-gray-500">
                                    No services available. Please create services first.
                                </div>
                                @endforelse
                            </div>
                            <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
                        </div>

                        <!-- Date and Time -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Select Date & Time</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="booking_date" value="Date" />
                                    <x-text-input id="booking_date" name="booking_date" type="date" 
                                                  class="mt-1 block w-full" 
                                                  :value="old('booking_date', date('Y-m-d'))" 
                                                  :min="date('Y-m-d')" 
                                                  required />
                                    <x-input-error :messages="$errors->get('booking_date')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="start_time" value="Start Time" />
                                    <x-text-input id="start_time" name="start_time" type="time" 
                                                  class="mt-1 block w-full" 
                                                  :value="old('start_time')" 
                                                  required />
                                    <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <x-input-label for="notes" value="Notes (Optional)" />
                            <textarea id="notes" name="notes" rows="3" 
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                      placeholder="Any special requirements or notes...">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <!-- Summary -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg" x-show="selectedService">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Booking Summary</h4>
                            <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <div>Duration: <span x-text="duration"></span> minutes</div>
                                <div>Estimated End Time: <span x-text="calculateEndTime()"></span></div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('organization.bookings.index', $organization) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600">
                                Cancel
                            </a>
                            <x-primary-button>
                                Create Booking
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bookingForm() {
            return {
                selectedService: {{ old('service_id', 'null') }},
                duration: 0,
                
                updateDuration(minutes) {
                    this.duration = minutes;
                },
                
                calculateEndTime() {
                    const startTime = document.getElementById('start_time').value;
                    if (!startTime || !this.duration) return '--:--';
                    
                    const [hours, minutes] = startTime.split(':').map(Number);
                    const totalMinutes = hours * 60 + minutes + this.duration;
                    const endHours = Math.floor(totalMinutes / 60) % 24;
                    const endMinutes = totalMinutes % 60;
                    
                    return `${String(endHours).padStart(2, '0')}:${String(endMinutes).padStart(2, '0')}`;
                }
            }
        }
    </script>
</x-app-layout>
