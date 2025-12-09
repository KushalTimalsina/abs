<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Subscription Plan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('superadmin.plans.update', $plan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Plan Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $plan->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $plan->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="price" :value="__('Price (NPR)')" />
                                <x-text-input id="price" class="block mt-1 w-full" type="number" step="0.01" name="price" :value="old('price', $plan->price)" required />
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="duration_days" :value="__('Duration (Days)')" />
                                <x-text-input id="duration_days" class="block mt-1 w-full" type="number" name="duration_days" :value="old('duration_days', $plan->duration_days)" required />
                                <x-input-error :messages="$errors->get('duration_days')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="max_services" :value="__('Max Services (leave empty for unlimited)')" />
                                <x-text-input id="max_services" class="block mt-1 w-full" type="number" name="max_services" :value="old('max_services', $plan->max_services)" />
                                <x-input-error :messages="$errors->get('max_services')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="max_team_members" :value="__('Max Team Members (leave empty for unlimited)')" />
                                <x-text-input id="max_team_members" class="block mt-1 w-full" type="number" name="max_team_members" :value="old('max_team_members', $plan->max_team_members)" />
                                <x-input-error :messages="$errors->get('max_team_members')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="max_bookings_per_month" :value="__('Max Bookings/Month (leave empty for unlimited)')" />
                                <x-text-input id="max_bookings_per_month" class="block mt-1 w-full" type="number" name="max_bookings_per_month" :value="old('max_bookings_per_month', $plan->max_bookings_per_month)" />
                                <x-input-error :messages="$errors->get('max_bookings_per_month')" class="mt-2" />
                            </div>

                            <div class="flex items-center mt-6">
                                <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                <label for="is_active" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Active</label>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Features</label>
                            <div class="space-y-2">
                                @php
                                    $features = old('features', $plan->features ?? []);
                                @endphp
                                <div class="flex items-center">
                                    <input id="custom_branding" type="checkbox" name="features[]" value="custom_branding" {{ in_array('custom_branding', $features) ? 'checked' : '' }} class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="custom_branding" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Custom Branding</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="analytics" type="checkbox" name="features[]" value="analytics" {{ in_array('analytics', $features) ? 'checked' : '' }} class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="analytics" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Analytics Dashboard</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="priority_support" type="checkbox" name="features[]" value="priority_support" {{ in_array('priority_support', $features) ? 'checked' : '' }} class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="priority_support" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Priority Support</label>
                                </div>
                            </div>
                        </div>

                        <!-- Online Payment Enabled -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="online_payment_enabled" value="1" {{ old('online_payment_enabled', $plan->online_payment_enabled) ? 'checked' : '' }} class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Online Payments (eSewa, Khalti, Stripe)</span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Allow organizations with this plan to configure and accept online payments</p>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('superadmin.plans.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Plan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
