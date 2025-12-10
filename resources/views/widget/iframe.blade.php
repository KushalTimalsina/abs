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
    <div class="min-h-screen bg-gray-50 p-4" x-data="bookingWidget()">
        <!-- Header with Auth -->
        <div class="max-w-2xl mx-auto mb-4">
            <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @if($widgetSettings->show_logo && $organization->logo)
                    <img src="{{ Storage::url($organization->logo) }}" alt="{{ $organization->name }}" class="w-16 h-16 rounded-lg object-cover">
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $organization->name }}</h1>
                        @if($organization->description)
                        <p class="text-sm text-gray-600 mt-1">{{ $organization->description }}</p>
                        @endif
                    </div>
                </div>
                
                <!-- Auth Section -->
                @auth
                <!-- Logged In User -->
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                        <a href="{{ route('customer.bookings') }}" class="text-xs text-blue-600 hover:text-blue-800">My Bookings</a>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                            Logout
                        </button>
                    </form>
                </div>
                @else
                <div class="flex items-center space-x-2">
                    <a href="{{ route('widget.auth.google', $organization->slug) }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Login with Google
                    </a>
                </div>
                @endauth
            </div>
        </div>

        <div class="max-w-2xl mx-auto">
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
                    <button @click="slot.available ? selectSlot(slot) : null"
                            :disabled="!slot.available"
                            :class="{
                                'p-4 border-2 rounded-lg text-center transition-colors': true,
                                'border-gray-200 hover:border-blue-500 hover:bg-blue-50 cursor-pointer': slot.available,
                                'border-gray-300 bg-gray-100 opacity-50 cursor-not-allowed': !slot.available
                            }">
                        <div class="font-semibold" :class="slot.available ? 'text-gray-900' : 'text-gray-500'" x-text="slot.start_time"></div>
                        <div class="text-sm" :class="slot.available ? 'text-gray-600' : 'text-gray-400'" x-text="slot.staff"></div>
                        <div x-show="!slot.available" class="text-xs text-red-500 mt-1">Past</div>
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

        <!-- Payment Selection -->
        <div x-show="step === 'payment'" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select Payment Method</h2>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <strong>Amount to Pay:</strong> NPR <span x-text="selectedService.price"></span>
                </p>
            </div>

            <div class="space-y-3 mb-6">
                @forelse($paymentGateways as $gateway)
                    @if($gateway->gateway_name === 'cash')
                        <!-- Cash Payment -->
                        <button @click="selectPaymentMethod('cash')" 
                                class="w-full flex items-center justify-between p-4 border-2 border-gray-300 rounded-lg hover:border-blue-500 transition-colors">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="font-semibold text-gray-900">Pay Cash</div>
                                    <div class="text-sm text-gray-600">{{ $gateway->settings['instructions'] ?? 'Pay at the venue' }}</div>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @elseif($gateway->gateway_name === 'bank_transfer')
                        <!-- Bank Transfer -->
                        <button @click="selectPaymentMethod('bank_transfer')" 
                                class="w-full flex items-center justify-between p-4 border-2 border-gray-300 rounded-lg hover:border-blue-500 transition-colors">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="font-semibold text-gray-900">Bank Transfer</div>
                                    <div class="text-sm text-gray-600">{{ $gateway->settings['bank_name'] ?? 'Transfer to bank account' }}</div>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @else
                        <!-- Online Payment (eSewa, Khalti, Stripe) -->
                        <button @click="selectPaymentMethod('{{ $gateway->gateway_name }}')" 
                                class="w-full flex items-center justify-between p-4 border-2 border-gray-300 rounded-lg hover:border-blue-500 transition-colors">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="font-semibold text-gray-900">{{ ucfirst($gateway->gateway_name) }}</div>
                                    <div class="text-sm text-gray-600">Pay online securely</div>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @endif
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <p>No payment methods available.</p>
                        <p class="text-sm mt-2">Please contact the organization.</p>
                    </div>
                @endforelse
            </div>

            <button @click="step = 'payment'" class="w-full text-center text-gray-600 hover:text-gray-900">
                ← Back to Payment Options
            </button>
        </div>

        <!-- Bank Transfer Details -->
        <div x-show="step === 'bank_details'" class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Bank Transfer Details</h2>
            
            @php
                $bankGateway = $paymentGateways->firstWhere('gateway_name', 'bank_transfer');
            @endphp
            
            @if($bankGateway && $bankGateway->settings)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-3">Transfer to this account:</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-blue-700">Bank Name:</span>
                        <span class="font-semibold text-blue-900">{{ $bankGateway->settings['bank_name'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-blue-700">Account Holder:</span>
                        <span class="font-semibold text-blue-900">{{ $bankGateway->settings['account_holder'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-blue-700">Account Number:</span>
                        <span class="font-semibold text-blue-900">{{ $bankGateway->settings['account_number'] ?? 'N/A' }}</span>
                    </div>
                    @if(!empty($bankGateway->settings['branch']))
                    <div class="flex justify-between">
                        <span class="text-blue-700">Branch:</span>
                        <span class="font-semibold text-blue-900">{{ $bankGateway->settings['branch'] }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-blue-700">Amount:</span>
                        <span class="font-semibold text-blue-900">NPR <span x-text="selectedService.price"></span></span>
                    </div>
                </div>
                @if(!empty($bankGateway->settings['instructions']))
                <div class="mt-3 pt-3 border-t border-blue-200">
                    <p class="text-sm text-blue-800">{{ $bankGateway->settings['instructions'] }}</p>
                </div>
                @endif
            </div>
            @endif

            <form @submit.prevent="submitBankTransfer()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Transaction ID / Reference Number <span class="text-red-500">*</span></label>
                    <input type="text" x-model="bankTransferData.transaction_id" required
                           class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Enter transaction ID">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Payment Proof <span class="text-red-500">*</span></label>
                    <input type="file" @change="bankTransferData.proof_image = $event.target.files[0]" 
                           accept="image/*" required
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Upload screenshot or photo of your bank transfer receipt</p>
                </div>

                <div class="flex space-x-3">
                    <button type="button" @click="step = 'payment'" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Back
                    </button>
                    <button type="submit" :disabled="submitting"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <span x-show="!submitting">Submit Payment Proof</span>
                        <span x-show="submitting">Submitting...</span>
                    </button>
                </div>
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
                    customer_name: '{{ auth()->check() ? auth()->user()->name : "" }}',
                    customer_email: '{{ auth()->check() ? auth()->user()->email : "" }}',
                    customer_phone: '{{ auth()->check() ? auth()->user()->phone : "" }}',
                    notes: ''
                },
                errors: {},
                submitError: '',
                bookingResult: {},
                bookingId: null,
                selectedPaymentMethod: '',
                bankTransferData: {
                    transaction_id: '',
                    proof_image: null
                },

                // Auth
                isLoggedIn: false,
                user: null,
                authToken: localStorage.getItem('widget_auth_token') || null,
                showAuthModal: false,
                authMode: 'login',
                authForm: {
                    name: '',
                    email: '',
                    phone: '',
                    password: '',
                    password_confirmation: ''
                },
                authErrors: {},
                authLoading: false,

                async init() {
                    if (this.authToken) {
                        await this.fetchUser();
                    }
                },

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
                        const response = await fetch(`{{ request()->getSchemeAndHttpHost() }}/api/widget/{{ $organization->slug }}/services/${this.selectedService.id}/slots?date=${this.selectedDate}`);
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
                        const response = await fetch('{{ request()->getSchemeAndHttpHost() }}/api/widget/{{ $organization->slug }}/bookings', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                service_id: this.selectedService.id,
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
                            this.bookingId = data.booking.id;
                            this.step = 'payment';
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

                async selectPaymentMethod(gateway) {
                    this.selectedPaymentMethod = gateway;
                    
                    if (gateway === 'cash') {
                        // For cash payment, move to confirmation
                        this.step = 'confirmation';
                    } else if (gateway === 'bank_transfer') {
                        // For bank transfer, show bank details and upload form
                        this.step = 'bank_details';
                    } else {
                        // For online payment (esewa, khalti, stripe), call payment API
                        try {
                            const response = await fetch(`{{ request()->getSchemeAndHttpHost() }}/api/widget/{{ $organization->slug }}/bookings/${this.bookingId}/payment`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ gateway })
                            });

                            const data = await response.json();

                            if (data.success && data.payment_url) {
                                // Redirect to payment gateway
                                window.location.href = data.payment_url;
                            } else {
                                alert('Payment initiation failed. Please try again.');
                            }
                        } catch (error) {
                            console.error('Payment error:', error);
                            alert('Payment initiation failed. Please try again.');
                        }
                    }
                },

                async submitBankTransfer() {
                    if (!this.bankTransferData.transaction_id || !this.bankTransferData.proof_image) {
                        alert('Please provide transaction ID and upload payment proof');
                        return;
                    }

                    this.submitting = true;

                    try {
                        const formData = new FormData();
                        formData.append('booking_id', this.bookingId);
                        formData.append('transaction_id', this.bankTransferData.transaction_id);
                        formData.append('proof_image', this.bankTransferData.proof_image);
                        formData.append('payment_method', 'bank_transfer');

                        const response = await fetch(`{{ request()->getSchemeAndHttpHost() }}/api/widget/{{ $organization->slug }}/bookings/${this.bookingId}/bank-transfer`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.step = 'confirmation';
                        } else {
                            alert(data.message || 'Failed to submit payment proof. Please try again.');
                        }
                    } catch (error) {
                        console.error('Bank transfer submission error:', error);
                        alert('Failed to submit payment proof. Please try again.');
                    } finally {
                        this.submitting = false;
                    }
                },

                formatDate(dateStr) {
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                },

                // Auth Methods
                async fetchUser() {
                    try {
                        const response = await fetch('{{ request()->getSchemeAndHttpHost() }}/api/widget/{{ $organization->slug }}/auth/user', {
                            headers: {
                                'Authorization': `Bearer ${this.authToken}`,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.isLoggedIn = true;
                            this.user = data.user;
                            this.prefillForm();
                        } else {
                            this.logout();
                        }
                    } catch (error) {
                        console.error('Fetch user error:', error);
                        this.logout();
                    }
                },

                async login() {
                    this.authLoading = true;
                    this.authErrors = {};
                    try {
                        const response = await fetch('{{ request()->getSchemeAndHttpHost() }}/api/widget/{{ $organization->slug }}/auth/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                email: this.authForm.email,
                                password: this.authForm.password
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.authToken = data.token;
                            localStorage.setItem('widget_auth_token', data.token);
                            this.isLoggedIn = true;
                            this.user = data.user;
                            this.showAuthModal = false;
                            this.authForm = { name: '', email: '', phone: '', password: '', password_confirmation: '' };
                            this.prefillForm();
                        } else {
                            this.authErrors = { general: data.message || 'Login failed' };
                        }
                    } catch (error) {
                        console.error('Login error:', error);
                        this.authErrors = { general: 'Login failed. Please try again.' };
                    } finally {
                        this.authLoading = false;
                    }
                },

                async register() {
                    this.authLoading = true;
                    this.authErrors = {};
                    try {
                        const response = await fetch('{{ request()->getSchemeAndHttpHost() }}/api/widget/{{ $organization->slug }}/auth/register', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.authForm.name,
                                email: this.authForm.email,
                                phone: this.authForm.phone,
                                password: this.authForm.password,
                                password_confirmation: this.authForm.password_confirmation
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.authToken = data.token;
                            localStorage.setItem('widget_auth_token', data.token);
                            this.isLoggedIn = true;
                            this.user = data.user;
                            this.showAuthModal = false;
                            this.authForm = { name: '', email: '', phone: '', password: '', password_confirmation: '' };
                            this.prefillForm();
                        } else {
                            this.authErrors = data.errors || { general: data.message || 'Registration failed' };
                        }
                    } catch (error) {
                        console.error('Register error:', error);
                        this.authErrors = { general: 'Registration failed. Please try again.' };
                    } finally {
                        this.authLoading = false;
                    }
                },

                async logout() {
                    if (this.authToken) {
                        try {
                            await fetch('{{ request()->getSchemeAndHttpHost() }}/api/widget/{{ $organization->slug }}/auth/logout', {
                                method: 'POST',
                                headers: {
                                    'Authorization': `Bearer ${this.authToken}`,
                                    'Accept': 'application/json'
                                }
                            });
                        } catch (error) {
                            console.error('Logout error:', error);
                        }
                    }
                    localStorage.removeItem('widget_auth_token');
                    this.authToken = null;
                    this.isLoggedIn = false;
                    this.user = null;
                    this.formData.customer_name = '';
                    this.formData.customer_email = '';
                    this.formData.customer_phone = '';
                },

                prefillForm() {
                    if (this.user) {
                        this.formData.customer_name = this.user.name;
                        this.formData.customer_email = this.user.email;
                        this.formData.customer_phone = this.user.phone;
                    }
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
                    bookingResult = {};
                    this.bookingId = null;
                    // Prefill if logged in
                    if (this.isLoggedIn) {
                        this.prefillForm();
                    }
                }
            }
        }
    </script>
</body>
</html>
