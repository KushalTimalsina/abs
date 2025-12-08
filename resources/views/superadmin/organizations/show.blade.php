<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $organization->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Organization Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 flex-1">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Organization Details</h3>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $organization->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $organization->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $organization->phone ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                                        <dd class="text-sm text-gray-900 dark:text-white">{{ $organization->address ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $organization->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $organization->is_active ? 'Active' : 'Suspended' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Details</h3>
                                @if($organization->subscription)
                                    <dl class="space-y-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white">{{ $organization->subscription->plan->name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white">{{ $organization->subscription->start_date->format('M d, Y') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white">{{ $organization->subscription->end_date->format('M d, Y') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                            <dd>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $organization->subscription->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $organization->subscription->is_active ? 'Active' : 'Expired' }}
                                                </span>
                                            </dd>
                                        </div>
                                    </dl>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No active subscription</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Services</div>
                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalServices'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Team Members</div>
                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalTeamMembers'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Bookings</div>
                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['totalBookings'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</div>
                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">NPR {{ number_format($stats['totalRevenue'], 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>
                    <div class="flex space-x-4">
                        @if($organization->is_active)
                            <form action="{{ route('superadmin.organizations.suspend', $organization) }}" method="POST" onsubmit="return confirm('Are you sure you want to suspend this organization?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                                    Suspend Organization
                                </button>
                            </form>
                        @else
                            <form action="{{ route('superadmin.organizations.activate', $organization) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    Activate Organization
                                </button>
                            </form>
                        @endif
                        
                        <form action="{{ route('superadmin.organizations.destroy', $organization) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this organization? This action cannot be undone!');">
                            @csrf
                            @method('DELETE')
                            <x-danger-button>
                                {{ __('Delete Organization') }}
                            </x-danger-button>
                        </form>

                        <a href="{{ route('superadmin.organizations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
