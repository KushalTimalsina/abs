<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subscription Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $organization = Auth::user()->organizations()->first();
                $subscription = $organization?->subscription;
            @endphp

            <!-- Current Subscription Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Current Subscription</h3>
                    
                    @if($subscription && $subscription->is_active)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Plan Details -->
                            <div>
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-lg p-6 text-white">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-2xl font-bold">{{ $subscription->plan->name }}</h4>
                                        <span class="px-3 py-1 bg-green-500 rounded-full text-sm font-semibold">Active</span>
                                    </div>
                                    <p class="text-blue-100 mb-4">{{ $subscription->plan->description }}</p>
                                    <div class="text-3xl font-bold mb-2">NPR {{ number_format($subscription->plan->price, 2) }}</div>
                                    <p class="text-blue-100 text-sm">per {{ $subscription->plan->duration_days }} days</p>
                                </div>
                            </div>

                            <!-- Subscription Info -->
                            <div class="space-y-4">
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Start Date</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $subscription->start_date->format('F d, Y') }}</p>
                                </div>
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">End Date</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $subscription->end_date->format('F d, Y') }}</p>
                                </div>
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Days Remaining</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $subscription->end_date->diffInDays(now()) }} days
                                        <span class="text-sm text-gray-500">({{ $subscription->end_date->diffForHumans() }})</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Features -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-4">Plan Features</h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">
                                        <strong>{{ $subscription->plan->max_team_members ?? '∞' }}</strong> Team Members
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">
                                        <strong>{{ $subscription->plan->max_services ?? '∞' }}</strong> Services
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-700 dark:text-gray-300">
                                        <strong>{{ $subscription->plan->slot_scheduling_days ?? 365 }}</strong> Days Scheduling
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-6 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <h4 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">No Active Subscription</h4>
                                    <p class="text-yellow-700 dark:text-yellow-300 mt-1">Please choose a subscription plan below to activate your account and access all features.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Available Plans -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        @if($subscription && $subscription->is_active)
                            Upgrade or Change Plan
                        @else
                            Choose Your Plan
                        @endif
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach(\App\Models\SubscriptionPlan::where('is_active', true)->orderBy('price')->get() as $plan)
                            <div class="relative border-2 {{ $subscription && $subscription->plan_id == $plan->id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700' }} rounded-lg p-6 hover:shadow-lg transition-shadow">
                                @if($subscription && $subscription->plan_id == $plan->id)
                                    <div class="absolute top-0 right-0 bg-blue-500 text-white text-xs px-3 py-1 rounded-bl-lg rounded-tr-lg font-semibold">
                                        Current Plan
                                    </div>
                                @endif
                                
                                <div class="mb-4">
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-2">{{ $plan->description }}</p>
                                </div>
                                
                                <div class="mb-6">
                                    <span class="text-4xl font-bold text-gray-900 dark:text-white">NPR {{ number_format($plan->price, 0) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">/{{ $plan->duration_days }} days</span>
                                </div>
                                
                                <ul class="space-y-3 mb-6">
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $plan->max_team_members ?? 'Unlimited' }} Team Members
                                    </li>
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $plan->max_services ?? 'Unlimited' }} Services
                                    </li>
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $plan->slot_scheduling_days ?? 365 }} Days Advance Scheduling
                                    </li>
                                    @if($plan->max_bookings_per_month)
                                    <li class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                        <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $plan->max_bookings_per_month }} Bookings/Month
                                    </li>
                                    @endif
                                </ul>
                                
                                @if($subscription && $subscription->plan_id == $plan->id)
                                    <button disabled class="w-full px-4 py-3 bg-gray-300 text-gray-500 rounded-lg font-semibold cursor-not-allowed">
                                        Current Plan
                                    </button>
                                @else
                                    <form action="{{ route('subscription.upgrade') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                        <button type="submit" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
                                            @if($subscription)
                                                @if($plan->price > $subscription->plan->price)
                                                    Upgrade to {{ $plan->name }}
                                                @else
                                                    Switch to {{ $plan->name }}
                                                @endif
                                            @else
                                                Subscribe Now
                                            @endif
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
