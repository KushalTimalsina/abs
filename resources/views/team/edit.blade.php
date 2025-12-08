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

                        <!-- Permissions (Optional - for future use) -->
                        <div class="mb-6">
                            <x-input-label :value="__('Permissions (Optional)')" />
                            <div class="mt-2 space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="manage_services" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Manage Services</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="manage_bookings" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Manage Bookings</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="view_reports" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">View Reports</span>
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Additional permissions for this team member</p>
                        </div>

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
