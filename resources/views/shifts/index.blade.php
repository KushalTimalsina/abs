<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Shifts - {{ $organization->name }}
            </h2>
            <button onclick="document.getElementById('createShiftModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Add Shifts
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($shifts->count() > 0)
                        <div class="space-y-6">
                            @foreach($shifts as $userId => $userShifts)
                                @php $user = $userShifts->first()->user; @endphp
                                <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h3 class="font-semibold text-lg text-gray-900 dark:text-white mb-4">{{ $user->name }}</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($userShifts as $shift)
                                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-3">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">
                                                        {{ ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$shift->day_of_week] }}
                                                    </p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}
                                                    </p>
                                                </div>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $shift->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $shift->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            <div class="flex gap-2 mt-3">
                                                <form action="{{ route('organization.shifts.destroy', [$organization, $shift]) }}" method="POST" onsubmit="return confirm('Delete this shift?');" class="flex-1">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full px-3 py-1 text-sm bg-red-100 hover:bg-red-200 dark:bg-red-900 dark:hover:bg-red-800 text-red-800 dark:text-red-200 rounded">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No shifts configured</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first shift.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Shifts Modal -->
    <div id="createShiftModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Create Multiple Shifts</h3>
                <form action="{{ route('organization.shifts.bulk-store', $organization) }}" method="POST" x-data="shiftForm()">
                    @csrf
                    
                    <!-- Team Member Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Team Member</label>
                        <select name="user_id" required class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="">Select team member...</option>
                            @foreach($teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Day of Week -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Day of Week</label>
                        <select name="day_of_week" required class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="">Select day...</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                            <option value="0">Sunday</option>
                        </select>
                    </div>

                    <!-- Time Ranges -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time Ranges</label>
                        <div class="space-y-2" id="timeRanges">
                            <template x-for="(range, index) in timeRanges" :key="index">
                                <div class="flex gap-2 items-center">
                                    <input type="time" :name="'shifts[' + index + '][start_time]'" required 
                                           class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                           x-model="range.start">
                                    <span class="text-gray-500">to</span>
                                    <input type="time" :name="'shifts[' + index + '][end_time]'" required 
                                           class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                           x-model="range.end">
                                    <button type="button" @click="removeTimeRange(index)" 
                                            x-show="timeRanges.length > 1"
                                            class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-800 rounded-md">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addTimeRange()" 
                                class="mt-2 px-4 py-2 bg-green-100 hover:bg-green-200 text-green-800 rounded-md text-sm font-medium">
                            + Add Another Time Range
                        </button>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('createShiftModal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Create Shifts</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function shiftForm() {
            return {
                timeRanges: [{ start: '', end: '' }],
                
                addTimeRange() {
                    this.timeRanges.push({ start: '', end: '' });
                },
                
                removeTimeRange(index) {
                    this.timeRanges.splice(index, 1);
                }
            }
        }
    </script>
</x-app-layout>
