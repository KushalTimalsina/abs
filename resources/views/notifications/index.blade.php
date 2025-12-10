<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Notifications
            </h2>
            <div class="flex items-center space-x-4">
                @if(auth()->guard('superadmin')->check() || isAdmin())
                @php
                    $createUrl = auth()->guard('superadmin')->check() ? url('/superadmin/notifications/create') : route('notifications.create');
                @endphp
                <a href="{{ $createUrl }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Send Notification
                </a>
                @endif
                @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        Mark all as read
                    </button>
                </form>
                @endif
            </div>
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

        <!-- Just show received notifications -->
        @include('notifications.partials.received-list', ['notifications' => $notifications])
    </div>
</x-app-layout>
