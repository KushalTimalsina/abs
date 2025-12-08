<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Notifications
            </h2>
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    Mark all as read
                </button>
            </form>
            @endif
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        @if($unreadCount > 0)
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <p class="text-sm text-blue-800 dark:text-blue-200">
                You have {{ $unreadCount }} unread notification{{ $unreadCount > 1 ? 's' : '' }}
            </p>
        </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            @forelse($notifications as $notification)
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 last:border-0 {{ $notification->read_at ? 'bg-white dark:bg-gray-800' : 'bg-blue-50 dark:bg-blue-900/10' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-1">
                            @if(!$notification->read_at)
                            <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                            @endif
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $notification->data['message'] ?? 'Notification' }}
                            </p>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2 ml-4">
                        @if(!$notification->read_at)
                        <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                Mark as read
                            </button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No notifications</p>
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
