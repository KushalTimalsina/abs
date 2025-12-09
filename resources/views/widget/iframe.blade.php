<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Book Appointment - {{ $organization->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --primary-color: {{ $widgetSettings->primary_color ?? '#3B82F6' }};
            --secondary-color: {{ $widgetSettings->secondary_color ?? '#1E40AF' }};
        }
        body {
            font-family: {{ $widgetSettings->font_family ?? 'Inter, sans-serif' }};
        }
        .btn-primary {
            background-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        {{ $widgetSettings->custom_css ?? '' }}
    </style>
</head>
<body class="bg-gray-50">
    <div class="widget-container max-w-2xl mx-auto p-6" x-data="bookingWidget()">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center space-x-4">
                @if($widgetSettings->show_logo && $organization->logo)
                <img src="{{ Storage::url($organization->logo) }}" alt="{{ $organization->name }}" class="w-16 h-16 rounded-lg object-cover">
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $organization->name }}</h1>
                    @if($organization->description)
                    <p class="text-gray-600 mt-1">{{ $organization->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Service Selection -->
        <div x-show="step === 'services'" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select a Service</h2>
            
            @if($services->count() > 0)
            <div class="space-y-3">
                @foreach($services as $service)
                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors cursor-pointer" 
                     @click="selectService({{ $service->id }}, '{{ $service->name }}', {{ $service->duration }}, {{ $service->price }})">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $service->name }}</h3>
                            @if($service->description)
                            <p class="text-sm text-gray-600 mt-1">{{ $service->description }}</p>
                            @endif
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                <span>⏱️ {{ $service->duration }} min</span>
                                <span>💰 NPR {{ number_format($service->price, 0) }}</span>
                            </div>
                        </div>
                        <button class="btn-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:shadow-lg transition-all">
                            Book Now
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p>No services available at the moment.</p>
            </div>
            @endif
        </div>

        <!-- Slot Selection -->
        <div x-show="step === 'slots'" class="bg-white rounded-lg shadow-sm p-6">
            <button @click="step = 'services'" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                ← Back to Services
            </button>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select Date & Time</h2>
            <p class="text-sm text-gray-600 mb-4" x-text="'Service: ' + selectedService.name"></p>

            <!-- Date Picker -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                <input type="date" 
                       x-model="selectedDate" 
                       @change="loadSlots()"
                       :min="minDate"
                       class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Loading available slots...</p>
            </div>

            <!-- Slots Grid -->
            <div x-show="!loading && slots.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <template x-for="slot in slots" :key="slot.id">
                    <button @click="selectSlot(slot)"
                            class="p-4 border-2 border-gray-200 rounded-lg text-center hover:border-blue-500 hover:bg-blue-50 transition-colors">
                        <div class="font-semibold text-gray-900" x-text="slot.start_time"></div>
                        <div class="text-sm text-gray-600" x-text="slot.staff"></div>
                    </button>
                </template>
            </div>

            <!-- No Slots Message -->
            <div x-show="!loading && slots.length === 0 && selectedDate" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-gray-600">No available slots for this date.</p>
                <p class="text-sm text-gray-500">Please try another date.</p>
            </div>
        </div>

        <!-- Booking Form -->
        <div x-show="step === 'form'" class="bg-white rounded-lg shadow-sm p-6">
            <button @click="step = 'slots'" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                ← Back to Slots
            </button>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Complete Your Booking</h2>

            <!-- Booking Summary -->
            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-2">Booking Summary</h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p><strong>Service:</strong> <span x-text="selectedService.name"></span></p>
                    <p><strong>Date:</strong> <span x-text="formatDate(selectedDate)"></span></p>
                    <p><strong>Time:</strong> <span x-text="selectedSlot.start_time + ' - ' + selectedSlot.end_time"></span></p>
                    <p><strong>Duration:</strong> <span x-text="selectedService.duration"></span> minutes</p>
                    <p><strong>Price:</strong> NPR <span x-text="selectedService.price"></span></p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitBooking()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.customer_name" required
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p x-show="errors.customer_name" x-text="errors.customer_name" class="mt-1 text-sm text-red-600"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" x-model="formData.customer_email" required
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p x-show="errors.customer_email" x-text="errors.customer_email" class="mt-1 text-sm text-red-600"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone Number <span class="text-red-500">*</span></label>
                    <input type="tel" x-model="formData.customer_phone" required
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p x-show="errors.customer_phone" x-text="errors.customer_phone" class="mt-1 text-sm text-red-600"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Additional Notes (Optional)</label>
                    <textarea x-model="formData.notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div x-show="submitError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <p x-text="submitError"></p>
                </div>

                <button type="submit" :disabled="submitting"
                        class="w-full btn-primary text-white px-4 py-2 rounded-lg font-semibold hover:shadow-lg transition-all disabled:opacity-50">
                    <span x-show="!submitting">Confirm Booking</span>
                    <span x-show="submitting">Processing...</span>
                </button>
            </form>
        </div>

        <!-- Confirmation -->
        <div x-show="step === 'confirmation'" class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Booking Confirmed!</h2>
                <p class="text-gray-600 mb-6">Your booking has been successfully created.</p>

                <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                    <p class="text-sm text-gray-600 mb-2"><strong>Booking Number:</strong> <span x-text="bookingResult.booking_number"></span></p>
                    <p class="text-sm text-gray-600 mb-2"><strong>Service:</strong> <span x-text="bookingResult.service"></span></p>
                    <p class="text-sm text-gray-600 mb-2"><strong>Date:</strong> <span x-text="bookingResult.date"></span></p>
                    <p class="text-sm text-gray-600"><strong>Time:</strong> <span x-text="bookingResult.time"></span></p>
                </div>

                <p class="text-sm text-gray-500 mb-6">A confirmation email has been sent to your email address.</p>

                <button @click="resetWidget()" class="btn-primary text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition-all">
                    Book Another Appointment
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-gray-500">
            <p>Powered by Appointment Booking System</p>
        </div>
    </div>

    <script>
        function bookingWidget() {
            return {
                step: 'services',
                loading: false,
                submitting: false,
                minDate: new Date().toISOString().split('T')[0],
                selectedDate: '',
                selectedService: {},
                selectedSlot: {},
                slots: [],
                formData: {
                    customer_name: '',
                    customer_email: '',
                    customer_phone: '',
                    notes: ''
                },
                errors: {},
                submitError: '',
                bookingResult: {},

                selectService(id, name, duration, price) {
                    this.selectedService = { id, name, duration, price };
                    this.selectedDate = this.minDate;
                    this.step = 'slots';
                    this.loadSlots();
                },

                async loadSlots() {
                    if (!this.selectedDate) return;
                    
                    this.loading = true;
                    this.slots = [];
                    
                    try {
                        const response = await fetch(`/api/widget/{{ $organization->id }}/services/${this.selectedService.id}/slots?date=${this.selectedDate}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.slots = data.slots;
                        }
                    } catch (error) {
                        console.error('Error loading slots:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                selectSlot(slot) {
                    this.selectedSlot = slot;
                    this.step = 'form';
                },

                async submitBooking() {
                    this.submitting = true;
                    this.errors = {};
                    this.submitError = '';

                    try {
                        const response = await fetch('/api/widget/{{ $organization->id }}/bookings', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                slot_id: this.selectedSlot.id,
                                customer_name: this.formData.customer_name,
                                customer_email: this.formData.customer_email,
                                customer_phone: this.formData.customer_phone,
                                notes: this.formData.notes
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.bookingResult = data.booking;
                            this.step = 'confirmation';
                        } else {
                            if (data.errors) {
                                this.errors = data.errors;
                            } else {
                                this.submitError = data.message || 'Failed to create booking. Please try again.';
                            }
                        }
                    } catch (error) {
                        console.error('Error submitting booking:', error);
                        this.submitError = 'An error occurred. Please try again.';
                    } finally {
                        this.submitting = false;
                    }
                },

                formatDate(dateStr) {
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                },

                resetWidget() {
                    this.step = 'services';
                    this.selectedService = {};
                    this.selectedSlot = {};
                    this.selectedDate = '';
                    this.slots = [];
                    this.formData = {
                        customer_name: '',
                        customer_email: '',
                        customer_phone: '',
                        notes: ''
                    };
                    this.errors = {};
                    this.submitError = '';
                    this.bookingResult = {};
                }
            }
        }
    </script>
</body>
</html>
