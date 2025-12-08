<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $organization->name }} - Dashboard
            </h2>
            
            @if($user->organizations()->count() > 1)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                    Switch Organization
                    <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
                    <div class="py-1">
                        @foreach($user->organizations as $org)
                        <form method="POST" action="{{ route('organization.switch', $org) }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $org->id === $organization->id ? 'bg-gray-50 dark:bg-gray-900' : '' }}">
                                {{ $org->name }}
                                @if($org->id === $organization->id)
                                <span class="text-xs text-green-600">(Current)</span>
                                @endif
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Today's Bookings -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Bookings</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $data['today_bookings'] }}</p>
                </div>
            </div>
        </div>

        <!-- Upcoming Bookings -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $data['upcoming_bookings'] }}</p>
                </div>
            </div>
        </div>

        @if($userRole === 'admin')
        <!-- Revenue (Admin only) -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg dark:bg-purple-900">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">This Month</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">Rs {{ number_format($data['this_month_revenue'], 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Total Customers (Admin only) -->
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Customers</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $data['total_customers'] }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @if($userRole === 'admin')
        <!-- Recent Bookings -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Bookings</h3>
                    <a href="{{ route('bookings.index', $organization) }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">View All</a>
                </div>
            </div>
            <div class="p-4">
                @forelse($data['recent_bookings'] as $booking)
                <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $booking->customer_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $booking->service->name }} • {{ $booking->booking_date->format('M d, Y') }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">No bookings yet</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
            </div>
            <div class="p-4 space-y-3">
                <a href="{{ route('bookings.create', $organization) }}" class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">New Booking</span>
                </a>
                
                <a href="{{ route('organization.services.index', $organization) }}" class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">Manage Services</span>
                </a>
                
                <a href="{{ route('organization.team.index', $organization) }}" class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">Team Members</span>
                </a>
                
                <a href="{{ route('organization.widget.customize', $organization) }}" class="flex items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">Widget Settings</span>
                </a>
            </div>
        </div>
        @elseif($userRole === 'team_member')
        <!-- My Schedule -->
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Upcoming Bookings</h3>
            </div>
            <div class="p-4">
                @forelse($data['my_upcoming_bookings'] as $booking)
                <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $booking->customer_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $booking->service->name }} • 
                            {{ $booking->booking_date->format('M d, Y') }} at {{ $booking->start_time->format('h:i A') }}
                        </p>
                    </div>
                    <a href="{{ route('bookings.show', [$organization, $booking]) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        View
                    </a>
                </div>
                @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">No upcoming bookings</p>
                @endforelse
            </div>
        </div>
        @elseif($userRole === 'frontdesk')
        <!-- Today's Schedule -->
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Today's Schedule</h3>
            </div>
            <div class="p-4">
                @forelse($data['today_schedule'] as $booking)
                <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $booking->start_time->format('h:i A') }} - {{ $booking->customer_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $booking->service->name }} with {{ $booking->staff->name }}
                        </p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">No bookings today</p>
                @endforelse
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
