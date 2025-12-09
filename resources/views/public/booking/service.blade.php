<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Book {{ $service->name }} - {{ $organization->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('public.booking.index', $organization->slug) }}" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
                    ← Back to Services
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $service->name }}</h1>
            </div>
        </header>

        <main class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Service Details -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Service Details</h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $service->description }}</p>
                            <div class="space-y-3">
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Duration: {{ $service->duration }} minutes</span>
                                </div>
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-2xl font-bold text-blue-600">NPR {{ number_format($service->price, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slot Selection -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Select Date & Time</h2>
                            
                            <div x-data="slotSelector()" x-init="init()">
                                <!-- Date Picker -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Date</label>
                                    <input type="date" 
                                           x-model="selectedDate" 
                                           @change="loadSlots()"
                                           :min="minDate"
                                           class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <!-- Loading State -->
                                <div x-show="loading" class="text-center py-8">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                    <p class="mt-2 text-gray-600 dark:text-gray-400">Loading available slots...</p>
                                </div>

                                <!-- Slots Grid -->
                                <div x-show="!loading && slots.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <template x-for="slot in slots" :key="slot.id">
                                        <a :href="`{{ route('public.booking.form', [$organization->slug, $service, '']) }}/${slot.id}`"
                                           class="block p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg text-center hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                            <div class="font-semibold text-gray-900 dark:text-white" x-text="slot.start_time"></div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400" x-text="slot.staff_name"></div>
                                        </a>
                                    </template>
                                </div>

                                <!-- No Slots Message -->
                                <div x-show="!loading && slots.length === 0 && selectedDate" class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="mt-2 text-gray-600 dark:text-gray-400">No available slots for this date.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500">Please try another date.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function slotSelector() {
            return {
                selectedDate: '',
                slots: [],
                loading: false,
                minDate: new Date().toISOString().split('T')[0],
                
                init() {
                    this.selectedDate = this.minDate;
                    this.loadSlots();
                },
                
                async loadSlots() {
                    if (!this.selectedDate) return;
                    
                    this.loading = true;
                    try {
                        const response = await fetch(`{{ route('public.booking.slots', [$organization->slug, $service]) }}?date=${this.selectedDate}`);
                        this.slots = await response.json();
                    } catch (error) {
                        console.error('Error loading slots:', error);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
