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

            <!-- Organization Header -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $organization->name }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $organization->email }}</p>
                            @if($organization->phone)
                                <p class="text-gray-600 dark:text-gray-400">{{ $organization->phone }}</p>
                            @endif
                        </div>
                        <a href="{{ route('organization.edit', $organization) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Edit Organization
                        </a>
                    </div>
                </div>
            </div>

            <!-- Subscription Info -->
            @if($subscription)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $subscription->plan->name }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $subscription->start_date->format('M d, Y') }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ $subscription->end_date->format('M d, Y') }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</div>
                                <div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $subscription->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $subscription->is_active ? 'Active' : 'Expired' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Team Members -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Team Members ({{ $teamMembers->count() }})</h3>
                        <a href="{{ route('organization.team.index', $organization) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Manage Team</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($teamMembers->take(6) as $member)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $member->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                                <div class="mt-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($member->role) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 col-span-3">No team members yet</p>
                        @endforelse
                    </div>
                    @if($teamMembers->count() > 6)
                        <div class="mt-4 text-center">
                            <a href="{{ route('organization.team.index', $organization) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                View all {{ $teamMembers->count() }} team members →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Services -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Services ({{ $services->count() }})</h3>
                        <a href="{{ route('organization.services.index', $organization) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Manage Services</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($services->take(6) as $service)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $service->name }}</div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $service->duration }} min</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white mt-2">NPR {{ number_format($service->price, 2) }}</div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 col-span-3">No services yet</p>
                        @endforelse
                    </div>
                    @if($services->count() > 6)
                        <div class="mt-4 text-center">
                            <a href="{{ route('organization.services.index', $organization) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                View all {{ $services->count() }} services →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
