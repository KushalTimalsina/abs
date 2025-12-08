<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $service->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $service->name }}</h3>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('organization.services.edit', [$organization, $service]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Edit Service
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Service Details -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Service Details</h4>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white mt-1">
                                        {{ $service->description ?? 'No description provided' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $service->duration }} minutes</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Price</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">NPR {{ number_format($service->price, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $service->created_at->format('M d, Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $service->updated_at->format('M d, Y') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Statistics -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistics</h4>
                            <div class="space-y-4">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Bookings</div>
                                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $service->bookings()->count() }}</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed Bookings</div>
                                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $service->bookings()->where('status', 'completed')->count() }}</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Bookings</div>
                                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $service->bookings()->whereIn('status', ['pending', 'confirmed'])->count() }}</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</div>
                                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        NPR {{ number_format($service->bookings()->where('status', 'completed')->sum('total_price'), 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Bookings -->
                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Bookings</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Price</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($service->bookings()->latest()->take(5)->get() as $booking)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $booking->customer_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $booking->slot->start_time->format('M d, Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $booking->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($booking->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : 
                                                       ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">NPR {{ number_format($booking->total_price, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No bookings yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('organization.services.index', $organization) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            ← Back to Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
