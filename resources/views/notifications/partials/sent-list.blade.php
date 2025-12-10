<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
    @forelse($sentNotifications as $sent)
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 last:border-0">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                    {{ $sent->title }}
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ $sent->message }}
                </p>
                <div class="flex items-center space-x-4 mt-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Sent {{ $sent->created_at->diffForHumans() }}
                    </p>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        • {{ $sent->recipients_count }} recipient(s)
                    </span>
                    <span class="px-2 py-1 text-xs rounded-full {{ $sent->type === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($sent->type === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : ($sent->type === 'error' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200')) }}">
                        {{ ucfirst($sent->type) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No sent notifications</p>
    </div>
    @endforelse
</div>

@if($sentNotifications && $sentNotifications->hasPages())
<div class="mt-4">
    {{ $sentNotifications->links() }}
</div>
@endif
