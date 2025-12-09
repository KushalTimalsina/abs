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
            
            <!-- Calendar View -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Availability Calendar</h3>
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

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-2">
                        @php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $startDate = now()->startOfWeek();
                        @endphp
                        
                        @foreach($days as $index => $day)
                        <div class="text-center">
                            <div class="font-semibold text-sm text-gray-700 dark:text-gray-300 mb-2">
                                {{ $day }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                {{ $startDate->copy()->addDays($index)->format('M d') }}
                            </div>
                            
                            @php
                                $currentDate = $startDate->copy()->addDays($index);
                                $daySlots = $slots->filter(function($slot) use ($currentDate) {
                                    return \Carbon\Carbon::parse($slot->date)->isSameDay($currentDate);
                                });
                            @endphp
                            
                            <div class="space-y-1">
                                @forelse($daySlots as $slot)
                                <div class="text-xs p-2 rounded {{ $slot->status === 'available' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($slot->status === 'booked' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : ($slot->status === 'rescheduled' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400')) }}">
                                    <div class="font-medium">{{ $slot->start_time->format('h:i A') }}</div>
                                    @if($slot->status === 'booked')
                                    <div class="text-xs">Booked</div>
                                    @else
                                    <form method="POST" action="{{ route('organization.slots.toggle', [$organization, $slot]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs underline hover:no-underline">
                                            {{ $slot->status === 'available' ? 'Block' : 'Unblock' }}
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                @empty
                                <div class="text-xs text-gray-400 p-2">No slots</div>
                                @endforelse
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Slots List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">All Slots</h3>
                    
                    @if($slots->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Staff</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($slots->take(50) as $slot)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $slot->date->format('M d, Y') ?? \Carbon\Carbon::parse($slot->date)->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $slot->start_time->format('h:i A') }} - {{ $slot->end_time->format('h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $slot->staff->name ?? 'Unassigned' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($slot->status === 'booked')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Booked
                                        </span>
                                        @elseif($slot->status === 'available')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Available
                                        </span>
                                        @elseif($slot->status === 'rescheduled')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Rescheduled
                                        </span>
                                        @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            Unavailable
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($slot->status !== 'booked')
                                        <form method="POST" action="{{ route('organization.slots.update-status', [$organization, $slot]) }}" class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md">
                                                <option value="available" {{ $slot->status === 'available' ? 'selected' : '' }}>Available</option>
                                                <option value="unavailable" {{ $slot->status === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                                                <option value="rescheduled" {{ $slot->status === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                            </select>
                                        </form>
                                        <form method="POST" action="{{ route('organization.slots.destroy', [$organization, $slot]) }}" class="inline ml-2" onsubmit="return confirm('Delete this slot?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                        @else
                                        <a href="{{ route('organization.bookings.show', [$organization, $slot->booking_id]) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                            View Booking
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No slots available</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by generating slots from your shifts.</p>
                    </div>
                    @endif
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
    </script>
</x-app-layout>
