<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Team Member - {{ $organization->name }}
            </h2>
            <a href="{{ route('organization.team.index', $organization) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Back to Team
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('organization.team.update', [$organization, $user]) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name (Read-only) -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" :value="$user->name" readonly />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Name cannot be changed</p>
                        </div>

                        <!-- Email (Read-only) -->
                        <div class="mb-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="email" :value="$user->email" readonly />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Email cannot be changed</p>
                        </div>

                        <!-- Role -->
                        <div class="mb-4">
                            <x-input-label for="role" :value="__('Role')" />
                            <select id="role" name="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select Role</option>
                                <option value="admin" {{ old('role', $user->pivot->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="team_member" {{ old('role', $user->pivot->role) == 'team_member' ? 'selected' : '' }}>Team Member</option>
                                <option value="frontdesk" {{ old('role', $user->pivot->role) == 'frontdesk' ? 'selected' : '' }}>Front Desk</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <strong>Admin:</strong> Full access to all features<br>
                                <strong>Team Member:</strong> Can manage bookings and services<br>
                                <strong>Front Desk:</strong> Can only view and create bookings
                            </p>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-6" id="permissions-section">
                            <div class="flex items-center justify-between mb-3">
                                <x-input-label :value="__('Permissions')" />
                                <button type="button" onclick="toggleAllPermissions()" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    Toggle All
                                </button>
                            </div>
                            
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 space-y-6">
                                @foreach($permissionsConfig as $category => $categoryData)
                                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $categoryData['label'] }}
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 ml-7">
                                            @foreach($categoryData['permissions'] as $permission => $label)
                                                <label class="flex items-start">
                                                    <input type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permission }}" 
                                                           {{ in_array($permission, $currentPermissions) ? 'checked' : '' }}
                                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 mt-1">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <strong>Note:</strong> Admins have all permissions by default. These settings only apply to Team Members and Front Desk roles.
                            </p>
                        </div>

                        <script>
                            function toggleAllPermissions() {
                                const checkboxes = document.querySelectorAll('#permissions-section input[type="checkbox"]');
                                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                                checkboxes.forEach(cb => cb.checked = !allChecked);
                            }
                        </script>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('organization.team.index', $organization) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Team Member') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
