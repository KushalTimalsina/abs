<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
    @forelse($notifications as $notification)
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 last:border-0 {{ $notification->read_at ? 'bg-white dark:bg-gray-800' : 'bg-blue-50 dark:bg-blue-900/10' }}">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-1">
                    @if(!$notification->read_at)
                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                    @endif
                    
                    @php
                        $type = $notification->data['type'] ?? 'info';
                        $typeColors = [
                            'info' => 'text-blue-600 dark:text-blue-400',
                            'success' => 'text-green-600 dark:text-green-400',
                            'warning' => 'text-yellow-600 dark:text-yellow-400',
                            'error' => 'text-red-600 dark:text-red-400',
                        ];
                        $typeColor = $typeColors[$type] ?? $typeColors['info'];
                    @endphp
                    
                    <svg class="w-5 h-5 {{ $typeColor }}" fill="currentColor" viewBox="0 0 20 20">
                        @if($type === 'success')
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        @elseif($type === 'warning')
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        @elseif($type === 'error')
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        @else
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        @endif
                    </svg>
                    
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $notification->data['title'] ?? 'Notification' }}
                    </p>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300 ml-7">
                    {{ $notification->data['message'] ?? 'No message' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 ml-7 mt-1">
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
