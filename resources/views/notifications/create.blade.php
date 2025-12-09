<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Send Notification
            </h2>
            <a href="{{ route('notifications.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Back to Notifications
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('notifications.store') }}" x-data="{ recipientType: 'all' }">
                        @csrf

                        <!-- Title -->
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Message -->
                        <div class="mb-4">
                            <x-input-label for="message" :value="__('Message')" />
                            <textarea id="message" name="message" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>

                        <!-- Type -->
                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Type')" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Info</option>
                                <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="error" {{ old('type') == 'error' ? 'selected' : '' }}>Error</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Recipient Type -->
                        <div class="mb-4">
                            <x-input-label :value="__('Send To')" />
                            <div class="mt-2 space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="recipient_type" value="all" x-model="recipientType" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">All Team Members ({{ $teamMembers->count() }} members)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="recipient_type" value="specific" x-model="recipientType" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Specific Members</span>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('recipient_type')" class="mt-2" />
                        </div>

                        <!-- Specific Recipients -->
                        <div class="mb-6" x-show="recipientType === 'specific'" x-cloak>
                            <x-input-label :value="__('Select Members')" />
                            <div class="mt-2 max-h-60 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-md p-3 space-y-2">
                                @foreach($teamMembers as $member)
                                <label class="flex items-center">
                                    <input type="checkbox" name="recipients[]" value="{{ $member->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $member->name }} ({{ $member->email }})
                                        <span class="text-xs text-gray-500">- {{ ucfirst($member->pivot->role) }}</span>
                                    </span>
                                </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('recipients')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('notifications.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Send Notification') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
