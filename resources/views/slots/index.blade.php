<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manage Slots - {{ $organization->name }}
            </h2>
            <button onclick="document.getElementById('generateModal').classList.remove('hidden')" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                Generate Slots from Shifts
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Compact Calendar View -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Availability Calendar</h3>
                        <div class="flex items-center space-x-4">
                            <!-- Legend -->
                            <div class="flex items-center space-x-3 text-xs">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded mr-1"></div>
                                    <span>Available</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded mr-1"></div>
                                    <span>Booked</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-gray-400 rounded mr-1"></div>
                                    <span>Blocked</span>
                                </div>
                            </div>
                            <!-- Week Navigation -->
                            <div class="flex items-center space-x-2">
                                <button onclick="previousWeek()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <span class="text-sm font-medium" id="weekDisplay">{{ date('M d, Y') }} - {{ date('M d, Y', strtotime('+6 days')) }}</span>
                                <button onclick="nextWeek()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Compact Calendar Grid -->
                    <div class="grid grid-cols-7 gap-4">
                        @php
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            $startDate = now()->startOfWeek()->subDay(); // Start from Sunday
                        @endphp
                        
                        @foreach($days as $index => $day)
                        <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:border-blue-400 transition-colors">
                            <!-- Day Header -->
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 px-4 py-3 border-b-2 border-gray-200 dark:border-gray-600">
                                <div class="font-bold text-base text-gray-800 dark:text-gray-200">
                                    {{ $day }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                                    {{ $startDate->copy()->addDays($index)->format('M d') }}
                                </div>
                            </div>
                            
                            @php
                                $currentDate = $startDate->copy()->addDays($index);
                                $daySlots = $slots->filter(function($slot) use ($currentDate) {
                                    return \Carbon\Carbon::parse($slot->date)->isSameDay($currentDate);
                                });
                                $groupedSlots = $daySlots->groupBy('status');
                            @endphp
                            
                            <!-- Slots Summary -->
                            <div class="p-4 space-y-3 min-h-[200px] max-h-[400px] overflow-y-auto">
                                @if($daySlots->count() > 0)
                                    <!-- Summary Badges -->
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @if($groupedSlots->has('available'))
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            {{ $groupedSlots->get('available')->count() }} Available
                                        </span>
                                        @endif
                                        @if($groupedSlots->has('booked'))
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $groupedSlots->get('booked')->count() }} Booked
                                        </span>
                                        @endif
                                        @if($groupedSlots->has('unavailable'))
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $groupedSlots->get('unavailable')->count() }} Blocked
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Expandable Slot List -->
                                    <details class="group">
                                        <summary class="cursor-pointer text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center">
                                            <svg class="w-4 h-4 mr-1 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            View {{ $daySlots->count() }} slots
                                        </summary>
                                        <div class="mt-3 space-y-2">
                                            @foreach($daySlots->take(20) as $slot)
                                            <div class="flex items-center justify-between text-sm p-2.5 rounded-md {{ $slot->status === 'available' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : ($slot->status === 'booked' ? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800' : 'bg-gray-50 dark:bg-gray-700/20 border border-gray-200 dark:border-gray-600') }}">
                                                <span class="font-semibold">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }}</span>
                                                @if($slot->status !== 'booked')
                                                <form method="POST" action="{{ route('organization.slots.toggle', [$organization, $slot]) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-lg hover:scale-125 transition-transform" title="{{ $slot->status === 'available' ? 'Block this slot' : 'Unblock this slot' }}">
                                                        {{ $slot->status === 'available' ? '🚫' : '✅' }}
                                                    </button>
                                                </form>
                                                @else
                                                <span class="text-lg">📅</span>
                                                @endif
                                            </div>
                                            @endforeach
                                            @if($daySlots->count() > 20)
                                            <div class="text-sm text-gray-500 text-center py-2 font-medium">
                                                +{{ $daySlots->count() - 20 }} more slots
                                            </div>
                                            @endif
                                        </div>
                                    </details>
                                @else
                                    <div class="text-sm text-gray-400 text-center py-8 font-medium">
                                        <div class="text-3xl mb-2">📭</div>
                                        No slots
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Generate Slots Modal -->
    <div id="generateModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Generate Slots from Shifts</h3>
                <form method="POST" action="{{ route('organization.slots.generate', $organization) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service</label>
                        <select name="service_id" required
                                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Select a service</option>
                            @foreach($organization->services()->where('is_active', true)->get() as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->duration }} min)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required
                               class="block w-full mb-2 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" min="{{ date('Y-m-d') }}" required
                               class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentWeekStart = new Date();
        currentWeekStart.setDate(currentWeekStart.getDate() - currentWeekStart.getDay() + 1);

        function updateWeekDisplay() {
            const weekEnd = new Date(currentWeekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);
            
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            document.getElementById('weekDisplay').textContent = 
                currentWeekStart.toLocaleDateString('en-US', options) + ' - ' + 
                weekEnd.toLocaleDateString('en-US', options);
        }

        function previousWeek() {
            currentWeekStart.setDate(currentWeekStart.getDate() - 7);
            updateWeekDisplay();
            window.location.reload();
        }

        function nextWeek() {
            currentWeekStart.setDate(currentWeekStart.getDate() + 7);
            updateWeekDisplay();
            window.location.reload();
        }

        updateWeekDisplay();
    </script>
</x-app-layout>
